<?php
namespace App\Lib;

/**
 * Parser de fichier .env
 * Charge les variables d'environnement depuis un fichier texte
 */
class Env
{
    /** @var array<string, string> Variables chargées */
    private static array $variables = [];

    /**
     * Charger un fichier .env
     */
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Fichier .env introuvable : {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignorer les commentaires et lignes vides
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Séparer clé et valeur au premier '='
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Retirer les guillemets encadrants
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            self::$variables[$key] = $value;
            $_ENV[$key] = $value;
        }
    }

    /**
     * Récupérer une variable d'environnement
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$variables[$key] ?? $_ENV[$key] ?? $default;
    }
}
