<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Mailer;
use App\Core\Notifier;
use App\Core\Validator;
use App\Core\View;

/**
 * Company & System Invitation Token Controller
 * Realises FR-UCM-01, FR-UCM-02, FR-UCM-03
 */
class CompanyController
{
    // FR-UCM-01: Coordinator Company Verification Directory
    public function index(): void
    {
        Auth::requireRole('coordinator');

        $status = trim($_GET['status'] ?? '');
        $sql = "SELECT c.*, u.name as rep_user_name, u.email as rep_user_email,
                       (SELECT COUNT(*) FROM placements p WHERE p.company_id = c.id) as interns_count,
                       (SELECT COUNT(*) FROM vacancies v WHERE v.company_id = c.id) as vacancies_count
                FROM companies c
                LEFT JOIN users u ON c.registered_by = u.id
                WHERE 1=1";
        $params = [];

        if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected', 'suspended'], true)) {
            $sql .= " AND c.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";
        $companies = Database::fetchAll($sql, $params);

        // Active tokens list for Coordinator review (FR-UCM-03)
        $tokens = Database::fetchAll(
            "SELECT it.*, p.position, u_st.name as student_name, s.student_id, c.name as company_name
             FROM invitation_tokens it
             JOIN placements p ON it.placement_id = p.id
             JOIN users u_st ON p.student_user_id = u_st.id
             JOIN students s ON u_st.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             ORDER BY it.created_at DESC"
        );

        View::render('companies/index', [
            'title' => 'Company Verification & Management',
            'companies' => $companies,
            'tokens' => $tokens,
            'currentStatus' => $status,
        ], 'layouts/main');
    }

    // FR-UCM-01: Partner Company Public Registration Form
    public function registerForm(): void
    {
        View::render('companies/register', [
            'title' => 'Partner Company Registration',
        ], 'layouts/auth');
    }

