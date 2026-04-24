<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Database;
use App\Lib\Session;
use App\Models\Book;

/**
 * Contrôleur de la liseuse PDF
 */
class ReaderController extends BaseController
{
    /**
     * Afficher la liseuse
     */
    public function read(string $slug): void
    {
        Auth::requireLogin();

        $book = Book::findBySlug($slug);
        if (!$book || $book->statut !== 'publie') {
            redirect('/catalogue');
        }

        $user = Auth::user();
        $db = Database::getInstance();

        // Déterminer le mode d'accès
        $ub = $db->fetch("SELECT id FROM user_books WHERE user_id = ? AND book_id = ?", [$user->id, $book->id]);
        $sub = $db->fetch("SELECT id FROM subscriptions WHERE user_id = ? AND statut = 'actif' AND date_fin > NOW()", [$user->id]);
        $hasFullAccess = (bool) $ub || (bool) $sub;

        $mode = $hasFullAccess ? 'full' : 'extrait';

        // Vérifier que le fichier existe
        $filePath = $mode === 'full' ? $book->fichier_complet_path : $book->fichier_extrait_path;
        if (!$filePath) {
            $filePath = $book->fichier_extrait_path ?? $book->fichier_complet_path;
            $mode = $book->fichier_extrait_path ? 'extrait' : 'full';
        }

        // Créer ou réutiliser une session de lecture
        $sessionToken = bin2hex(random_bytes(32));
        $db->insert('reading_sessions', [
            'user_id'       => $user->id,
            'book_id'       => $book->id,
            'session_token' => $sessionToken,
            'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent'    => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'page_debut'    => 1,
            'statut'        => 'active',
        ]);

        // Récupérer la progression existante
        $progress = $db->fetch(
            "SELECT derniere_page_lue FROM reading_progress WHERE user_id = ? AND book_id = ?",
            [$user->id, $book->id]
        );
        $lastPage = $progress ? (int) $progress->derniere_page_lue : 1;

        // Rendre la vue plein écran (sans layout)
        $viewFile = BASE_PATH . '/app/views/reader/read.php';
        extract([
            'book'         => $book,
            'mode'         => $mode,
            'sessionToken' => $sessionToken,
            'lastPage'     => $lastPage,
            'maxPages'     => $mode === 'extrait' ? FREE_PREVIEW_PAGES : (int) $book->nombre_pages,
            'hasFullAccess'=> $hasFullAccess,
        ]);
        require $viewFile;
    }

    /**
     * Servir le PDF en streaming sécurisé
     */
    public function streamPDF(string $sessionToken, string $fileType): void
    {
        $db = Database::getInstance();

        $session = $db->fetch(
            "SELECT rs.*, b.fichier_complet_path, b.fichier_extrait_path
             FROM reading_sessions rs
             JOIN books b ON rs.book_id = b.id
             WHERE rs.session_token = ? AND rs.statut = 'active'",
            [$sessionToken]
        );

        if (!$session) {
            http_response_code(403);
            echo 'Session invalide';
            return;
        }

        $filePath = $fileType === 'full' ? $session->fichier_complet_path : $session->fichier_extrait_path;
        if (!$filePath) {
            $filePath = $session->fichier_extrait_path ?? $session->fichier_complet_path;
        }

        $absolutePath = BASE_PATH . '/' . $filePath;

        if (!file_exists($absolutePath)) {
            http_response_code(404);
            echo 'Fichier introuvable';
            return;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline');
        header('Content-Length: ' . filesize($absolutePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');

        readfile($absolutePath);
        exit;
    }

    /**
     * Sauvegarder la progression de lecture (AJAX)
     */
    public function saveProgress(): void
    {
        if (!Auth::check()) {
            $this->json(['error' => 'not_logged_in'], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $sessionToken = $input['session_token'] ?? '';
        $page = max(1, (int) ($input['page'] ?? 1));
        $temps = max(0, (int) ($input['temps_secondes'] ?? 0));

        $db = Database::getInstance();
        $userId = Auth::id();

        // Mettre à jour la session de lecture
        $session = $db->fetch(
            "SELECT id, book_id FROM reading_sessions WHERE session_token = ? AND user_id = ?",
            [$sessionToken, $userId]
        );

        if (!$session) {
            $this->json(['error' => 'session_invalid'], 400);
        }

        $db->update('reading_sessions', [
            'page_fin'              => $page,
            'pages_lues_session'    => $page,
            'temps_lecture_secondes'=> $temps,
        ], 'id = ?', [$session->id]);

        // Mettre à jour reading_progress (upsert)
        $book = Book::find($session->book_id);
        $totalPages = $book ? (int) $book->nombre_pages : 100;
        $pourcentage = $totalPages > 0 ? round(($page / $totalPages) * 100, 2) : 0;

        $existing = $db->fetch(
            "SELECT user_id FROM reading_progress WHERE user_id = ? AND book_id = ?",
            [$userId, $session->book_id]
        );

        if ($existing) {
            $db->update('reading_progress', [
                'derniere_page_lue'  => $page,
                'total_pages_lues'   => $page,
                'total_temps_lecture'=> $temps,
                'pourcentage_complete'=> min(100, $pourcentage),
                'derniere_lecture_at' => date('Y-m-d H:i:s'),
                'livre_termine'      => $pourcentage >= 95 ? 1 : 0,
            ], 'user_id = ? AND book_id = ?', [$userId, $session->book_id]);
        } else {
            $db->insert('reading_progress', [
                'user_id'             => $userId,
                'book_id'             => $session->book_id,
                'derniere_page_lue'   => $page,
                'total_pages_lues'    => $page,
                'total_temps_lecture' => $temps,
                'pourcentage_complete'=> min(100, $pourcentage),
                'premiere_lecture_at' => date('Y-m-d H:i:s'),
                'derniere_lecture_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->json(['ok' => true, 'page' => $page, 'pourcentage' => $pourcentage]);
    }
}
