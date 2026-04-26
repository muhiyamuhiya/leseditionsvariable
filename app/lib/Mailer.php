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
     */
    public static function send(string $to, string $subject, string $body, array $attachments = []): bool
    {
        $env = function_exists('env') ? env('APP_ENV', 'development') : 'development';
        $bcc = self::adminBccEmail();

        if ($env === 'production') {
            return self::sendViaResend($to, $subject, $body, $bcc, $attachments);
        }

        return self::sendToLogFile($to, $subject, $body, $bcc, $attachments);
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
    private static function sendViaResend(string $to, string $subject, string $html, string $bcc = '', array $attachments = []): bool
    {
        $apiKey = env('RESEND_API_KEY', '');
        if ($apiKey === '') {
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
            return true;
        }

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
        return self::send($user->email, "Vérifie ton adresse — {$appName}", $html);
    }

    public static function sendPasswordResetEmail(object $user, string $token): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('password_reset', compact('user', 'token'));
        return self::send($user->email, "Nouveau mot de passe — {$appName}", $html);
    }

    public static function sendWelcomeEmail(object $user): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('welcome', compact('user'));
        return self::send($user->email, "Bienvenue sur {$appName} !", $html);
    }

    public static function sendSubscriptionCancellation(object $user, string $dateFin): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('subscription_cancellation', compact('user', 'dateFin'));
        return self::send($user->email, "Annulation de ton abonnement — {$appName}", $html);
    }

    public static function sendDeletionRequest(object $user, string $token): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('deletion_request', compact('user', 'token'));
        return self::send($user->email, "Confirmation de suppression de compte — {$appName}", $html);
    }

    public static function sendDeletionFinal(string $email, string $prenom): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('deletion_final', compact('email', 'prenom'));
        return self::send($email, "Ton compte a été supprimé — {$appName}", $html);
    }

    public static function sendNewsletterWelcome(string $email, string $prenom = ''): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('newsletter_welcome', compact('prenom'));
        return self::send($email, "Bienvenue dans la newsletter — {$appName}", $html);
    }

    public static function sendAuthorCandidatureReceived(object $user): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('author_candidature_received', compact('user'));
        return self::send($user->email, "Candidature reçue — {$appName}", $html);
    }

    public static function sendBookSubmitted(object $user, string $titreLivre): bool
    {
        $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
        $html = self::renderTemplate('book_submitted', compact('user', 'titreLivre'));
        return self::send($user->email, "Livre soumis — {$appName}", $html);
    }

    /**
     * Notif admin : nouvelle candidature auteur.
     * Adresse cible : MAIL_FROM_EMAIL (l'équipe) — le BCC à angello est ajouté automatiquement.
     */
    public static function sendAdminCandidatureNotif(object $user): bool
    {
        $to = function_exists('env') ? env('MAIL_FROM_EMAIL', 'contact@leseditionsvariable.com') : 'contact@leseditionsvariable.com';
        $html = self::renderTemplate('admin_new_candidature', compact('user'));
        return self::send($to, '🆕 Nouvelle candidature auteur — ' . trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')), $html);
    }

    /**
     * Notif admin : nouveau livre soumis.
     */
    public static function sendAdminBookNotif(object $user, string $titreLivre): bool
    {
        $to = function_exists('env') ? env('MAIL_FROM_EMAIL', 'contact@leseditionsvariable.com') : 'contact@leseditionsvariable.com';
        $html = self::renderTemplate('admin_new_book', compact('user', 'titreLivre'));
        return self::send($to, '📚 Nouveau livre soumis — ' . $titreLivre, $html);
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

        return self::send($user->email, $subject, $html, $attachments);
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
        return self::send($user->email, "Ton abonnement se renouvelle dans 3 jours — {$appName}", $html);
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
        return self::send($user->email, "Abonnement renouvelé — {$appName}", $html);
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
        return self::send($user->email, "⚠️ Échec du paiement de ton abonnement — {$appName}", $html);
    }
}
