<?php
namespace App\Lib;

/**
 * Classe Mailer — Envoi d'emails transactionnels.
 *
 * - Production : Resend API
 * - Dev/staging : écriture dans logs/mails.log
 *
 * BCC automatique : tous les emails sont copiés à l'adresse de l'admin
 * (variable d'env ADMIN_BCC_EMAIL, fallback angellomuhiya@gmail.com)
 * pour permettre de superviser ce qui sort de la plateforme.
 *
 * Templates : les helpers utilisent renderTemplate() qui charge les
 * vues depuis app/views/emails/*.php (wrappées par layout.php).
 */
class Mailer
{
    /**
     * Envoyer un email — wrap automatiquement avec BCC admin.
     *
     * $attachments : tableau de pièces jointes au format
     *   [['filename' => 'recu.pdf', 'content' => '<binaire ou base64>', 'content_type' => 'application/pdf']]
     * Si la valeur de 'content' est binaire brute, elle sera encodée en base64 pour Resend.
     *
     * $context : contexte facultatif pour le tracking en DB (table email_log) :
     *   ['template' => 'welcome', 'user_id' => 1, 'sequence_id' => 1, 'sequence_step' => 2]
     */
    public static function send(string $to, string $subject, string $body, array $attachments = [], array $context = []): bool
    {
        $env = function_exists('env') ? env('APP_ENV', 'development') : 'development';
        $bcc = self::adminBccEmail();

        if ($env === 'production') {
            $providerId = '';
            $errMsg = null;
            $ok = self::sendViaResend($to, $subject, $body, $bcc, $attachments, $providerId, $errMsg);
            self::logToDb($to, $subject, $context, $ok ? 'sent' : 'error', $providerId, $errMsg);
            return $ok;
        }

        $ok = self::sendToLogFile($to, $subject, $body, $bcc, $attachments);
        // En dev on logge aussi pour pouvoir tester l'historique admin
        self::logToDb($to, $subject, $context, $ok ? 'sent' : 'error', '', null);
        return $ok;
    }

