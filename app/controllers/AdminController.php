<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Mailer;
use App\Lib\Notification;
use App\Lib\Session;

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
            'abonnes'      => $db->fetch("SELECT COUNT(DISTINCT user_id) as v FROM subscriptions WHERE statut IN ('actif','annule') AND date_fin >= NOW()")->v ?? 0,
            'livres'       => $db->fetch("SELECT COUNT(*) as v FROM books WHERE statut='publie'")->v ?? 0,
            'auteurs'      => $db->fetch("SELECT COUNT(*) as v FROM authors WHERE statut_validation='valide'")->v ?? 0,
            'lecteurs'     => $db->fetch("SELECT COUNT(*) as v FROM users WHERE role='lecteur' AND (statut='actif' OR statut IS NULL)")->v ?? 0,
        ];

        // Compteurs "action requise" — défensif contre tables manquantes (prod sans migrations)
        $safeCount = function (string $sql) use ($db): int {
            $row = $db->fetch($sql);
            return ($row && isset($row->v)) ? (int) $row->v : 0;
        };
        $alerts = [
            'candidatures'      => $safeCount("SELECT COUNT(*) as v FROM authors WHERE statut_validation='en_attente'"),
            'livres_revue'      => $safeCount("SELECT COUNT(*) as v FROM books WHERE statut='en_revue'"),
            'commandes_devis'   => $safeCount("SELECT COUNT(*) as v FROM editorial_orders WHERE statut='en_attente_devis'"),
            'commandes_livrer'  => $safeCount("SELECT COUNT(*) as v FROM editorial_orders WHERE statut='en_cours'"),
            'paiements_echoues' => $safeCount("SELECT COUNT(*) as v FROM transactions_log WHERE statut='echoue' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
            'abos_annules'      => $safeCount("SELECT COUNT(*) as v FROM subscriptions WHERE statut='annule' AND date_annulation >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
        ];
        $alerts['total'] = $alerts['candidatures'] + $alerts['livres_revue'] + $alerts['commandes_devis'] + $alerts['commandes_livrer'];

        $candidaturesRecentes = $db->fetchAll(
            "SELECT a.id, a.created_at, u.prenom, u.nom, u.email, u.avatar_url
             FROM authors a JOIN users u ON u.id = a.user_id
             WHERE a.statut_validation = 'en_attente'
             ORDER BY a.created_at DESC LIMIT 5"
        );

        $commandesRecentes = $db->fetchAll(
            "SELECT o.id, o.titre_projet, o.statut, o.created_at, o.montant_propose, o.devise,
                    s.nom AS service_nom, s.icon AS service_icon,
                    u.prenom, u.nom
             FROM editorial_orders o
             JOIN editorial_services s ON s.id = o.service_id
             JOIN users u ON u.id = o.user_id
             WHERE o.statut IN ('en_attente_devis','en_cours')
             ORDER BY o.created_at DESC LIMIT 5"
        );

        $topLivres = $db->fetchAll("SELECT b.titre, b.total_ventes, b.slug FROM books b WHERE b.statut='publie' ORDER BY b.total_ventes DESC LIMIT 5");

        $this->adminView('dashboard', [
            'titre'                => 'Tableau de bord',
            'stats'                => $stats,
            'alerts'               => $alerts,
            'candidaturesRecentes' => $candidaturesRecentes,
            'commandesRecentes'    => $commandesRecentes,
            'topLivres'            => $topLivres,
        ]);
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
            'accessible_abonnement_essentiel' => (int) ($_POST['accessible_abonnement_essentiel'] ?? 0),
            'accessible_abonnement_premium'   => (int) ($_POST['accessible_abonnement_premium'] ?? 0),
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

        $bookData = [
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
            'accessible_abonnement_essentiel' => (int) ($_POST['accessible_abonnement_essentiel'] ?? 1),
            'accessible_abonnement_premium'   => (int) ($_POST['accessible_abonnement_premium'] ?? 1),
        ];

        if ($bookData['statut'] === 'publie') {
            $bookData['date_publication'] = date('Y-m-d H:i:s');
        }

        $id = $db->insert('books', $bookData);

        // Upload couverture après création
        if ($id && !empty($_FILES['couverture']['tmp_name'])) {
            $file = $_FILES['couverture'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 2 * 1024 * 1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = $slug . '-' . time() . '.' . $ext;
                $absPath = BASE_PATH . '/storage/covers/' . $filename;
                if (!is_dir(dirname($absPath))) mkdir(dirname($absPath), 0755, true);
                move_uploaded_file($file['tmp_name'], $absPath);
                $db->update('books', [
                    'couverture_path'    => 'storage/covers/' . $filename,
                    'couverture_url_web' => '/image/covers/' . $filename,
                ], 'id = ?', [$id]);
            }
        }

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

    public function authorEdit(string $id): void
    {
        Auth::requireAdmin();
        $author = $this->db()->fetch(
            "SELECT a.*, u.prenom, u.nom, u.email FROM authors a JOIN users u ON a.user_id=u.id WHERE a.id = ?",
            [(int) $id]
        );
        if (!$author) { redirect('/admin/auteurs'); }
        $this->adminView('auteurs/edit', ['titre' => 'Éditer : ' . ($author->nom_plume ?: $author->prenom . ' ' . $author->nom), 'author' => $author]);
    }

    public function authorUpdate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $id = (int) $id;
        $author = $db->fetch("SELECT slug FROM authors WHERE id = ?", [$id]);

        $data = [
            'nom_plume'          => trim($_POST['nom_plume'] ?? '') ?: null,
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
        ];

        // Upload photo
        if (!empty($_FILES['photo']['tmp_name'])) {
            $file = $_FILES['photo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 2 * 1024 * 1024) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = ($author->slug ?? 'author-' . $id) . '-' . time() . '.' . $ext;
                $absPath = BASE_PATH . '/storage/authors/' . $filename;
                if (!is_dir(dirname($absPath))) mkdir(dirname($absPath), 0755, true);
                move_uploaded_file($file['tmp_name'], $absPath);
                $data['photo_auteur'] = '/image/authors/' . $filename;
            }
        }

        $db->update('authors', $data, 'id = ?', [$id]);
        audit('author_update', 'authors', $id);
        Session::flash('admin_success', 'Auteur mis à jour.');
        redirect('/admin/auteurs');
    }

    public function authorCandidatures(): void
    {
        Auth::requireAdmin();
        $candidatures = $this->db()->fetchAll(
            "SELECT a.*, u.prenom, u.nom, u.email FROM authors a JOIN users u ON a.user_id=u.id WHERE a.statut_validation='en_attente' ORDER BY a.created_at ASC"
        );
        $this->adminView('candidatures/index', ['titre' => 'Candidatures auteurs', 'candidatures' => $candidatures]);
    }

    public function authorCandidatureShow(string $id): void
    {
        Auth::requireAdmin();
        $db = $this->db();
        $author = $db->fetch(
            "SELECT a.*, u.prenom, u.nom, u.email, u.telephone, u.pays as user_pays, u.created_at as user_created_at
             FROM authors a JOIN users u ON a.user_id=u.id WHERE a.id = ?",
            [(int) $id]
        );
        if (!$author) { redirect('/admin/candidatures'); return; }
        $livresEnRevue = $db->fetchAll("SELECT * FROM books WHERE author_id = ? AND statut IN ('brouillon','en_revue')", [$author->id]);
        $this->adminView('candidatures/show', ['titre' => 'Candidature : ' . $author->prenom . ' ' . $author->nom, 'author' => $author, 'livresEnRevue' => $livresEnRevue]);
    }

    public function bookPreview(string $id): void
    {
        Auth::requireAdmin();
        $db = $this->db();
        $book = $db->fetch("SELECT b.*, c.nom as cat_nom, c.slug as cat_slug FROM books b LEFT JOIN categories c ON b.category_id=c.id WHERE b.id = ?", [(int)$id]);
        if (!$book) { redirect('/admin/livres'); return; }
        $author = $db->fetch("SELECT a.*, u.prenom, u.nom, u.email FROM authors a JOIN users u ON a.user_id=u.id WHERE a.id = ?", [$book->author_id]);
        $this->adminView('livres/preview', ['titre' => 'Aperçu : ' . $book->titre, 'book' => $book, 'author' => $author]);
    }

    public function bookPublish(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $bookId = (int) $id;
        $db->update('books', ['statut' => 'publie', 'date_publication' => date('Y-m-d H:i:s')], 'id = ?', [$bookId]);
        audit('book_publish', 'books', $bookId);

        // Notifier l'auteur de la publication
        $info = $db->fetch(
            "SELECT b.titre, b.slug, u.id AS user_id
             FROM books b JOIN authors a ON a.id = b.author_id JOIN users u ON u.id = a.user_id
             WHERE b.id = ?",
            [$bookId]
        );
        if ($info) {
            Notification::create(
                (int) $info->user_id,
                'book_published',
                'Ton livre est publié !',
                '« ' . $info->titre . ' » est maintenant disponible sur Les éditions Variable.',
                '/livre/' . $info->slug,
                'book'
            );
        }

        Session::flash('admin_success', 'Livre publié.');
        redirect('/admin/livres');
    }

    public function authorValidate(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $authorId = (int) $id;
        $db->update('authors', ['statut_validation' => 'valide', 'date_validation' => date('Y-m-d H:i:s'), 'valide_par_admin_id' => Auth::id()], 'id = ?', [$authorId]);
        audit('author_validate', 'authors', $authorId);

        $author = $db->fetch("SELECT user_id FROM authors WHERE id = ?", [$authorId]);
        if ($author) {
            Notification::create(
                (int) $author->user_id,
                'candidacy_accepted',
                'Félicitations, tu es auteur !',
                'Ta candidature a été acceptée. Tu peux maintenant soumettre ton premier livre.',
                '/auteur',
                'check'
            );
        }

        Session::flash('admin_success', 'Auteur validé.');
        redirect('/admin/candidatures');
    }

    public function authorRefuse(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $authorId = (int) $id;
        $motif = trim($_POST['motif'] ?? '');
        $db->update('authors', ['statut_validation' => 'refuse', 'notes_admin' => $motif], 'id = ?', [$authorId]);
        audit('author_refuse', 'authors', $authorId);

        $author = $db->fetch("SELECT user_id FROM authors WHERE id = ?", [$authorId]);
        if ($author) {
            Notification::create(
                (int) $author->user_id,
                'candidacy_rejected',
                'Candidature non retenue',
                'Ta candidature n\'a pas été retenue cette fois. Tu peux retenter ou nous écrire pour comprendre.',
                '/contact',
                'alert'
            );
        }

        Session::flash('admin_success', 'Candidature refusée.');
        redirect('/admin/candidatures');
    }

    // =====================================================================
    // UTILISATEURS — liste + détail + soft delete
    // =====================================================================

    public function usersList(): void
    {
        Auth::requireAdmin();
        $db = $this->db();

        $search  = trim($_GET['q'] ?? '');
        $role    = $_GET['role'] ?? 'tous';
        $statut  = $_GET['statut'] ?? 'tous';
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $where  = ["1=1"];
        $params = [];

        if ($search !== '') {
            $where[] = "(u.email LIKE ? OR u.prenom LIKE ? OR u.nom LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        if (in_array($role, ['lecteur','auteur','admin'], true)) {
            $where[] = "u.role = ?";
            $params[] = $role;
        }
        if ($statut === 'actif') {
            $where[] = "(u.statut = 'actif' OR u.statut IS NULL)";
        } elseif ($statut === 'supprime') {
            $where[] = "u.statut = 'supprime'";
        }

        $whereClause = implode(' AND ', $where);

        $total = (int) ($db->fetch("SELECT COUNT(*) AS n FROM users u WHERE {$whereClause}", $params)->n ?? 0);

        $listParams = $params;
        $listParams[] = $perPage;
        $listParams[] = $offset;
        $users = $db->fetchAll(
            "SELECT u.id, u.email, u.prenom, u.nom, u.role, u.statut, u.avatar_url, u.created_at, u.deleted_at,
                    (SELECT COUNT(*) FROM user_books WHERE user_id = u.id AND source = 'achat_unitaire') AS nb_achats,
                    (SELECT COUNT(*) FROM subscriptions WHERE user_id = u.id) AS nb_abonnements,
                    (SELECT MAX(date_fin) FROM subscriptions WHERE user_id = u.id AND statut IN ('actif','annule')) AS date_fin_abo,
                    (SELECT SUM(montant) FROM transactions_log WHERE user_id = u.id AND statut = 'reussi') AS total_depense
             FROM users u
             WHERE {$whereClause}
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?",
            $listParams
        );

        $this->adminView('users/list', [
            'titre'      => 'Utilisateurs',
            'users'      => $users,
            'search'     => $search,
            'role'       => $role,
            'statut'     => $statut,
            'page'       => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'total'      => $total,
        ]);
    }

    public function userDetail(string $id): void
    {
        Auth::requireAdmin();
        $db = $this->db();
        $id = (int) $id;

        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            Session::flash('admin_error', 'Utilisateur introuvable.');
            redirect('/admin/lecteurs');
            return;
        }

        $achats = $db->fetchAll(
            "SELECT ub.id, ub.date_ajout, b.id AS book_id, b.titre, b.slug, b.couverture_url_web, b.prix_unitaire_usd
             FROM user_books ub
             JOIN books b ON ub.book_id = b.id
             WHERE ub.user_id = ? AND ub.source = 'achat_unitaire'
             ORDER BY ub.date_ajout DESC",
            [$id]
        );

        $abonnements = $db->fetchAll(
            "SELECT * FROM subscriptions WHERE user_id = ? ORDER BY date_debut DESC",
            [$id]
        );

        $transactions = $db->fetchAll(
            "SELECT * FROM transactions_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
            [$id]
        );

        $avis = $db->fetchAll(
            "SELECT r.id, r.note, r.titre, r.commentaire, r.approuve, r.created_at, b.titre AS book_titre, b.slug AS book_slug
             FROM reviews r
             JOIN books b ON r.book_id = b.id
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC",
            [$id]
        );

        $this->adminView('users/detail', [
            'titre'        => 'Utilisateur — ' . $user->prenom . ' ' . $user->nom,
            'user'         => $user,
            'achats'       => $achats,
            'abonnements'  => $abonnements,
            'transactions' => $transactions,
            'avis'         => $avis,
        ]);
    }

    public function deleteUser(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $id = (int) $id;

        $user = $db->fetch("SELECT id, role, email, prenom FROM users WHERE id = ?", [$id]);
        if (!$user) {
            Session::flash('admin_error', 'Utilisateur introuvable.');
            redirect('/admin/lecteurs');
            return;
        }
        if ($user->role === 'admin') {
            Session::flash('admin_error', 'Impossible de supprimer un compte admin.');
            redirect('/admin/lecteurs/' . $id);
            return;
        }

        // Soft delete : anonymisation, conservation des stats agrégées
        $db->update('users', [
            'statut'     => 'supprime',
            'email'      => $user->email . '_deleted_' . time(),
            'prenom'     => 'Compte',
            'nom'        => 'Supprimé (admin)',
            'telephone'  => null,
            'avatar_url' => null,
            'bio'        => null,
            'deleted_at' => date('Y-m-d H:i:s'),
            'actif'      => 0,
        ], 'id = ?', [$id]);

        audit('admin_delete_user', 'users', $id, null, ['by' => Auth::id()]);

        Session::flash('admin_success', 'Compte anonymisé avec succès.');
        redirect('/admin/lecteurs');
    }

    public function restoreUser(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        Session::flash('admin_error', 'La restauration n\'est pas disponible : l\'email a été anonymisé.');
        redirect('/admin/lecteurs/' . (int) $id);
    }

    // =====================================================================
    // SERVICES ÉDITORIAUX — gestion admin
    // =====================================================================
    public function editorialOrdersList(): void
    {
        Auth::requireAdmin();
        $db = $this->db();
        $statut = $_GET['statut'] ?? 'tous';
        $q      = trim($_GET['q'] ?? '');

        $where = ["1=1"];
        $params = [];
        $statutsValides = ['en_attente_devis','devis_envoye','accepte','en_cours','livre','annule','rembourse'];
        if (in_array($statut, $statutsValides, true)) {
            $where[] = "o.statut = ?";
            $params[] = $statut;
        }
        if ($q !== '') {
            $where[] = "(u.email LIKE ? OR u.prenom LIKE ? OR u.nom LIKE ? OR o.titre_projet LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $whereClause = implode(' AND ', $where);

        $orders = $db->fetchAll(
            "SELECT o.*, s.nom AS service_nom, s.icon AS service_icon,
                    u.id AS user_id, u.email, u.prenom, u.nom
             FROM editorial_orders o
             JOIN editorial_services s ON s.id = o.service_id
             JOIN users u ON u.id = o.user_id
             WHERE {$whereClause}
             ORDER BY o.created_at DESC",
            $params
        );

        $this->adminView('editorial/list', [
            'titre'  => 'Services éditoriaux',
            'orders' => $orders,
            'statut' => $statut,
            'q'      => $q,
        ]);
    }

    public function editorialOrderDetail(string $id): void
    {
        Auth::requireAdmin();
        $db = $this->db();
        $order = $db->fetch(
            "SELECT o.*, s.nom AS service_nom, s.slug AS service_slug, s.icon AS service_icon, s.sur_devis,
                    u.id AS u_id, u.email, u.prenom, u.nom
             FROM editorial_orders o
             JOIN editorial_services s ON s.id = o.service_id
             JOIN users u ON u.id = o.user_id
             WHERE o.id = ?",
            [(int) $id]
        );
        if (!$order) {
            Session::flash('admin_error', 'Commande introuvable.');
            redirect('/admin/services-editoriaux');
            return;
        }

        $this->adminView('editorial/detail', [
            'titre' => 'Commande #' . $order->id,
            'order' => $order,
        ]);
    }

    public function sendQuote(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $orderId = (int) $id;
        $order = $db->fetch("SELECT id, user_id, statut FROM editorial_orders WHERE id = ?", [$orderId]);
        if (!$order) { redirect('/admin/services-editoriaux'); return; }

        $montant = (float) ($_POST['montant_propose'] ?? 0);
        $devise  = strtoupper(trim($_POST['devise'] ?? 'USD'));
        $notes   = trim($_POST['notes_admin'] ?? '');

        if ($montant <= 0) {
            Session::flash('admin_error', 'Montant invalide.');
            redirect('/admin/services-editoriaux/' . $orderId);
            return;
        }

        $db->update('editorial_orders', [
            'statut'           => 'devis_envoye',
            'montant_propose'  => $montant,
            'devise'           => in_array($devise, ['USD','EUR','CDF','CAD'], true) ? $devise : 'USD',
            'notes_admin'      => $notes ?: null,
        ], 'id = ?', [$orderId]);

        Notification::create(
            (int) $order->user_id,
            'editorial_quote',
            'Devis reçu pour ta commande',
            'On t\'a envoyé un devis de ' . number_format($montant, 2) . ' ' . $devise . '. Connecte-toi pour le voir.',
            '/auteur/mes-commandes-editoriales/' . $orderId,
            'mail'
        );

        Session::flash('admin_success', 'Devis envoyé.');
        redirect('/admin/services-editoriaux/' . $orderId);
    }

    public function updateOrderStatus(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $orderId = (int) $id;
        $order = $db->fetch("SELECT id, user_id FROM editorial_orders WHERE id = ?", [$orderId]);
        if (!$order) { redirect('/admin/services-editoriaux'); return; }

        $newStatut = $_POST['statut'] ?? '';
        $valides = ['en_attente_devis','devis_envoye','accepte','en_cours','livre','annule','rembourse'];
        if (!in_array($newStatut, $valides, true)) {
            Session::flash('admin_error', 'Statut invalide.');
            redirect('/admin/services-editoriaux/' . $orderId);
            return;
        }

        $update = ['statut' => $newStatut];
        $note = trim($_POST['notes_admin'] ?? '');
        if ($note !== '') $update['notes_admin'] = $note;

        $db->update('editorial_orders', $update, 'id = ?', [$orderId]);

        Notification::create(
            (int) $order->user_id,
            'editorial_status',
            'Mise à jour de ta commande',
            'Le statut est passé à : ' . $newStatut . '.',
            '/auteur/mes-commandes-editoriales/' . $orderId,
            'bell'
        );

        Session::flash('admin_success', 'Statut mis à jour.');
        redirect('/admin/services-editoriaux/' . $orderId);
    }

    public function uploadDelivery(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();
        $db = $this->db();
        $orderId = (int) $id;
        $order = $db->fetch("SELECT id, user_id FROM editorial_orders WHERE id = ?", [$orderId]);
        if (!$order) { redirect('/admin/services-editoriaux'); return; }

        if (empty($_FILES['delivery']['tmp_name']) || ($_FILES['delivery']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Session::flash('admin_error', 'Aucun fichier reçu.');
            redirect('/admin/services-editoriaux/' . $orderId);
            return;
        }

        $allowed = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        $mime = $_FILES['delivery']['type'] ?? '';
        if (!isset($allowed[$mime]) || $_FILES['delivery']['size'] > 100 * 1024 * 1024) {
            Session::flash('admin_error', 'Format non supporté ou fichier trop lourd (max 100 Mo).');
            redirect('/admin/services-editoriaux/' . $orderId);
            return;
        }

        $ext = $allowed[$mime];
        $name = 'delivery-' . $orderId . '-' . time() . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
        $rel = 'storage/editorial/deliveries/' . $name;
        $abs = BASE_PATH . '/' . $rel;
        if (!is_dir(dirname($abs))) mkdir(dirname($abs), 0755, true);
        if (!move_uploaded_file($_FILES['delivery']['tmp_name'], $abs)) {
            Session::flash('admin_error', 'Échec de l\'enregistrement du fichier.');
            redirect('/admin/services-editoriaux/' . $orderId);
            return;
        }

        $db->update('editorial_orders', [
            'fichier_livraison_url' => $rel,
            'statut'                => 'livre',
            'livre_at'              => date('Y-m-d H:i:s'),
        ], 'id = ?', [$orderId]);

        Notification::create(
            (int) $order->user_id,
            'editorial_delivered',
            'Ta commande est livrée !',
            'Connecte-toi pour télécharger ton livrable.',
            '/auteur/mes-commandes-editoriales/' . $orderId,
            'check'
        );

        Session::flash('admin_success', 'Livraison enregistrée et auteur notifié.');
        redirect('/admin/services-editoriaux/' . $orderId);
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
