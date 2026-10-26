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
 * Final Report Submission & Assessment Controller
 * Realises FR-FRS-01 (Upload with originality declaration, single upload, routing)
 * Realises FR-EVAL-02 (Industrial training final report marked by University Supervisor, 20% weight)
 */
class FinalReportController
{
    // FR-FRS-01 / UC-07: Student Final Report View
    public function index(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $userId = (int) $user['id'];

        $placement = Database::fetchOne(
            "SELECT p.*, c.name as company_name, u_sup.name as supervisor_name
             FROM placements p
             JOIN companies c ON p.company_id = c.id
             LEFT JOIN users u_sup ON p.university_supervisor_id = u_sup.id
             WHERE p.student_user_id = :uid AND p.status IN ('internship_active', 'completed')
             ORDER BY p.id DESC LIMIT 1",
            ['uid' => $userId]
        );

        if (!$placement) {
            View::render('final_reports/no_active', ['title' => 'Final Report Submission'], 'layouts/main');
            return;
        }

        $report = Database::fetchOne(
            "SELECT * FROM final_reports WHERE placement_id = :pid",
            ['pid' => $placement['id']]
        );

        View::render('final_reports/index', [
            'title' => 'Industrial Training Final Report',
            'placement' => $placement,
            'report' => $report,
        ], 'layouts/main');
    }

    // FR-FRS-01 / UC-07: Student Upload Final Report
    public function upload(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];

        $placement = Database::fetchOne(
            "SELECT p.*, u.name as student_name FROM placements p
             JOIN users u ON p.student_user_id = u.id
             WHERE p.student_user_id = :uid AND p.status IN ('internship_active', 'completed')",
            ['uid' => $userId]
        );

        if (!$placement) {
            Flash::danger('Active internship placement required.');
            header('Location: ' . url('final-report'));
            exit;
        }

        $originality = isset($_POST['originality_confirmed']) && $_POST['originality_confirmed'] === '1';
        if (!$originality) {
            Flash::danger('You must confirm the originality declaration before submitting your final report.');
            header('Location: ' . url('final-report'));
            exit;
        }

        if (empty($_FILES['final_report_file']) || $_FILES['final_report_file']['error'] !== UPLOAD_ERR_OK) {
            Flash::danger('Please choose a valid final report file (PDF or DOCX, max 10MB).');
            header('Location: ' . url('final-report'));
            exit;
        }

        try {
            $stored = FileStore::store($_FILES['final_report_file'], 'reports');
        } catch (\Throwable $e) {
            Flash::danger('File upload failed: ' . $e->getMessage());
            header('Location: ' . url('final-report'));
            exit;
        }

        Database::transaction(function($pdo) use ($placement, $stored) {
            $stmt = $pdo->prepare(
                "INSERT INTO final_reports (placement_id, stored_name, original_name, originality_confirmed, submitted_at, created_at)
                 VALUES (:pid, :stored, :orig, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    stored_name = VALUES(stored_name),
                    original_name = VALUES(original_name),
                    originality_confirmed = 1,
                    submitted_at = NOW()"
            );
            $stmt->execute([
                'pid' => $placement['id'],
                'stored' => $stored['stored_name'],
                'orig' => $stored['original_name'],
            ]);

            // Automatic routing & notification to University Supervisor and Company Supervisor (FR-FRS-01)
            $title = "Final Internship Report Submitted";
            $body = "Student {$placement['student_name']} has uploaded their Industrial Training Final Report with signed originality declaration.";

            if (!empty($placement['university_supervisor_id'])) {
                Notifier::send((int)$placement['university_supervisor_id'], 'final_report', $title, $body, '/final-report/marking');
            }
            if (!empty($placement['company_supervisor_user_id'])) {
                Notifier::send((int)$placement['company_supervisor_user_id'], 'final_report', $title, $body, '/placements/interns');
            }
        });

        Flash::success('Final report submitted successfully with originality confirmation! Routed to academic and company supervisors (FR-FRS-01).');
        header('Location: ' . url('final-report'));
        exit;
    }

