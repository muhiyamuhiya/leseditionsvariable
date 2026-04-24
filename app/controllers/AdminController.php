<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Session;
use App\Lib\Mailer;

/**
 * Contrôleur du dashboard admin
 */
class AdminController extends BaseController
{
    private function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Afficher une vue admin dans le layout admin
     */
    protected function adminView(string $viewName, array $data = []): void
    {
        extract($data);
        $viewFile = BASE_PATH . '/app/views/admin/' . $viewName . '.php';
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/admin.php';
    }

    // =====================================================================
    // DASHBOARD
    // =====================================================================
    public function dashboard(): void
    {
        Auth::requireAdmin();
        $db = $this->db();

        $stats = [
            'ca_mois'      => $db->fetch("SELECT COALESCE(SUM(prix_paye_usd),0) as v FROM sales WHERE statut='payee' AND MONTH(date_vente)=MONTH(NOW()) AND YEAR(date_vente)=YEAR(NOW())")->v ?? 0,
            'abonnes'      => $db->fetch("SELECT COUNT(*) as v FROM subscriptions WHERE statut='actif' AND date_fin >= NOW()")->v ?? 0,
            'livres'       => $db->fetch("SELECT COUNT(*) as v FROM books WHERE statut='publie'")->v ?? 0,
            'auteurs'      => $db->fetch("SELECT COUNT(*) as v FROM authors WHERE statut_validation='valide'")->v ?? 0,
            'candidatures' => $db->fetch("SELECT COUNT(*) as v FROM authors WHERE statut_validation='en_attente'")->v ?? 0,
            'livres_revue' => $db->fetch("SELECT COUNT(*) as v FROM books WHERE statut='en_revue'")->v ?? 0,
        ];

        $topLivres = $db->fetchAll("SELECT b.titre, b.total_ventes, b.slug FROM books b WHERE b.statut='publie' ORDER BY b.total_ventes DESC LIMIT 5");
        $topAuteurs = $db->fetchAll("SELECT a.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as display_name, a.total_ventes_cumul FROM authors a JOIN users u ON a.user_id=u.id WHERE a.statut_validation='valide' ORDER BY a.total_ventes_cumul DESC LIMIT 5");

        $this->adminView('dashboard', ['titre' => 'Tableau de bord', 'stats' => $stats, 'topLivres' => $topLivres, 'topAuteurs' => $topAuteurs]);
    }

    // =====================================================================
    // LIVRES
    // =====================================================================
    public function books(): void
    {
        Auth::requireAdmin();
        $db = $this->db();

        $statut = $_GET['statut'] ?? null;
        $q = $_GET['q'] ?? null;
        $where = '1=1';
        $params = [];

        if ($statut) { $where .= " AND b.statut = ?"; $params[] = $statut; }
        if ($q) { $where .= " AND b.titre LIKE ?"; $params[] = "%{$q}%"; }

        $livres = $db->fetchAll(
            "SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as author_name, c.nom as cat_nom
             FROM books b JOIN authors a ON b.author_id=a.id JOIN users u ON a.user_id=u.id LEFT JOIN categories c ON b.category_id=c.id
             WHERE {$where} ORDER BY b.created_at DESC",
            $params
        );

        $this->adminView('livres/index', ['titre' => 'Livres', 'livres' => $livres, 'filtreStatut' => $statut, 'filtreQ' => $q]);
    }

    public function bookEdit(string $id): void
    {
        Auth::requireAdmin();
        $db = $this->db();
        $book = $db->fetch("SELECT * FROM books WHERE id = ?", [(int) $id]);
        if (!$book) { redirect('/admin/livres'); }
        $authors = $db->fetchAll("SELECT a.id, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as name FROM authors a JOIN users u ON a.user_id=u.id WHERE a.statut_validation='valide' ORDER BY name");
        $categories = $db->fetchAll("SELECT id, nom FROM categories WHERE actif=1 ORDER BY ordre_affichage");
        $this->adminView('livres/edit', ['titre' => 'Éditer : ' . $book->titre, 'book' => $book, 'authors' => $authors, 'categories' => $categories]);
    }

