<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Category
 */
class Category extends BaseModel
{
    protected static string $table = 'categories';

    /**
     * Trouver par slug
     */
    public static function findBySlug(string $slug): object|false
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM categories WHERE slug = ?", [$slug]);
    }

    /**
     * Catégories actives triées
     */
    public static function findActive(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM categories WHERE actif = 1 ORDER BY ordre_affichage ASC"
        );
    }
}