    /**
     * Persiste une entrée dans email_log (best-effort : un échec d'INSERT
     * ne fait pas crasher l'envoi).
     */
    private static function logToDb(string $to, string $subject, array $context, string $result, string $providerId = '', ?string $errMsg = null): void
    {
        try {
            \App\Lib\Database::getInstance()->insert('email_log', [
                'user_id'       => $context['user_id'] ?? null,
                'to_email'      => $to,
                'template'      => $context['template'] ?? null,
                'subject'       => mb_substr($subject, 0, 300),
                'sequence_id'   => $context['sequence_id'] ?? null,
                'sequence_step' => $context['sequence_step'] ?? null,
                'result'        => $result === 'sent' ? 'sent' : 'error',
                'error_message' => $errMsg,
                'provider_id'   => $providerId ?: null,
                'sent_at'       => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('Mailer::logToDb — ' . $e->getMessage());
        }
    }

    /**
     * Rendu d'un template email avec wrap layout.
     * Retourne le HTML complet prêt à être envoyé.
     */
    public static function renderTemplate(string $template, array $data = []): string
    {
        $templateFile = BASE_PATH . '/app/views/emails/' . $template . '.php';
        if (!file_exists($templateFile)) {
            throw new \RuntimeException("Template email introuvable : {$template}");
        }

        // Variables globales utiles à tous les templates
        $data['appName'] = $data['appName'] ?? (function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable');
        $data['appUrl']  = $data['appUrl']  ?? rtrim((string) (function_exists('env') ? env('APP_URL', 'https://leseditionsvariable.com') : 'https://leseditionsvariable.com'), '/');

        extract($data, EXTR_SKIP);

        ob_start();
        require $templateFile;
        return (string) ob_get_clean();
    }

    /**
     * Envoi réel via Resend API (production), avec BCC admin.
     */
    private static function sendViaResend(string $to, string $subject, string $html, string $bcc = '', array $attachments = [], string &$providerId = '', ?string &$errMsg = null): bool
    {
        $apiKey = env('RESEND_API_KEY', '');
        if ($apiKey === '') {
            $errMsg = 'RESEND_API_KEY manquante';
            error_log('Resend non configuré : RESEND_API_KEY manquante. Email vers ' . $to . ' non envoyé.');
            return self::sendToLogFile($to, $subject, $html, $bcc, $attachments);
        }

        $fromEmail = env('MAIL_FROM_EMAIL', 'contact@leseditionsvariable.com');
        $fromName  = env('MAIL_FROM_NAME', 'Les éditions Variable');
        $from      = $fromName . ' <' . $fromEmail . '>';

        $payload = [
            'from'    => $from,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
        ];

        // Ne pas BCC quand on s'envoie à soi-même (évite le doublon dans la boîte admin)
        if ($bcc !== '' && strcasecmp($bcc, $to) !== 0) {
            $payload['bcc'] = [$bcc];
        }

        // Pièces jointes : Resend attend chaque fichier au format
        // { "filename": "...", "content": "<base64>" } (ou "path" pour URL).
        if (!empty($attachments)) {
            $payload['attachments'] = array_map(static function (array $a): array {
                $content = (string) ($a['content'] ?? '');
                // Si pas déjà encodé en base64, on encode (heuristique : un PDF brut commence par "%PDF")
                if ($content !== '' && !preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $content)) {
                    $content = base64_encode($content);
                }
                return [
                    'filename' => $a['filename'] ?? 'piece-jointe.bin',
                    'content'  => $content,
                ];
            }, $attachments);
        }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $decoded = json_decode((string) $response, true);
            if (is_array($decoded) && !empty($decoded['id'])) {
                $providerId = (string) $decoded['id'];
            }
            return true;
        }

        $errMsg = "HTTP {$httpCode} — " . mb_substr((string) $response, 0, 250);
        error_log("Resend a échoué (HTTP {$httpCode}) pour {$to} — {$subject} : " . $response);
        return false;
    }

    /**
     * Envoi simulé : écriture dans logs/mails.log (dev/staging).
     * Inclut le BCC dans l'entête du log pour visibilité.
     */
    private static function sendToLogFile(string $to, string $subject, string $body, string $bcc = '', array $attachments = []): bool
    {
        $logFile = BASE_PATH . '/logs/mails.log';
        $date = date('Y-m-d H:i:s');
        $separator = str_repeat('=', 70);

        $entry = PHP_EOL . $separator . PHP_EOL;
        $entry .= "DATE    : {$date}" . PHP_EOL;
        $entry .= "À       : {$to}" . PHP_EOL;
        if ($bcc !== '' && strcasecmp($bcc, $to) !== 0) {
            $entry .= "BCC     : {$bcc}" . PHP_EOL;
        }
        $entry .= "SUJET   : {$subject}" . PHP_EOL;
        if (!empty($attachments)) {
            $names = array_map(static fn (array $a) => ($a['filename'] ?? '?') . ' (' . self::humanSize(strlen((string) ($a['content'] ?? ''))) . ')', $attachments);
            $entry .= "PJ      : " . implode(', ', $names) . PHP_EOL;
        }
        $entry .= "CONTENU :" . PHP_EOL;
        $entry .= $separator . PHP_EOL;
        $entry .= strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], PHP_EOL, $body)) . PHP_EOL;
        $entry .= $separator . PHP_EOL;

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        // En dev/staging on dump la PJ à côté pour pouvoir vérifier visuellement
        if (!empty($attachments)) {
            $dir = BASE_PATH . '/logs/mails-attachments';
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            foreach ($attachments as $i => $a) {
                $fn = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($a['filename'] ?? ('pj-' . $i . '.bin')));
                $content = (string) ($a['content'] ?? '');
                // Si déjà base64 (heuristique : que des chars b64 et longueur multiple de 4), on décode pour le dump
                if ($content !== '' && preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $content) && strlen(rtrim($content)) % 4 === 0) {
                    $decoded = base64_decode($content, true);
                    if ($decoded !== false) { $content = $decoded; }
                }
                file_put_contents($dir . '/' . date('Ymd-His') . '-' . $fn, $content);
            }
        }

        return true;
    }

    /**
     * Format lisible d'une taille en octets (3.2 KB, 1.4 MB, etc.).
     */
    private static function humanSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / (1024 * 1024), 2) . ' MB';
    }

    /**
     * Adresse BCC administrateur — configurable via env ADMIN_BCC_EMAIL,
     * sinon par défaut angellomuhiya@gmail.com (supervision des envois).
     */
    private static function adminBccEmail(): string
    {
        return (string) (function_exists('env')
            ? env('ADMIN_BCC_EMAIL', 'angellomuhiya@gmail.com')
            : 'angellomuhiya@gmail.com');
    }

    // =====================================================================
    // Helpers — emails transactionnels (templates)
    // =====================================================================

    public static function sendVerificationEmail(object $user, string $token): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('verification', compact('user', 'token'));
        return self::send($user->email, "Vérifie ton adresse — {$appName}", $html, [], ['template' => 'verification', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    public static function sendPasswordResetEmail(object $user, string $token): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('password_reset', compact('user', 'token'));
        return self::send($user->email, "Nouveau mot de passe — {$appName}", $html, [], ['template' => 'password_reset', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    public static function sendWelcomeEmail(object $user): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('welcome', compact('user'));
        return self::send($user->email, "Bienvenue sur {$appName} !", $html, [], ['template' => 'welcome', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    public static function sendSubscriptionCancellation(object $user, string $dateFin): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('subscription_cancellation', compact('user', 'dateFin'));
        return self::send($user->email, "Annulation de ton abonnement — {$appName}", $html, [], ['template' => 'subscription_cancellation', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    public static function sendDeletionRequest(object $user, string $token): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('deletion_request', compact('user', 'token'));
        return self::send($user->email, "Confirmation de suppression de compte — {$appName}", $html, [], ['template' => 'deletion_request', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    public static function sendDeletionFinal(string $email, string $prenom): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('deletion_final', compact('email', 'prenom'));
        return self::send($email, "Ton compte a été supprimé — {$appName}", $html, [], ['template' => 'deletion_final', 'user_id' => null]);
    }

    public static function sendNewsletterWelcome(string $email, string $prenom = ''): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('newsletter_welcome', compact('prenom'));
        return self::send($email, "Bienvenue dans la newsletter — {$appName}", $html, [], ['template' => 'newsletter_welcome', 'user_id' => null]);
    }

    public static function sendAuthorCandidatureReceived(object $user): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('author_candidature_received', compact('user'));
        return self::send($user->email, "Candidature reçue — {$appName}", $html, [], ['template' => 'author_candidature_received', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    public static function sendBookSubmitted(object $user, string $titreLivre): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('book_submitted', compact('user', 'titreLivre'));
        return self::send($user->email, "Livre soumis — {$appName}", $html, [], ['template' => 'book_submitted', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    /**
     * Notif admin : nouvelle candidature auteur.
     * Adresse cible : MAIL_FROM_EMAIL (l'équipe) — le BCC à angello est ajouté automatiquement.
     */
    public static function sendAdminCandidatureNotif(object $user): bool
    {
        $to = function_exists('env') ? env('MAIL_FROM_EMAIL', 'contact@leseditionsvariable.com') : 'contact@leseditionsvariable.com';
        $html = self::renderTemplate('admin_new_candidature', compact('user'));
        return self::send($to, '🆕 Nouvelle candidature auteur — ' . trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')), $html, [], ['template' => 'admin_new_candidature', 'user_id' => null]);
    }

    /**
     * Notif admin : nouveau livre soumis.
     */
    public static function sendAdminBookNotif(object $user, string $titreLivre): bool
    {
        $to = function_exists('env') ? env('MAIL_FROM_EMAIL', 'contact@leseditionsvariable.com') : 'contact@leseditionsvariable.com';
        $html = self::renderTemplate('admin_new_book', compact('user', 'titreLivre'));
        return self::send($to, '📚 Nouveau livre soumis — ' . $titreLivre, $html, [], ['template' => 'admin_new_book', 'user_id' => null]);
    }

    // =====================================================================
    // Helpers — emails de paiement (achat livre, abonnement, renouvellement)
    // =====================================================================

    /**
     * Reçu de paiement (achat livre OU abonnement).
     * Joint un PDF généré à la volée via dompdf.
     *
     * @param string $kind            'book' ou 'subscription'
     * @param string $itemLabel       Titre du livre ou label du plan
     * @param float  $amount          Montant payé
     * @param string $currency        Devise ISO (USD, EUR, CDF, ...)
     * @param string $paymentMethod   'stripe' | 'money_fusion' | autre
     * @param string $transactionId   ID transaction provider (peut être vide)
     * @param string $dateIso         Date du paiement (Y-m-d H:i:s)
     */
    public static function sendPaymentReceipt(
        object $user,
        string $kind,
        string $itemLabel,
        float $amount,
        string $currency,
        string $paymentMethod,
        string $transactionId = '',
        ?string $dateIso = null
    ): bool {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $dateIso = $dateIso ?: date('Y-m-d H:i:s');

        $payload = compact('user', 'kind', 'itemLabel', 'amount', 'currency', 'paymentMethod', 'transactionId', 'dateIso');
        $html = self::renderTemplate('payment_receipt', $payload);

        // Génération du PDF — si dompdf échoue, on envoie l'email sans pièce jointe
        $attachments = [];
        try {
            $pdfBin = \App\Lib\ReceiptPdf::render($payload);
            $attachments[] = [
                'filename' => \App\Lib\ReceiptPdf::suggestedFilename($payload),
                'content'  => $pdfBin, // sera base64 par sendViaResend
            ];
        } catch (\Throwable $e) {
            error_log('Mailer::sendPaymentReceipt — génération PDF échouée : ' . $e->getMessage());
        }

        $subject = $kind === 'subscription'
            ? "Reçu — Abonnement {$itemLabel} | {$appName}"
            : "Reçu — Achat « {$itemLabel} » | {$appName}";

        return self::send($user->email, $subject, $html, $attachments, ['template' => 'payment_receipt', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    /**
     * Rappel J-3 avant renouvellement automatique d'abonnement.
     */
    public static function sendRenewalReminder(
        object $user,
        string $planLabel,
        float $amount,
        string $currency,
        string $dateRenewIso
    ): bool {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('subscription_renewal_reminder', compact('user', 'planLabel', 'amount', 'currency', 'dateRenewIso'));
        return self::send($user->email, "Ton abonnement se renouvelle dans 3 jours — {$appName}", $html, [], ['template' => 'subscription_renewal_reminder', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    /**
     * Confirmation de renouvellement réussi.
     */
    public static function sendSubscriptionRenewed(
        object $user,
        string $planLabel,
        float $amount,
        string $currency,
        string $dateNextRenewIso,
        string $transactionId = ''
    ): bool {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('subscription_renewed', compact('user', 'planLabel', 'amount', 'currency', 'dateNextRenewIso', 'transactionId'));
        return self::send($user->email, "Abonnement renouvelé — {$appName}", $html, [], ['template' => 'subscription_renewed', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    /**
     * Échec de paiement (renouvellement) — invite à mettre à jour la carte.
     */
    public static function sendPaymentFailed(
        object $user,
        string $planLabel,
        float $amount,
        string $currency,
        string $dateRetryIso,
        int $attemptsRemaining = 1
    ): bool {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('payment_failed', compact('user', 'planLabel', 'amount', 'currency', 'dateRetryIso', 'attemptsRemaining'));
        return self::send($user->email, "⚠️ Échec du paiement de ton abonnement — {$appName}", $html, [], ['template' => 'payment_failed', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    // =====================================================================
    // Drip campaign — séquence d'onboarding (J+0/J+2/J+7/J+14/J+30)
    // =====================================================================
    //
    // Chaque helper applique sa propre logique de skip. Si la condition de
    // skip est remplie, le helper retourne false sans envoyer (le cron Phase 3
    // utilise ça pour avancer dans la séquence sans envoi inutile).
    //
    // L'enrichissement des données (top 3, nouveautés...) est fait ici pour
    // que les helpers soient indépendants — le cron passe juste un user.
    // =====================================================================

    /**
     * J+2 : "Découvre les 3 best-sellers".
     * Skip si l'user a déjà commencé à lire un livre (reading_progress).
     *
     * @return bool true si l'email a été envoyé, false si skip ou échec
     */
    public static function sendDripDay2(object $user): bool
    {
        $db = \App\Lib\Database::getInstance();

        // Skip : déjà commencé à lire
        $hasRead = $db->fetch("SELECT 1 FROM reading_progress WHERE user_id = ? LIMIT 1", [$user->id]);
        if ($hasRead) return false;

        // Top 3 livres les plus lus, publiés
        $books = $db->fetchAll(
            "SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom, ' ', u.nom)) AS author_display
               FROM books b
               JOIN authors a ON a.id = b.author_id
               JOIN users u ON u.id = a.user_id
              WHERE b.statut = 'publie'
              ORDER BY b.total_lectures DESC, b.total_ventes DESC
              LIMIT 3"
        );

        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('drip_day2', compact('user', 'books'));
        return self::send($user->email, "3 livres qui marchent fort sur Variable | {$appName}", $html, [], ['template' => 'drip_day2', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    /**
     * J+7 : "Pourquoi t'abonner".
     * Skip si l'user a déjà un abonnement actif.
     */
    public static function sendDripDay7(object $user): bool
    {
        $db = \App\Lib\Database::getInstance();

        $hasSub = $db->fetch(
            "SELECT 1 FROM subscriptions WHERE user_id = ? AND statut = 'actif' LIMIT 1",
            [$user->id]
        );
        if ($hasSub) return false;

        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('drip_day7', compact('user'));
        return self::send($user->email, "Et si tu lisais sans limite pour 3\$/mois ? | {$appName}", $html, [], ['template' => 'drip_day7', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    /**
     * J+14 : "Nouveautés de la semaine".
     * Pas de skip — envoyé à tous (info catalogue, valable même pour abonnés).
     */
    public static function sendDripDay14(object $user): bool
    {
        $db = \App\Lib\Database::getInstance();

        // 3 derniers livres publiés
        $books = $db->fetchAll(
            "SELECT b.*, COALESCE(a.nom_plume, CONCAT(u.prenom, ' ', u.nom)) AS author_display
               FROM books b
               JOIN authors a ON a.id = b.author_id
               JOIN users u ON u.id = a.user_id
              WHERE b.statut = 'publie'
              ORDER BY b.date_publication DESC, b.id DESC
              LIMIT 3"
        );

        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('drip_day14', compact('user', 'books'));
        return self::send($user->email, "Nouveautés Variable | {$appName}", $html, [], ['template' => 'drip_day14', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    /**
     * J+30 : "On t'a oublié ?" — code promo de réactivation.
     * Cible : user inactif (pas de connexion depuis 14 jours OU jamais reconnecté).
     * Skip si user récemment actif (a ouvert le site dans les 14 derniers jours).
     *
     * Génère un code promo unique de 20% valable 30 jours via App\Lib\PromoCode.
     * Idempotent : si un code 'drip_day30' existe déjà pour ce user, on le réutilise.
     */
    public static function sendDripDay30(object $user): bool
    {
        $db = \App\Lib\Database::getInstance();

        // Skip si actif récemment (connexion < 14 jours)
        $row = $db->fetch("SELECT derniere_connexion FROM users WHERE id = ?", [$user->id]);
        if ($row && $row->derniere_connexion && strtotime((string) $row->derniere_connexion) > strtotime('-14 days')) {
            return false;
        }

        // Code promo : réutilise un code 'drip_day30' actif si présent, sinon en génère un
        $existing = \App\Lib\PromoCode::findActiveForUser((int) $user->id, 'drip_day30');
        if ($existing) {
            $promoCode    = (string) $existing->code;
            $discountPct  = (int) $existing->discount_pct;
            $validUntilIso = (string) ($existing->valid_until ?: date('Y-m-d', strtotime('+30 days')));
        } else {
            $promoCode    = \App\Lib\PromoCode::generateForUser(
                (int) $user->id, 20, 30, 'drip_day30', 'REVIENS'
            );
            $discountPct  = 20;
            $validUntilIso = date('Y-m-d', strtotime('+30 days'));
        }

        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('drip_day30', compact('user', 'promoCode', 'discountPct', 'validUntilIso'));
        return self::send($user->email, "On t'a oublié ? Tiens, -{$discountPct}% pour revenir | {$appName}", $html, [], ['template' => 'drip_day30', 'user_id' => (int) ($user->id ?? 0) ?: null]);
    }

    // =====================================================================
    // Helpers — versements aux auteurs (sprint Payouts)
    // =====================================================================

    /**
     * Confirmation à l'auteur que sa demande de versement est bien reçue.
     */
    public static function sendPayoutRequested(object $user, float $amount, string $method): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('payout_requested', compact('user', 'amount', 'method'));
        $amountFmt = number_format($amount, 2);
        return self::send($user->email, "Demande de versement reçue ({$amountFmt} USD) — {$appName}", $html, [], [
            'template' => 'payout_requested', 'user_id' => (int) ($user->id ?? 0) ?: null,
        ]);
    }

    /**
     * Notif admin : nouvelle demande de versement à traiter.
     * Adresse cible : MAIL_FROM_EMAIL (l'équipe), BCC angello automatique.
     */
    public static function sendAdminPayoutRequest(object $user, string $authorName, float $amount, string $method): bool
    {
        $to = function_exists('env') ? env('MAIL_FROM_EMAIL', 'contact@leseditionsvariable.com') : 'contact@leseditionsvariable.com';
        $html = self::renderTemplate('admin_new_payout_request', compact('user', 'authorName', 'amount', 'method'));
        $amountFmt = number_format($amount, 2);
        $name = $authorName !== '' ? $authorName : trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
        return self::send($to, "💰 Nouvelle demande versement {$amountFmt} USD — {$name}", $html, [], [
            'template' => 'admin_new_payout_request', 'user_id' => null,
        ]);
    }

    /**
     * Confirmation à l'auteur que son versement a été effectué (avec référence).
     */
    public static function sendPayoutProcessed(object $user, float $amount, string $method, string $reference): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('payout_processed', compact('user', 'amount', 'method', 'reference'));
        $amountFmt = number_format($amount, 2);
        return self::send($user->email, "Versement effectué ({$amountFmt} USD) — {$appName}", $html, [], [
            'template' => 'payout_processed', 'user_id' => (int) ($user->id ?? 0) ?: null,
        ]);
    }

    /**
     * Notif à l'auteur d'un refus de versement, avec le motif clair pour
     * qu'il puisse corriger et re-demander (le montant reste disponible).
     */
    public static function sendPayoutRejected(object $user, float $amount, string $reason): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('payout_rejected', compact('user', 'amount', 'reason'));
        return self::send($user->email, "Demande de versement refusée — {$appName}", $html, [], [
            'template' => 'payout_rejected', 'user_id' => (int) ($user->id ?? 0) ?: null,
        ]);
    }
}