    public function bookUpdate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $id = (int) $id;

        $data = [
            'titre'                 => trim($_POST['titre']),
            'slug'                  => trim($_POST['slug']),
            'sous_titre'            => trim($_POST['sous_titre'] ?? '') ?: null,
            'author_id'             => (int) $_POST['author_id'],
            'category_id'           => (int) $_POST['category_id'] ?: null,
            'description_courte'    => trim($_POST['description_courte'] ?? ''),
            'description_longue'    => trim($_POST['description_longue'] ?? ''),
            'mots_cles'             => trim($_POST['mots_cles'] ?? ''),
            'isbn'                  => trim($_POST['isbn'] ?? '') ?: null,
            'langue'                => trim($_POST['langue'] ?? 'fr'),
            'nombre_pages'          => (int) ($_POST['nombre_pages'] ?? 0) ?: null,
            'annee_publication'     => (int) ($_POST['annee_publication'] ?? 0) ?: null,
            'prix_unitaire_usd'     => (float) ($_POST['prix_unitaire_usd'] ?? 0),
            'prix_unitaire_cdf'     => (float) ($_POST['prix_unitaire_cdf'] ?? 0) ?: null,
            'prix_unitaire_eur'     => (float) ($_POST['prix_unitaire_eur'] ?? 0) ?: null,
            'prix_unitaire_cad'     => (float) ($_POST['prix_unitaire_cad'] ?? 0) ?: null,
            'accessible_abonnement' => isset($_POST['accessible_abonnement']) ? 1 : 0,
            'mis_en_avant'          => isset($_POST['mis_en_avant']) ? 1 : 0,
            'nouveaute'             => isset($_POST['nouveaute']) ? 1 : 0,
            'statut'                => $_POST['statut'] ?? 'brouillon',
        ];

        if ($data['statut'] === 'publie') {
            $existing = $db->fetch("SELECT date_publication FROM books WHERE id = ?", [$id]);
            if (!$existing->date_publication) {
                $data['date_publication'] = date('Y-m-d H:i:s');
            }
        }

        // Upload couverture
        if (!empty($_FILES['couverture']['tmp_name'])) {
            $file = $_FILES['couverture'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 2 * 1024 * 1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = ($data['slug'] ?? 'book-' . $id) . '-' . time() . '.' . $ext;
                $absPath = BASE_PATH . '/storage/covers/' . $filename;
                if (!is_dir(dirname($absPath))) mkdir(dirname($absPath), 0755, true);
                move_uploaded_file($file['tmp_name'], $absPath);
                $data['couverture_path'] = 'storage/covers/' . $filename;
                $data['couverture_url_web'] = '/image/covers/' . $filename;
            }
        }

        $db->update('books', $data, 'id = ?', [$id]);
        audit('book_update', 'books', $id, null, $data);

