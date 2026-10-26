<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\View;

/**
 * Notifications Controller
 * Realises FR-COM-02 (In-App Notification Centre, Mark as Read, Direct Action Links)
 */
class NotificationController
{
    // FR-COM-02: Notifications List
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $userId = (int) $user['id'];

        $notifications = Database::fetchAll(
            "SELECT * FROM notifications 
             WHERE user_id = :uid 
             ORDER BY created_at DESC 
             LIMIT 100",
            ['uid' => $userId]
        );

        $unreadCount = 0;
        foreach ($notifications as $n) {
            if (empty($n['read_at'])) {
                $unreadCount++;
            }
        }

        View::render('notifications/index', [
            'title' => 'Notification Centre',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ], 'layouts/main');
    }

    // FR-COM-02: Mark Single Notification as Read
    public function markRead(): void
    {
        Auth::requireAuth();
        Csrf::checkOrAbort();

        $user = Auth::user();
        $id = (int) ($_POST['id'] ?? 0);

        Database::query(
            "UPDATE notifications SET read_at = NOW() WHERE id = :id AND user_id = :uid",
            ['id' => $id, 'uid' => $user['id']]
        );

        $redirect = $_POST['redirect'] ?? url('notifications');
        header('Location: ' . $redirect);
        exit;
    }

    // FR-COM-02: Mark All Notifications as Read
    public function markAllRead(): void
    {
        Auth::requireAuth();
        Csrf::checkOrAbort();

        $user = Auth::user();
        Database::query(
            "UPDATE notifications SET read_at = NOW() WHERE user_id = :uid AND read_at IS NULL",
            ['uid' => $user['id']]
        );

        Flash::success('All notifications marked as read.');
        header('Location: ' . url('notifications'));
        exit;
    }
}
