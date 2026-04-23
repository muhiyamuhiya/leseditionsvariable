<?php
namespace App\Lib;

/**
 * Classe Database — Singleton PDO
 * Gère toutes les interactions avec la base de données MySQL
 */
class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;

    /**
     * Constructeur privé — connexion PDO
     */
    private function __construct()
    {
        $host = Env::get('DB_HOST', 'localhost');
        $port = Env::get('DB_PORT', '3306');
        $name = Env::get('DB_NAME', 'leseditionsvariable');
        $user = Env::get('DB_USER', 'root');
        $pass = Env::get('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            $this->pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE  => \PDO::FETCH_OBJ,
                \PDO::ATTR_EMULATE_PREPARES    => false,
            ]);
        } catch (\PDOException $e) {
            $this->logError($e->getMessage());
            throw new \RuntimeException('Erreur de connexion à la base de données.');
        }
    }

    /** Empêcher le clonage */
    private function __clone() {}

    /**
     * Obtenir l'instance unique de Database
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Récupérer une seule ligne
     */
    public function fetch(string $sql, array $params = []): object|false
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer toutes les lignes
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            $this->logError($e->getMessage());
            return [];
        }
    }

    /**
     * Insérer une ligne et retourner l'ID généré
     */
    public function insert(string $table, array $data): int|false
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));

            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));

            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour des lignes — retourne le nombre de lignes affectées
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int|false
    {
        try {
            $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));

            $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([...array_values($data), ...$whereParams]);

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer des lignes — retourne le nombre de lignes supprimées
     */
    public function delete(string $table, string $where, array $whereParams = []): int|false
    {
        try {
            $sql = "DELETE FROM {$table} WHERE {$where}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($whereParams);

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }

    /**
     * Écrire une erreur dans le fichier de log
     */
    private function logError(string $message): void
    {
        $logFile = BASE_PATH . '/logs/db.log';
        $date = date('Y-m-d H:i:s');
        $entry = "[{$date}] ERREUR DB : {$message}" . PHP_EOL;
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
