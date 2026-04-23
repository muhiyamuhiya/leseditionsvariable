<?php
namespace App\Lib;

/**
 * Routeur simple
 * Supporte GET, POST, PUT, DELETE avec routes statiques et dynamiques (:param)
 */
class Router
{
    /** @var array Liste des routes enregistrées */
    private array $routes = [];

    /**
     * Enregistrer une route GET
     */
    public function get(string $path, string $action, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $action, $middleware);
    }

    /**
     * Enregistrer une route POST
     */
    public function post(string $path, string $action, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $action, $middleware);
    }

    /**
     * Enregistrer une route PUT
     */
    public function put(string $path, string $action, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $action, $middleware);
    }

    /**
     * Enregistrer une route DELETE
     */
    public function delete(string $path, string $action, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $action, $middleware);
    }

    /**
     * Ajouter une route au registre interne
     */
    private function addRoute(string $method, string $path, string $action, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'action'     => $action,
            'middleware'  => $middleware,
        ];
    }

    /**
     * Dispatcher la requête HTTP courante vers le bon contrôleur
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        // Support PUT/DELETE via champ caché _method dans les formulaires
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);

            if ($params !== false) {
                // Exécuter les middlewares associés à cette route
                foreach ($route['middleware'] as $middlewareName) {
                    $this->runMiddleware($middlewareName);
                }

                // Appeler le contrôleur et la méthode
                $this->callAction($route['action'], $params);
                return;
            }
        }

        // Aucune route trouvée — afficher la page 404
        $this->notFound();
    }

    /**
     * Extraire l'URI propre depuis la requête
     */
    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Retirer les query strings (?foo=bar)
        $uri = strtok($uri, '?');

        // Retirer le slash final sauf pour la racine
        $uri = rtrim($uri, '/');

        return $uri === '' ? '/' : $uri;
    }

    /**
     * Vérifier si une route correspond à l'URI et extraire les paramètres
     * Retourne un tableau de paramètres nommés ou false si pas de match
     */
    private function matchRoute(string $routePath, string $uri): array|false
    {
        // Convertir les :param en groupes nommés regex
        $pattern = preg_replace('#:([a-zA-Z_]+)#', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Ne retourner que les paramètres nommés (pas les indices numériques)
            return array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Appeler la méthode d'un contrôleur
     */
    private function callAction(string $action, array $params): void
    {
        [$controllerName, $method] = explode('@', $action);

        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Contrôleur introuvable : {$controllerClass}");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Méthode introuvable : {$controllerClass}@{$method}");
        }

        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Exécuter un middleware par nom
     * Structure prête pour les middlewares (auth, admin, guest, etc.)
     */
    private function runMiddleware(string $name): void
    {
        // Sera implémenté dans les prochaines phases
        // Exemple futur :
        // match ($name) {
        //     'auth'  => AuthMiddleware::handle(),
        //     'admin' => AdminMiddleware::handle(),
        //     'guest' => GuestMiddleware::handle(),
        // };
    }

    /**
     * Afficher la page 404
     */
    private function notFound(): void
    {
        http_response_code(404);

        $viewFile = BASE_PATH . '/app/views/errors/404.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo '<h1>404 — Page introuvable</h1>';
            echo '<p>La page que vous cherchez n\'existe pas.</p>';
            echo '<p><a href="/">Retour à l\'accueil</a></p>';
        }
    }
}
