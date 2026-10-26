<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Notification Service (FR-COM-02)
 * Creates in-app notifications and email notifications for all system events.
 */
class Notifier
{
    public static function send(
        int $userId,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        bool $sendEmail = true
    ): void {
        // 1. In-app notification
        try {
            Database::execute(
                "INSERT INTO notifications (user_id, type, title, body, link, read_at, created_at)
                 VALUES (:user_id, :type, :title, :body, :link, NULL, NOW())",
                [
                    'user_id' => $userId,
                    'type' => $type,
                    'title' => $title,
                    'body' => $body,
                    'link' => $link,
                ]
            );
        } catch (\Throwable $e) {
            error_log("Failed to insert notification: " . $e->getMessage());
        }

        // 2. Email alert
        if ($sendEmail) {
            $user = Database::fetchOne("SELECT email, name FROM users WHERE id = :id", ['id' => $userId]);
            if ($user && !empty($user['email'])) {
                $emailBody = "<h3>{$title}</h3><p>Dear {$user['name']},</p><p>{$body}</p>";
                if ($link) {
                    $fullUrl = url($link);
                    $emailBody .= "<p><a href='{$fullUrl}'>Click here to view in InternFlow</a></p>";
                }
                $emailBody .= "<hr><p><small>UNITEN College of Computing and Informatics (CCI) — InternFlow</small></p>";

                Mailer::send($user['email'], "[InternFlow] " . $title, $emailBody);
            }
        }
    }

    /**
     * Unread count for current user
     */
    public static function getUnreadCount(int $userId): int
    {
        return (int) Database::fetchValue(
            "SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND read_at IS NULL",
            ['uid' => $userId]
        );
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(int $notificationId, int $userId): void
    {
        Database::execute(
            "UPDATE notifications SET read_at = NOW() WHERE id = :id AND user_id = :uid",
            ['id' => $notificationId, 'uid' => $userId]
        );
    }

    /**
     * Mark all as read
     */
    public static function markAllAsRead(int $userId): void
    {
        Database::execute(
            "UPDATE notifications SET read_at = NOW() WHERE user_id = :uid AND read_at IS NULL",
            ['uid' => $userId]
        );
    }
}
