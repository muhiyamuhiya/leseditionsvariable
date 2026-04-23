<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Category
 * Gère les catégories de livres
 */
class Category extends BaseModel
{
    protected static string $table = 'categories';

    /**
     * Récupérer toutes les catégories actives, triées par ordre d'affichage
     */
    public static function findActive(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM categories WHERE actif = 1 ORDER BY ordre_affichage ASC"
        );
    }
}