    // FR-EVAL-02 / UC-14: University Supervisor Report Marking
    public function markingList(): void
    {
        $this->marking();
    }

    public function marking(): void
    {
        Auth::requireAnyRole(['university_supervisor', 'coordinator']);
        $user = Auth::user();

        if ($user['role'] === 'university_supervisor') {
            $placements = Database::fetchAll(
                "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name,
                        fr.id as report_id, fr.original_name, fr.stored_name, fr.submitted_at, fr.mark as report_mark, fr.marked_by
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN students s ON u.id = s.user_id
                 JOIN companies c ON p.company_id = c.id
                 LEFT JOIN final_reports fr ON p.id = fr.placement_id
                 WHERE p.university_supervisor_id = :uid AND p.status IN ('internship_active', 'completed')
                 ORDER BY p.id DESC",
                ['uid' => $user['id']]
            );
        } else {
            // Coordinator view
            $placements = Database::fetchAll(
                "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name,
                        fr.id as report_id, fr.original_name, fr.stored_name, fr.submitted_at, fr.mark as report_mark, fr.marked_by,
                        u_sup.name as supervisor_name
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN students s ON u.id = s.user_id
                 JOIN companies c ON p.company_id = c.id
                 LEFT JOIN users u_sup ON p.university_supervisor_id = u_sup.id
                 LEFT JOIN final_reports fr ON p.id = fr.placement_id
                 WHERE p.status IN ('internship_active', 'completed')
                 ORDER BY p.id DESC"
            );
        }

        View::render('final_reports/marking', [
            'title' => 'Industrial Training Final Report Assessment',
            'placements' => $placements,
            'isCoordinator' => ($user['role'] === 'coordinator'),
        ], 'layouts/main');
    }

    // FR-EVAL-02 / UC-14: Save Report Mark
    public function markReport(): void
    {
        $this->saveMark();
    }

    public function saveMark(): void
    {
        Auth::requireAnyRole(['university_supervisor', 'coordinator']);
        Csrf::checkOrAbort();

        $user = Auth::user();
        $placementId = (int) ($_POST['placement_id'] ?? 0);
        $mark = (float) ($_POST['mark'] ?? -1);

        if ($placementId <= 0 || $mark < 0 || $mark > 100) {
            Flash::danger('Please provide a valid mark between 0.00 and 100.00.');
            header('Location: ' . url('final-report/marking'));
            exit;
        }

        Auth::requirePlacementAccess($placementId);

        $report = Database::fetchOne("SELECT * FROM final_reports WHERE placement_id = :pid", ['pid' => $placementId]);
        if (!$report) {
            Flash::danger('No final report submitted by student yet.');
            header('Location: ' . url('final-report/marking'));
            exit;
        }

        Database::query(
            "UPDATE final_reports SET mark = :mark, marked_by = :uid, updated_at = NOW() WHERE placement_id = :pid",
            [
                'mark' => $mark,
                'uid' => $user['id'],
                'pid' => $placementId,
            ]
        );

        $placement = Database::fetchOne("SELECT student_user_id FROM placements WHERE id = :pid", ['pid' => $placementId]);
        if ($placement) {
            Notifier::send(
                (int)$placement['student_user_id'],
                'report_graded',
                'Final Report Graded',
                "Your University Supervisor has graded your Industrial Training Final Report ({$mark}%).",
                '/final-report'
            );
        }

        Flash::success("Final report mark ({$mark}%) saved successfully (FR-EVAL-02).");
        header('Location: ' . url('final-report/marking'));
        exit;
    }

    // Authenticated Download
    public function download(): void
    {
        Auth::requireAuth();
        $id = (int) ($_GET['id'] ?? 0);

        $report = Database::fetchOne("SELECT * FROM final_reports WHERE id = :id", ['id' => $id]);
        if (!$report || empty($report['stored_name'])) {
            http_response_code(404);
            die("Report not found.");
        }

        Auth::requirePlacementAccess((int)$report['placement_id']);
        FileStore::serve($report['stored_name'], $report['original_name'] ?: 'Final_Report.pdf', 'application/pdf', true);
    }
}
