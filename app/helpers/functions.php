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
 * Récupérer la valeur brute du token CSRF (pour les requêtes AJAX)
 */
function csrf_token(): string
{
    return App\Lib\CSRF::getToken();
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

/**
 * URL de la couverture d'un livre (ou null pour fallback gradient)
 */
function book_cover_url(object $book): ?string
{
    if (!empty($book->couverture_url_web)) {
        return $book->couverture_url_web;
    }
    return null;
}

/**
 * URL de la photo d'un auteur (ou null)
 */
function author_photo_url(object $author): ?string
{
    return !empty($author->photo_url_web) ? $author->photo_url_web : (!empty($author->photo_auteur) ? $author->photo_auteur : null);
}

/**
 * Initiales d'un auteur pour le placeholder
 */
function author_initials(object $author): string
{
    $p = mb_strtoupper(mb_substr($author->prenom ?? '', 0, 1));
    $n = mb_strtoupper(mb_substr($author->nom ?? '', 0, 1));
    return ($p . $n) ?: '?';
}

/**
 * Dégradé de couverture placeholder basé sur l'ID du livre
 */
function book_cover_gradient(int $bookId): string
{
    $gradients = [
        'from-red-900 via-red-950 to-amber-950',
        'from-amber-900 via-orange-900 to-red-950',
        'from-emerald-900 via-teal-900 to-cyan-950',
        'from-rose-900 via-pink-950 to-purple-950',
        'from-indigo-900 via-purple-900 to-violet-950',
        'from-yellow-900 via-amber-900 to-orange-950',
        'from-fuchsia-900 via-pink-900 to-rose-950',
        'from-green-900 via-emerald-900 to-teal-950',
        'from-slate-800 via-slate-900 to-gray-950',
        'from-blue-900 via-cyan-900 to-teal-950',
    ];
    return $gradients[$bookId % count($gradients)];
}

/**
 * Formater le prix d'un livre selon la devise
 */
function format_price(object $book, string $devise = 'USD'): string
{
    $field = 'prix_unitaire_' . strtolower($devise);
    $price = (float) ($book->$field ?? $book->prix_unitaire_usd ?? 0);
    $symbols = ['USD' => '$', 'CDF' => 'Fc', 'EUR' => '€', 'CAD' => 'CA$', 'XOF' => 'CFA'];
    $symbol = $symbols[strtoupper($devise)] ?? '$';
    return number_format($price, 2, ',', ' ') . ' ' . $symbol;
}

/**
 * Nom d'affichage de l'auteur d'un livre
 */
function book_author_name(object $livre): string
{
    return $livre->author_nom_plume ?: ($livre->author_prenom . ' ' . $livre->author_nom);
}

/**
 * Enregistrer une action admin dans audit_log
 */
function audit(string $action, ?string $entityType = null, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void
{
    $db = App\Lib\Database::getInstance();
    $db->insert('audit_log', [
        'admin_id'    => App\Lib\Auth::id() ?? 0,
        'action'      => $action,
        'entity_type' => $entityType,
        'entity_id'   => $entityId,
        'old_values'  => $oldValues ? json_encode($oldValues) : null,
        'new_values'  => $newValues ? json_encode($newValues) : null,
        'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}

/**
 * Générer des étoiles HTML (ambre pleines + grises vides)
 */
function stars_html(float $note, int $max = 5): string
{
    $html = '';
    $full = (int) floor($note);
    for ($i = 1; $i <= $max; $i++) {
        $color = $i <= $full ? 'text-accent' : 'text-border';
        $html .= '<svg class="w-4 h-4 ' . $color . ' inline-block" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
    }
    return $html;
}
