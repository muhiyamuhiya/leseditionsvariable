<?php
namespace App\Controllers;

use App\Lib\CSRF;
use App\Lib\Database;
use App\Lib\Mailer;
use App\Lib\Session;

/**
 * Inscription newsletter mensuelle (publique)
 */
class NewsletterController extends BaseController
{
    public function subscribe(): void
    {
        CSRF::check();

        $email = trim($_POST['email'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '') ?: null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('newsletter_error', 'Email invalide.');
            redirect('/newsletter');
            return;
        }

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id, unsubscribed_at FROM newsletter_subscribers WHERE email = ?", [$email]);

        if ($existing) {
            // Réactiver une éventuelle désinscription précédente
            if ($existing->unsubscribed_at !== null) {
                $db->update('newsletter_subscribers', [
                    'unsubscribed_at' => null,
                    'prenom'          => $prenom,
                ], 'id = ?', [$existing->id]);
                Session::flash('newsletter_success', 'Re-bienvenue ! Tu es à nouveau abonné à notre newsletter.');
            } else {
                Session::flash('newsletter_success', 'Tu es déjà abonné. À très vite dans ta boîte mail.');
            }
        } else {
            $token = bin2hex(random_bytes(32));
            $db->insert('newsletter_subscribers', [
                'email'              => $email,
                'prenom'             => $prenom,
                'confirmation_token' => $token,
                'created_at'         => date('Y-m-d H:i:s'),
            ]);

            // Email de bienvenue (pas de double opt-in pour MVP, on envoie direct)
            $appName = function_exists('env') ? env('APP_NAME', 'Les éditions Variable') : 'Les éditions Variable';
            $bonjour = $prenom ? 'Bonjour ' . htmlspecialchars($prenom) : 'Bonjour';
            Mailer::send($email, "Bienvenue dans la newsletter — {$appName}", "
                <h2>Merci de ton inscription !</h2>
                <p>{$bonjour},</p>
                <p>Tu recevras notre prochaine édition très bientôt. Une fois par mois, on te raconte les coulisses, les nouveaux livres, les conseils d'auteurs et les codes promo.</p>
                <p>Pas de spam. Promis.</p>
                <p>L'équipe {$appName}</p>
            ");

            Session::flash('newsletter_success', 'Bienvenue ! Tu recevras notre prochaine édition.');
        }

        redirect('/newsletter');
    }
}
