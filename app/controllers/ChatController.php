<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Notifier;
use App\Core\View;

/**
 * 4-Way Group Chat Controller
 * Realises FR-COM-01 / UC-18 (Unified 4-Way Group Chat linking Student, Company Supervisor, University Supervisor, Coordinator)
 */
class ChatController
{
    // FR-COM-01 / UC-18: Chat View
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $role = $user['role'];
        $userId = (int) $user['id'];

        // Find available conversations for current user
        if ($role === 'student') {
            $placements = Database::fetchAll(
                "SELECT p.id as placement_id, p.position, c.name as company_name, cv.id as conversation_id
                 FROM placements p
                 JOIN companies c ON p.company_id = c.id
                 JOIN conversations cv ON p.id = cv.placement_id
                 WHERE p.student_user_id = :uid",
                ['uid' => $userId]
            );
        } elseif ($role === 'company') {
            if (!empty($user['scope_placement_id'])) {
                $placements = Database::fetchAll(
                    "SELECT p.id as placement_id, p.position, u.name as student_name, cv.id as conversation_id
                     FROM placements p
                     JOIN users u ON p.student_user_id = u.id
                     JOIN conversations cv ON p.id = cv.placement_id
                     WHERE p.id = :pid",
                    ['pid' => $user['scope_placement_id']]
                );
            } else {
                $company = Database::fetchOne(
                    "SELECT company_id FROM company_users WHERE user_id = :uid",
                    ['uid' => $userId]
                );
                $cid = $company['company_id'] ?? 0;
                $placements = Database::fetchAll(
                    "SELECT p.id as placement_id, p.position, u.name as student_name, cv.id as conversation_id
                     FROM placements p
                     JOIN users u ON p.student_user_id = u.id
                     JOIN conversations cv ON p.id = cv.placement_id
                     WHERE p.company_id = :cid",
                    ['cid' => $cid]
                );
            }
        } elseif ($role === 'university_supervisor') {
            $placements = Database::fetchAll(
                "SELECT p.id as placement_id, p.position, u.name as student_name, c.name as company_name, cv.id as conversation_id
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN companies c ON p.company_id = c.id
                 JOIN conversations cv ON p.id = cv.placement_id
                 WHERE p.university_supervisor_id = :uid",
                ['uid' => $userId]
            );
        } else {
            // Coordinator: can monitor all conversations (FR-COM-01)
            $placements = Database::fetchAll(
                "SELECT p.id as placement_id, p.position, u.name as student_name, c.name as company_name, cv.id as conversation_id
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN companies c ON p.company_id = c.id
                 JOIN conversations cv ON p.id = cv.placement_id
                 ORDER BY cv.id DESC"
            );
        }

        // Active conversation
        $conversationId = (int) ($_GET['conversation_id'] ?? 0);
        if ($conversationId <= 0 && !empty($placements)) {
            $conversationId = (int) $placements[0]['conversation_id'];
        }

        $activePlacement = null;
        $messages = [];
        if ($conversationId > 0) {
            $activePlacement = Database::fetchOne(
                "SELECT p.*, cv.id as conversation_id, u_stu.name as student_name, c.name as company_name,
                        u_uni.name as uni_supervisor_name, p.supervisor_contact_name as company_supervisor_name
                 FROM conversations cv
                 JOIN placements p ON cv.placement_id = p.id
                 JOIN users u_stu ON p.student_user_id = u_stu.id
                 JOIN companies c ON p.company_id = c.id
                 LEFT JOIN users u_uni ON p.university_supervisor_id = u_uni.id
                 WHERE cv.id = :cid",
                ['cid' => $conversationId]
            );

            if ($activePlacement) {
                // Fetch messages
                $messages = Database::fetchAll(
                    "SELECT m.*, u.name as sender_name, u.role as sender_role
                     FROM messages m
                     JOIN users u ON m.sender_id = u.id
                     WHERE m.conversation_id = :cid
                     ORDER BY m.id ASC",
                    ['cid' => $conversationId]
                );
            }
        }

        View::render('chat/index', [
            'title' => '4-Way Internship Group Chat',
            'placements' => $placements,
            'activePlacement' => $activePlacement,
            'conversationId' => $conversationId,
            'messages' => $messages,
            'user' => $user,
        ], 'layouts/main');
    }

    // FR-COM-01: Send Message (Text or Meeting Link)
    public function send(): void
    {
        Auth::requireAuth();
        Csrf::checkOrAbort();

        $user = Auth::user();
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');
        $meetingLink = trim($_POST['meeting_link'] ?? '');

        if (!empty($meetingLink)) {
            $body = "🔗 Shared Meeting Link: " . $meetingLink . ($body ? "\n" . $body : '');
        }

        if (empty($body) || $conversationId <= 0) {
            Flash::danger('Message content cannot be empty.');
            header('Location: ' . url("chat?conversation_id={$conversationId}"));
            exit;
        }

        $conversation = Database::fetchOne("SELECT * FROM conversations WHERE id = :cid", ['cid' => $conversationId]);
        if (!$conversation) {
            Flash::danger('Conversation not found.');
            header('Location: ' . url('chat'));
            exit;
        }

        Auth::requirePlacementAccess((int)$conversation['placement_id']);

        Database::query(
            "INSERT INTO messages (conversation_id, sender_id, body, created_at)
             VALUES (:cid, :sid, :body, NOW())",
            [
                'cid' => $conversationId,
                'sid' => $user['id'],
                'body' => $body,
            ]
        );

        // Notify other conversation participants
        $placement = Database::fetchOne(
            "SELECT student_user_id, company_supervisor_user_id, university_supervisor_id 
             FROM placements WHERE id = :pid",
            ['pid' => $conversation['placement_id']]
        );

        if ($placement) {
            $participants = [
                (int)$placement['student_user_id'],
                (int)$placement['company_supervisor_user_id'],
                (int)$placement['university_supervisor_id'],
            ];

            foreach ($participants as $pId) {
                if ($pId > 0 && $pId !== (int)$user['id']) {
                    Notifier::send(
                        $pId,
                        'chat_message',
                        "New Message from {$user['name']}",
                        substr($body, 0, 120),
                        "/chat?conversation_id={$conversationId}"
                    );
                }
            }
        }

        header('Location: ' . url("chat?conversation_id={$conversationId}"));
        exit;
    }

    // FR-COM-01: AJAX message polling endpoint
    public function getMessages(): void
    {
        Auth::requireAuth();
        $conversationId = (int) ($_GET['conversation_id'] ?? 0);
        $afterId = (int) ($_GET['after_id'] ?? 0);

        if ($conversationId <= 0) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }

        $messages = Database::fetchAll(
            "SELECT m.*, u.name as sender_name, u.role as sender_role
             FROM messages m
             JOIN users u ON m.sender_id = u.id
             WHERE m.conversation_id = :cid AND m.id > :after_id
             ORDER BY m.id ASC",
            ['cid' => $conversationId, 'after_id' => $afterId]
        );

        header('Content-Type: application/json');
        echo json_encode($messages);
        exit;
    }
}
