<?php
namespace App\Lib;

use App\Models\User;

/**
 * Classe Auth — Gestion de l'authentification
 * Connexion, déconnexion, vérification des droits
 */
class Auth
{
    /** @var object|null Cache de l'utilisateur connecté */
    private static ?object $cachedUser = null;

    /**
     * Tenter de connecter un utilisateur
     * Retourne true si la connexion réussit, false sinon
     */
    public static function login(string $email, string $password): bool
    {
        $user = User::findByEmail($email);

        // Utilisateur introuvable
        if (!$user) {
            Session::flash('error', 'Email ou mot de passe incorrect.');
            return false;
        }

        // Compte bloqué
        if (User::isBlocked($user->id)) {
            $minutes = ceil((strtotime($user->bloque_jusqu_a) - time()) / 60);
            Session::flash('error', "Compte temporairement bloqué. Réessayez dans {$minutes} minute(s).");
            return false;
        }

        // Compte désactivé
        if (!$user->actif) {
            Session::flash('error', 'Ce compte a été désactivé. Contactez le support.');
            return false;
        }

        // Email non vérifié
        if (!$user->email_verifie) {
            Session::flash('error', 'Veuillez d\'abord vérifier votre adresse email. Consultez votre boîte de réception.');
            return false;
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $user->password_hash)) {
            User::incrementFailedAttempts($user->id);
            $remaining = 5 - ($user->nombre_tentatives_echec + 1);
            if ($remaining > 0) {
                Session::flash('error', "Email ou mot de passe incorrect. {$remaining} tentative(s) restante(s).");
            } else {
                Session::flash('error', 'Trop de tentatives échouées. Compte bloqué pendant 15 minutes.');
            }
            return false;
        }

        // Connexion réussie
        User::resetFailedAttempts($user->id);
        Session::set('user_id', $user->id);
        self::$cachedUser = null;

        Session::flash('success', "Bienvenue, {$user->prenom} !");
        return true;
    }

    /**
     * Déconnecter l'utilisateur
     */
    public static function logout(): void
    {
        self::$cachedUser = null;
        Session::destroy();
    }

    /**
     * Vérifier si un utilisateur est connecté
     */
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Récupérer l'objet User de l'utilisateur connecté
     */
    public static function user(): ?object
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser === null) {
            $user = User::find((int) Session::get('user_id'));
            self::$cachedUser = $user ?: null;
        }

        return self::$cachedUser;
    }

    /**
     * Récupérer l'ID de l'utilisateur connecté
     */
    public static function id(): ?int
    {
        return self::check() ? (int) Session::get('user_id') : null;
    }

    /**
     * Exiger une connexion — redirige vers /connexion si pas connecté
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Vous devez être connecté pour accéder à cette page.');
            redirect('/connexion');
        }
    }

    /**
     * Exiger un rôle spécifique — redirige si pas le bon rôle
     */
    public static function requireRole(string $role): void
    {
        self::requireLogin();

        $user = self::user();
        if (!$user || $user->role !== $role) {
            Session::flash('error', 'Vous n\'avez pas les permissions nécessaires.');
            redirect('/');
        }
    }

    /**
     * Exiger le rôle admin
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        $user = self::user();
        if (!$user || $user->role !== 'admin') {
            http_response_code(403);
            redirect('/');
        }
    }
}
