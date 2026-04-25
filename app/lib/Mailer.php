<?php
namespace App\Lib;

/**
 * Classe Mailer — Envoi d'emails
 * Mode développement : écrit les mails dans logs/mails.log
 * À remplacer par PHPMailer en production
 */
class Mailer
{
    /**
     * Envoyer un email (simulation en développement)
     * En mode local, écrit dans logs/mails.log au lieu d'envoyer réellement
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        $logFile = BASE_PATH . '/logs/mails.log';
        $date = date('Y-m-d H:i:s');
        $separator = str_repeat('=', 70);

        $entry = PHP_EOL . $separator . PHP_EOL;
        $entry .= "DATE    : {$date}" . PHP_EOL;
        $entry .= "À       : {$to}" . PHP_EOL;
        $entry .= "SUJET   : {$subject}" . PHP_EOL;
        $entry .= "CONTENU :" . PHP_EOL;
        $entry .= $separator . PHP_EOL;
        $entry .= strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], PHP_EOL, $body)) . PHP_EOL;
        $entry .= $separator . PHP_EOL;

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        return true;
    }

    /**
     * Envoyer l'email de vérification d'adresse email
     */
    public static function sendVerificationEmail(object $user, string $token): bool
    {
        $url = env('APP_URL') . '/verifier-email/' . $token;
        $appName = env('APP_NAME', 'Les éditions Variable');

        $body = "
        <h2>Bienvenue sur {$appName}, {$user->prenom} !</h2>
        <p>Merci pour votre inscription. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
        <p><a href=\"{$url}\">{$url}</a></p>
        <p>Ce lien est valable pendant 48 heures.</p>
        <p>Si vous n'avez pas créé de compte, ignorez simplement cet email.</p>
        <br>
        <p>Cordialement,<br>L'équipe {$appName}</p>
        ";

        return self::send(
            $user->email,
            "Vérifiez votre adresse email — {$appName}",
            $body
        );
    }

    /**
     * Envoyer l'email de réinitialisation du mot de passe
     */
    public static function sendPasswordResetEmail(object $user, string $token): bool
    {
        $url = env('APP_URL') . '/reset-password/' . $token;
        $appName = env('APP_NAME', 'Les éditions Variable');

        $body = "
        <h2>Réinitialisation de votre mot de passe</h2>
        <p>Bonjour {$user->prenom},</p>
        <p>Vous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous :</p>
        <p><a href=\"{$url}\">{$url}</a></p>
        <p>Ce lien est valable pendant 1 heure. Si vous n'avez pas fait cette demande, ignorez cet email.</p>
        <br>
        <p>Cordialement,<br>L'équipe {$appName}</p>
        ";

        return self::send(
            $user->email,
            "Réinitialisation de mot de passe — {$appName}",
            $body
        );
    }

    /**
     * Envoyer l'email de bienvenue après vérification
     */
    public static function sendWelcomeEmail(object $user): bool
    {
        $appName = env('APP_NAME', 'Les éditions Variable');
        $appUrl = env('APP_URL');

        $body = "
        <h2>Votre compte est activé !</h2>
        <p>Bonjour {$user->prenom},</p>
        <p>Votre adresse email a été vérifiée avec succès. Votre compte sur {$appName} est maintenant pleinement actif.</p>
        <p>Vous pouvez dès à présent :</p>
        <ul>
            <li>Parcourir notre catalogue de livres</li>
            <li>Acheter des ebooks d'auteurs africains francophones</li>
            <li>Souscrire à un abonnement pour un accès illimité</li>
        </ul>
        <p><a href=\"{$appUrl}/connexion\">Se connecter</a></p>
        <br>
        <p>Bonne lecture !<br>L'équipe {$appName}</p>
        ";

        return self::send(
            $user->email,
            "Bienvenue sur {$appName} !",
            $body
        );
    }

    /**
     * Confirmation d'annulation d'abonnement (l'accès reste actif jusqu'à dateFin)
     */
    public static function sendSubscriptionCancellation(object $user, string $dateFin): bool
    {
        $appName = env('APP_NAME', 'Les éditions Variable');
        $dateLisible = date('d/m/Y', strtotime($dateFin));
        $body = "
        <h2>Ton abonnement a été annulé</h2>
        <p>Bonjour {$user->prenom},</p>
        <p>Nous avons bien enregistré l'annulation de ton abonnement. Tu gardes l'accès au catalogue jusqu'au <strong>{$dateLisible}</strong>.</p>
        <p>Tu peux le réactiver à tout moment avant cette date depuis ton compte.</p>
        <br>
        <p>À bientôt sur {$appName} !</p>
        ";
        return self::send($user->email, "Annulation de ton abonnement — {$appName}", $body);
    }

    /**
     * Email avec lien de confirmation pour suppression de compte (RGPD)
     */
    public static function sendDeletionRequest(object $user, string $token): bool
    {
        $url = env('APP_URL') . '/supprimer-compte/confirmer/' . $token;
        $appName = env('APP_NAME', 'Les éditions Variable');
        $body = "
        <h2>Confirmation de suppression de compte</h2>
        <p>Bonjour {$user->prenom},</p>
        <p>Tu as demandé la suppression de ton compte sur {$appName}. Pour confirmer, clique sur ce lien dans les 24 heures :</p>
        <p><a href=\"{$url}\">{$url}</a></p>
        <p>Si tu n'as pas demandé cette suppression, ignore simplement cet email — ton compte ne sera pas supprimé.</p>
        <br>
        <p>L'équipe {$appName}</p>
        ";
        return self::send($user->email, "Confirmation de suppression de compte — {$appName}", $body);
    }

    /**
     * Email final après suppression effective
     */
    public static function sendDeletionFinal(string $email, string $prenom): bool
    {
        $appName = env('APP_NAME', 'Les éditions Variable');
        $body = "
        <h2>Ton compte a été supprimé</h2>
        <p>Bonjour {$prenom},</p>
        <p>Ton compte sur {$appName} a été supprimé conformément à ta demande.</p>
        <p>Merci d'avoir fait partie de l'aventure.</p>
        <br>
        <p>L'équipe {$appName}</p>
        ";
        return self::send($email, "Ton compte a été supprimé — {$appName}", $body);
    }
}
