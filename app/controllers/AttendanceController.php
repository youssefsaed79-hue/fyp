<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\FileStore;
use App\Core\Flash;
use App\Core\Notifier;
use App\Core\Validator;
use App\Core\View;
use App\Services\AttendanceRule;
use App\Services\BatchVerificationService;

/**
 * Attendance Management Controller
 * Realises FR-ATT-01, FR-ATT-02, FR-ATT-03
 */
class AttendanceController
{
    // FR-ATT-01: Student Daily Attendance Log & History Screen
    public function index(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $userId = (int) $user['id'];

        $placement = Database::fetchOne(
            "SELECT p.*, c.name as company_name FROM placements p
             JOIN companies c ON p.company_id = c.id
             WHERE p.student_user_id = :uid AND p.status IN ('internship_active', 'completed')
             ORDER BY p.id DESC LIMIT 1",
            ['uid' => $userId]
        );

        if (!$placement) {
            View::render('attendance/no_active', [
                'title' => 'Daily Attendance',
            ], 'layouts/main');
            return;
        }

        $placementId = (int) $placement['id'];

        // Monthly stats calculation (FR-ATT-03)
        $monthlyStats = AttendanceRule::computeMonthlyAttendance($placementId);

        // Fetch all attendance logs for this placement
        $records = Database::fetchAll(
            "SELECT a.*, u_ver.name as verifier_name
             FROM attendance a
             LEFT JOIN users u_ver ON a.verified_by = u_ver.id
             WHERE a.placement_id = :pid
             ORDER BY a.work_date DESC",
            ['pid' => $placementId]
        );

        // Check if today already logged
        $todayStr = date('Y-m-d');
        $todayLogged = Database::fetchOne(
            "SELECT * FROM attendance WHERE placement_id = :pid AND work_date = :today",
            ['pid' => $placementId, 'today' => $todayStr]
        );

        View::render('attendance/index', [
            'title' => 'Daily Attendance Register',
            'placement' => $placement,
            'records' => $records,
            'monthlyStats' => $monthlyStats,
            'todayStr' => $todayStr,
            'todayLogged' => $todayLogged,
        ], 'layouts/main');
    }

    // FR-ATT-01: Submit Daily Attendance Record
    public function logDaily(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];

        $placement = Database::fetchOne(
            "SELECT * FROM placements WHERE student_user_id = :uid AND status = 'internship_active'",
            ['uid' => $userId]
        );

        if (!$placement) {
            Flash::danger('You do not have an active internship placement to log attendance for.');
            header('Location: ' . url('attendance'));
            exit;
        }

        $workDate = trim($_POST['work_date'] ?? date('Y-m-d'));
        $status = trim($_POST['status'] ?? 'present');
        $arrangement = trim($_POST['work_arrangement'] ?? 'onsite');
        $checkIn = !empty($_POST['check_in']) ? trim($_POST['check_in']) : null;
        $checkOut = !empty($_POST['check_out']) ? trim($_POST['check_out']) : null;
        $hours = !empty($_POST['hours']) ? (float) $_POST['hours'] : 8.00;
        $absenceReason = trim($_POST['absence_reason'] ?? '');

        // Validate date is within placement range
        if ($workDate < $placement['start_date'] || $workDate > $placement['end_date']) {
            Flash::danger("Attendance date must be within your approved internship period ({$placement['start_date']} to {$placement['end_date']}).");
            header('Location: ' . url('attendance'));
            exit;
        }

