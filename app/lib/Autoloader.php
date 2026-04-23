<?php
namespace App\Lib;

/**
 * Autoloader PSR-4 manuel
 * Charge automatiquement les classes selon leur namespace
 */
class Autoloader
{
    /** @var array<string, string> Correspondance namespace → répertoire */
    private array $namespaces = [];

    public function __construct()
    {
        $this->namespaces = [
            'App\\Controllers\\' => BASE_PATH . '/app/controllers/',
            'App\\Models\\'      => BASE_PATH . '/app/models/',
            'App\\Lib\\'         => BASE_PATH . '/app/lib/',
        ];
    }

    /**
     * Enregistrer l'autoloader auprès de PHP
     */
    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Charger une classe à partir de son nom complet (namespace inclus)
     */
    public function loadClass(string $class): bool
    {
        foreach ($this->namespaces as $prefix => $baseDir) {
            // Vérifier si la classe correspond à ce namespace
            if (strpos($class, $prefix) === 0) {
                // Extraire le nom relatif de la classe (sans le préfixe namespace)
                $relativeClass = substr($class, strlen($prefix));

                // Construire le chemin du fichier
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }
            }
        }

        return false;
    }
}
