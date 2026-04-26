<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Database;
use App\Models\Chat;

/**
 * Contrôleur du chat visiteur — envoi de messages, matching bot,
 * collecte d'email hors heures de bureau.
 */
class ChatController extends BaseController
{
    private const MIN_MSG_LENGTH = 1;
    private const MAX_MSG_LENGTH = 2000;

    /**
     * POST /chat/send
     * Body: message, session_id, conversation_id (optionnel)
     * Réponse JSON : { ok, conversation_id, bot_message?, no_match?, ask_email?, office_hours }
     */
    public function send(): void
    {
        $this->checkCSRF();

        $message = trim((string) ($_POST['message'] ?? ''));
        $sessionId = trim((string) ($_POST['session_id'] ?? ''));

        if ($message === '' || mb_strlen($message) < self::MIN_MSG_LENGTH) {
            $this->json(['error' => 'message_vide'], 400);
            return;
        }
        if (mb_strlen($message) > self::MAX_MSG_LENGTH) {
            $this->json(['error' => 'message_trop_long'], 400);
            return;
        }
        if ($sessionId === '' || strlen($sessionId) > 64) {
            $this->json(['error' => 'session_invalide'], 400);
            return;
        }

        $userId = Auth::check() ? Auth::id() : null;

        // Trouve ou crée la conversation
        $conv = Chat::findOrCreateConversation($sessionId, $userId);

        // Enregistre le message visiteur (ou user)
        $senderType = $userId !== null ? 'user' : 'visiteur';
        Chat::addMessage($conv->id, $senderType, $message, $userId, false);

        // Tente le matching bot
        $botResponse = Chat::matchBotResponse($message);
        $officeHours = Chat::isOfficeHours();

        if ($botResponse !== null) {
            // Match trouvé — enregistre la réponse bot et incrémente l'usage
            Chat::addMessage($conv->id, 'bot', $botResponse->answer, null, true);
            Chat::incrementResponseUsage((int) $botResponse->id);

            $this->json([
                'ok'              => true,
                'conversation_id' => $conv->id,
                'bot_message'     => [
                    'content'    => $botResponse->answer,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
                'no_match'        => false,
                'office_hours'    => $officeHours,
            ]);
            return;
        }

        // Pas de match — flag pour admin et message bot adapté aux heures
        Chat::flagForAdmin((int) $conv->id);

        if ($officeHours) {
            $botMsg = "Je n'ai pas la réponse précise à ta question. Angello va te répondre dans quelques minutes. ⏱️";
            $askEmail = false;
        } else {
            $botMsg = "Je n'ai pas la réponse à ta question. Laisse ton email ci-dessous, Angello te répondra dès demain matin (8h, heure de Kinshasa). 📧";
            $askEmail = $userId === null && empty($conv->visitor_email);
        }

        Chat::addMessage((int) $conv->id, 'bot', $botMsg, null, true);

        $this->json([
            'ok'              => true,
            'conversation_id' => $conv->id,
            'bot_message'     => [
                'content'    => $botMsg,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            'no_match'        => true,
            'ask_email'       => $askEmail,
            'office_hours'    => $officeHours,
        ]);
    }

    /**
     * POST /chat/leave-email
     * Body: session_id, email, name (optionnel)
     * Sauvegarde l'email sur la conversation + inscrit à la newsletter (source=chat).
     */
    public function leaveEmail(): void
    {
        $this->checkCSRF();

        $sessionId = trim((string) ($_POST['session_id'] ?? ''));
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $name = trim((string) ($_POST['name'] ?? ''));

        if ($sessionId === '' || !$email) {
            $this->json(['error' => 'donnees_invalides'], 400);
            return;
        }

        $userId = Auth::check() ? Auth::id() : null;
        $conv = Chat::findOrCreateConversation($sessionId, $userId);

        Chat::setVisitorEmail((int) $conv->id, $email, $name !== '' ? $name : null);

        // Confirmation visible dans le chat
        Chat::addMessage(
            (int) $conv->id,
            'bot',
            "Merci ! On reviendra vers toi à <strong>" . htmlspecialchars($email) . "</strong>. ✅",
            null,
            true
        );

        // Inscription newsletter avec source=chat (sans erreur si déjà abonné)
        $this->subscribeToNewsletter($email, $name);

        $this->json([
            'ok'              => true,
            'conversation_id' => $conv->id,
        ]);
    }

    /**
     * GET /chat/conversation/{id}
     * Param query : session_id (pour vérification d'accès visiteur)
     * Retourne les messages de la conversation.
     */
    public function getConversation(string $id): void
    {
        $convId = (int) $id;
        $sessionId = trim((string) ($_GET['session_id'] ?? ''));

        $conv = Chat::findConversation($convId);
        if (!$conv) {
            $this->json(['error' => 'introuvable'], 404);
            return;
        }

        // Autorisation : owner par session_id, owner par user_id, ou admin
        $isOwnerSession = $sessionId !== '' && hash_equals($conv->session_id, $sessionId);
        $isOwnerUser = Auth::check() && $conv->user_id !== null && (int) $conv->user_id === Auth::id();
        $isAdmin = Auth::check() && (Auth::user()?->role === 'admin');

        if (!$isOwnerSession && !$isOwnerUser && !$isAdmin) {
            $this->json(['error' => 'non_autorise'], 403);
            return;
        }

        $this->json([
            'ok'           => true,
            'conversation' => [
                'id'             => $conv->id,
                'statut'         => $conv->statut,
                'visitor_email'  => $conv->visitor_email,
                'visitor_name'   => $conv->visitor_name,
            ],
            'messages' => array_map(fn($m) => [
                'id'              => $m->id,
                'sender_type'     => $m->sender_type,
                'content'         => $m->content,
                'is_bot_response' => (bool) $m->is_bot_response,
                'created_at'      => $m->created_at,
            ], Chat::getMessages($convId)),
        ]);
    }

    // ---------------------------------------------------------------------
    // Helpers privés
    // ---------------------------------------------------------------------

    /**
     * Inscrit l'email à la newsletter avec source=chat.
     * Idempotent : si l'email existe déjà, on ne fait rien (pas d'écrasement).
     */
    private function subscribeToNewsletter(string $email, string $name): void
    {
        $db = Database::getInstance();

        $existing = $db->fetch(
            "SELECT id FROM newsletter_subscribers WHERE email = ?",
            [$email]
        );

        if ($existing) {
            return;
        }

        $db->insert('newsletter_subscribers', [
            'email'              => $email,
            'prenom'             => $name !== '' ? $name : null,
            'source'             => 'chat',
            'confirmation_token' => bin2hex(random_bytes(32)),
            'created_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Validation CSRF (compatible body POST ou header X-CSRF-Token).
     */
    private function checkCSRF(): void
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CSRF::validate($token)) {
            http_response_code(403);
            $this->json(['error' => 'csrf'], 403);
            exit;
        }
    }
}
