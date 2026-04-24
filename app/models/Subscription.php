<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Subscription
 */
class Subscription extends BaseModel
{
    protected static string $table = 'subscriptions';

    public static function isUserActive(int $userId): bool
    {
        $db = Database::getInstance();
        $sub = $db->fetch(
            "SELECT id FROM subscriptions WHERE user_id = ? AND statut = 'actif' AND date_fin >= NOW()",
            [$userId]
        );
        return (bool) $sub;
    }

    public static function getActive(int $userId): ?object
    {
        $db = Database::getInstance();
        $sub = $db->fetch(
            "SELECT * FROM subscriptions WHERE user_id = ? AND statut = 'actif' AND date_fin >= NOW() ORDER BY date_fin DESC LIMIT 1",
            [$userId]
        );
        return $sub ?: null;
    }
}
