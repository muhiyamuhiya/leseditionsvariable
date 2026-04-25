<?php
namespace App\Lib;

/**
 * Helper de gestion des notifications utilisateur
 */
class Notification
{
    /**
     * Crée une notification pour un utilisateur. Retourne l'id ou false.
     *
     * @param int    $userId  destinataire
     * @param string $type    code court (ex: 'purchase_confirmed', 'new_review')
     * @param string $title   titre court (200 chars max)
     * @param string $message message détaillé (peut être null)
     * @param string|null $linkUrl URL relative à ouvrir au clic
     * @param string $icon    clé d'icône (bell, check, star, book, alert, mail, cart, premium)
     */
    public static function create(int $userId, string $type, string $title, string $message = '', ?string $linkUrl = null, string $icon = 'bell'): int|false
    {
        $db = Database::getInstance();
        return $db->insert('notifications', [
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => mb_substr($title, 0, 200),
            'message'    => $message ?: null,
            'link_url'   => $linkUrl,
            'icon'       => $icon,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Crée la même notification pour tous les admins (utile pour notifier l'équipe).
     */
    public static function createForAdmins(string $type, string $title, string $message = '', ?string $linkUrl = null, string $icon = 'bell'): void
    {
        $db = Database::getInstance();
        $admins = $db->fetchAll("SELECT id FROM users WHERE role = 'admin' AND (statut = 'actif' OR statut IS NULL)");
        foreach ($admins as $admin) {
            self::create((int) $admin->id, $type, $title, $message, $linkUrl, $icon);
        }
    }

    public static function unreadCount(int $userId): int
    {
        $db = Database::getInstance();
        $row = $db->fetch("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND read_at IS NULL", [$userId]);
        return (int) ($row->cnt ?? 0);
    }

    /**
     * @return array<\stdClass>
     */
    public static function recent(int $userId, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT id, type, title, message, link_url, icon, read_at, created_at
             FROM notifications WHERE user_id = ?
             ORDER BY created_at DESC LIMIT {$limit}",
            [$userId]
        );
    }

    /**
     * @return array<\stdClass>
     */
    public static function all(int $userId, int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ?
             ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            [$userId]
        );
    }

    public static function markAsRead(int $userId, ?int $notificationId = null): void
    {
        $db = Database::getInstance();
        if ($notificationId !== null) {
            $db->update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'user_id = ? AND id = ? AND read_at IS NULL', [$userId, $notificationId]);
        } else {
            $db->update('notifications', ['read_at' => date('Y-m-d H:i:s')], 'user_id = ? AND read_at IS NULL', [$userId]);
        }
    }

    public static function delete(int $userId, int $notificationId): void
    {
        $db = Database::getInstance();
        $db->delete('notifications', 'user_id = ? AND id = ?', [$userId, $notificationId]);
    }
}
