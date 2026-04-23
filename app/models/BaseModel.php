<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle de base
 * Classe abstraite parente de tous les modèles de l'application
 * Fournit les opérations CRUD communes
 */
abstract class BaseModel
{
    protected Database $db;

    /** @var string Nom de la table en base de données */
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Trouver un enregistrement par son ID
     */
    public function findById(int $id): object|false
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Récupérer tous les enregistrements
     */
    public function findAll(string $orderBy = 'id DESC'): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY {$orderBy}"
        );
    }

    /**
     * Créer un enregistrement — retourne l'ID inséré
     */
    public function create(array $data): int|false
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Mettre à jour un enregistrement par son ID
     */
    public function update(int $id, array $data): int|false
    {
        return $this->db->update($this->table, $data, 'id = ?', [$id]);
    }

    /**
     * Supprimer un enregistrement par son ID
     */
    public function delete(int $id): int|false
    {
        return $this->db->delete($this->table, 'id = ?', [$id]);
    }

    /**
     * Compter les enregistrements selon une condition
     */
    public function count(string $where = '1=1', array $params = []): int
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}",
            $params
        );
        return $result ? (int) $result->total : 0;
    }
}