        Session::flash('admin_success', 'Livre mis à jour.');
        redirect('/admin/livres');
    }

    public function bookCreate(): void
    {
        Auth::requireAdmin();
        $db = $this->db();
        $authors = $db->fetchAll("SELECT a.id, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as name FROM authors a JOIN users u ON a.user_id=u.id WHERE a.statut_validation='valide' ORDER BY name");
        $categories = $db->fetchAll("SELECT id, nom FROM categories WHERE actif=1 ORDER BY ordre_affichage");
        $this->adminView('livres/create', ['titre' => 'Nouveau livre', 'authors' => $authors, 'categories' => $categories]);
    }

    public function bookStore(): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();

        $slug = trim($_POST['slug']) ?: strtolower(preg_replace('/[^a-z0-9]+/', '-', transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', trim($_POST['titre']))));

        $id = $db->insert('books', [
            'titre' => trim($_POST['titre']),
            'slug' => $slug,
            'sous_titre' => trim($_POST['sous_titre'] ?? '') ?: null,
            'author_id' => (int) $_POST['author_id'],
            'category_id' => (int) ($_POST['category_id'] ?? 0) ?: null,
            'description_courte' => trim($_POST['description_courte'] ?? ''),
            'description_longue' => trim($_POST['description_longue'] ?? ''),
            'prix_unitaire_usd' => (float) ($_POST['prix_unitaire_usd'] ?? 9.99),
            'statut' => $_POST['statut'] ?? 'brouillon',
            'langue' => 'fr',
            'editeur' => 'Les éditions Variable',
            'accessible_abonnement' => isset($_POST['accessible_abonnement']) ? 1 : 0,
        ]);

        audit('book_create', 'books', $id);
        Session::flash('admin_success', 'Livre créé.');
        redirect('/admin/livres');
    }

    public function bookDelete(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $this->db()->delete('books', 'id = ?', [(int) $id]);
        audit('book_delete', 'books', (int) $id);
        Session::flash('admin_success', 'Livre supprimé.');
        redirect('/admin/livres');
    }

    // =====================================================================
    // AUTEURS
    // =====================================================================
    public function authors(): void
    {
        Auth::requireAdmin();
        $auteurs = $this->db()->fetchAll(
            "SELECT a.*, u.prenom, u.nom, u.email, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as display_name
             FROM authors a JOIN users u ON a.user_id=u.id ORDER BY a.created_at DESC"
        );
        $this->adminView('auteurs/index', ['titre' => 'Auteurs', 'auteurs' => $auteurs]);
    }

    public function authorCandidatures(): void
    {
        Auth::requireAdmin();
        $candidatures = $this->db()->fetchAll(
            "SELECT a.*, u.prenom, u.nom, u.email FROM authors a JOIN users u ON a.user_id=u.id WHERE a.statut_validation='en_attente' ORDER BY a.created_at ASC"
        );
        $this->adminView('candidatures/index', ['titre' => 'Candidatures auteurs', 'candidatures' => $candidatures]);
    }

    public function authorValidate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $this->db()->update('authors', ['statut_validation' => 'valide', 'date_validation' => date('Y-m-d H:i:s'), 'valide_par_admin_id' => Auth::id()], 'id = ?', [(int) $id]);
        audit('author_validate', 'authors', (int) $id);
        Session::flash('admin_success', 'Auteur validé.');
        redirect('/admin/candidatures');
    }

    public function authorRefuse(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $this->db()->update('authors', ['statut_validation' => 'refuse', 'notes_admin' => trim($_POST['motif'] ?? '')], 'id = ?', [(int) $id]);
        audit('author_refuse', 'authors', (int) $id);
        Session::flash('admin_success', 'Candidature refusée.');
        redirect('/admin/candidatures');
    }

    // =====================================================================
    // LECTEURS
    // =====================================================================
    public function readers(): void
    {
        Auth::requireAdmin();
        $lecteurs = $this->db()->fetchAll(
            "SELECT u.*, (SELECT COUNT(*) FROM user_books WHERE user_id=u.id) as nb_livres,
                    (SELECT COUNT(*) FROM subscriptions WHERE user_id=u.id AND statut='actif' AND date_fin>=NOW()) as abo_actif
             FROM users u WHERE u.role='lecteur' ORDER BY u.created_at DESC"
        );
        $this->adminView('lecteurs/index', ['titre' => 'Lecteurs', 'lecteurs' => $lecteurs]);
    }

    // =====================================================================
    // CATÉGORIES
    // =====================================================================
    public function categories(): void
    {
        Auth::requireAdmin();
        $cats = $this->db()->fetchAll("SELECT * FROM categories ORDER BY ordre_affichage ASC");
        $this->adminView('categories/index', ['titre' => 'Catégories', 'cats' => $cats]);
    }

    public function categoriesUpdate(): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        foreach ($_POST['cat'] ?? [] as $id => $data) {
            $db->update('categories', [
                'nom' => trim($data['nom']),
                'ordre_affichage' => (int) ($data['ordre'] ?? 0),
                'actif' => isset($data['actif']) ? 1 : 0,
            ], 'id = ?', [(int) $id]);
        }
        audit('categories_update', 'categories');
        Session::flash('admin_success', 'Catégories mises à jour.');
        redirect('/admin/categories');
    }

    // =====================================================================
    // COMMERCE
    // =====================================================================
    public function subscriptions(): void
    {
        Auth::requireAdmin();
        $abos = $this->db()->fetchAll(
            "SELECT s.*, u.prenom, u.nom, u.email FROM subscriptions s JOIN users u ON s.user_id=u.id ORDER BY s.created_at DESC"
        );
        $this->adminView('abonnements/index', ['titre' => 'Abonnements', 'abos' => $abos]);
    }

    public function sales(): void
    {
        Auth::requireAdmin();
        $ventes = $this->db()->fetchAll(
            "SELECT s.*, u.prenom, u.nom, b.titre as book_titre, COALESCE(a.nom_plume, CONCAT(au.prenom,' ',au.nom)) as author_name
             FROM sales s JOIN users u ON s.user_id=u.id JOIN books b ON s.book_id=b.id JOIN authors a ON s.author_id=a.id JOIN users au ON a.user_id=au.id
             ORDER BY s.date_vente DESC"
        );
        $totalCA = $this->db()->fetch("SELECT COALESCE(SUM(prix_paye_usd),0) as v FROM sales WHERE statut='payee'")->v ?? 0;
        $this->adminView('ventes/index', ['titre' => 'Ventes', 'ventes' => $ventes, 'totalCA' => $totalCA]);
    }

    public function payouts(): void
    {
        Auth::requireAdmin();
        $versements = $this->db()->fetchAll(
            "SELECT ap.*, COALESCE(a.nom_plume, CONCAT(u.prenom,' ',u.nom)) as author_name, a.methode_versement, a.numero_mobile_money, a.email_paypal
             FROM author_payouts ap JOIN authors a ON ap.author_id=a.id JOIN users u ON a.user_id=u.id ORDER BY ap.created_at DESC"
        );
        $this->adminView('versements/index', ['titre' => 'Versements auteurs', 'versements' => $versements]);
    }

    public function payoutMarkPaid(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $this->db()->update('author_payouts', [
            'statut' => 'verse',
            'date_versement' => date('Y-m-d H:i:s'),
            'reference_versement' => trim($_POST['reference'] ?? ''),
        ], 'id = ?', [(int) $id]);
        audit('payout_paid', 'author_payouts', (int) $id);
        Session::flash('admin_success', 'Versement marqué comme effectué.');
        redirect('/admin/versements');
    }

    // =====================================================================
    // PARAMÈTRES
    // =====================================================================
    public function settings(): void
    {
        Auth::requireAdmin();
        $settings = $this->db()->fetchAll("SELECT * FROM settings ORDER BY `key`");
        $this->adminView('parametres/index', ['titre' => 'Paramètres', 'settings' => $settings]);
    }

    public function settingsUpdate(): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        foreach ($_POST['setting'] ?? [] as $key => $value) {
            $db->update('settings', ['value' => trim($value)], '`key` = ?', [$key]);
        }
        audit('settings_update', 'settings');
        Session::flash('admin_success', 'Paramètres enregistrés.');
        redirect('/admin/parametres');
    }

    // =====================================================================
    // JOURNAL
    // =====================================================================
    public function auditLog(): void
    {
        Auth::requireAdmin();
        $logs = $this->db()->fetchAll(
            "SELECT al.*, u.prenom, u.nom FROM audit_log al JOIN users u ON al.admin_id=u.id ORDER BY al.created_at DESC LIMIT 100"
        );
        $this->adminView('journal/index', ['titre' => 'Journal d\'audit', 'logs' => $logs]);
    }
}
