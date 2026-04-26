<?php
namespace App\Models;

use App\Lib\Database;

/**
 * Modèle Chat — gère les 3 tables : chat_conversations, chat_messages, chat_responses.
 * Inclut le moteur de matching mots-clés du bot.
 */
class Chat
{
    // Heures de bureau Kinshasa (UTC+1) : lun-sam 8h-19h
    private const TIMEZONE = 'Africa/Kinshasa';
    private const OFFICE_HOUR_START = 8;
    private const OFFICE_HOUR_END = 19;

    // Score minimum pour qu'une réponse bot soit retournée
    private const MATCH_THRESHOLD = 2;

    // ---------------------------------------------------------------------
    // CONVERSATIONS
    // ---------------------------------------------------------------------

    /**
     * Trouve une conversation par session_id (ouverte ou en attente),
     * ou en crée une nouvelle. Lie user_id si fourni.
     */
    public static function findOrCreateConversation(string $sessionId, ?int $userId = null): object
    {
        $db = Database::getInstance();

        $conv = $db->fetch(
            "SELECT * FROM chat_conversations
             WHERE session_id = ? AND statut != 'archivee'
             ORDER BY id DESC LIMIT 1",
            [$sessionId]
        );

        if ($conv) {
            // Mise à jour user_id si l'utilisateur s'est connecté entre-temps
            if ($userId !== null && $conv->user_id === null) {
                $db->update('chat_conversations', ['user_id' => $userId], 'id = ?', [$conv->id]);
                $conv->user_id = $userId;
            }
            return $conv;
        }

        $id = $db->insert('chat_conversations', [
            'user_id'         => $userId,
            'session_id'      => $sessionId,
            'statut'          => 'ouverte',
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);

        return $db->fetch("SELECT * FROM chat_conversations WHERE id = ?", [$id]);
    }

    public static function findConversation(int $id): object|false
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM chat_conversations WHERE id = ?",
            [$id]
        );
    }

    /**
     * Met à jour visitor_email (et visitor_name si fourni) d'une conversation visiteur.
     */
    public static function setVisitorEmail(int $convId, string $email, ?string $name = null): void
    {
        $data = ['visitor_email' => $email];
        if ($name !== null && $name !== '') {
            $data['visitor_name'] = $name;
        }
        Database::getInstance()->update('chat_conversations', $data, 'id = ?', [$convId]);
    }

    /**
     * Met une conversation en statut "en_attente_admin" et flag has_unread_for_admin.
     */
    public static function flagForAdmin(int $convId): void
    {
        Database::getInstance()->update('chat_conversations', [
            'statut'               => 'en_attente_admin',
            'has_unread_for_admin' => 1,
            'last_message_at'      => date('Y-m-d H:i:s'),
        ], 'id = ?', [$convId]);
    }

    /**
     * Touch last_message_at (pour tri admin) — appelé sur tout nouveau message.
     */
    public static function touchConversation(int $convId): void
    {
        Database::getInstance()->update('chat_conversations', [
            'last_message_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$convId]);
    }

    public static function markAdminRead(int $convId): void
    {
        Database::getInstance()->update('chat_conversations', [
            'has_unread_for_admin' => 0,
        ], 'id = ?', [$convId]);
    }

    public static function archiveConversation(int $convId): void
    {
        Database::getInstance()->update('chat_conversations', [
            'statut'               => 'archivee',
            'has_unread_for_admin' => 0,
        ], 'id = ?', [$convId]);
    }

    public static function setStatutRepondue(int $convId): void
    {
        Database::getInstance()->update('chat_conversations', [
            'statut'               => 'repondue',
            'has_unread_for_admin' => 0,
        ], 'id = ?', [$convId]);
    }

    // ---------------------------------------------------------------------
    // MESSAGES
    // ---------------------------------------------------------------------

    /**
     * Ajoute un message dans une conversation et touche last_message_at.
     */
    public static function addMessage(
        int $convId,
        string $senderType,
        string $content,
        ?int $senderUserId = null,
        bool $isBotResponse = false
    ): int {
        $db = Database::getInstance();

        $id = $db->insert('chat_messages', [
            'conversation_id'  => $convId,
            'sender_type'      => $senderType,
            'sender_user_id'   => $senderUserId,
            'content'          => $content,
            'is_bot_response'  => $isBotResponse ? 1 : 0,
        ]);

        self::touchConversation($convId);

        return (int) $id;
    }

    public static function getMessages(int $convId): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY id ASC",
            [$convId]
        );
    }

    // ---------------------------------------------------------------------
    // MATCHING BOT
    // ---------------------------------------------------------------------

    /**
     * Cherche la meilleure réponse bot pour un message donné.
     * Retourne l'objet chat_responses ou null si aucun match au-dessus du threshold.
     */
    public static function matchBotResponse(string $message): ?object
    {
        $normalized = self::normalizeText($message);
        if ($normalized === '') {
            return null;
        }

        $responses = Database::getInstance()->fetchAll(
            "SELECT * FROM chat_responses WHERE actif = 1"
        );

        $best = null;
        $bestScore = 0;

        foreach ($responses as $resp) {
            $score = self::scoreResponse($resp, $normalized);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $resp;
            }
        }

        return ($bestScore >= self::MATCH_THRESHOLD) ? $best : null;
    }

    /**
     * Calcule le score d'une réponse pour un message normalisé.
     * Premier mot-clé (priorité) : +2, autres mots-clés : +1 chacun.
     */
    private static function scoreResponse(object $resp, string $normalizedMessage): int
    {
        $keywords = array_filter(array_map('trim', explode(',', strtolower($resp->keywords))));
        if (empty($keywords)) {
            return 0;
        }

        $score = 0;
        $isFirst = true;

        foreach ($keywords as $kw) {
            $kwNorm = self::normalizeText($kw);
            if ($kwNorm === '') {
                continue;
            }
            // Match mot complet OU expression entière contenue dans le message
            if (self::messageContainsKeyword($normalizedMessage, $kwNorm)) {
                $score += $isFirst ? 2 : 1;
            }
            $isFirst = false;
        }

        return $score;
    }

    /**
     * Vérifie si un keyword (mot ou expression) est présent dans le message normalisé.
     * - Si le keyword contient un espace : on cherche la sous-chaîne exacte
     * - Sinon : on cherche le mot entier (avec frontières) pour éviter les faux positifs
     */
    private static function messageContainsKeyword(string $message, string $keyword): bool
    {
        if (str_contains($keyword, ' ')) {
            return str_contains($message, $keyword);
        }
        // Mot simple : recherche avec frontières \b
        return (bool) preg_match('/\b' . preg_quote($keyword, '/') . '\b/u', $message);
    }

    /**
     * Normalise un texte : lowercase, retire accents, retire ponctuation, compacte espaces.
     */
    public static function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        // Retirer les accents
        $text = self::stripAccents($text);
        // Retirer ponctuation (garder lettres, chiffres, espaces)
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        // Compacter les espaces
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }

    private static function stripAccents(string $text): string
    {
        $map = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'œ' => 'oe',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
        ];
        return strtr($text, $map);
    }

    /**
     * Incrémente times_used pour analytics.
     */
    public static function incrementResponseUsage(int $responseId): void
    {
        $db = Database::getInstance();
        $row = $db->fetch("SELECT times_used FROM chat_responses WHERE id = ?", [$responseId]);
        if ($row) {
            $db->update('chat_responses', ['times_used' => (int) $row->times_used + 1], 'id = ?', [$responseId]);
        }
    }

    // ---------------------------------------------------------------------
    // OFFICE HOURS
    // ---------------------------------------------------------------------

    /**
     * Vérifie si l'heure actuelle est dans les heures de bureau Kinshasa.
     * Lun-Sam 8h-19h.
     */
    public static function isOfficeHours(): bool
    {
        $now = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
        $dayOfWeek = (int) $now->format('N'); // 1 = lundi, 7 = dimanche
        $hour = (int) $now->format('G');

        if ($dayOfWeek === 7) {
            return false; // dimanche fermé
        }

        return $hour >= self::OFFICE_HOUR_START && $hour < self::OFFICE_HOUR_END;
    }

    // ---------------------------------------------------------------------
    // ADMIN — listing & badge
    // ---------------------------------------------------------------------

    /**
     * Récupère les conversations pour le dashboard admin.
     * Filtres : 'toutes' | 'non_lues' | 'visiteurs' | 'membres' | 'archivees'
     */
    public static function getConversationsForAdmin(string $filter = 'toutes'): array
    {
        $db = Database::getInstance();

        $where = '1=1';
        $params = [];

        switch ($filter) {
            case 'non_lues':
                $where = 'has_unread_for_admin = 1 AND statut != \'archivee\'';
                break;
            case 'visiteurs':
                $where = 'user_id IS NULL AND statut != \'archivee\'';
                break;
            case 'membres':
                $where = 'user_id IS NOT NULL AND statut != \'archivee\'';
                break;
            case 'archivees':
                $where = 'statut = \'archivee\'';
                break;
            case 'toutes':
            default:
                $where = 'statut != \'archivee\'';
                break;
        }

        return $db->fetchAll(
            "SELECT c.*, u.prenom AS user_prenom, u.nom AS user_nom, u.email AS user_email,
                    (SELECT content FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) AS last_message_content,
                    (SELECT sender_type FROM chat_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) AS last_sender_type
             FROM chat_conversations c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE {$where}
             ORDER BY c.last_message_at DESC
             LIMIT 200",
            $params
        );
    }

    /**
     * Compte les conversations non lues pour le badge admin.
     */
    public static function getUnreadAdminCount(): int
    {
        $row = Database::getInstance()->fetch(
            "SELECT COUNT(*) AS n FROM chat_conversations
             WHERE has_unread_for_admin = 1 AND statut != 'archivee'"
        );
        return $row ? (int) $row->n : 0;
    }

    // ---------------------------------------------------------------------
    // RESPONSES (CRUD admin)
    // ---------------------------------------------------------------------

    public static function getAllResponses(?bool $onlyActive = null): array
    {
        $where = ($onlyActive === true) ? 'WHERE actif = 1'
               : (($onlyActive === false) ? 'WHERE actif = 0' : '');

        return Database::getInstance()->fetchAll(
            "SELECT * FROM chat_responses {$where} ORDER BY category ASC, id ASC"
        );
    }

    public static function findResponse(int $id): object|false
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM chat_responses WHERE id = ?", [$id]
        );
    }

    public static function createResponse(array $data): int|false
    {
        return Database::getInstance()->insert('chat_responses', $data);
    }

    public static function updateResponse(int $id, array $data): int|false
    {
        return Database::getInstance()->update('chat_responses', $data, 'id = ?', [$id]);
    }

    public static function deleteResponse(int $id): int|false
    {
        return Database::getInstance()->delete('chat_responses', 'id = ?', [$id]);
    }
}
