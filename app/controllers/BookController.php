<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\BookAccess;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Notification;
use App\Lib\Session;
use App\Models\Book;
use App\Models\Category;
use App\Models\Review;

/**
 * Contrôleur du catalogue et des livres
 */
class BookController extends BaseController
{
    /**
     * Page catalogue avec filtres et pagination
     */
    public function catalogue(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 24;
        $offset = ($page - 1) * $perPage;

        $categorySlug = $_GET['categorie'] ?? null;
        $search = !empty($_GET['q']) ? trim($_GET['q']) : null;
        $tri = $_GET['tri'] ?? 'recent';

        $orderMap = [
            'recent'    => 'b.date_publication DESC',
            'populaire' => 'b.total_ventes DESC, b.total_lectures DESC',
            'prix_asc'  => 'b.prix_unitaire_usd ASC',
            'prix_desc' => 'b.prix_unitaire_usd DESC',
        ];
        $orderBy = $orderMap[$tri] ?? $orderMap['recent'];

        $livres = Book::findPublished($perPage, $offset, $categorySlug, $search, $orderBy);
        $total = Book::countPublished($categorySlug, $search);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $categorieActive = $categorySlug ? Category::findBySlug($categorySlug) : null;
        $categories = Category::findActive();

        $this->view('catalogue/index', [
            'titre'           => $categorieActive ? $categorieActive->nom : 'Catalogue',
            'livres'          => $livres,
            'categories'      => $categories,
            'categorieActive' => $categorieActive,
            'total'           => $total,
            'page'            => $page,
            'totalPages'      => $totalPages,
            'search'          => $search,
            'tri'             => $tri,
            'categorySlug'    => $categorySlug,
        ]);
    }

    /**
     * Page détail d'un livre
     */
    public function show(string $slug): void
    {
        $book = Book::findBySlug($slug);
        if (!$book || $book->statut !== 'publie') {
            http_response_code(404);
            $this->view('errors/404', ['titre' => 'Livre introuvable']);
            return;
        }

        // Livres du même auteur et similaires
        $memeAuteur = Book::findByAuthor($book->author_id, 4, $book->id);
        $similaires = Book::findSimilar($book->id, $book->category_id, 4);

        // Avis
        $avis = Review::findByBook($book->id, 5);
        $noteMoyenne = Review::averageForBook($book->id);

        // Statut utilisateur connecté
        $user = Auth::user();
        $aAchete = false;
        $estAbonne = false;
        $progression = null;
        $aDejaNote = false;

        if ($user) {
            $db = Database::getInstance();

            // A acheté ? (filtre sur source='achat_unitaire' — un simple favori ne compte pas)
            $aAchete = BookAccess::hasBought($user, $book->id);

            // Est abonné actif ?
            $sub = $db->fetch(
                "SELECT id FROM subscriptions WHERE user_id = ? AND statut = 'actif' AND date_fin > NOW()",
                [$user->id]
            );
            $estAbonne = (bool) $sub;

            // Progression de lecture
            $progression = $db->fetch(
                "SELECT * FROM reading_progress WHERE user_id = ? AND book_id = ?",
                [$user->id, $book->id]
            );

            $aDejaNote = Review::userHasReviewed($user->id, $book->id);

            // Favori ?
            $ubFav = $db->fetch("SELECT favori FROM user_books WHERE user_id = ? AND book_id = ?", [$user->id, $book->id]);
            $estFavori = $ubFav ? (bool) $ubFav->favori : false;
        }

        $this->view('book/show', [
            'titre'       => $book->titre,
            'description' => $book->description_courte,
            'book'        => $book,
            'memeAuteur'  => $memeAuteur,
            'similaires'  => $similaires,
            'avis'        => $avis,
            'noteMoyenne' => $noteMoyenne,
            'user'        => $user,
            'aAchete'     => $aAchete,
            'estAbonne'   => $estAbonne,
            'estFavori'   => $estFavori ?? false,
            'progression' => $progression,
            'aDejaNote'   => $aDejaNote,
        ]);
    }

    /**
     * Toggle favori (AJAX)
     */
    public function toggleFavorite(string $slug): void
    {
        if (!Auth::check()) {
            $this->json(['error' => 'not_logged_in'], 401);
        }

        $book = Book::findBySlug($slug);
        if (!$book) {
            $this->json(['error' => 'not_found'], 404);
        }

        // Vérifier le token CSRF depuis le header
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CSRF::validate($token)) {
            $this->json(['error' => 'csrf'], 403);
        }

