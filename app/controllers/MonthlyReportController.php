<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\FileStore;
use App\Core\Flash;
use App\Core\Notifier;
use App\Core\View;

/**
 * Monthly Progress Report Management Controller
 * Realises FR-MPR-01 (Single upload once per 4-week period, monitoring & feedback, no separate mark)
 */
class MonthlyReportController
{
    // FR-MPR-01: Student Monthly Reports Screen
    public function index(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $userId = (int) $user['id'];

        $placement = Database::fetchOne(
            "SELECT * FROM placements WHERE student_user_id = :uid AND status IN ('internship_active', 'completed')
             ORDER BY id DESC LIMIT 1",
            ['uid' => $userId]
        );

        if (!$placement) {
            View::render('monthly_reports/no_active', ['title' => 'Monthly Reports'], 'layouts/main');
            return;
        }

        $reports = Database::fetchAll(
            "SELECT * FROM monthly_reports WHERE placement_id = :pid ORDER BY period_no ASC",
            ['pid' => $placement['id']]
        );

        $reportsByPeriod = [];
        foreach ($reports as $r) {
            $reportsByPeriod[(int)$r['period_no']] = $r;
        }

        View::render('monthly_reports/index', [
            'title' => 'Monthly Progress Reports',
            'placement' => $placement,
            'reportsByPeriod' => $reportsByPeriod,
        ], 'layouts/main');
    }

    // FR-MPR-01: Submit Monthly Report
    public function submit(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];
        $periodNo = (int) ($_POST['period_no'] ?? 1);
        $summary = trim($_POST['summary'] ?? '');

        if ($periodNo < 1 || $periodNo > 3) {
            Flash::danger('Invalid reporting period (valid: 1, 2, or 3 for 12 weeks).');
            header('Location: ' . url('monthly-reports'));
            exit;
        }

        if (empty($summary)) {
            Flash::danger('Please write a progress summary for this 4-week period.');
            header('Location: ' . url('monthly-reports'));
            exit;
        }

        $placement = Database::fetchOne(
            "SELECT p.*, u.name as student_name FROM placements p
             JOIN users u ON p.student_user_id = u.id
             WHERE p.student_user_id = :uid AND p.status IN ('internship_active', 'completed')",
            ['uid' => $userId]
        );

        if (!$placement) {
            Flash::danger('Active placement required.');
            header('Location: ' . url('monthly-reports'));
            exit;
        }

        $storedName = null;
        $originalName = null;
        if (!empty($_FILES['report_file']) && $_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $stored = FileStore::store($_FILES['report_file'], 'reports');
                $storedName = $stored['stored_name'];
                $originalName = $stored['original_name'];
            } catch (\Throwable $e) {
                Flash::danger('File upload failed: ' . $e->getMessage());
                header('Location: ' . url('monthly-reports'));
                exit;
            }
        }

        Database::transaction(function($pdo) use ($placement, $periodNo, $summary, $storedName, $originalName) {
            $stmt = $pdo->prepare(
                "INSERT INTO monthly_reports (placement_id, period_no, summary, stored_name, original_name, submitted_at, created_at)
                 VALUES (:pid, :period, :summary, :stored, :orig, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    summary = VALUES(summary),
                    stored_name = COALESCE(VALUES(stored_name), stored_name),
                    original_name = COALESCE(VALUES(original_name), original_name),
                    submitted_at = NOW()"
            );
            $stmt->execute([
                'pid' => $placement['id'],
                'period' => $periodNo,
                'summary' => $summary,
                'stored' => $storedName,
                'orig' => $originalName,
            ]);

            // Notify both evaluators automatically (FR-MPR-01)
            $title = "Monthly Progress Report Submitted (Period {$periodNo})";
            $body = "Student {$placement['student_name']} has submitted their Month {$periodNo} (Weeks " . (($periodNo - 1) * 4 + 1) . "–" . ($periodNo * 4) . ") progress report.";

            if (!empty($placement['company_supervisor_user_id'])) {
                Notifier::send((int)$placement['company_supervisor_user_id'], 'monthly_report', $title, $body, '/monthly-reports/supervisor?placement_id=' . $placement['id']);
            }
            if (!empty($placement['university_supervisor_id'])) {
                Notifier::send((int)$placement['university_supervisor_id'], 'monthly_report', $title, $body, '/monthly-reports/supervisor?placement_id=' . $placement['id']);
            }
        });

        Flash::success("Month {$periodNo} progress summary submitted successfully! Both evaluators have been notified (FR-MPR-01).");
        header('Location: ' . url('monthly-reports'));
        exit;
    }

    // FR-MPR-01: Supervisor View Monthly Reports
    public function supervisorView(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $placementId = (int) ($_GET['placement_id'] ?? 0);

        if ($placementId > 0) {
            Auth::requirePlacementAccess($placementId);
            $placement = Database::fetchOne(
                "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN students s ON u.id = s.user_id
                 JOIN companies c ON p.company_id = c.id
                 WHERE p.id = :id",
                ['id' => $placementId]
            );
        } else {
            // Find first assigned student
            $placement = Database::fetchOne(
                "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN students s ON u.id = s.user_id
                 JOIN companies c ON p.company_id = c.id
                 WHERE p.university_supervisor_id = :uid OR p.company_supervisor_user_id = :uid
                 ORDER BY p.id DESC LIMIT 1",
                ['uid' => $user['id']]
            );
        }

        $reports = [];
        if ($placement) {
            $reports = Database::fetchAll(
                "SELECT * FROM monthly_reports WHERE placement_id = :pid ORDER BY period_no ASC",
                ['pid' => $placement['id']]
            );
        }

        View::render('monthly_reports/supervisor', [
            'title' => 'Monthly Progress Reports Monitoring',
            'placement' => $placement,
            'reports' => $reports,
        ], 'layouts/main');
    }

    // Download Monthly Report File
    public function download(): void
    {
        Auth::requireAuth();
        $id = (int) ($_GET['id'] ?? 0);

        $report = Database::fetchOne("SELECT * FROM monthly_reports WHERE id = :id", ['id' => $id]);
        if (!$report || empty($report['stored_name'])) {
            http_response_code(404);
            die("Report attachment not found.");
        }

        Auth::requirePlacementAccess((int) $report['placement_id']);
        FileStore::serve($report['stored_name'], $report['original_name'] ?: 'Monthly_Report.pdf', 'application/pdf', true);
    }
}
