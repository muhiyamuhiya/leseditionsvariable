<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Review — Avis des lecteurs
 */
class Review extends BaseModel
{
    protected static string $table = 'reviews';

    /**
     * Avis approuvés pour un livre
     */
    public static function findByBook(int $bookId, int $limit = 5): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT r.*, u.prenom, u.nom
             FROM reviews r
             JOIN users u ON r.user_id = u.id
             WHERE r.book_id = ? AND r.approuve = 1
             ORDER BY r.created_at DESC
             LIMIT ?",
            [$bookId, $limit]
        );
    }

    /**
     * Note moyenne d'un livre
     */
    public static function averageForBook(int $bookId): float
    {
        $db = Database::getInstance();
        $r = $db->fetch(
            "SELECT AVG(note) as avg_note FROM reviews WHERE book_id = ? AND approuve = 1",
            [$bookId]
        );
        return $r && $r->avg_note ? round((float) $r->avg_note, 1) : 0;
    }

    /**
     * Vérifier si un utilisateur a déjà laissé un avis
     */
    public static function userHasReviewed(int $userId, int $bookId): bool
    {
        $db = Database::getInstance();
        $r = $db->fetch(
            "SELECT id FROM reviews WHERE user_id = ? AND book_id = ?",
            [$userId, $bookId]
        );
        return (bool) $r;
    }
}
