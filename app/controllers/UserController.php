<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Validator;
use App\Core\View;

/**
 * User Management Controller (FR-AUTH-04)
 * Coordinator manages students, university supervisors, and company accounts.
 */
class UserController
{
    // FR-AUTH-04: User Directory and Filters
    public function index(): void
    {
        Auth::requireRole('coordinator');

        $role = trim($_GET['role'] ?? '');
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $sql = "SELECT u.*, s.student_id, s.programme, s.verified, s.profile_complete,
                       c.name as company_name
                FROM users u
                LEFT JOIN students s ON u.id = s.user_id
                LEFT JOIN company_users cu ON u.id = cu.user_id
                LEFT JOIN companies c ON cu.company_id = c.id
                WHERE 1=1";
        $params = [];

        if ($role !== '' && in_array($role, ['student', 'company', 'university_supervisor', 'coordinator'], true)) {
            $sql .= " AND u.role = :role";
            $params['role'] = $role;
        }

        if ($status !== '' && in_array($status, ['active', 'suspended', 'inactive', 'pending'], true)) {
            $sql .= " AND u.status = :status";
            $params['status'] = $status;
        }

        if ($search !== '') {
            $sql .= " AND (u.name LIKE :search1 OR u.email LIKE :search2 OR s.student_id LIKE :search3)";
            $params['search1'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        $sql .= " ORDER BY u.created_at DESC";

        $users = Database::fetchAll($sql, $params);

        View::render('users/index', [
            'title' => 'User Management',
            'users' => $users,
            'currentRole' => $role,
            'currentSearch' => $search,
            'currentStatus' => $status,
        ], 'layouts/main');
    }

    // FR-AUTH-04: Create User Form (Single Student or University Supervisor)
    public function create(): void
    {
        Auth::requireRole('coordinator');

        View::render('users/create', [
            'title' => 'Create New User Account',
            'role' => $_GET['type'] ?? 'student',
        ], 'layouts/main');
    }

    // FR-AUTH-04: Store New User Account
    public function store(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $role = trim($_POST['role'] ?? 'student');
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? 'Password@123');

        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:student,university_supervisor,coordinator',
        ];

        if ($role === 'student') {
            $rules['student_id'] = 'required|unique:students,student_id';
            $rules['programme'] = 'required';
            $rules['intake'] = 'required';
        }

        $validator = Validator::make($_POST, $rules);
        if ($validator->fails()) {
            Flash::danger($validator->firstError() ?? 'Validation failed.');
            header('Location: ' . url('users/create', ['type' => $role]));
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $verified = isset($_POST['verified']) ? 1 : 0;

        Database::transaction(function($pdo) use ($role, $name, $email, $passwordHash, $verified) {
            $stmt = $pdo->prepare(
                "INSERT INTO users (role, name, email, password_hash, must_change_password, status, created_at)
                 VALUES (:role, :name, :email, :pw, 1, 'active', NOW())"
            );
            $stmt->execute([
                'role' => $role,
                'name' => $name,
                'email' => $email,
                'pw' => $passwordHash,
            ]);
            $userId = (int) $pdo->lastInsertId();

            if ($role === 'student') {
                $stmtSt = $pdo->prepare(
                    "INSERT INTO students (user_id, student_id, programme, intake, cgpa, verified, profile_complete, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 0, NOW())"
                );
                $stmtSt->execute([
                    $userId,
                    trim($_POST['student_id'] ?? ''),
                    trim($_POST['programme'] ?? ''),
                    trim($_POST['intake'] ?? ''),
                    !empty($_POST['cgpa']) ? (float) $_POST['cgpa'] : null,
                    $verified
                ]);
            }

            // Send notification email
            Mailer::send(
                $email,
                "Your InternFlow Account Has Been Created",
                "<p>Dear {$name},</p><p>An account has been created for you on InternFlow with role: <strong>{$role}</strong>.</p><p>Login: {$email}</p>"
            );
        });

        Flash::success("User '{$name}' created successfully.");
        header('Location: ' . url('users', ['role' => $role]));
        exit;
    }

    // FR-AUTH-04: Toggle User Status (Active, Suspended, Inactive)
    public function toggleStatus(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? 'active');

        if (!in_array($newStatus, ['active', 'suspended', 'inactive'], true)) {
            Flash::danger('Invalid user status specified.');
            header('Location: ' . url('users'));
            exit;
        }

        // Prevent suspending self
        if ($userId === Auth::id()) {
            Flash::danger('You cannot alter your own account status.');
            header('Location: ' . url('users'));
            exit;
        }

        Database::execute(
            "UPDATE users SET status = :status WHERE id = :id",
            ['status' => $newStatus, 'id' => $userId]
        );

        Flash::success("User status updated to '{$newStatus}'.");
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('users')));
        exit;
    }
}
