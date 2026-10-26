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
 * Placement Lifecycle & University Approval Controller
 * Realises FR-APM-03, FR-APM-05, FR-LOG-01, FR-COM-01
 */
class PlacementController
{
    // Student View: My Placement Details
    public function myPlacement(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $userId = (int) $user['id'];

        $placement = Database::fetchOne(
            "SELECT p.*, c.name as company_name, c.industry as company_industry, c.address as company_address,
                    c.representative_name, c.representative_email,
                    u_uni.name as uni_supervisor_name, u_uni.email as uni_supervisor_email,
                    u_comp.name as comp_supervisor_name, u_comp.email as comp_supervisor_email
             FROM placements p
             JOIN companies c ON p.company_id = c.id
             LEFT JOIN users u_uni ON p.university_supervisor_id = u_uni.id
             LEFT JOIN users u_comp ON p.company_supervisor_user_id = u_comp.id
             WHERE p.student_user_id = :uid
             ORDER BY p.id DESC LIMIT 1",
            ['uid' => $userId]
        );

        View::render('placements/my', [
            'title' => 'My Placement Details',
            'placement' => $placement,
            'user' => $user,
        ], 'layouts/main');
    }

    // FR-APM-03: Self-Sourced Placement Registration Form
    public function selfSourceForm(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();

        // Check if student already has confirmed placement
        $existing = Database::fetchOne(
            "SELECT id FROM placements WHERE student_user_id = :uid AND status IN ('placement_pending', 'university_approved', 'internship_active', 'completed')",
            ['uid' => $user['id']]
        );
        if ($existing) {
            Flash::warning('You already have a registered placement.');
            header('Location: ' . url('placements/my'));
            exit;
        }

        View::render('placements/self_source', [
            'title' => 'Register Self-Sourced Placement',
        ], 'layouts/main');
    }

    // FR-APM-03 / FR-UCM-02: Process Self-Sourced Placement Registration
    public function selfSource(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];

        // Eligibility check
        if (empty($user['profile_complete']) || empty($user['verified'])) {
            Flash::danger('Eligibility Gate: You must have a verified profile and uploaded CV before registering a placement.');
            header('Location: ' . url('profile'));
            exit;
        }

        $rules = [
            'company_name' => 'required|min:3',
            'registration_no' => 'required',
            'industry' => 'required',
            'address' => 'required',
            'supervisor_name' => 'required|min:3',
            'supervisor_email' => 'required|email',
            'position' => 'required|min:3',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ];

        $validator = Validator::make($_POST, $rules);
        if ($validator->fails()) {
            Flash::danger($validator->firstError() ?? 'Validation failed.');
            header('Location: ' . url('placements/self-source'));
            exit;
        }

        Database::transaction(function($pdo) use ($userId, $user) {
            // 1. Create or find self-sourced company
            $compName = trim($_POST['company_name']);
            $compEmail = trim($_POST['supervisor_email']);

            $stmtC = $pdo->prepare(
                "INSERT INTO companies (name, registration_no, industry, address, representative_name, representative_email, source, status, created_at)
                 VALUES (:name, :reg, :ind, :addr, :rname, :remail, 'self_sourced', 'approved', NOW())"
            );
            $stmtC->execute([
                'name' => $compName,
                'reg' => trim($_POST['registration_no']),
                'ind' => trim($_POST['industry']),
                'addr' => trim($_POST['address']),
                'rname' => trim($_POST['supervisor_name']),
                'remail' => $compEmail,
            ]);
            $companyId = (int) $pdo->lastInsertId();

            // 2. Create placement record (placement_pending)
            $stmtP = $pdo->prepare(
                "INSERT INTO placements (student_user_id, company_id, source, position, start_date, end_date, supervisor_contact_name, supervisor_contact_email, status, created_at)
                 VALUES (:uid, :cid, 'self_sourced', :pos, :start, :end, :sname, :semail, 'placement_pending', NOW())"
            );
            $stmtP->execute([
                'uid' => $userId,
                'cid' => $companyId,
                'pos' => trim($_POST['position']),
                'start' => trim($_POST['start_date']),
                'end' => trim($_POST['end_date']),
                'sname' => trim($_POST['supervisor_name']),
                'semail' => $compEmail,
            ]);
            $placementId = (int) $pdo->lastInsertId();

            // 3. Auto-withdraw any other active applications (FR-APM-04)
            $pdo->prepare("UPDATE applications SET status = 'withdrawn' WHERE student_user_id = ? AND status NOT IN ('rejected', 'withdrawn')")
                ->execute([$userId]);

            // 4. Generate System Invitation Token (FR-UCM-02)
            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $expiryHours = (int) (Database::fetchValue("SELECT setting_value FROM settings WHERE setting_key = 'token_expiry_hours'") ?: 72);
            $expiresAt = date('Y-m-d H:i:s', time() + ($expiryHours * 3600));

            $stmtT = $pdo->prepare(
                "INSERT INTO invitation_tokens (token_hash, placement_id, invited_email, expires_at, created_at)
                 VALUES (:hash, :pid, :email, :exp, NOW())"
            );
            $stmtT->execute([
                'hash' => $tokenHash,
                'pid' => $placementId,
                'email' => $compEmail,
                'exp' => $expiresAt,
            ]);

            // 5. Send invitation email with token link (FR-UCM-02)
            $inviteUrl = url('invite/accept', ['token' => $rawToken]);
            $subject = "UNITEN Industrial Training Host Supervisor System Invitation";
            $body = "<h3>Dear " . htmlspecialchars($_POST['supervisor_name']) . ",</h3>
                <p>UNITEN student <strong>{$user['name']}</strong> has registered you as their workplace host supervisor for a 12-week industrial training placement.</p>
                <p><strong>Placement Details:</strong><br>
                Position: " . htmlspecialchars($_POST['position']) . "<br>
                Duration: " . htmlspecialchars($_POST['start_date']) . " to " . htmlspecialchars($_POST['end_date']) . "</p>
                <p>Please register your scoped supervisor account using your single-use System Invitation Token link:</p>
                <p><a href='{$inviteUrl}'>{$inviteUrl}</a></p>
                <p>This invitation link is valid for {$expiryHours} hours.</p>
                <hr><p><small>UNITEN College of Computing &amp; Informatics</small></p>";

            Mailer::send($compEmail, $subject, $body);

            // Notify coordinator
            $coordinators = Database::fetchAll("SELECT id FROM users WHERE role = 'coordinator'");
            foreach ($coordinators as $coord) {
                Notifier::send(
                    (int) $coord['id'],
                    'self_source_registered',
                    'Self-Sourced Placement Registered',
                    "Student {$user['name']} registered self-sourced placement at {$compName}. Token dispatched.",
                    '/placements/approvals'
                );
            }
        });

