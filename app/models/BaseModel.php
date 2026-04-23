<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle de base — Pattern Active Record simplifié
 * Classe abstraite parente de tous les modèles de l'application
 * Toutes les méthodes CRUD sont statiques
 */
abstract class BaseModel
{
    /** @var string Nom de la table — à définir dans chaque classe fille */
    protected static string $table;

    /**
     * Trouver un enregistrement par son ID
     */
    public static function find(int $id): object|false
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM " . static::$table . " WHERE id = ?",
            [$id]
        );
    }

    /**
     * Récupérer tous les enregistrements
     */
    public static function findAll(string $orderBy = 'id DESC'): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM " . static::$table . " ORDER BY {$orderBy}"
        );
    }

    /**
     * Créer un enregistrement — retourne l'ID inséré
     */
    public static function create(array $data): int|false
    {
        $db = Database::getInstance();
        return $db->insert(static::$table, $data);
    }

    /**
     * Mettre à jour un enregistrement par son ID
     */
    public static function update(int $id, array $data): int|false
    {
        $db = Database::getInstance();
        return $db->update(static::$table, $data, 'id = ?', [$id]);
    }

    /**
     * Supprimer un enregistrement par son ID
     */
    public static function delete(int $id): int|false
    {
        $db = Database::getInstance();
        return $db->delete(static::$table, 'id = ?', [$id]);
    }

    /**
     * Compter les enregistrements selon une condition
     */
    public static function count(string $where = '1=1', array $params = []): int
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as total FROM " . static::$table . " WHERE {$where}",
            $params
        );
        return $result ? (int) $result->total : 0;
    }
}