        // Check evidence upload if provided
        $evidencePath = null;
        if (!empty($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
            try {
                $stored = FileStore::store($_FILES['evidence'], 'evidence');
                $evidencePath = $stored['stored_name'];
            } catch (\Throwable $e) {
                Flash::danger('Evidence upload error: ' . $e->getMessage());
                header('Location: ' . url('attendance'));
                exit;
            }
        }

        try {
            Database::execute(
                "INSERT INTO attendance (placement_id, work_date, check_in, check_out, hours, work_arrangement, status, absence_reason, evidence_path, verification, created_at)
                 VALUES (:pid, :wdate, :cin, :cout, :hrs, :arr, :status, :reason, :ev, 'pending_verification', NOW())
                 ON DUPLICATE KEY UPDATE
                    check_in = VALUES(check_in), check_out = VALUES(check_out), hours = VALUES(hours),
                    work_arrangement = VALUES(work_arrangement), status = VALUES(status),
                    absence_reason = VALUES(absence_reason),
                    evidence_path = COALESCE(VALUES(evidence_path), evidence_path),
                    verification = 'pending_verification', flag_comment = NULL",
                [
                    'pid' => $placement['id'],
                    'wdate' => $workDate,
                    'cin' => $checkIn,
                    'cout' => $checkOut,
                    'hrs' => $hours,
                    'arr' => $arrangement,
                    'status' => $status,
                    'reason' => $absenceReason ?: null,
                    'ev' => $evidencePath,
                ]
            );

            Flash::success("Attendance for {$workDate} recorded as 'Pending Verification'.");
        } catch (\Throwable $e) {
            Flash::danger('Failed to record attendance: ' . $e->getMessage());
        }

        header('Location: ' . url('attendance'));
        exit;
    }

    // FR-ATT-02: Company Supervisor Batch Verification Screen
    public function batchView(): void
    {
        Auth::requireRole('company');
        $user = Auth::user();
        $companyId = (int) ($user['company']['id'] ?? 0);
        $scopePid = $user['scope_placement_id'] ?? null;
        $selectedPlacementId = (int) ($_GET['placement_id'] ?? 0);

        // Fetch company interns
        $where = "p.status IN ('internship_active', 'completed')";
        $params = [];
        if ($scopePid) {
            $where .= " AND p.id = :spid";
            $params['spid'] = $scopePid;
        } else {
            $where .= " AND p.company_id = :cid";
            $params['cid'] = $companyId;
        }

        $interns = Database::fetchAll(
            "SELECT p.*, u.name as student_name, s.student_id
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             WHERE {$where} ORDER BY u.name ASC",
            $params
        );

        if ($selectedPlacementId === 0 && !empty($interns)) {
            $selectedPlacementId = (int) $interns[0]['id'];
        }

        $records = [];
        $monthlyStats = null;
        if ($selectedPlacementId > 0) {
            Auth::requirePlacementAccess($selectedPlacementId);

            $records = Database::fetchAll(
                "SELECT a.*, u_ver.name as verifier_name
                 FROM attendance a
                 LEFT JOIN users u_ver ON a.verified_by = u_ver.id
                 WHERE a.placement_id = :pid
                 ORDER BY a.verification = 'pending_verification' DESC, a.work_date DESC",
                ['pid' => $selectedPlacementId]
            );

            $monthlyStats = AttendanceRule::computeMonthlyAttendance($selectedPlacementId);
        }

        View::render('attendance/batch', [
            'title' => 'Batch Attendance Verification',
            'interns' => $interns,
            'selectedPlacementId' => $selectedPlacementId,
            'records' => $records,
            'monthlyStats' => $monthlyStats,
        ], 'layouts/main');
    }

    // FR-ATT-02: Process Batch Verification & Flagging in One Action
    public function batchVerify(): void
    {
        Auth::requireRole('company');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $placementId = (int) ($_POST['placement_id'] ?? 0);
        Auth::requirePlacementAccess($placementId);

        $selectedIds = $_POST['record_ids'] ?? [];
        if (!is_array($selectedIds)) {
            $selectedIds = [];
        }

        $flagComments = $_POST['flag_comment'] ?? [];
        if (!is_array($flagComments)) {
            $flagComments = [];
        }

        // Filter flag comments that have actual text
        $flaggedData = [];
        foreach ($flagComments as $rId => $comment) {
            if (trim((string)$comment) !== '') {
                $flaggedData[(int)$rId] = trim((string)$comment);
            }
        }

        if (empty($selectedIds) && empty($flaggedData)) {
            Flash::warning('No attendance records were selected for verification or flagging.');
            header('Location: ' . url('attendance/batch', ['placement_id' => $placementId]));
            exit;
        }

        $batchService = new BatchVerificationService();
        $verifiedCount = $batchService->processBatch((int)$user['id'], $placementId, $selectedIds, $flaggedData);

        $flagCount = count($flaggedData);
        Flash::success("Batch verification complete: {$verifiedCount} records verified, {$flagCount} days flagged with comments.");
        header('Location: ' . url('attendance/batch', ['placement_id' => $placementId]));
        exit;
    }
}
