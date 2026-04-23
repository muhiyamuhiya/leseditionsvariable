<?php
namespace App\Lib;

/**
 * Protection CSRF
 * Génère et valide des tokens pour sécuriser les formulaires POST
 */
class CSRF
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Générer un nouveau token CSRF et le stocker en session
     */
    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $token);
        return $token;
    }

    /**
     * Récupérer le token actuel (ou en générer un nouveau)
     */
    public static function getToken(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            return self::generateToken();
        }
        return Session::get(self::TOKEN_KEY);
    }

    /**
     * Générer un champ HTML caché contenant le token CSRF
     */
    public static function tokenField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Valider un token CSRF soumis contre celui en session
     */
    public static function validate(?string $token = null): bool
    {
        $token = $token ?? ($_POST['_csrf_token'] ?? '');
        $sessionToken = Session::get(self::TOKEN_KEY, '');

        if (empty($token) || empty($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Vérifier automatiquement le CSRF sur les requêtes POST
     * Stoppe l'exécution si le token est invalide
     */
    public static function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !self::validate()) {
            http_response_code(403);
            die('Erreur de sécurité : token CSRF invalide.');
        }
    }
}
