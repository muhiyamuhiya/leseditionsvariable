<?php
namespace App\Lib;

/**
 * Wrapper sécurisé pour les sessions PHP
 * Gère le démarrage, la régénération d'ID et les messages flash
 */
class Session
{
    /**
     * Démarrer la session avec des paramètres sécurisés
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = (int) Env::get('SESSION_LIFETIME', 7200);

        // Paramètres de sécurité des cookies de session
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        // HTTPS uniquement en production
        if (Env::get('APP_ENV') === 'production') {
            ini_set('session.cookie_secure', '1');
        }

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'httponly'  => true,
            'samesite'  => 'Lax',
        ]);

        session_start();

        // Régénérer l'ID de session toutes les 30 minutes (protection fixation)
        if (!isset($_SESSION['_last_regeneration'])) {
            self::regenerate();
        } elseif (time() - $_SESSION['_last_regeneration'] > 1800) {
            self::regenerate();
        }
    }

    /**
     * Stocker une valeur en session
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Récupérer une valeur de la session
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifier si une clé existe en session
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Supprimer une clé de la session
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Détruire la session entière (déconnexion)
     */
    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Stocker un message flash (disponible uniquement pour la prochaine requête)
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Récupérer et supprimer un message flash
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Régénérer l'ID de session (protection contre la fixation)
     */
    private static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }
}
