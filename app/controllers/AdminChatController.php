<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Session;
use App\Models\Chat;

/**
 * Contrôleur du dashboard admin pour le chat custom :
 *   - Liste/détail des conversations
 *   - Réponse admin
 *   - Marquage lu / archivage
 *   - CRUD des réponses pré-écrites du bot
 */
class AdminChatController extends BaseController
{
    /**
     * GET /admin/chat
     * Liste des conversations + conversation sélectionnée si ?conversation_id=X
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $filter = $_GET['filter'] ?? 'toutes';
        $allowed = ['toutes', 'non_lues', 'visiteurs', 'membres', 'archivees'];
        if (!in_array($filter, $allowed, true)) {
            $filter = 'toutes';
        }

        $conversations = Chat::getConversationsForAdmin($filter);

        $selectedId = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : 0;
        $selectedConv = null;
        $selectedMessages = [];

        if ($selectedId > 0) {
            $selectedConv = Chat::findConversation($selectedId);
            if ($selectedConv) {
                $selectedMessages = Chat::getMessages($selectedId);
                // Marque la conversation comme lue côté admin dès qu'elle est ouverte
                if ($selectedConv->has_unread_for_admin) {
                    Chat::markAdminRead($selectedId);
                }
            }
        }

        $this->view('admin/chat/index', [
            'titre'             => 'Chat',
            'conversations'     => $conversations,
            'selectedConv'      => $selectedConv,
            'selectedMessages'  => $selectedMessages,
            'currentFilter'     => $filter,
            'unreadCount'       => Chat::getUnreadAdminCount(),
        ], 'admin');
    }

    /**
     * POST /admin/chat/reply/:id
     * Admin envoie une réponse à une conversation.
     */
    public function reply(string $id): void
    {
        Auth::requireAdmin();
        $this->checkCSRF();

        $convId = (int) $id;
        $conv = Chat::findConversation($convId);
        if (!$conv) {
            Session::flash('error', 'Conversation introuvable.');
            redirect('/admin/chat');
        }

        $content = trim((string) ($_POST['content'] ?? ''));
        if ($content === '') {
            Session::flash('error', 'Le message est vide.');
            redirect('/admin/chat?conversation_id=' . $convId);
        }
        if (mb_strlen($content) > 5000) {
            Session::flash('error', 'Message trop long (max 5000 caractères).');
            redirect('/admin/chat?conversation_id=' . $convId);
        }

        Chat::addMessage($convId, 'admin', $content, Auth::id(), false);
        Chat::setStatutRepondue($convId);

        Session::flash('success', 'Réponse envoyée.');
        redirect('/admin/chat?conversation_id=' . $convId);
    }

    /**
     * POST /admin/chat/mark-read/:id
     * Marque une conversation comme lue (sans la déplacer).
     */
    public function markRead(string $id): void
    {
        Auth::requireAdmin();
        $this->checkCSRF();
        Chat::markAdminRead((int) $id);

        if ($this->wantsJson()) {
            $this->json(['ok' => true]);
            return;
        }
        redirect('/admin/chat');
    }

    /**
     * POST /admin/chat/archive/:id
     * Archive une conversation (la sort de toutes les listes sauf "archivées").
     */
    public function archive(string $id): void
    {
        Auth::requireAdmin();
        $this->checkCSRF();
        Chat::archiveConversation((int) $id);

        Session::flash('success', 'Conversation archivée.');
        redirect('/admin/chat');
    }

    // -----------------------------------------------------------------
    // CRUD réponses pré-écrites du bot
    // -----------------------------------------------------------------

    /**
     * GET /admin/chat/responses
     * Liste + formulaire d'ajout/édition.
     */
    public function responses(): void
    {
        Auth::requireAdmin();

        $editingId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        $editing = null;
        if ($editingId > 0) {
            $editing = Chat::findResponse($editingId);
        }

        $this->view('admin/chat/responses', [
            'titre'     => 'Réponses du bot',
            'responses' => Chat::getAllResponses(null),
            'editing'   => $editing,
        ], 'admin');
    }

    /**
     * POST /admin/chat/responses
     * Création d'une réponse.
     */
    public function responseStore(): void
    {
        Auth::requireAdmin();
        $this->checkCSRF();

        $data = $this->validateResponseInput();
        if ($data === null) {
            redirect('/admin/chat/responses');
        }

        Chat::createResponse($data + [
            'actif'      => 1,
            'times_used' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Session::flash('success', 'Réponse ajoutée.');
        redirect('/admin/chat/responses');
    }

    /**
     * POST /admin/chat/responses/:id
     * Mise à jour d'une réponse (édition + toggle actif).
     */
    public function responseUpdate(string $id): void
    {
        Auth::requireAdmin();
        $this->checkCSRF();

        $respId = (int) $id;
        $existing = Chat::findResponse($respId);
        if (!$existing) {
            Session::flash('error', 'Réponse introuvable.');
            redirect('/admin/chat/responses');
        }

        // Toggle actif rapide (sans formulaire complet)
        if (isset($_POST['toggle_actif'])) {
            Chat::updateResponse($respId, ['actif' => $existing->actif ? 0 : 1]);
            Session::flash('success', $existing->actif ? 'Réponse désactivée.' : 'Réponse réactivée.');
            redirect('/admin/chat/responses');
        }

        $data = $this->validateResponseInput();
        if ($data === null) {
            redirect('/admin/chat/responses?edit=' . $respId);
        }

        Chat::updateResponse($respId, $data);
        Session::flash('success', 'Réponse mise à jour.');
        redirect('/admin/chat/responses');
    }

    /**
     * POST /admin/chat/responses/:id/supprimer
     * Suppression définitive d'une réponse.
     */
    public function responseDelete(string $id): void
    {
        Auth::requireAdmin();
        $this->checkCSRF();

        Chat::deleteResponse((int) $id);
        Session::flash('success', 'Réponse supprimée.');
        redirect('/admin/chat/responses');
    }

    // -----------------------------------------------------------------
    // API JSON (badge polling et liste compacte)
    // -----------------------------------------------------------------

    /**
     * GET /admin/chat/api/unread-count
     * Renvoie le nombre de conversations non lues — pour le badge sidebar.
     */
    public function apiUnreadCount(): void
    {
        Auth::requireAdmin();
        $this->json(['count' => Chat::getUnreadAdminCount()]);
    }

    // -----------------------------------------------------------------
    // Helpers privés
    // -----------------------------------------------------------------

    private function validateResponseInput(): ?array
    {
        $keywords = trim((string) ($_POST['keywords'] ?? ''));
        $question = trim((string) ($_POST['question'] ?? ''));
        $answer   = trim((string) ($_POST['answer'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));

        if ($keywords === '' || $question === '' || $answer === '') {
            Session::flash('error', 'Mots-clés, question et réponse sont obligatoires.');
            return null;
        }

        return [
            'keywords' => $keywords,
            'question' => $question,
            'answer'   => $answer,
            'category' => $category !== '' ? $category : null,
        ];
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xrw    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strtolower($xrw) === 'xmlhttprequest';
    }

    private function checkCSRF(): void
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CSRF::validate($token)) {
            http_response_code(403);
            if ($this->wantsJson()) {
                $this->json(['error' => 'csrf'], 403);
                return;
            }
            die('Erreur de sécurité : token CSRF invalide.');
        }
    }
}