        Flash::success('Self-sourced placement registered! A System Invitation Token has been emailed to your supervisor, and your placement is pending university approval (FR-APM-03).');
        header('Location: ' . url('placements/my'));
        exit;
    }

    // FR-APM-05: Coordinator Placement Approvals Queue
    public function approvals(): void
    {
        Auth::requireRole('coordinator');

        $status = trim($_GET['status'] ?? 'placement_pending');
        $sql = "SELECT p.*, u_st.name as student_name, u_st.email as student_email, s.student_id, s.programme, s.cgpa,
                       c.name as company_name, c.source as company_source, c.industry as company_industry,
                       u_uni.name as uni_supervisor_name
                FROM placements p
                JOIN users u_st ON p.student_user_id = u_st.id
                JOIN students s ON u_st.id = s.user_id
                JOIN companies c ON p.company_id = c.id
                LEFT JOIN users u_uni ON p.university_supervisor_id = u_uni.id
                WHERE 1=1";
        $params = [];

        if ($status !== 'all') {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC";
        $placements = Database::fetchAll($sql, $params);

        View::render('placements/approvals', [
            'title' => 'Placement Approvals Administration',
            'placements' => $placements,
            'currentStatus' => $status,
        ], 'layouts/main');
    }

    // FR-APM-05: Approve or Reject Pending Placement
    public function approve(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $placementId = (int) ($_POST['placement_id'] ?? 0);
        $action = trim($_POST['action'] ?? 'approve'); // 'approve' or 'reject'
        $coordinatorId = (int) Auth::id();

        $placement = Database::fetchOne("SELECT * FROM placements WHERE id = :id", ['id' => $placementId]);
        if (!$placement) {
            Flash::danger('Placement record not found.');
            header('Location: ' . url('placements/approvals'));
            exit;
        }

        if ($action === 'reject') {
            Database::execute(
                "UPDATE placements SET status = 'rejected', approved_by = :uid, approved_at = NOW() WHERE id = :id",
                ['uid' => $coordinatorId, 'id' => $placementId]
            );
            Notifier::send(
                (int) $placement['student_user_id'],
                'placement_rejected',
                'Placement Request Rejected',
                'Your internship placement request was not approved by the coordinator. Please consult with CCI.',
                '/placements/my'
            );
            Flash::warning('Placement rejected.');
            header('Location: ' . url('placements/approvals'));
            exit;
        }

        // Action: Approve -> Move to 'university_approved'
        Database::execute(
            "UPDATE placements SET status = 'university_approved', approved_by = :uid, approved_at = NOW() WHERE id = :id",
            ['uid' => $coordinatorId, 'id' => $placementId]
        );

        Notifier::send(
            (int) $placement['student_user_id'],
            'placement_approved',
            'Placement Approved by University',
            'Your internship placement has been approved! It will be activated for monitoring upon training start date.',
            '/placements/my'
        );

        Flash::success("Placement #{$placementId} approved and moved to 'University Approved'. You may now assign a university supervisor.");
        header('Location: ' . url('placements/approvals'));
        exit;
    }

    // FR-APM-05 / FR-LOG-01 / FR-COM-01: Activate Placement
    public function activate(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $placementId = (int) ($_POST['placement_id'] ?? 0);
        $placement = Database::fetchOne("SELECT * FROM placements WHERE id = :id", ['id' => $placementId]);

        if (!$placement || $placement['status'] !== 'university_approved') {
            Flash::danger('Placement must be in University Approved status before activation.');
            header('Location: ' . url('placements/approvals'));
            exit;
        }

        Database::transaction(function($pdo) use ($placementId, $placement) {
            // 1. Move to 'internship_active'
            $pdo->prepare("UPDATE placements SET status = 'internship_active' WHERE id = ?")->execute([$placementId]);

            // 2. AUTOMATIC LOGBOOK GENERATION (FR-LOG-01):
            // "On activation of the internship generate one weekly logbook per week between approved start and end dates (≈12)"
            $start = new \DateTime($placement['start_date']);
            $end = new \DateTime($placement['end_date']);
            $interval = new \DateInterval('P7D');
            $weekNo = 1;

            $stmtLog = $pdo->prepare(
                "INSERT IGNORE INTO logbooks (placement_id, week_no, week_start, week_end, status, created_at)
                 VALUES (?, ?, ?, ?, 'draft', NOW())"
            );

            $currentStart = clone $start;
            while ($currentStart < $end && $weekNo <= 12) {
                $weekEnd = clone $currentStart;
                $weekEnd->modify('+6 days');
                if ($weekEnd > $end) {
                    $weekEnd = clone $end;
                }

                $stmtLog->execute([
                    $placementId,
                    $weekNo,
                    $currentStart->format('Y-m-d'),
                    $weekEnd->format('Y-m-d'),
                ]);

                $currentStart->modify('+7 days');
                $weekNo++;
            }

            // 3. UNIFIED 4-WAY GROUP CHAT INITIALIZATION (FR-COM-01):
            // "On placement activation create ONE group conversation linking Student, Company Supervisor, Uni Supervisor and Coordinator"
            $pdo->prepare("INSERT IGNORE INTO conversations (placement_id, created_at) VALUES (?, NOW())")
                ->execute([$placementId]);
            $conv = Database::fetchOne("SELECT id FROM conversations WHERE placement_id = :pid", ['pid' => $placementId]);
            if ($conv) {
                $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, body, created_at) VALUES (?, ?, ?, NOW())")
                    ->execute([
                        $conv['id'],
                        Auth::id(),
                        "Unified 4-Way Internship Chat initiated for placement #{$placementId}. Welcome Student, Host Supervisor, and Visiting Lecturer."
                    ]);
            }

            // 4. Notify student
            Notifier::send(
                (int) $placement['student_user_id'],
                'internship_activated',
                '🚀 Internship Activated!',
                'Your industrial training is now active! Daily attendance logging and weekly digital logbooks are now unlocked.',
                '/dashboard'
            );
        });

        Flash::success("Placement #{$placementId} activated! 12 weekly logbooks generated and 4-way group chat conversation created.");
        header('Location: ' . url('placements/approvals'));
        exit;
    }

    // Company: View Current Interns
    public function companyInterns(): void
    {
        Auth::requireRole('company');
        $user = Auth::user();
        $companyId = (int) ($user['company']['id'] ?? 0);
        $scopePid = $user['scope_placement_id'] ?? null;

        $where = "1=1";
        $params = [];
        if ($scopePid) {
            $where = "p.id = :spid";
            $params['spid'] = $scopePid;
        } else {
            $where = "p.company_id = :cid";
            $params['cid'] = $companyId;
        }

        $interns = Database::fetchAll(
            "SELECT p.*, u.name as student_name, u.email as student_email, s.student_id, s.programme,
                    u_uni.name as uni_supervisor_name
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             LEFT JOIN users u_uni ON p.university_supervisor_id = u_uni.id
             WHERE {$where}
             ORDER BY p.status ASC, p.start_date DESC",
            $params
        );

        View::render('placements/interns', [
            'title' => 'My Interns',
            'interns' => $interns,
        ], 'layouts/main');
    }

    // Coordinator: Monitoring & Late Submissions Overview
    public function monitoring(): void
    {
        Auth::requireRole('coordinator');

        $activePlacements = Database::fetchAll(
            "SELECT p.*, u_st.name as student_name, s.student_id, c.name as company_name,
                    u_uni.name as uni_supervisor_name,
                    (SELECT COUNT(*) FROM attendance a WHERE a.placement_id = p.id AND a.verification = 'verified') as verified_att,
                    (SELECT COUNT(*) FROM logbooks l WHERE l.placement_id = p.id AND l.status = 'approved') as approved_logs,
                    (SELECT COUNT(*) FROM logbooks l WHERE l.placement_id = p.id AND l.status = 'draft' AND l.week_end < CURDATE()) as overdue_logs
             FROM placements p
             JOIN users u_st ON p.student_user_id = u_st.id
             JOIN students s ON u_st.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             LEFT JOIN users u_uni ON p.university_supervisor_id = u_uni.id
             WHERE p.status = 'internship_active'
             ORDER BY overdue_logs DESC, p.start_date ASC"
        );

        View::render('placements/monitoring', [
            'title' => 'Internship Monitoring & Late Submissions',
            'activePlacements' => $activePlacements,
        ], 'layouts/main');
    }
}
