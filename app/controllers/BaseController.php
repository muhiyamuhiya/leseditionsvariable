<?php
namespace App\Controllers;

/**
 * Contrôleur de base
 * Classe abstraite parente de tous les contrôleurs de l'application
 */
abstract class BaseController
{
    /**
     * Afficher une vue dans un layout
     *
     * @param string $viewName  Chemin de la vue (ex: 'home/index')
     * @param array  $data      Données à passer à la vue
     * @param string $layout    Nom du layout à utiliser
     */
    protected function view(string $viewName, array $data = [], string $layout = 'main'): void
    {
        // Extraire les données pour les rendre accessibles dans la vue
        extract($data);

        // Chemin de la vue
        $viewFile = BASE_PATH . '/app/views/' . $viewName . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vue introuvable : {$viewFile}");
        }

        // Capturer le contenu de la vue dans un buffer
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Charger le layout avec le contenu injecté
        $layoutFile = BASE_PATH . '/app/views/layouts/' . $layout . '.php';

        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout introuvable : {$layoutFile}");
        }

        require $layoutFile;
    }

    /**
     * Redirection HTTP
     */
    protected function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header("Location: {$url}");
        exit;
    }

    /**
     * Réponse JSON
     */
    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
