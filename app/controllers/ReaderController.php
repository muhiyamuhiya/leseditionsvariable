<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\BookAccess;
use App\Lib\Database;
use App\Lib\Session;
use App\Models\Book;
use App\Models\Subscription;

/**
 * Contrôleur de la liseuse PDF
 */
class ReaderController extends BaseController
{
    /**
     * Lire le livre complet (achat ou abonnement requis)
     */
    public function read(string $slug): void
    {
        $this->renderReader($slug, 'full');
    }

    /**
     * Lire l'extrait gratuit (connecté suffit)
     */
    public function readExtrait(string $slug): void
    {
        $this->renderReader($slug, 'extrait');
    }

    /**
     * Logique commune de la liseuse
     */
    private function renderReader(string $slug, string $mode): void
    {
        Auth::requireLogin();

        $book = Book::findBySlug($slug);
        if (!$book || $book->statut !== 'publie') {
            redirect('/catalogue');
            return;
        }

        $user = Auth::user();
        $db = Database::getInstance();

        // Matrice d'accès centralisée — distingue achat / Essentiel / Premium
        $hasFullAccess = BookAccess::canReadFull($user, $book->id);

        if ($mode === 'full' && !$hasFullAccess) {
            // Cas particulier : user qui avait accès via abonnement mais l'a perdu
            $hadAbo = $db->fetch(
                "SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'abonnement'",
                [$user->id, $book->id]
            );
            if ($hadAbo) {
                Session::flash('error', "Ton abonnement a expiré ou ne couvre plus ce livre. Renouvelle-le pour reprendre la lecture, ou achète ce livre à l'unité.");
                Session::flash('cta_url', '/abonnement');
                Session::flash('cta_label', 'Renouveler mon abonnement');
            } else {
                $access = BookAccess::getRequiredAccess($user, $book->id);
                Session::flash('error', $access['message'] ?? 'Accès refusé.');
                if (!empty($access['cta_url']) && !empty($access['cta_label'])) {
                    Session::flash('cta_url', $access['cta_url']);
                    Session::flash('cta_label', $access['cta_label']);
                }
            }
            redirect('/livre/' . $book->slug);
            return;
        }

        // Tracking biblio : si l'accès vient d'un abonnement actif, on enregistre une ligne
        // user_books source='abonnement' pour que le livre apparaisse dans "Ma bibliothèque".
        // On ne tracke que les lecteurs (les admins/auteurs ne doivent pas voir leur biblio
        // se remplir des livres qu'ils previewent).
        if ($mode === 'full' && $hasFullAccess && ($user->role ?? '') === 'lecteur') {
            $existing = $db->fetch(
                "SELECT id, source FROM user_books WHERE user_id = ? AND book_id = ?",
                [$user->id, $book->id]
            );
            $sub = Subscription::getActive($user->id);
            if ($sub) {
                if (!$existing) {
                    $db->insert('user_books', [
                        'user_id'      => $user->id,
                        'book_id'      => $book->id,
                        'source'       => 'abonnement',
                        'date_ajout'   => date('Y-m-d H:i:s'),
                        'dernier_acces'=> date('Y-m-d H:i:s'),
                    ]);
                } elseif ($existing->source === 'favori') {
                    // Upgrade : la ligne existait juste comme favori, on passe à 'abonnement'
                    // (le flag favori reste à 1 si actif)
                    $db->update('user_books', [
                        'source'        => 'abonnement',
                        'dernier_acces' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$existing->id]);
                } else {
                    // Achat existant — juste mettre à jour le dernier accès
                    $db->update('user_books', ['dernier_acces' => date('Y-m-d H:i:s')], 'id = ?', [$existing->id]);
                }
            } elseif ($existing) {
                // Pas d'abo (donc accès via achat/admin/auteur) — juste touch dernier_acces
                $db->update('user_books', ['dernier_acces' => date('Y-m-d H:i:s')], 'id = ?', [$existing->id]);
            }
        }

        if ($mode === 'extrait' && !BookAccess::canReadExtract($user)) {
            redirect('/livre/' . $book->slug);
            return;
        }

        // Créer une session de lecture
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

        // Progression existante
        $progress = $db->fetch(
            "SELECT derniere_page_lue FROM reading_progress WHERE user_id = ? AND book_id = ?",
            [$user->id, $book->id]
        );
        $lastPage = $progress ? (int) $progress->derniere_page_lue : 1;

        // En mode extrait, toujours commencer à la page 1
        if ($mode === 'extrait') {
            $lastPage = 1;
        }

        $maxPages = $mode === 'extrait' ? FREE_PREVIEW_PAGES : (int) $book->nombre_pages;

        // Vue plein écran (sans layout header/footer)
        $viewFile = BASE_PATH . '/app/views/reader/read.php';
        extract([
            'book'          => $book,
            'mode'          => $mode,
            'sessionToken'  => $sessionToken,
            'lastPage'      => $lastPage,
            'maxPages'      => $maxPages,
            'hasFullAccess' => $hasFullAccess,
        ]);
        require $viewFile;
    }

