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

        try {
            // Slug : on en génère un unique à partir du nom de plume saisi (ou prenom+nom).
            // Si l'auteur a déjà un row (re-soumission), on conserve son slug existant.
            $existing = $db->fetch("SELECT id, slug FROM authors WHERE user_id = ?", [$user->id]);
            if ($existing) {
                $slug = (string) $existing->slug;
            } else {
                $base = trim((string) ($_POST['nom_plume'] ?? '')) ?: ($user->prenom . ' ' . $user->nom);
                $slug = \App\Models\Author::createUniqueSlug($base);
            }

            $data = [
                'nom_plume'          => trim((string) ($_POST['nom_plume'] ?? '')) ?: null,
                'slug'               => $slug,
                'biographie_courte'  => trim((string) ($_POST['biographie_courte'] ?? '')),
                'biographie_longue'  => trim((string) ($_POST['biographie_longue'] ?? '')),
                'pays_origine'       => trim((string) ($_POST['pays_origine'] ?? '')) ?: null,
                'ville_residence'    => trim((string) ($_POST['ville_residence'] ?? '')) ?: null,
                'site_web'           => trim((string) ($_POST['site_web'] ?? '')) ?: null,
                'facebook_url'       => trim((string) ($_POST['facebook_url'] ?? '')) ?: null,
                'instagram_url'      => trim((string) ($_POST['instagram_url'] ?? '')) ?: null,
                'twitter_x_url'      => trim((string) ($_POST['twitter_x_url'] ?? '')) ?: null,
                'linkedin_url'       => trim((string) ($_POST['linkedin_url'] ?? '')) ?: null,
                'methode_versement'  => $_POST['methode_versement'] ?? 'mobile_money',
                'numero_mobile_money'=> trim((string) ($_POST['numero_mobile_money'] ?? '')) ?: null,
                'email_paypal'       => trim((string) ($_POST['email_paypal'] ?? '')) ?: null,
                'statut_validation'  => 'en_attente',
            ];

            // Upload photo (best-effort)
            if (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
                $file = $_FILES['photo'];
                if (in_array($file['type'], ['image/jpeg','image/png','image/webp'], true) && $file['size'] <= 2 * 1024 * 1024) {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $filename = $slug . '-' . time() . '.' . $ext;
                    $absPath = BASE_PATH . '/storage/authors/' . $filename;
                    if (!is_dir(dirname($absPath))) mkdir(dirname($absPath), 0755, true);
                    if (move_uploaded_file($file['tmp_name'], $absPath)) {
                        $data['photo_auteur'] = '/image/authors/' . $filename;
                    }
                }
            }

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

            // Emails (best-effort — un échec mailer ne doit pas bloquer la candidature)
            try {
                Mailer::sendAdminCandidatureNotif($user);
                Mailer::sendAuthorCandidatureReceived($user);
            } catch (\Throwable $e) {
                error_log('submitApplication mail : ' . $e->getMessage());
            }

            Session::flash('success', 'Ta candidature a été soumise avec succès.');
            redirect('/auteur');
        } catch (\Throwable $e) {
            // Catch fatal/Error/TypeError pour éviter la page blanche en prod
            error_log('AuthorDashboardController::submitApplication : ' . $e->getMessage());
            Session::flash('error', 'Une erreur technique est survenue, ta candidature n\'a pas pu être enregistrée. Réessaie ou contacte le support.');
            redirect('/auteur/candidater');
        }
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

        if (!$author) {
            Session::flash('error', 'Tu dois d\'abord déposer ta candidature d\'auteur.');
            redirect('/auteur/candidater');
            return;
        }
        if ($author->statut_validation === 'en_attente') {
            Session::flash('error', 'Ta candidature est en cours de revue. Tu pourras publier une fois validée.');
            redirect('/auteur');
            return;
        }
        if ($author->statut_validation !== 'valide') {
            Session::flash('error', 'Ta candidature n\'a pas été validée. Contacte l\'équipe pour plus d\'infos.');
            redirect('/auteur');
            return;
        }

        $categories = Category::findActive();
        $this->authorView('books/create', ['titre' => 'Nouveau livre', 'categories' => $categories]);
    }

    public function storeBook(): void
    {
        Auth::requireAuthor();
        CSRF::check();

        $author = Auth::getAuthorRecord();

        // Cas 1 : pas de candidature du tout (admin promu sans row authors, ou bug)
        if (!$author) {
            Session::flash('error', 'Tu dois d\'abord déposer ta candidature d\'auteur.');
            redirect('/auteur/candidater');
            return;
        }

        // Cas 2 : candidature en attente
        if ($author->statut_validation === 'en_attente') {
            Session::flash('error', 'Ta candidature est en cours de revue. Tu pourras publier une fois validée.');
            redirect('/auteur');
            return;
        }

        // Cas 3 : candidature refusée
        if ($author->statut_validation !== 'valide') {
            Session::flash('error', 'Ta candidature n\'a pas été validée. Contacte l\'équipe pour plus d\'infos.');
            redirect('/auteur');
            return;
        }

        $db = $this->db();
        $titreRaw = trim((string) ($_POST['titre'] ?? ''));
        if ($titreRaw === '') {
            Session::flash('error', 'Le titre du livre est obligatoire.');
            redirect('/auteur/livres/nouveau');
            return;
        }

        $slug = trim((string) ($_POST['slug'] ?? '')) ?: \App\Models\Author::slugify($titreRaw);

        $bookData = [
            'author_id'             => (int) $author->id,
            'titre'                 => $titreRaw,
            'slug'                  => $slug,
            'sous_titre'            => trim((string) ($_POST['sous_titre'] ?? '')) ?: null,
            'category_id'           => (int) ($_POST['category_id'] ?? 0) ?: null,
            'description_courte'    => trim((string) ($_POST['description_courte'] ?? '')),
            'description_longue'    => trim((string) ($_POST['description_longue'] ?? '')),
            'mots_cles'             => trim((string) ($_POST['mots_cles'] ?? '')),
            'isbn'                  => trim((string) ($_POST['isbn'] ?? '')) ?: null,
            'langue'                => trim((string) ($_POST['langue'] ?? 'fr')),
            'nombre_pages'          => (int) ($_POST['nombre_pages'] ?? 0) ?: null,
            'prix_unitaire_usd'     => (float) ($_POST['prix_unitaire_usd'] ?? 9.99),
            'statut'                => 'en_revue',
            'editeur'               => 'Les éditions Variable',
            'accessible_abonnement_essentiel' => isset($_POST['accessible_abonnement_essentiel']) ? 1 : 0,
            'accessible_abonnement_premium'   => isset($_POST['accessible_abonnement_premium']) ? 1 : 0,
        ];

        try {
            $id = $db->insert('books', $bookData);
        } catch (\Throwable $e) {
            error_log('AuthorDashboardController::storeBook insert : ' . $e->getMessage());
            Session::flash('error', 'Impossible d\'enregistrer le livre. Vérifie que le titre n\'est pas déjà utilisé et réessaie.');
            redirect('/auteur/livres/nouveau');
            return;
        }

        if (!$id) {
            Session::flash('error', 'Erreur d\'enregistrement. Réessaie.');
            redirect('/auteur/livres/nouveau');
            return;
        }

        // Upload couverture (best-effort)
        try {
            if (!empty($_FILES['couverture']['tmp_name']) && is_uploaded_file($_FILES['couverture']['tmp_name'])) {
                $file = $_FILES['couverture'];
                if (in_array($file['type'], ['image/jpeg','image/png','image/webp'], true) && $file['size'] <= 2 * 1024 * 1024) {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $fn  = $slug . '-' . time() . '.' . $ext;
                    $abs = BASE_PATH . '/storage/covers/' . $fn;
                    if (!is_dir(dirname($abs))) mkdir(dirname($abs), 0755, true);
                    if (move_uploaded_file($file['tmp_name'], $abs)) {
                        $db->update('books', [
                            'couverture_path'    => 'storage/covers/' . $fn,
                            'couverture_url_web' => '/image/covers/' . $fn,
                        ], 'id = ?', [$id]);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('storeBook cover upload : ' . $e->getMessage());
        }

        // Upload PDF + extrait (best-effort)
        try {
            if (!empty($_FILES['manuscrit']['tmp_name']) && is_uploaded_file($_FILES['manuscrit']['tmp_name'])) {
                $file = $_FILES['manuscrit'];
                if ($file['type'] === 'application/pdf' && $file['size'] <= 50 * 1024 * 1024) {
                    $pdfDir     = BASE_PATH . '/storage/books/';
                    $extractDir = BASE_PATH . '/storage/extracts/';
                    if (!is_dir($pdfDir))     mkdir($pdfDir, 0755, true);
                    if (!is_dir($extractDir)) mkdir($extractDir, 0755, true);

                    $pdfPath     = $pdfDir . $slug . '.pdf';
                    $extractPath = $extractDir . $slug . '-extrait.pdf';

                    if (move_uploaded_file($file['tmp_name'], $pdfPath)) {
                        $update = ['fichier_complet_path' => 'storage/books/' . $slug . '.pdf'];
                        try {
                            PDFProcessor::generateExtract($pdfPath, $extractPath, FREE_PREVIEW_PAGES);
                            if (file_exists($extractPath)) {
                                $update['fichier_extrait_path'] = 'storage/extracts/' . $slug . '-extrait.pdf';
                            }
                        } catch (\Throwable $e) {
                            error_log('storeBook extract gen : ' . $e->getMessage());
                        }
                        $db->update('books', $update, 'id = ?', [$id]);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('storeBook pdf upload : ' . $e->getMessage());
        }

        // Emails (best-effort — un échec mailer ne doit jamais 500 sur un upload)
        try {
            $user = Auth::user();
            Mailer::sendAdminBookNotif($user, $bookData['titre']);
            Mailer::sendBookSubmitted($user, $bookData['titre']);
        } catch (\Throwable $e) {
            error_log('storeBook mail notif : ' . $e->getMessage());
        }

        Session::flash('author_success', 'Ton livre a été soumis pour validation.');
        redirect('/auteur/livres');
    }

    /**
     * Aperçu d'un livre par l'auteur, dans le contexte du dashboard auteur.
     * Sécurité : un auteur ne peut prévisualiser que ses propres livres
     * (filtrage par author_id). Toute tentative sur un livre tiers retourne
     * un flash error + redirect vers la liste — pas de 403 brut pour rester
     * proche du flux UX habituel.
     */
    public function previewBook(string $slug): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();
        if (!$author) {
            Session::flash('error', 'Tu dois d\'abord déposer ta candidature d\'auteur.');
            redirect('/auteur/candidater');
            return;
        }

        $db = $this->db();
        $book = $db->fetch(
            "SELECT b.*, c.nom AS cat_nom
               FROM books b
          LEFT JOIN categories c ON c.id = b.category_id
              WHERE b.slug = ? AND b.author_id = ?",
            [$slug, $author->id]
        );
        if (!$book) {
            Session::flash('error', 'Livre introuvable ou tu n\'es pas l\'auteur.');
            redirect('/auteur/livres');
            return;
        }

        $this->authorView('books/preview', [
            'titre'  => 'Aperçu : ' . $book->titre,
            'book'   => $book,
            'author' => $author,
        ]);
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
        // L'historique exclut les rows internes 'available' (créés par le cron pool)
        // pour ne montrer que les vraies demandes de versement.
        $versements = $this->db()->fetchAll(
            "SELECT * FROM author_payouts
              WHERE author_id = ?
                AND statut IN ('requested','en_cours','verse','refuse','annule','echec')
              ORDER BY created_at DESC",
            [$author->id]
        );
        $this->authorView('payouts', ['titre' => 'Mes versements', 'versements' => $versements]);
    }

    /**
     * GET /auteur/revenus
     * Vue agrégée : totaux, solde versable, bouton "Demander un versement".
     */
    public function showRevenues(): void
    {
        Auth::requireAuthor();
        $author = Auth::getAuthorRecord();
        if (!$author) { redirect('/auteur'); return; }
        if ($author->statut_validation !== 'valide') { redirect('/auteur'); return; }

        $balance = \App\Lib\PayoutBalance::compute((int) $author->id);

        // Détail par livre (top 10 + total) — pour le tableau "mes livres"
        $byBook = $this->db()->fetchAll(
            "SELECT b.id, b.titre, b.slug,
                    COUNT(s.id)                         AS nb_ventes,
                    COALESCE(SUM(s.revenu_auteur), 0)   AS revenu_total,
                    b.total_pages_lues_cumul            AS pages_lues
               FROM books b
          LEFT JOIN sales s ON s.book_id = b.id AND s.statut = 'payee'
              WHERE b.author_id = ?
              GROUP BY b.id
              ORDER BY revenu_total DESC, b.created_at DESC
              LIMIT 50",
            [$author->id]
        );

        // Settings affichés (pour transparence)
        $seuil = (float) ($this->db()->fetch("SELECT `value` FROM settings WHERE `key` = 'seuil_minimum_versement_usd'")->value ?? PAYOUT_MIN_USD);

        $this->authorView('revenus', [
            'titre'   => 'Mes revenus',
            'author'  => $author,
            'balance' => $balance,
            'byBook'  => $byBook,
            'seuil'   => $seuil,
        ]);
    }

    /**
     * POST /auteur/versements/demander
     * Crée une row author_payouts statut='requested' agrégeant le solde
     * disponible. Annule les rows 'available' préexistants (déjà inclus).
     */
    public function requestPayout(): void
    {
        Auth::requireAuthor();
        CSRF::check();
        $author = Auth::getAuthorRecord();
        if (!$author || $author->statut_validation !== 'valide') {
            Session::flash('error', 'Tu dois être un auteur validé pour demander un versement.');
            redirect('/auteur');
            return;
        }

        $balance = \App\Lib\PayoutBalance::compute((int) $author->id);
        $seuilRow = $this->db()->fetch("SELECT `value` FROM settings WHERE `key` = 'seuil_minimum_versement_usd'");
        $seuil = (float) ($seuilRow->value ?? PAYOUT_MIN_USD);

        if ($balance['available'] < $seuil) {
            Session::flash('error', 'Solde disponible insuffisant. Minimum requis : ' . number_format($seuil, 2) . ' $ — disponible : ' . number_format($balance['available'], 2) . ' $.');
            redirect('/auteur/revenus');
            return;
        }

        // La méthode peut être ré-affirmée via le form (au cas où l'auteur
        // veut changer sa préférence avant la demande)
        $methodPosted = $_POST['methode_versement'] ?? null;
        $allowedMethods = ['mobile_money', 'banque', 'paypal', 'stripe'];
        if ($methodPosted && in_array($methodPosted, $allowedMethods, true)) {
            $this->db()->update('authors', ['methode_versement' => $methodPosted], 'id = ?', [$author->id]);
            $author = Auth::getAuthorRecord(); // refresh
        }

        $accountSnapshot = \App\Lib\PayoutBalance::snapshotAccount($author);
        if ($accountSnapshot === '') {
            Session::flash('error', 'Tes coordonnées de paiement sont vides. Renseigne-les dans ton profil avant de demander un versement.');
            redirect('/auteur/profil');
            return;
        }

        $db = $this->db();

        // Période = du dernier versement traité (verse ou requested ou en_cours)
        // jusqu'à maintenant. Si aucun, depuis l'inscription de l'auteur.
        $lastPayout = $db->fetch(
            "SELECT created_at FROM author_payouts
              WHERE author_id = ? AND statut IN ('verse','requested','en_cours')
              ORDER BY created_at DESC LIMIT 1",
            [$author->id]
        );
        $periodeDebut = $lastPayout ? date('Y-m-d', strtotime($lastPayout->created_at)) : date('Y-m-d', strtotime($author->created_at));

        // Insertion de la demande
        $payoutId = $db->insert('author_payouts', [
            'author_id'                  => (int) $author->id,
            'periode_debut'              => $periodeDebut,
            'periode_fin'                => date('Y-m-d'),
            'revenus_ventes_unitaires'   => $balance['sales_total'] - $balance['total_paid'] - $balance['total_pending'],
            'revenus_pool_abonnement'    => $balance['pool_available'],
            'total_a_verser'             => $balance['available'],
            'devise'                     => 'USD',
            'methode_versement'          => (string) $author->methode_versement,
            'statut'                     => 'requested',
            'requested_at'               => date('Y-m-d H:i:s'),
            'requested_method'           => (string) $author->methode_versement,
            'requested_account_snapshot' => $accountSnapshot,
        ]);

        // Annule les rows 'available' qui ont été agrégés dans cette demande
        if ($payoutId) {
            $db->update(
                'author_payouts',
                ['statut' => 'annule', 'notes' => 'Agrégé dans la demande #' . (int) $payoutId],
                "author_id = ? AND statut = 'available' AND id != ?",
                [$author->id, $payoutId]
            );
        }

        // Notification interne admin
        try {
            \App\Lib\Notification::createForAdmins(
                'payout_requested',
                'Nouvelle demande de versement',
                ($author->nom_plume ?: 'Un auteur') . ' demande ' . number_format($balance['available'], 2) . ' $ via ' . str_replace('_', ' ', (string) $author->methode_versement) . '.',
                '/admin/finances',
                'check'
            );
        } catch (\Throwable $e) {
            error_log('requestPayout notification : ' . $e->getMessage());
        }

        // Emails (commit 5 — best-effort, ne plante pas si helpers manquants)
        $user = Auth::user();
        try {
            if (method_exists(Mailer::class, 'sendPayoutRequested')) {
                Mailer::sendPayoutRequested($user, $balance['available'], (string) $author->methode_versement);
            }
            if (method_exists(Mailer::class, 'sendAdminPayoutRequest')) {
                Mailer::sendAdminPayoutRequest($user, (string) ($author->nom_plume ?: ''), $balance['available'], (string) $author->methode_versement);
            }
        } catch (\Throwable $e) {
            error_log('requestPayout mail : ' . $e->getMessage());
        }

        Session::flash('author_success', 'Demande envoyée : ' . number_format($balance['available'], 2) . ' $. On traite ça sous quelques jours.');
        redirect('/auteur/versements');
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
