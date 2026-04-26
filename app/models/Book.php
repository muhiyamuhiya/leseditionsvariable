<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Book
 */
class Book extends BaseModel
{
    protected static string $table = 'books';

    /**
     * Requête de base avec jointures auteur + catégorie
     */
    private static function baseSelect(): string
    {
        // LEFT JOIN users : un auteur classique (is_classic=1, user_id NULL)
        // n'a pas de compte user, et un INNER JOIN aurait fait disparaître ses
        // livres du catalogue public. COALESCE et CONCAT_WS gèrent les NULL.
        return "SELECT b.*,
                    COALESCE(a.nom_plume, CONCAT_WS(' ', u.prenom, u.nom)) AS author_display,
                    u.prenom AS author_prenom,
                    u.nom AS author_nom,
                    a.nom_plume AS author_nom_plume,
                    a.slug AS author_slug,
                    c.nom AS category_nom,
                    c.slug AS category_slug
                FROM books b
                JOIN authors a ON b.author_id = a.id
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN categories c ON b.category_id = c.id";
    }

    /**
     * Trouver par slug
     */
    public static function findBySlug(string $slug): object|false
    {
        $db = Database::getInstance();
        return $db->fetch(
            self::baseSelect() . " WHERE b.slug = ?",
            [$slug]
        );
    }

    /**
     * Trouver par id
     */
    public static function find(int $id): object|false
    {
        $db = Database::getInstance();
        return $db->fetch(
            self::baseSelect() . " WHERE b.id = ?",
            [$id]
        );
    }

    /**
     * Livres publiés avec filtres et pagination
     */
    public static function findPublished(int $limit = 20, int $offset = 0, ?string $categorySlug = null, ?string $search = null, string $orderBy = 'b.created_at DESC'): array
    {
        $db = Database::getInstance();
        $where = "b.statut = 'publie'";
        $params = [];

        if ($categorySlug) {
            $where .= " AND c.slug = ?";
            $params[] = $categorySlug;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $where .= " AND (b.titre LIKE ? OR b.description_courte LIKE ? OR CONCAT(u.prenom, ' ', u.nom) LIKE ? OR a.nom_plume LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $params[] = $limit;
        $params[] = $offset;

        return $db->fetchAll(
            self::baseSelect() . " WHERE {$where} ORDER BY {$orderBy} LIMIT ? OFFSET ?",
            $params
        );
    }

    /**
     * Compter les livres publiés avec filtres
     */
    public static function countPublished(?string $categorySlug = null, ?string $search = null): int
    {
        $db = Database::getInstance();
        $where = "b.statut = 'publie'";
        $params = [];

        if ($categorySlug) {
            $where .= " AND c.slug = ?";
            $params[] = $categorySlug;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $where .= " AND (b.titre LIKE ? OR b.description_courte LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }

        $result = $db->fetch(
            "SELECT COUNT(*) as total FROM books b LEFT JOIN categories c ON b.category_id = c.id WHERE {$where}",
            $params
        );
        return $result ? (int) $result->total : 0;
    }

    /**
     * Nouveautés (plus récents)
     */
    public static function findNouveautes(int $limit = 10): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            self::baseSelect() . " WHERE b.statut = 'publie' ORDER BY b.date_publication DESC, b.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    /**
     * Tendances (plus vendus / lus)
     */
    public static function findTendances(int $limit = 10): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            self::baseSelect() . " WHERE b.statut = 'publie' ORDER BY b.total_ventes DESC, b.total_lectures DESC LIMIT ?",
            [$limit]
        );
    }

    /**
     * Livres par catégorie
     */
    public static function findByCategory(string $categorySlug, int $limit = 10): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            self::baseSelect() . " WHERE b.statut = 'publie' AND c.slug = ? ORDER BY b.date_publication DESC LIMIT ?",
            [$categorySlug, $limit]
        );
    }

    /**
     * Livres du même auteur
     */
    public static function findByAuthor(int $authorId, int $limit = 4, ?int $excludeBookId = null): array
    {
        $db = Database::getInstance();
        $exclude = $excludeBookId ? 'AND b.id != ?' : '';
        $params = [$authorId];
        if ($excludeBookId) $params[] = $excludeBookId;
        $params[] = $limit;

        return $db->fetchAll(
            self::baseSelect() . " WHERE b.statut = 'publie' AND b.author_id = ? {$exclude} ORDER BY b.date_publication DESC LIMIT ?",
            $params
        );
    }

    /**
     * Livres similaires (même catégorie, sauf le livre courant)
     */
    public static function findSimilar(int $bookId, ?int $categoryId, int $limit = 4): array
    {
        $db = Database::getInstance();
        if (!$categoryId) return [];

        return $db->fetchAll(
            self::baseSelect() . " WHERE b.statut = 'publie' AND b.category_id = ? AND b.id != ? ORDER BY RAND() LIMIT ?",
            [$categoryId, $bookId, $limit]
        );
    }

    /**
     * Livres recommandés (mis en avant, sinon aléatoire)
     */
    public static function findRecommandes(int $limit = 10): array
    {
        $db = Database::getInstance();
        $results = $db->fetchAll(
            self::baseSelect() . " WHERE b.statut = 'publie' AND b.mis_en_avant = 1 ORDER BY RAND() LIMIT ?",
            [$limit]
        );

        if (count($results) < $limit) {
            $ids = array_map(fn($r) => $r->id, $results);
            $exclude = $ids ? 'AND b.id NOT IN (' . implode(',', array_fill(0, count($ids), '?')) . ')' : '';
            $remaining = $limit - count($results);
            $params = array_merge($ids, [$remaining]);
            $extra = $db->fetchAll(
                self::baseSelect() . " WHERE b.statut = 'publie' {$exclude} ORDER BY RAND() LIMIT ?",
                $params
            );
            $results = array_merge($results, $extra);
        }

        return $results;
    }
}