    /**
     * Servir le PDF en streaming sécurisé
     *
     * SÉCURITÉ : chaque appel re-valide les droits du user pour le $fileType demandé.
     * On NE FAIT PAS confiance à la session de lecture seule : un user qui a ouvert
     * l'extrait pourrait sinon appeler /lire/pdf/<token>/full et récupérer le livre
     * complet sans l'avoir acheté.
     */
    public function streamPDF(string $sessionToken, string $fileType): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo 'Non autorisé';
            exit;
        }

        $db = Database::getInstance();
        $user = Auth::user();

        $session = $db->fetch(
            "SELECT rs.*, b.fichier_complet_path, b.fichier_extrait_path
             FROM reading_sessions rs
             JOIN books b ON rs.book_id = b.id
             WHERE rs.session_token = ? AND rs.user_id = ? AND rs.statut = 'active'",
            [$sessionToken, $user->id]
        );

        if (!$session) {
            http_response_code(403);
            echo 'Session invalide';
            exit;
        }

        // Validation des droits selon le type demandé — ne pas se fier à la session seule
        if ($fileType === 'full') {
            if (!BookAccess::canReadFull($user, (int) $session->book_id)) {
                http_response_code(403);
                exit('Accès refusé');
            }
            $filePath = $session->fichier_complet_path;
        } elseif ($fileType === 'extrait') {
            if (!BookAccess::canReadExtract($user)) {
                http_response_code(403);
                exit('Accès refusé');
            }
            // PAS de fallback sur le fichier complet : c'est exactement la faille à fermer
            $filePath = $session->fichier_extrait_path;
            if (!$filePath) {
                http_response_code(404);
                exit('Extrait non disponible pour ce livre.');
            }
            // Garde-fou : refuser de servir si l'extrait est en réalité une copie du livre
            // complet (Ghostscript pas installé → fallback de génération qui copie tout).
            $absoluteExtrait = BASE_PATH . '/' . $filePath;
            $absoluteFull    = $session->fichier_complet_path ? BASE_PATH . '/' . $session->fichier_complet_path : null;
            if ($absoluteFull && file_exists($absoluteExtrait) && file_exists($absoluteFull)
                && filesize($absoluteExtrait) === filesize($absoluteFull)) {
                http_response_code(403);
                exit('Extrait non encore généré pour ce livre. Réessaie plus tard.');
            }
        } else {
            http_response_code(400);
            exit('Type de fichier invalide');
        }

        $absolutePath = BASE_PATH . '/' . $filePath;

        if (!file_exists($absolutePath)) {
            http_response_code(404);
            echo 'Fichier PDF introuvable';
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($absolutePath) . '"');
        header('Content-Length: ' . filesize($absolutePath));
        header('Cache-Control: private, max-age=3600');
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
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $sessionToken = $input['session_token'] ?? '';
        $page = max(1, (int) ($input['page'] ?? 1));
        $temps = max(0, (int) ($input['temps_secondes'] ?? 0));

        $db = Database::getInstance();
        $userId = Auth::id();

        $session = $db->fetch(
            "SELECT id, book_id FROM reading_sessions WHERE session_token = ? AND user_id = ?",
            [$sessionToken, $userId]
        );

        if (!$session) {
            $this->json(['error' => 'session_invalid'], 400);
            return;
        }

        $db->update('reading_sessions', [
            'page_fin'               => $page,
            'pages_lues_session'     => $page,
            'temps_lecture_secondes' => $temps,
        ], 'id = ?', [$session->id]);

        // Upsert reading_progress
        $book = Book::find($session->book_id);
        $totalPages = $book ? (int) $book->nombre_pages : 100;
        $pourcentage = $totalPages > 0 ? round(($page / $totalPages) * 100, 2) : 0;

        $existing = $db->fetch(
            "SELECT user_id FROM reading_progress WHERE user_id = ? AND book_id = ?",
            [$userId, $session->book_id]
        );

        if ($existing) {
            $db->update('reading_progress', [
                'derniere_page_lue'   => $page,
                'total_pages_lues'    => $page,
                'total_temps_lecture' => $temps,
                'pourcentage_complete'=> min(100, $pourcentage),
                'derniere_lecture_at' => date('Y-m-d H:i:s'),
                'livre_termine'       => $pourcentage >= 95 ? 1 : 0,
            ], 'user_id = ? AND book_id = ?', [$userId, $session->book_id]);
        } else {
            $db->insert('reading_progress', [
                'user_id'              => $userId,
                'book_id'              => $session->book_id,
                'derniere_page_lue'    => $page,
                'total_pages_lues'     => $page,
                'total_temps_lecture'  => $temps,
                'pourcentage_complete' => min(100, $pourcentage),
                'premiere_lecture_at'  => date('Y-m-d H:i:s'),
                'derniere_lecture_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $this->json(['ok' => true, 'page' => $page, 'pourcentage' => $pourcentage]);
    }
}
