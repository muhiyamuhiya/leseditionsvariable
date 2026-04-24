<?php
namespace App\Lib;

use App\Models\Subscription;

/**
 * Matrice d'accès centralisée pour les livres
 */
class BookAccess
{
    public static function canReadFull(?object $user, int $bookId): bool
    {
        if (!$user) return false;
        if ($user->role === 'admin') return true;

        $db = Database::getInstance();
        $bought = $db->fetch("SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'achat_unitaire'", [$user->id, $bookId]);
        if ($bought) return true;

        return Subscription::isUserActive($user->id);
    }

    public static function canReadExtract(?object $user): bool
    {
        return $user !== null;
    }

    public static function canReview(?object $user, int $bookId): bool
    {
        if (!$user) return false;
        if ($user->role === 'admin') return true;

        // Seules les sources légitimes (achat ou abonnement) ouvrent le droit d'avis
        $db = Database::getInstance();
        $owned = $db->fetch(
            "SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source IN ('achat_unitaire','abonnement')",
            [$user->id, $bookId]
        );
        if ($owned) return true;

        return Subscription::isUserActive($user->id);
    }

    public static function canFavorite(?object $user): bool
    {
        return $user !== null;
    }

    public static function hasBought(?object $user, int $bookId): bool
    {
        if (!$user) return false;
        $db = Database::getInstance();
        return (bool) $db->fetch("SELECT 1 FROM user_books WHERE user_id = ? AND book_id = ? AND source = 'achat_unitaire'", [$user->id, $bookId]);
    }
}
