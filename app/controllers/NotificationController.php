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
