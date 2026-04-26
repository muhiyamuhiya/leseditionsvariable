<?php
namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\CSRF;
use App\Lib\Notification;
use App\Lib\Session;

/**
 * Contrôleur des notifications utilisateur
 */
class NotificationController extends BaseController
{
    public function index(): void
    {
        Auth::requireLogin();
        $userId = Auth::id();

        $notifications = Notification::all($userId, 100, 0);
        $unread = Notification::unreadCount($userId);

        $this->view('notifications/index', [
            'titre'         => 'Mes notifications',
            'notifications' => $notifications,
            'unread'        => $unread,
        ]);
    }

    public function markAllRead(): void
    {
        Auth::requireLogin();
        $this->checkCSRF();
        Notification::markAsRead(Auth::id());

        if ($this->wantsJson()) {
            $this->json(['ok' => true]);
            return;
        }
        Session::flash('success', 'Toutes les notifications ont été marquées comme lues.');
        redirect('/notifications');
    }

    public function markRead(string $id): void
    {
        Auth::requireLogin();
        $this->checkCSRF();
        Notification::markAsRead(Auth::id(), (int) $id);

        if ($this->wantsJson()) {
            $this->json(['ok' => true]);
            return;
        }
        redirect('/notifications');
    }

    /**
     * GET /notifications/:id/aller
     * Marque la notification comme lue et redirige vers sa cible (link_url).
     *
     * Pourquoi un GET sans CSRF : le marquage de lecture est idempotent et
     * non-destructif (on ne peut que passer NULL → date, jamais l'inverse).
     * C'est le pattern Stripe / GitHub / Slack pour les notifs cliquables.
     * On vérifie quand même que l'utilisateur connecté est bien le destinataire.
     */
    public function open(string $id): void
    {
        Auth::requireLogin();
        $userId = Auth::id();
        $notifId = (int) $id;

        // Récupère la notif APRÈS check user_id (pas de fuite d'info entre users)
        $notif = \App\Lib\Database::getInstance()->fetch(
            "SELECT id, user_id, link_url FROM notifications WHERE id = ? AND user_id = ?",
            [$notifId, $userId]
        );

        if (!$notif) {
            redirect('/notifications');
            return;
        }

        // Marque lue (idempotent)
        Notification::markAsRead($userId, $notifId);

        // Redirect vers la cible si présente et interne (sécurité : pas de open redirect)
        $target = (string) ($notif->link_url ?? '');
        if ($target !== '' && preg_match('/^\/[^\/]/', $target) && !str_contains($target, '//')) {
            redirect($target);
            return;
        }

        redirect('/notifications');
    }

    public function destroy(string $id): void
    {
        Auth::requireLogin();
        $this->checkCSRF();
        Notification::delete(Auth::id(), (int) $id);

        if ($this->wantsJson()) {
            $this->json(['ok' => true]);
            return;
        }
        Session::flash('success', 'Notification supprimée.');
        redirect('/notifications');
    }

    public function apiRecent(): void
    {
        Auth::requireLogin();
        $userId = Auth::id();
        $this->json([
            'notifications' => Notification::recent($userId, 10),
            'unread_count'  => Notification::unreadCount($userId),
        ]);
    }

    public function apiCount(): void
    {
        Auth::requireLogin();
        $this->json(['count' => Notification::unreadCount(Auth::id())]);
    }

    private function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xrw    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json') || strtolower($xrw) === 'xmlhttprequest';
    }

    /**
     * Validation CSRF compatible body POST ou header X-CSRF-Token (pour les fetch JS)
     */
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
