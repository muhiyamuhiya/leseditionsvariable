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

            // Email de bienvenue stylé (template + BCC admin auto)
            Mailer::sendNewsletterWelcome($email, (string) $prenom);

            Session::flash('newsletter_success', 'Bienvenue ! Tu recevras notre prochaine édition.');
        }

        redirect('/newsletter');
    }
}