    // FR-UCM-01: Process Partner Company Registration
    public function register(): void
    {
        Csrf::checkOrAbort();

        $rules = [
            'company_name' => 'required|min:3',
            'registration_no' => 'required',
            'industry' => 'required',
            'address' => 'required',
            'representative_name' => 'required|min:3',
            'representative_email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ];

        $validator = Validator::make($_POST, $rules);
        if ($validator->fails()) {
            Flash::danger($validator->firstError() ?? 'Validation failed.');
            header('Location: ' . url('companies/register'));
            exit;
        }

        $pwHash = password_hash((string)$_POST['password'], PASSWORD_BCRYPT);

        Database::transaction(function($pdo) use ($pwHash) {
            // 1. Create company record (status: pending)
            $stmtC = $pdo->prepare(
                "INSERT INTO companies (name, registration_no, industry, address, representative_name, representative_email, source, status, created_at)
                 VALUES (:name, :reg, :ind, :addr, :rname, :remail, 'partner', 'pending', NOW())"
            );
            $stmtC->execute([
                'name' => trim($_POST['company_name']),
                'reg' => trim($_POST['registration_no']),
                'ind' => trim($_POST['industry']),
                'addr' => trim($_POST['address']),
                'rname' => trim($_POST['representative_name']),
                'remail' => trim($_POST['representative_email']),
            ]);
            $companyId = (int) $pdo->lastInsertId();

            // 2. Create user account
            $stmtU = $pdo->prepare(
                "INSERT INTO users (role, name, email, password_hash, must_change_password, status, created_at)
                 VALUES ('company', :name, :email, :pw, 0, 'active', NOW())"
            );
            $stmtU->execute([
                'name' => trim($_POST['representative_name']),
                'email' => trim($_POST['representative_email']),
                'pw' => $pwHash,
            ]);
            $userId = (int) $pdo->lastInsertId();

            // Link company user & registered_by
            $pdo->prepare("INSERT INTO company_users (user_id, company_id) VALUES (?, ?)")->execute([$userId, $companyId]);
            $pdo->prepare("UPDATE companies SET registered_by = ? WHERE id = ?")->execute([$userId, $companyId]);

            // Notify coordinators
            $coordinators = Database::fetchAll("SELECT id FROM users WHERE role = 'coordinator'");
            foreach ($coordinators as $coord) {
                Notifier::send(
                    (int) $coord['id'],
                    'company_registered',
                    'New Partner Company Registration',
                    "Company '" . trim($_POST['company_name']) . "' has registered and is pending verification.",
                    '/companies?status=pending'
                );
            }
        });

        Flash::success('Company profile submitted successfully! Your account will be activated once the UNITEN Industrial Training Coordinator reviews and approves your registration.');
        header('Location: ' . url('login'));
        exit;
    }

    // FR-UCM-01: Coordinator Approve, Reject or Suspend Company
    public function updateStatus(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $companyId = (int) ($_POST['company_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');

        if (!in_array($newStatus, ['approved', 'rejected', 'suspended', 'pending'], true)) {
            Flash::danger('Invalid company status specified.');
            header('Location: ' . url('companies'));
            exit;
        }

        Database::execute(
            "UPDATE companies SET status = :status WHERE id = :id",
            ['status' => $newStatus, 'id' => $companyId]
        );

        // Notify representative
        $company = Database::fetchOne("SELECT * FROM companies WHERE id = :id", ['id' => $companyId]);
        if ($company && !empty($company['representative_email'])) {
            Mailer::send(
                $company['representative_email'],
                "InternFlow Partner Company Status Update: " . ucfirst($newStatus),
                "<p>Dear {$company['representative_name']},</p><p>Your company profile for <strong>{$company['name']}</strong> status has been updated to: <strong>" . strtoupper($newStatus) . "</strong>.</p>"
            );
        }

        Flash::success("Company status updated to '{$newStatus}'.");
        header('Location: ' . url('companies'));
        exit;
    }

    // FR-UCM-01: Company Profile View & Edit
    public function profile(): void
    {
        Auth::requireRole('company');
        $user = Auth::user();
        $company = $user['company'] ?? null;

        if (!$company) {
            Flash::danger('No company profile associated with this account.');
            header('Location: ' . url('dashboard'));
            exit;
        }

        View::render('companies/profile', [
            'title' => 'Company Profile',
            'company' => $company,
            'user' => $user,
        ], 'layouts/main');
    }

    public function updateProfile(): void
    {
        Auth::requireRole('company');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $company = $user['company'] ?? null;
        if (!$company) {
            Flash::danger('Company not found.');
            header('Location: ' . url('dashboard'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $industry = trim($_POST['industry'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name) || empty($industry) || empty($address)) {
            Flash::danger('Please fill in all company profile fields.');
            header('Location: ' . url('companies/profile'));
            exit;
        }

        Database::execute(
            "UPDATE companies SET name = :name, industry = :ind, address = :addr WHERE id = :id",
            ['name' => $name, 'ind' => $industry, 'addr' => $address, 'id' => $company['id']]
        );

        Flash::success('Company details updated successfully.');
        header('Location: ' . url('companies/profile'));
        exit;
    }

    // FR-UCM-02 / FR-UCM-03: Accept System Invitation Token
    public function acceptInvitation(): void
    {
        $token = trim($_GET['token'] ?? '');
        if (empty($token)) {
            View::render('auth/token_invalid', [
                'title' => 'Invalid Invitation Link',
                'message' => 'The invitation token is missing.'
            ], 'layouts/auth');
            return;
        }

        $tokenHash = hash('sha256', $token);
        $invite = Database::fetchOne(
            "SELECT it.*, p.position, p.start_date, p.end_date,
                    u_st.name as student_name, s.student_id, s.programme,
                    c.name as company_name
             FROM invitation_tokens it
             JOIN placements p ON it.placement_id = p.id
             JOIN users u_st ON p.student_user_id = u_st.id
             JOIN students s ON u_st.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             WHERE it.token_hash = :hash",
            ['hash' => $tokenHash]
        );

        if (!$invite) {
            View::render('auth/token_invalid', [
                'title' => 'Invalid Invitation',
                'message' => 'This invitation token does not exist or has been removed.'
            ], 'layouts/auth');
            return;
        }

        if (!empty($invite['revoked'])) {
            View::render('auth/token_invalid', [
                'title' => 'Invitation Revoked',
                'message' => 'This invitation token has been revoked by the university coordinator.'
            ], 'layouts/auth');
            return;
        }

        if (!empty($invite['used_at'])) {
            View::render('auth/token_invalid', [
                'title' => 'Token Already Used',
                'message' => 'This invitation token has already been used to create a supervisor account. Please sign in.'
            ], 'layouts/auth');
            return;
        }

        if (strtotime($invite['expires_at']) < time()) {
            View::render('auth/token_invalid', [
                'title' => 'Invitation Expired',
                'message' => 'This invitation token expired on ' . date('d M Y, H:i', strtotime($invite['expires_at'])) . '. Please request the student or coordinator to re-issue an invitation.'
            ], 'layouts/auth');
            return;
        }

        View::render('auth/token_register', [
            'title' => 'Supervisor Registration — System Invitation',
            'invite' => $invite,
            'token' => $token,
        ], 'layouts/auth');
    }

    // FR-UCM-03: Register Invited Scoped Account
    public function registerInvited(): void
    {
        Csrf::checkOrAbort();

        $token = trim($_POST['token'] ?? '');
        $tokenHash = hash('sha256', $token);

        $invite = Database::fetchOne(
            "SELECT it.*, p.company_id FROM invitation_tokens it
             JOIN placements p ON it.placement_id = p.id
             WHERE it.token_hash = :hash AND it.used_at IS NULL AND it.revoked = 0 AND it.expires_at > NOW()",
            ['hash' => $tokenHash]
        );

        if (!$invite) {
            Flash::danger('Invalid, expired or already used invitation token.');
            header('Location: ' . url('login'));
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');

        if (empty($name) || strlen($password) < 8) {
            Flash::danger('Name is required and password must be at least 8 characters long.');
            header('Location: ' . url('invite/accept', ['token' => $token]));
            exit;
        }

        if ($password !== $confirm) {
            Flash::danger('Password confirmation does not match.');
            header('Location: ' . url('invite/accept', ['token' => $token]));
            exit;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $invitedEmail = $invite['invited_email'];

        Database::transaction(function($pdo) use ($invite, $name, $invitedEmail, $passwordHash) {
            // Check if user with this email already exists
            $existing = Database::fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $invitedEmail]);
            if ($existing) {
                $userId = (int) $existing['id'];
                // Update scope
                $pdo->prepare("UPDATE users SET scope_placement_id = ?, status = 'active' WHERE id = ?")
                    ->execute([$invite['placement_id'], $userId]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO users (role, name, email, password_hash, must_change_password, status, scope_placement_id, created_at)
                     VALUES ('company', :name, :email, :pw, 0, 'active', :scope_pid, NOW())"
                );
                $stmt->execute([
                    'name' => $name,
                    'email' => $invitedEmail,
                    'pw' => $passwordHash,
                    'scope_pid' => $invite['placement_id'],
                ]);
                $userId = (int) $pdo->lastInsertId();
            }

            // Link company user
            $pdo->prepare("INSERT IGNORE INTO company_users (user_id, company_id) VALUES (?, ?)")
                ->execute([$userId, $invite['company_id']]);

            // Update placement company supervisor
            $pdo->prepare("UPDATE placements SET company_supervisor_user_id = ? WHERE id = ?")
                ->execute([$userId, $invite['placement_id']]);

            // Mark token as used
            $pdo->prepare("UPDATE invitation_tokens SET used_at = NOW() WHERE id = ?")
                ->execute([$invite['id']]);
        });

        Flash::success('Supervisor registration completed successfully! Your account is activated and linked to the student placement. You can now sign in.');
        header('Location: ' . url('login'));
        exit;
    }
}
