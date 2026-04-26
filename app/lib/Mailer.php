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
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        $env = function_exists('env') ? env('APP_ENV', 'development') : 'development';
        $bcc = self::adminBccEmail();

        if ($env === 'production') {
            return self::sendViaResend($to, $subject, $body, $bcc);
        }

        return self::sendToLogFile($to, $subject, $body, $bcc);
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
    private static function sendViaResend(string $to, string $subject, string $html, string $bcc = ''): bool
    {
        $apiKey = env('RESEND_API_KEY', '');
        if ($apiKey === '') {
            error_log('Resend non configuré : RESEND_API_KEY manquante. Email vers ' . $to . ' non envoyé.');
            return self::sendToLogFile($to, $subject, $html, $bcc);
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
    private static function sendToLogFile(string $to, string $subject, string $body, string $bcc = ''): bool
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
        $entry .= "CONTENU :" . PHP_EOL;
        $entry .= $separator . PHP_EOL;
        $entry .= strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], PHP_EOL, $body)) . PHP_EOL;
        $entry .= $separator . PHP_EOL;

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        return true;
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
}
