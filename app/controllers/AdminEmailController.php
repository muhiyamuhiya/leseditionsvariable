<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Mailer;
use App\Lib\ReceiptPdf;
use App\Lib\Session;

/**
 * Admin — preview & test des templates emails.
 *
 * Routes (déclarées dans public_html/index.php) :
 *   GET  /admin/emails                          → liste des templates
 *   GET  /admin/emails/preview/:template         → vue admin avec iframe
 *   GET  /admin/emails/preview/:template?raw=1   → HTML brut (pour iframe)
 *   POST /admin/emails/preview/:template/test    → envoi à l'admin connecté
 */
class AdminEmailController extends BaseController
{
    /**
     * Catalogue des templates avec leurs données fictives par défaut.
     * Format : 'template_slug' => ['label' => str, 'fixtures' => array, 'has_pdf' => bool]
     */
    private function templates(): array
    {
        $fakeUser = (object) [
            'id' => 1, 'prenom' => 'Aïcha', 'nom' => 'Mbenza',
            'email' => 'aicha.test@example.com',
        ];

        return [
            // === Phase 1 (paiement) ===
            'payment_receipt' => [
                'label'   => 'Reçu de paiement (livre OU abonnement)',
                'category'=> 'paiement',
                'has_pdf' => true,
                'fixtures'=> [
                    'user'          => $fakeUser,
                    'kind'          => 'subscription',
                    'itemLabel'     => 'Premium Mensuel',
                    'amount'        => 8.00,
                    'currency'      => 'USD',
                    'paymentMethod' => 'stripe',
                    'transactionId' => 'cs_test_a1b2c3d4',
                    'dateIso'       => date('Y-m-d H:i:s'),
                ],
            ],
            'subscription_renewal_reminder' => [
                'label'   => 'Rappel J-3 avant renouvellement',
                'category'=> 'paiement',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'         => $fakeUser,
                    'planLabel'    => 'Essentiel Annuel',
                    'amount'       => 30.00,
                    'currency'     => 'USD',
                    'dateRenewIso' => date('Y-m-d', strtotime('+3 days')),
                ],
            ],
            'subscription_renewed' => [
                'label'   => 'Renouvellement réussi',
                'category'=> 'paiement',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'             => $fakeUser,
                    'planLabel'        => 'Essentiel Mensuel',
                    'amount'           => 3.00,
                    'currency'         => 'USD',
                    'dateNextRenewIso' => date('Y-m-d', strtotime('+30 days')),
                    'transactionId'    => 'in_test_renew_999',
                ],
            ],
            'payment_failed' => [
                'label'   => 'Échec de paiement',
                'category'=> 'paiement',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'              => $fakeUser,
                    'planLabel'         => 'Premium Mensuel',
                    'amount'            => 8.00,
                    'currency'          => 'USD',
                    'dateRetryIso'      => date('Y-m-d', strtotime('+3 days')),
                    'attemptsRemaining' => 2,
                ],
            ],
            // === Onboarding ===
            'welcome' => [
                'label'   => 'Bienvenue (après inscription)',
                'category'=> 'onboarding',
                'has_pdf' => false,
                'fixtures'=> ['user' => $fakeUser],
            ],
            'drip_day2' => [
                'label'   => 'Drip J+2 — 3 best-sellers',
                'category'=> 'onboarding',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'  => $fakeUser,
                    'books' => self::fakeBooks(),
                ],
            ],
            'drip_day7' => [
                'label'   => 'Drip J+7 — Pourquoi t\'abonner',
                'category'=> 'onboarding',
                'has_pdf' => false,
                'fixtures'=> ['user' => $fakeUser],
            ],
            'drip_day14' => [
                'label'   => 'Drip J+14 — Nouveautés',
                'category'=> 'onboarding',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'  => $fakeUser,
                    'books' => self::fakeBooks(),
                ],
            ],
            'drip_day30' => [
                'label'   => 'Drip J+30 — Réactivation -20%',
                'category'=> 'onboarding',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'           => $fakeUser,
                    'promoCode'      => 'REVIENS-A1B2C3',
                    'discountPct'    => 20,
                    'validUntilIso'  => date('Y-m-d', strtotime('+30 days')),
                ],
            ],
            'verification' => [
                'label'   => 'Vérification email',
                'category'=> 'onboarding',
                'has_pdf' => false,
                'fixtures'=> ['user' => $fakeUser, 'token' => 'fake_verification_token_abc123'],
            ],
            'password_reset' => [
                'label'   => 'Réinitialisation mot de passe',
                'category'=> 'onboarding',
                'has_pdf' => false,
                'fixtures'=> ['user' => $fakeUser, 'token' => 'fake_reset_token_xyz789'],
            ],
            'newsletter_welcome' => [
                'label'   => 'Bienvenue newsletter',
                'category'=> 'onboarding',
                'has_pdf' => false,
                'fixtures'=> ['prenom' => 'Aïcha'],
            ],
            // === Compte ===
            'subscription_cancellation' => [
                'label'   => 'Annulation abonnement',
                'category'=> 'compte',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'    => $fakeUser,
                    'dateFin' => date('Y-m-d', strtotime('+22 days')),
                ],
            ],
            'deletion_request' => [
                'label'   => 'Demande de suppression compte',
                'category'=> 'compte',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'  => $fakeUser,
                    'token' => 'fake_deletion_token_def456',
                ],
            ],
            'deletion_final' => [
                'label'   => 'Suppression compte confirmée',
                'category'=> 'compte',
                'has_pdf' => false,
                'fixtures'=> [
                    'email'  => $fakeUser->email,
                    'prenom' => $fakeUser->prenom,
                ],
            ],
            // === Auteur ===
            'author_candidature_received' => [
                'label'   => 'Candidature auteur reçue',
                'category'=> 'auteur',
                'has_pdf' => false,
                'fixtures'=> ['user' => $fakeUser],
            ],
            'book_submitted' => [
                'label'   => 'Livre soumis',
                'category'=> 'auteur',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'       => $fakeUser,
                    'titreLivre' => 'Les rivières du Kasaï',
                ],
            ],
            // === Notifs admin ===
            'admin_new_candidature' => [
                'label'   => 'Notif admin : nouvelle candidature',
                'category'=> 'admin',
                'has_pdf' => false,
                'fixtures'=> ['user' => $fakeUser],
            ],
            'admin_new_book' => [
                'label'   => 'Notif admin : nouveau livre',
                'category'=> 'admin',
                'has_pdf' => false,
                'fixtures'=> [
                    'user'       => $fakeUser,
                    'titreLivre' => 'Les rivières du Kasaï',
                ],
            ],
        ];
    }

    /**
     * Données fictives utilisées pour les drip templates qui affichent des livres.
     */
    private static function fakeBooks(): array
    {
        return [
            (object) [
                'titre' => 'L\'amour fou de Kinshasa',
                'slug' => 'amour-fou-kinshasa',
                'description_courte' => 'Une histoire d\'amour brûlante dans le Kinshasa contemporain, entre tradition et modernité.',
                'author_display' => 'Aïcha Mbenza',
                'date_publication' => date('Y-m-d', strtotime('-3 days')),
                'couverture_url_web' => '',
                'couverture_path' => '',
            ],
            (object) [
                'titre' => 'Les rivières du Kasaï',
                'slug' => 'rivieres-kasai',
                'description_courte' => 'Saga familiale qui suit trois générations au cœur de la forêt équatoriale.',
                'author_display' => 'Patrice Lualua',
                'date_publication' => date('Y-m-d', strtotime('-7 days')),
                'couverture_url_web' => '',
                'couverture_path' => '',
            ],
            (object) [
                'titre' => 'Mémoires d\'un griot urbain',
                'slug' => 'memoires-griot-urbain',
                'description_courte' => 'Le récit autobiographique d\'un musicien congolais devenu écrivain à Montréal.',
                'author_display' => 'Joseph Kibambe',
                'date_publication' => date('Y-m-d', strtotime('-12 days')),
                'couverture_url_web' => '',
                'couverture_path' => '',
            ],
        ];
    }

    /**
     * Afficher une vue admin dans le layout admin.
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

    /**
     * GET /admin/emails
     * Liste de tous les templates groupés par catégorie.
     */
    public function index(): void
    {
        Auth::requireAdmin();

        // Groupement par catégorie
        $grouped = [];
        foreach ($this->templates() as $slug => $tpl) {
            $cat = $tpl['category'] ?? 'autre';
            $grouped[$cat][$slug] = $tpl;
        }

        $this->adminView('emails/index', [
            'titre'    => 'Emails — Aperçu & test',
            'grouped'  => $grouped,
        ]);
    }

    /**
     * GET /admin/emails/preview/:template
     * Vue admin avec iframe ou — si ?raw=1 — HTML brut pour l'iframe.
     */
    public function preview(string $template): void
    {
        Auth::requireAdmin();

        $catalog = $this->templates();
        if (!isset($catalog[$template])) {
            Session::flash('error', "Template inconnu : {$template}");
            redirect('/admin/emails');
            return;
        }
        $tpl = $catalog[$template];

        // Mode "raw" : on renvoie juste le HTML rendu (utilisé par l'iframe)
        if (($_GET['raw'] ?? '') === '1') {
            $html = Mailer::renderTemplate($template, $tpl['fixtures']);
            header('Content-Type: text/html; charset=utf-8');
            // X-Frame-Options DENY est posé globalement par .htaccess — on l'override pour same-origin
            header('X-Frame-Options: SAMEORIGIN');
            echo $html;
            exit;
        }

        $this->adminView('emails/preview', [
            'titre'    => 'Aperçu : ' . $tpl['label'],
            'template' => $template,
            'tpl'      => $tpl,
            'allTemplates' => $catalog,
        ]);
    }

    /**
     * POST /admin/emails/preview/:template/test
     * Envoie le template (avec données fictives) à l'admin connecté.
     */
    public function sendTest(string $template): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $catalog = $this->templates();
        if (!isset($catalog[$template])) {
            Session::flash('error', "Template inconnu : {$template}");
            redirect('/admin/emails');
            return;
        }

        $admin = Auth::user();
        $tpl   = $catalog[$template];

        // On utilise l'email de l'admin connecté comme destinataire ET on remplace
        // le user fictif des fixtures pour cohérence (sinon "Bonjour Aïcha" envoyé à Angello).
        $fixtures = $tpl['fixtures'];
        if (isset($fixtures['user']) && is_object($fixtures['user'])) {
            $fixtures['user'] = (object) [
                'id'     => $admin->id,
                'prenom' => $admin->prenom,
                'nom'    => $admin->nom,
                'email'  => $admin->email,
            ];
        }

        try {
            $html = Mailer::renderTemplate($template, $fixtures);

            $attachments = [];
            if (!empty($tpl['has_pdf'])) {
                $pdf = ReceiptPdf::render($fixtures);
                $attachments[] = [
                    'filename' => ReceiptPdf::suggestedFilename($fixtures),
                    'content'  => $pdf,
                ];
            }

            $subject = '[TEST] ' . $tpl['label'];
            $ok = Mailer::send($admin->email, $subject, $html, $attachments, [
                'template' => $template,
                'user_id'  => (int) $admin->id,
            ]);

            if ($ok) {
                Session::flash('success', "Email test « {$tpl['label']} » envoyé à {$admin->email}.");
            } else {
                Session::flash('error', "Échec d'envoi (voir logs/error.log).");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Erreur : ' . $e->getMessage());
            error_log('AdminEmailController::sendTest — ' . $e->getMessage());
        }

        redirect('/admin/emails/preview/' . $template);
    }

    /**
     * GET /admin/emails/sequences
     * Liste des séquences avec stats par statut (running/completed/cancelled).
     */
    public function sequences(): void
    {
        Auth::requireAdmin();
        $db = Database::getInstance();

        $sequences = $db->fetchAll(
            "SELECT s.*,
                    (SELECT COUNT(*) FROM email_sequence_steps WHERE sequence_id = s.id) AS nb_steps,
                    (SELECT COUNT(*) FROM email_user_progress WHERE sequence_id = s.id AND status = 'running')   AS nb_running,
                    (SELECT COUNT(*) FROM email_user_progress WHERE sequence_id = s.id AND status = 'completed') AS nb_completed,
                    (SELECT COUNT(*) FROM email_user_progress WHERE sequence_id = s.id AND status IN ('cancelled', 'paused')) AS nb_cancelled
               FROM email_sequences s
              ORDER BY s.id ASC"
        );

        // Détail des steps pour chaque séquence
        foreach ($sequences as $seq) {
            $seq->steps = $db->fetchAll(
                "SELECT * FROM email_sequence_steps WHERE sequence_id = ? ORDER BY sort_order",
                [$seq->id]
            );
        }

        $this->adminView('emails/sequences', [
            'titre'     => 'Emails — Séquences',
            'sequences' => $sequences,
        ]);
    }

    /**
     * POST /admin/emails/sequences/:id/toggle
     * Active/désactive une séquence (la flag active=0 stoppe le cron sur ses steps).
     */
    public function toggleSequence(string $id): void
    {
        Auth::requireAdmin();
        CSRF::check();

        $db  = Database::getInstance();
        $seq = $db->fetch("SELECT id, slug, active FROM email_sequences WHERE id = ?", [(int) $id]);
        if (!$seq) {
            Session::flash('error', 'Séquence introuvable.');
            redirect('/admin/emails/sequences');
            return;
        }

        $newActive = $seq->active ? 0 : 1;
        $db->update('email_sequences', ['active' => $newActive], 'id = ?', [$seq->id]);

        Session::flash('success',
            $newActive
                ? "Séquence « {$seq->slug} » activée."
                : "Séquence « {$seq->slug} » désactivée."
        );
        redirect('/admin/emails/sequences');
    }

    /**
     * GET /admin/emails/sent
     * Historique des envois email_log avec filtres :
     *   - q (email destinataire LIKE)
     *   - template (slug)
     *   - result (sent | error)
     *   - from / to (dates ISO)
     */
    public function sent(): void
    {
        Auth::requireAdmin();
        $db = Database::getInstance();

        $q        = trim((string) ($_GET['q'] ?? ''));
        $template = trim((string) ($_GET['template'] ?? ''));
        $result   = trim((string) ($_GET['result'] ?? ''));
        $from     = trim((string) ($_GET['from'] ?? ''));
        $to       = trim((string) ($_GET['to'] ?? ''));
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $perPage  = 50;

        $where  = ['1=1'];
        $params = [];
        if ($q !== '')        { $where[] = 'l.to_email LIKE ?';    $params[] = '%' . $q . '%'; }
        if ($template !== '') { $where[] = 'l.template = ?';        $params[] = $template; }
        if ($result !== '')   { $where[] = 'l.result = ?';          $params[] = $result; }
        if ($from !== '')     { $where[] = 'l.sent_at >= ?';        $params[] = $from . ' 00:00:00'; }
        if ($to !== '')       { $where[] = 'l.sent_at <= ?';        $params[] = $to . ' 23:59:59'; }

        $whereSql = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int) ($db->fetch("SELECT COUNT(*) AS c FROM email_log l WHERE {$whereSql}", $params)->c ?? 0);

        $rows = $db->fetchAll(
            "SELECT l.*, u.prenom, u.nom
               FROM email_log l
          LEFT JOIN users u ON u.id = l.user_id
              WHERE {$whereSql}
              ORDER BY l.sent_at DESC
              LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Liste des templates distincts pour le filter dropdown
        $templates = $db->fetchAll(
            "SELECT DISTINCT template FROM email_log WHERE template IS NOT NULL ORDER BY template"
        );

        $this->adminView('emails/sent', [
            'titre'     => 'Emails — Historique des envois',
            'rows'      => $rows,
            'templates' => array_map(fn ($r) => $r->template, $templates),
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'filters'   => compact('q', 'template', 'result', 'from', 'to'),
        ]);
    }
}
