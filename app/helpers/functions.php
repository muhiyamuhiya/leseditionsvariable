<?php
/**
 * Fonctions utilitaires globales
 * Accessibles partout dans l'application
 */

/**
 * Récupérer une variable d'environnement
 */
function env(string $key, mixed $default = null): mixed
{
    return App\Lib\Env::get($key, $default);
}

/**
 * Échappement HTML pour prévenir les attaques XSS
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Générer l'URL complète vers un asset (CSS, JS, image)
 */
function asset(string $path): string
{
    return env('APP_URL', '') . '/assets/' . ltrim($path, '/');
}

/**
 * Générer une URL complète du site
 */
function url(string $path = ''): string
{
    return env('APP_URL', '') . '/' . ltrim($path, '/');
}

/**
 * Redirection HTTP
 */
function redirect(string $url, int $code = 302): void
{
    http_response_code($code);
    header("Location: {$url}");
    exit;
}

/**
 * Vérifier si la requête courante est de type POST
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Générer un champ caché avec le token CSRF
 */
function csrf_field(): string
{
    return App\Lib\CSRF::tokenField();
}

/**
 * Stocker ou récupérer un message flash
 * flash('success', 'Bravo !') → stocke
 * flash('success')             → récupère et supprime
 */
function flash(string $key, mixed $value = null): mixed
{
    if ($value !== null) {
        App\Lib\Session::flash($key, $value);
        return null;
    }
    return App\Lib\Session::getFlash($key);
}

/**
 * Récupérer une valeur de la session
 */
function session(string $key, mixed $default = null): mixed
{
    return App\Lib\Session::get($key, $default);
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function isAuthenticated(): bool
{
    return App\Lib\Session::has('user_id');
}

/**
 * Récupérer l'ID de l'utilisateur connecté
 */
function currentUserId(): ?int
{
    $id = App\Lib\Session::get('user_id');
    return $id !== null ? (int) $id : null;
}