        $db = Database::getInstance();
        $userId = Auth::id();

        $existing = $db->fetch("SELECT id, favori FROM user_books WHERE user_id = ? AND book_id = ?", [$userId, $book->id]);

        if ($existing) {
            $newFavori = $existing->favori ? 0 : 1;
            $db->update('user_books', ['favori' => $newFavori], 'id = ?', [$existing->id]);
            $this->json(['favori' => (bool) $newFavori]);
        } else {
            // Source dédiée : ne donne PAS accès à la lecture (BookAccess filtre sur 'achat_unitaire' ou abonnement actif)
            $db->insert('user_books', [
                'user_id'    => $userId,
                'book_id'    => $book->id,
                'source'     => 'favori',
                'favori'     => 1,
                'date_ajout' => date('Y-m-d H:i:s'),
            ]);
            $this->json(['favori' => true]);
        }
    }

    /**
     * Soumettre un avis
     */
    public function submitReview(string $slug): void
    {
        CSRF::check();
        Auth::requireLogin();

        $book = Book::findBySlug($slug);
        if (!$book) {
            redirect('/catalogue');
        }

        $user = Auth::user();
        if (Review::userHasReviewed($user->id, $book->id)) {
            Session::flash('avis_error', 'Tu as déjà laissé un avis pour ce livre.');
            redirect('/livre/' . $slug);
        }

        $note = max(1, min(5, (int) ($_POST['note'] ?? 5)));
        $titre = trim($_POST['titre_avis'] ?? '');
        $commentaire = trim($_POST['commentaire'] ?? '');

        if (empty($commentaire) || mb_strlen($commentaire) < 10) {
            Session::flash('avis_error', 'Ton commentaire doit contenir au moins 10 caractères.');
            redirect('/livre/' . $slug);
        }

        Review::create([
            'user_id'     => $user->id,
            'book_id'     => $book->id,
            'note'        => $note,
            'titre'       => $titre ?: null,
            'commentaire' => $commentaire,
            'approuve'    => 1,
        ]);

        // Recalculer note_moyenne et nombre_avis
        $db = Database::getInstance();
        $stats = $db->fetch("SELECT AVG(note) as avg_note, COUNT(*) as nb FROM reviews WHERE book_id = ? AND approuve = 1", [$book->id]);
        if ($stats) {
            $db->update('books', [
                'note_moyenne' => round((float) $stats->avg_note, 2),
                'nombre_avis'  => (int) $stats->nb,
            ], 'id = ?', [$book->id]);
        }

        // Notifier l'auteur du livre (si différent du reviewer)
        $authorUser = $db->fetch(
            "SELECT u.id FROM authors a JOIN users u ON u.id = a.user_id WHERE a.id = ?",
            [$book->author_id]
        );
        if ($authorUser && (int) $authorUser->id !== (int) $user->id) {
            Notification::create(
                (int) $authorUser->id,
                'new_review',
                'Nouvel avis sur ton livre',
                $user->prenom . ' a laissé un avis ' . $note . '★ sur « ' . $book->titre . ' ».',
                '/livre/' . $book->slug . '#avis',
                'star'
            );
        }

        Session::flash('avis_success', 'Ton avis a été publié !');
        redirect('/livre/' . $slug . '#avis');
    }

    /**
     * API recherche instantanée
     */
    public function searchApi(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (mb_strlen($q) < 2) {
            $this->json([]);
        }

        $livres = Book::findPublished(6, 0, null, $q);

        $results = array_map(function ($l) {
            return [
                'titre'    => $l->titre,
                'slug'     => $l->slug,
                'auteur'   => $l->author_nom_plume ?: ($l->author_prenom . ' ' . $l->author_nom),
                'categorie'=> $l->category_nom ?? '',
                'prix'     => number_format($l->prix_unitaire_usd, 2) . ' $',
            ];
        }, $livres);

        $this->json($results);
    }

    /**
     * Catalogue filtré par catégorie via URL propre
     */
    public function byCategory(string $slug): void
    {
        $_GET['categorie'] = $slug;
        $this->catalogue();
    }
}
