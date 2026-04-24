<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Author
 */
class Author extends BaseModel
{
    protected static string $table = 'authors';

    /**
     * Trouver un auteur par son slug (avec infos user)
     */
    public static function findBySlug(string $slug): object|false
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT a.*, u.prenom, u.nom, u.email
             FROM authors a
             JOIN users u ON a.user_id = u.id
             WHERE a.slug = ?",
            [$slug]
        );
    }

    /**
     * Tous les auteurs validés avec infos user
     */
    public static function findActive(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT a.*, u.prenom, u.nom, u.email
             FROM authors a
             JOIN users u ON a.user_id = u.id
             WHERE a.statut_validation = 'valide'
             ORDER BY u.nom ASC"
        );
    }
}
