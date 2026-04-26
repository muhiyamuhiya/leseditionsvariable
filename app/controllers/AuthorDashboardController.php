<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Mailer;
use App\Lib\Session;
use App\Lib\PDFProcessor;
use App\Models\Category;

/**
 * Dashboard auteur
 */
class AuthorDashboardController extends BaseController
{
    private function db(): Database { return Database::getInstance(); }

    protected function authorView(string $viewName, array $data = []): void
    {
        extract($data);
        $viewFile = BASE_PATH . '/app/views/author/' . $viewName . '.php';
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/author.php';
    }

    // =====================================================================
    // CANDIDATURE
    // =====================================================================
    public function showApplication(): void
    {
        Auth::requireLogin();
        $user = Auth::user();

        // Déjà auteur validé ?
        $author = Auth::getAuthorRecord();
        if ($author && $author->statut_validation === 'valide') {
            redirect('/auteur');
        }

        $categories = Category::findActive();
        $this->view('author/apply', [
            'titre' => 'Devenir auteur',
            'user' => $user,
            'author' => $author,
            'categories' => $categories,
        ]);
    }

    public function submitApplication(): void
    {
        Auth::requireLogin();
        CSRF::check();
        $db = $this->db();
        $user = Auth::user();

        $slug = trim($_POST['slug'] ?? '');
        if (!$slug) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $user->prenom . ' ' . $user->nom)));
        }

        $data = [
            'nom_plume'          => trim($_POST['nom_plume'] ?? '') ?: null,
            'slug'               => $slug,
            'biographie_courte'  => trim($_POST['biographie_courte'] ?? ''),
            'biographie_longue'  => trim($_POST['biographie_longue'] ?? ''),
            'pays_origine'       => trim($_POST['pays_origine'] ?? '') ?: null,
            'ville_residence'    => trim($_POST['ville_residence'] ?? '') ?: null,
            'site_web'           => trim($_POST['site_web'] ?? '') ?: null,
            'facebook_url'       => trim($_POST['facebook_url'] ?? '') ?: null,
            'instagram_url'      => trim($_POST['instagram_url'] ?? '') ?: null,
            'twitter_x_url'      => trim($_POST['twitter_x_url'] ?? '') ?: null,
            'linkedin_url'       => trim($_POST['linkedin_url'] ?? '') ?: null,
            'methode_versement'  => $_POST['methode_versement'] ?? 'mobile_money',
            'numero_mobile_money'=> trim($_POST['numero_mobile_money'] ?? '') ?: null,
            'email_paypal'       => trim($_POST['email_paypal'] ?? '') ?: null,
            'statut_validation'  => 'en_attente',
        ];

        // Upload photo
        if (!empty($_FILES['photo']['tmp_name'])) {
            $file = $_FILES['photo'];
            if (in_array($file['type'], ['image/jpeg','image/png','image/webp']) && $file['size'] <= 2 * 1024 * 1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = $slug . '-' . time() . '.' . $ext;
                $absPath = BASE_PATH . '/storage/authors/' . $filename;
                if (!is_dir(dirname($absPath))) mkdir(dirname($absPath), 0755, true);
                move_uploaded_file($file['tmp_name'], $absPath);
                $data['photo_auteur'] = '/image/authors/' . $filename;
            }
        }

        $existing = $db->fetch("SELECT id FROM authors WHERE user_id = ?", [$user->id]);
        if ($existing) {
            $db->update('authors', $data, 'id = ?', [$existing->id]);
        } else {
            $data['user_id'] = $user->id;
            $db->insert('authors', $data);
        }

        // Mettre à jour le rôle
        if ($user->role === 'lecteur') {
            $db->update('users', ['role' => 'auteur'], 'id = ?', [$user->id]);
        }

        // Emails (templates HTML stylés + BCC admin auto)
        Mailer::sendAdminCandidatureNotif($user);
        Mailer::sendAuthorCandidatureReceived($user);

        Session::flash('success', 'Ta candidature a été soumise avec succès.');
        redirect('/auteur');
    }

    // =====================================================================
    // DASHBOARD
    // =====================================================================
    public function dashboard(): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();

        if (!$author) {
            redirect('/auteur/candidater');
            return;
        }

        if ($author->statut_validation === 'en_attente') {
            $this->view('author/pending', ['titre' => 'Candidature en attente', 'author' => $author]);
            return;
        }

        if ($author->statut_validation === 'refuse') {
            $this->view('author/refused', ['titre' => 'Candidature refusée', 'author' => $author]);
            return;
        }

        $db = $this->db();
        $user = Auth::user();

        $stats = [
            'livres' => $db->fetch("SELECT COUNT(*) as v FROM books WHERE author_id = ? AND statut = 'publie'", [$author->id])->v ?? 0,
            'ventes_mois' => $db->fetch("SELECT COUNT(*) as v FROM sales WHERE author_id = ? AND statut = 'payee' AND MONTH(date_vente) = MONTH(NOW()) AND YEAR(date_vente) = YEAR(NOW())", [$author->id])->v ?? 0,
            'revenus_mois' => $db->fetch("SELECT COALESCE(SUM(revenu_auteur), 0) as v FROM sales WHERE author_id = ? AND statut = 'payee' AND MONTH(date_vente) = MONTH(NOW()) AND YEAR(date_vente) = YEAR(NOW())", [$author->id])->v ?? 0,
            'pages_lues' => $db->fetch("SELECT COALESCE(SUM(b.total_pages_lues_cumul), 0) as v FROM books b WHERE b.author_id = ?", [$author->id])->v ?? 0,
        ];

        // Construire les alertes contextuelles (action requise par l'auteur)
        $alertes = [];

        $devisRecus = $db->fetchAll(
            "SELECT o.id, o.titre_projet, o.montant_propose, o.devise, s.nom AS service_nom
             FROM editorial_orders o JOIN editorial_services s ON s.id = o.service_id
             WHERE o.user_id = ? AND o.statut = 'devis_envoye'
             ORDER BY o.updated_at DESC",
            [$user->id]
        );
        foreach ($devisRecus as $d) {
            $alertes[] = [
                'icon'    => '💰',
                'title'   => 'Tu as un nouveau devis',
                'message' => $d->service_nom . ' — ' . number_format((float) $d->montant_propose, 2) . ' ' . $d->devise,
                'url'     => '/auteur/mes-commandes-editoriales/' . (int) $d->id,
            ];
        }

        $aPayer = $db->fetchAll(
            "SELECT o.id, o.titre_projet, o.montant_propose, o.devise, s.nom AS service_nom
             FROM editorial_orders o JOIN editorial_services s ON s.id = o.service_id
             WHERE o.user_id = ? AND o.statut = 'accepte' AND o.montant_propose IS NOT NULL
             ORDER BY o.updated_at DESC",
            [$user->id]
        );
        foreach ($aPayer as $p) {
            $alertes[] = [
                'icon'    => '💳',
                'title'   => 'Commande prête à payer',
                'message' => $p->service_nom . ' — ' . number_format((float) $p->montant_propose, 2) . ' ' . $p->devise,
                'url'     => '/auteur/mes-commandes-editoriales/' . (int) $p->id . '/payer',
            ];
        }

        $livraisons = $db->fetchAll(
            "SELECT o.id, s.nom AS service_nom
             FROM editorial_orders o JOIN editorial_services s ON s.id = o.service_id
             WHERE o.user_id = ? AND o.statut = 'livre'
               AND o.livre_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             ORDER BY o.livre_at DESC",
            [$user->id]
        );
        foreach ($livraisons as $l) {
            $alertes[] = [
                'icon'    => '📦',
                'title'   => 'Ta commande est livrée !',
                'message' => $l->service_nom . ' — télécharge ton livrable',
                'url'     => '/auteur/mes-commandes-editoriales/' . (int) $l->id,
            ];
        }

        $livresEnRevue = $db->fetchAll(
            "SELECT id, titre FROM books WHERE author_id = ? AND statut = 'en_revue' ORDER BY updated_at DESC",
            [$author->id]
        );
        foreach ($livresEnRevue as $l) {
            $alertes[] = [
                'icon'    => '⏳',
                'title'   => 'Livre en cours de validation',
                'message' => '« ' . $l->titre . ' » sera publié dès validation par l\'admin',
                'url'     => '/auteur/livres',
            ];
        }

        $nouveauxAvis = (int) ($db->fetch(
            "SELECT COUNT(*) AS v FROM reviews r
             JOIN books b ON b.id = r.book_id
             WHERE b.author_id = ? AND r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$author->id]
        )->v ?? 0);
        if ($nouveauxAvis > 0) {
            $alertes[] = [
                'icon'    => '⭐',
                'title'   => $nouveauxAvis . ' nouvel' . ($nouveauxAvis > 1 ? 's' : '') . ' avis cette semaine',
                'message' => 'Sur tes livres publiés',
                'url'     => '/auteur/livres',
            ];
        }

        $dernLivres = $db->fetchAll("SELECT b.*, c.nom as cat_nom FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE b.author_id = ? ORDER BY b.created_at DESC LIMIT 3", [$author->id]);

        $this->authorView('dashboard', [
            'titre'      => 'Tableau de bord',
            'author'     => $author,
            'stats'      => $stats,
            'alertes'    => $alertes,
            'dernLivres' => $dernLivres,
        ]);
    }

    // =====================================================================
    // LIVRES
    // =====================================================================
    public function books(): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();
        if (!$author || $author->statut_validation !== 'valide') { redirect('/auteur'); return; }

        $livres = $this->db()->fetchAll(
            "SELECT b.*, c.nom as cat_nom FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE b.author_id = ? ORDER BY b.created_at DESC",
            [$author->id]
        );
        $this->authorView('books/index', ['titre' => 'Mes livres', 'livres' => $livres]);
    }

    public function createBook(): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();
        if (!$author || $author->statut_validation !== 'valide') { redirect('/auteur'); return; }

        $categories = Category::findActive();
        $this->authorView('books/create', ['titre' => 'Nouveau livre', 'categories' => $categories]);
    }

    public function storeBook(): void
    {
        Auth::requireAuthor();
        CSRF::check();
        $author = Auth::getAuthorRecord();
        if (!$author || $author->statut_validation !== 'valide') { redirect('/auteur'); return; }

        $db = $this->db();
        $slug = trim($_POST['slug'] ?? '') ?: strtolower(preg_replace('/[^a-z0-9]+/', '-', transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', trim($_POST['titre']))));

        $bookData = [
            'author_id'             => $author->id,
            'titre'                 => trim($_POST['titre']),
            'slug'                  => $slug,
            'sous_titre'            => trim($_POST['sous_titre'] ?? '') ?: null,
            'category_id'           => (int) ($_POST['category_id'] ?? 0) ?: null,
            'description_courte'    => trim($_POST['description_courte'] ?? ''),
            'description_longue'    => trim($_POST['description_longue'] ?? ''),
            'mots_cles'             => trim($_POST['mots_cles'] ?? ''),
            'isbn'                  => trim($_POST['isbn'] ?? '') ?: null,
            'langue'                => trim($_POST['langue'] ?? 'fr'),
            'nombre_pages'          => (int) ($_POST['nombre_pages'] ?? 0) ?: null,
            'prix_unitaire_usd'     => (float) ($_POST['prix_unitaire_usd'] ?? 9.99),
            'statut'                => 'en_revue',
            'editeur'               => 'Les éditions Variable',
            'accessible_abonnement_essentiel' => isset($_POST['accessible_abonnement_essentiel']) ? 1 : 0,
            'accessible_abonnement_premium'   => isset($_POST['accessible_abonnement_premium']) ? 1 : 0,
        ];

        $id = $db->insert('books', $bookData);

        // Upload couverture
        if ($id && !empty($_FILES['couverture']['tmp_name'])) {
            $file = $_FILES['couverture'];
            if (in_array($file['type'], ['image/jpeg','image/png','image/webp']) && $file['size'] <= 2 * 1024 * 1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $fn = $slug . '-' . time() . '.' . $ext;
                $abs = BASE_PATH . '/storage/covers/' . $fn;
                if (!is_dir(dirname($abs))) mkdir(dirname($abs), 0755, true);
                move_uploaded_file($file['tmp_name'], $abs);
                $db->update('books', ['couverture_path' => 'storage/covers/' . $fn, 'couverture_url_web' => '/image/covers/' . $fn], 'id = ?', [$id]);
            }
        }

        // Upload PDF
        if ($id && !empty($_FILES['manuscrit']['tmp_name'])) {
            $file = $_FILES['manuscrit'];
            if ($file['type'] === 'application/pdf' && $file['size'] <= 50 * 1024 * 1024) {
                $pdfPath = BASE_PATH . '/storage/books/' . $slug . '.pdf';
                move_uploaded_file($file['tmp_name'], $pdfPath);
                $extractPath = BASE_PATH . '/storage/extracts/' . $slug . '-extrait.pdf';
                PDFProcessor::generateExtract($pdfPath, $extractPath, FREE_PREVIEW_PAGES);
                $db->update('books', [
                    'fichier_complet_path' => 'storage/books/' . $slug . '.pdf',
                    'fichier_extrait_path' => 'storage/extracts/' . $slug . '-extrait.pdf',
                ], 'id = ?', [$id]);
            }
        }

        // Emails (templates HTML stylés + BCC admin auto)
        $user = Auth::user();
        Mailer::sendAdminBookNotif($user, $bookData['titre']);
        Mailer::sendBookSubmitted($user, $bookData['titre']);

        Session::flash('author_success', 'Ton livre a été soumis pour validation.');
        redirect('/auteur/livres');
    }

    public function editBook(string $id): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();
        $book = $this->db()->fetch("SELECT * FROM books WHERE id = ? AND author_id = ?", [(int)$id, $author->id]);
        if (!$book) { redirect('/auteur/livres'); return; }
        $categories = Category::findActive();
        $this->authorView('books/edit', ['titre' => 'Éditer : ' . $book->titre, 'book' => $book, 'categories' => $categories]);
    }

    public function updateBook(string $id): void
    {
        Auth::requireAuthor();
        CSRF::check();
        $author = Auth::getAuthorRecord();
        $db = $this->db();
        $id = (int)$id;
        $book = $db->fetch("SELECT * FROM books WHERE id = ? AND author_id = ?", [$id, $author->id]);
        if (!$book) { redirect('/auteur/livres'); return; }

        $data = [
            'titre'              => trim($_POST['titre']),
            'sous_titre'         => trim($_POST['sous_titre'] ?? '') ?: null,
            'category_id'        => (int)($_POST['category_id'] ?? 0) ?: null,
            'description_courte' => trim($_POST['description_courte'] ?? ''),
            'description_longue' => trim($_POST['description_longue'] ?? ''),
            'mots_cles'          => trim($_POST['mots_cles'] ?? ''),
            'prix_unitaire_usd'  => (float)($_POST['prix_unitaire_usd'] ?? 9.99),
            'accessible_abonnement_essentiel' => isset($_POST['accessible_abonnement_essentiel']) ? 1 : 0,
            'accessible_abonnement_premium'   => isset($_POST['accessible_abonnement_premium']) ? 1 : 0,
        ];

        if (!empty($_FILES['couverture']['tmp_name'])) {
            $file = $_FILES['couverture'];
            if (in_array($file['type'], ['image/jpeg','image/png','image/webp']) && $file['size'] <= 2*1024*1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $fn = $book->slug . '-' . time() . '.' . $ext;
                $abs = BASE_PATH . '/storage/covers/' . $fn;
                move_uploaded_file($file['tmp_name'], $abs);
                $data['couverture_path'] = 'storage/covers/' . $fn;
                $data['couverture_url_web'] = '/image/covers/' . $fn;
            }
        }

        $db->update('books', $data, 'id = ?', [$id]);
        Session::flash('author_success', 'Livre mis à jour.');
        redirect('/auteur/livres');
    }

    // =====================================================================
    // VENTES & VERSEMENTS
    // =====================================================================
    public function sales(): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();
        if (!$author) { redirect('/auteur'); return; }
        $ventes = $this->db()->fetchAll(
            "SELECT s.*, b.titre as book_titre FROM sales s JOIN books b ON s.book_id = b.id WHERE s.author_id = ? ORDER BY s.date_vente DESC",
            [$author->id]
        );
        $totalRevenus = $this->db()->fetch("SELECT COALESCE(SUM(revenu_auteur), 0) as v FROM sales WHERE author_id = ? AND statut = 'payee'", [$author->id])->v ?? 0;
        $this->authorView('sales', ['titre' => 'Mes ventes', 'ventes' => $ventes, 'totalRevenus' => $totalRevenus]);
    }

    public function payouts(): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();
        if (!$author) { redirect('/auteur'); return; }
        $versements = $this->db()->fetchAll("SELECT * FROM author_payouts WHERE author_id = ? ORDER BY created_at DESC", [$author->id]);
        $this->authorView('payouts', ['titre' => 'Mes versements', 'versements' => $versements]);
    }

    // =====================================================================
    // PROFIL
    // =====================================================================
    public function profile(): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();
        if (!$author) { redirect('/auteur'); return; }
        $this->authorView('profile', ['titre' => 'Mon profil', 'author' => $author]);
    }

    public function updateProfile(): void
    {
        Auth::requireAuthor();
        CSRF::check();
        $author = Auth::getAuthorRecord();
        if (!$author) { redirect('/auteur'); return; }
        $db = $this->db();

        $data = [
            'nom_plume'         => trim($_POST['nom_plume'] ?? '') ?: null,
            'biographie_courte' => trim($_POST['biographie_courte'] ?? ''),
            'biographie_longue' => trim($_POST['biographie_longue'] ?? ''),
            'pays_origine'      => trim($_POST['pays_origine'] ?? '') ?: null,
            'ville_residence'   => trim($_POST['ville_residence'] ?? '') ?: null,
            'site_web'          => trim($_POST['site_web'] ?? '') ?: null,
            'facebook_url'      => trim($_POST['facebook_url'] ?? '') ?: null,
            'instagram_url'     => trim($_POST['instagram_url'] ?? '') ?: null,
            'twitter_x_url'     => trim($_POST['twitter_x_url'] ?? '') ?: null,
            'linkedin_url'      => trim($_POST['linkedin_url'] ?? '') ?: null,
            'methode_versement' => $_POST['methode_versement'] ?? $author->methode_versement,
            'numero_mobile_money'=> trim($_POST['numero_mobile_money'] ?? '') ?: null,
            'email_paypal'      => trim($_POST['email_paypal'] ?? '') ?: null,
        ];

        if (!empty($_FILES['photo']['tmp_name'])) {
            $file = $_FILES['photo'];
            if (in_array($file['type'], ['image/jpeg','image/png','image/webp']) && $file['size'] <= 2*1024*1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $fn = $author->slug . '-' . time() . '.' . $ext;
                $abs = BASE_PATH . '/storage/authors/' . $fn;
                if (!is_dir(dirname($abs))) mkdir(dirname($abs), 0755, true);
                move_uploaded_file($file['tmp_name'], $abs);
                $data['photo_auteur'] = '/image/authors/' . $fn;
            }
        }

        $db->update('authors', $data, 'id = ?', [$author->id]);
        Session::flash('author_success', 'Profil mis à jour.');
        redirect('/auteur/profil');
    }
}
