<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Notifier;
use App\Core\View;
use App\Services\GradeEngine;

/**
 * Evaluation & Marking Controller
 * Realises FR-EVAL-01 (Host Supervisor Evaluation digital rubric, 40%)
 * Realises FR-EVAL-02 (University Supervisor Visiting Lecturer Assessment, 30%)
 * Realises FR-EVAL-03 (Weighted Final Grade Combination: 40/30/20/10, Locking)
 */
class EvaluationController
{
    // FR-EVAL-01 / UC-12: Company Supervisor List of Interns to Evaluate
    public function hostList(): void
    {
        Auth::requireRole('company');
        $user = Auth::user();

        // Check if token-scoped supervisor or full partner company
        if (!empty($user['scope_placement_id'])) {
            $placements = Database::fetchAll(
                "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN students s ON u.id = s.user_id
                 JOIN companies c ON p.company_id = c.id
                 WHERE p.id = :pid AND p.status IN ('internship_active', 'completed')
                 ORDER BY p.id DESC",
                ['pid' => $user['scope_placement_id']]
            );
        } else {
            $company = Database::fetchOne(
                "SELECT c.id FROM companies c
                 JOIN company_users cu ON c.id = cu.company_id
                 WHERE cu.user_id = :uid",
                ['uid' => $user['id']]
            );
            $cid = $company['id'] ?? 0;
            $placements = Database::fetchAll(
                "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN students s ON u.id = s.user_id
                 JOIN companies c ON p.company_id = c.id
                 WHERE p.company_id = :cid AND p.status IN ('internship_active', 'completed')
                 ORDER BY p.id DESC",
                ['cid' => $cid]
            );
        }

        // Attach evaluations summary to each placement
        foreach ($placements as &$p) {
            $evals = Database::fetchAll(
                "SELECT id, type, period_no, total_pct, submitted_at FROM evaluations 
                 WHERE placement_id = :pid AND type IN ('host_monthly', 'host_final')
                 ORDER BY type ASC, period_no ASC",
                ['pid' => $p['id']]
            );
            $p['evaluations'] = $evals;
        }
        unset($p);

        View::render('evaluations/host_list', [
            'title' => 'Host Supervisor Evaluations (40%)',
            'placements' => $placements,
        ], 'layouts/main');
    }

    // FR-EVAL-01 / UC-12: Host Rubric Evaluation Form
    public function hostRubric(): void
    {
        Auth::requireRole('company');
        $placementId = (int) ($_GET['placement_id'] ?? 0);
        $type = $_GET['type'] ?? 'host_final';
        $periodNo = isset($_GET['period_no']) ? (int)$_GET['period_no'] : null;

        Auth::requirePlacementAccess($placementId);

        $placement = Database::fetchOne(
            "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             WHERE p.id = :pid",
            ['pid' => $placementId]
        );

        if (!$placement) {
            Flash::danger('Placement not found.');
            header('Location: ' . url('evaluations/host'));
            exit;
        }

        // Check if existing evaluation exists
        $existing = Database::fetchOne(
            "SELECT * FROM evaluations 
             WHERE placement_id = :pid AND type = :type AND (period_no = :period OR (period_no IS NULL AND :period2 IS NULL))",
            ['pid' => $placementId, 'type' => $type, 'period' => $periodNo, 'period2' => $periodNo]
        );

        $scores = [];
        if ($existing && !empty($existing['scores_json'])) {
            $scores = json_decode($existing['scores_json'], true) ?: [];
        }

        View::render('evaluations/host_rubric', [
            'title' => ($type === 'host_monthly' ? "Host Monthly Evaluation (Month {$periodNo})" : 'Host Final Evaluation') . ' — 40%',
            'placement' => $placement,
            'type' => $type,
            'periodNo' => $periodNo,
            'existing' => $existing,
            'scores' => $scores,
        ], 'layouts/main');
    }

    // FR-EVAL-01 / UC-12: Submit Host Evaluation Rubric
    public function hostSubmit(): void
    {
        Auth::requireRole('company');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $placementId = (int) ($_POST['placement_id'] ?? 0);
        $type = $_POST['type'] ?? 'host_final';
        $periodNo = !empty($_POST['period_no']) ? (int)$_POST['period_no'] : null;
        $comments = trim($_POST['comments'] ?? '');

        Auth::requirePlacementAccess($placementId);

        // 9 Rubric Criteria specified in FR-EVAL-01:
        $criteria = [
            'attendance',
            'punctuality',
            'communication',
            'technical_knowledge',
            'work_quality',
            'problem_solving',
            'teamwork',
            'professional_behaviour',
            'training_objectives',
        ];

        $scores = [];
        $sum = 0;
        foreach ($criteria as $c) {
            $val = (int) ($_POST['score_' . $c] ?? 0);
            if ($val < 1 || $val > 5) {
                Flash::danger('All 9 evaluation criteria must be rated on a scale of 1 to 5.');
                header('Location: ' . url("evaluations/host-rubric?placement_id={$placementId}&type={$type}" . ($periodNo ? "&period_no={$periodNo}" : '')));
                exit;
            }
            $scores[$c] = $val;
            $sum += $val;
        }

        // Convert sum (out of 45) to percentage
        $totalPct = round(($sum / 45.0) * 100.0, 2);

        Database::transaction(function($pdo) use ($placementId, $user, $type, $periodNo, $scores, $comments, $totalPct) {
            // Delete existing if re-evaluating
            $stmtDel = $pdo->prepare(
                "DELETE FROM evaluations 
                 WHERE placement_id = :pid AND type = :type AND (period_no = :period OR (period_no IS NULL AND :period2 IS NULL))"
            );
            $stmtDel->execute(['pid' => $placementId, 'type' => $type, 'period' => $periodNo, 'period2' => $periodNo]);

            $stmtIns = $pdo->prepare(
                "INSERT INTO evaluations 
                    (placement_id, evaluator_id, type, period_no, scores_json, comments, total_pct, submitted_at, created_at)
                 VALUES 
                    (:pid, :eid, :type, :period, :scores, :comments, :total_pct, NOW(), NOW())"
            );
            $stmtIns->execute([
                'pid' => $placementId,
                'eid' => $user['id'],
                'type' => $type,
                'period' => $periodNo,
                'scores' => json_encode($scores),
                'comments' => $comments,
                'total_pct' => $totalPct,
            ]);

            // Notify University Supervisor & Coordinator
            $placement = Database::fetchOne("SELECT student_user_id, university_supervisor_id FROM placements WHERE id = :pid", ['pid' => $placementId]);
            if ($placement && !empty($placement['university_supervisor_id'])) {
                Notifier::send(
                    (int)$placement['university_supervisor_id'],
                    'evaluation_submitted',
                    'Host Supervisor Evaluation Submitted',
                    "Host Company Supervisor submitted {$type} evaluation ({$totalPct}%).",
                    "/evaluations/visiting?placement_id={$placementId}"
                );
            }
        });

        Flash::success("Host supervisor evaluation submitted successfully ({$totalPct}% converted). Counts for 40% of the overall evaluation (FR-EVAL-01).");
        header('Location: ' . url('evaluations/host'));
        exit;
    }

    // FR-EVAL-02 / UC-14: University Supervisor List of Students for Visiting Assessment
    public function visitingList(): void
    {
        Auth::requireAnyRole(['university_supervisor', 'coordinator']);
        $user = Auth::user();

        if ($user['role'] === 'university_supervisor') {
            $placements = Database::fetchAll(
                "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN students s ON u.id = s.user_id
                 JOIN companies c ON p.company_id = c.id
                 WHERE p.university_supervisor_id = :uid AND p.status IN ('internship_active', 'completed')
                 ORDER BY p.id DESC",
                ['uid' => $user['id']]
            );
        } else {
            $placements = Database::fetchAll(
                "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name, u_sup.name as supervisor_name
                 FROM placements p
                 JOIN users u ON p.student_user_id = u.id
                 JOIN students s ON u.id = s.user_id
                 JOIN companies c ON p.company_id = c.id
                 LEFT JOIN users u_sup ON p.university_supervisor_id = u_sup.id
                 WHERE p.status IN ('internship_active', 'completed')
                 ORDER BY p.id DESC"
            );
        }

        foreach ($placements as &$p) {
            $visiting = Database::fetchOne(
                "SELECT * FROM evaluations WHERE placement_id = :pid AND type = 'visiting_lecturer'",
                ['pid' => $p['id']]
            );
            $p['visiting_eval'] = $visiting;

            // Also attach GradeEngine calculation status
            $p['grade_calc'] = GradeEngine::calculateForPlacement((int)$p['id']);
        }
        unset($p);

        View::render('evaluations/visiting_list', [
            'title' => 'Visiting Lecturer Assessment (30%)',
            'placements' => $placements,
            'isCoordinator' => ($user['role'] === 'coordinator'),
        ], 'layouts/main');
    }

    // FR-EVAL-02 / UC-14: Visiting Lecturer Rubric Form
    public function visitingRubric(): void
    {
        Auth::requireAnyRole(['university_supervisor', 'coordinator']);
        $placementId = (int) ($_GET['placement_id'] ?? 0);

        Auth::requirePlacementAccess($placementId);

        $placement = Database::fetchOne(
            "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             WHERE p.id = :pid",
            ['pid' => $placementId]
        );

        if (!$placement) {
            Flash::danger('Placement not found.');
            header('Location: ' . url('evaluations/visiting'));
            exit;
        }

        $existing = Database::fetchOne(
            "SELECT * FROM evaluations WHERE placement_id = :pid AND type = 'visiting_lecturer'",
            ['pid' => $placementId]
        );

        $scores = [];
        if ($existing && !empty($existing['scores_json'])) {
            $scores = json_decode($existing['scores_json'], true) ?: [];
        }

        View::render('evaluations/visiting_rubric', [
            'title' => 'Visiting Lecturer Assessment (30%)',
            'placement' => $placement,
            'existing' => $existing,
            'scores' => $scores,
        ], 'layouts/main');
    }

    // FR-EVAL-02 / UC-14: Save Visiting Lecturer Assessment
    public function visitingSubmit(): void
    {
        Auth::requireAnyRole(['university_supervisor', 'coordinator']);
        Csrf::checkOrAbort();

        $user = Auth::user();
        $placementId = (int) ($_POST['placement_id'] ?? 0);
        $visitDate = trim($_POST['visit_date'] ?? date('Y-m-d'));
        $comments = trim($_POST['comments'] ?? '');

        Auth::requirePlacementAccess($placementId);

        // Visiting Rubric: 4 criteria (each 1 to 5)
        // 1. Technical Competence & Scope
        // 2. Oral Presentation & Defense
        // 3. Host Employer Consultation
        // 4. Academic Alignment & Logbook Review
        $rubricItems = [
            'technical_scope',
            'presentation_defense',
            'employer_consultation',
            'academic_alignment',
        ];

        $scores = ['visit_date' => $visitDate];
        $sum = 0;
        foreach ($rubricItems as $item) {
            $val = (int) ($_POST['score_' . $item] ?? 0);
            if ($val < 1 || $val > 5) {
                Flash::danger('Please rate all 4 assessment criteria from 1 to 5.');
                header('Location: ' . url("evaluations/visiting-rubric?placement_id={$placementId}"));
                exit;
            }
            $scores[$item] = $val;
            $sum += $val;
        }

        // 4 items * 5 = 20 max -> percentage
        $totalPct = round(($sum / 20.0) * 100.0, 2);

        Database::transaction(function($pdo) use ($placementId, $user, $scores, $comments, $totalPct) {
            $pdo->prepare("DELETE FROM evaluations WHERE placement_id = :pid AND type = 'visiting_lecturer'")
                ->execute(['pid' => $placementId]);

            $stmt = $pdo->prepare(
                "INSERT INTO evaluations 
                    (placement_id, evaluator_id, type, period_no, scores_json, comments, total_pct, submitted_at, created_at)
                 VALUES 
                    (:pid, :eid, 'visiting_lecturer', NULL, :scores, :comments, :total_pct, NOW(), NOW())"
            );
            $stmt->execute([
                'pid' => $placementId,
                'eid' => $user['id'],
                'scores' => json_encode($scores),
                'comments' => $comments,
                'total_pct' => $totalPct,
            ]);
        });

        Flash::success("Visiting lecturer assessment recorded successfully ({$totalPct}%). Constitutes 30% of final grade (FR-EVAL-02).");
        header('Location: ' . url('evaluations/visiting'));
        exit;
    }

    // FR-EVAL-03 / UC-17: Coordinator Final Grade Overview & Lock
    public function lockOverview(): void
    {
        Auth::requireRole('coordinator');

        $placements = Database::fetchAll(
            "SELECT p.*, u.name as student_name, s.student_id, c.name as company_name,
                    u_sup.name as supervisor_name
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             LEFT JOIN users u_sup ON p.university_supervisor_id = u_sup.id
             WHERE p.status IN ('internship_active', 'completed')
             ORDER BY p.id DESC"
        );

        $gradeSummaries = [];
        foreach ($placements as $p) {
            $gradeSummaries[$p['id']] = GradeEngine::calculateForPlacement((int)$p['id']);
        }

        View::render('evaluations/lock', [
            'title' => 'Consolidated Assessment & Final Grade Lock',
            'placements' => $placements,
            'gradeSummaries' => $gradeSummaries,
        ], 'layouts/main');
    }

    // FR-EVAL-03: Action to Lock Final Grade
    public function lockGrade(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $placementId = (int) ($_POST['placement_id'] ?? 0);

        if ($placementId <= 0) {
            Flash::danger('Invalid placement selected.');
            header('Location: ' . url('evaluations/lock'));
            exit;
        }

        $success = GradeEngine::lockGrade($placementId, (int)$user['id']);
        if ($success) {
            Flash::success('Final grade calculated and locked successfully! Student internship status updated to Completed (FR-EVAL-03).');
        } else {
            Flash::danger('Cannot lock grade: Not all 4 assessment components (Host 40%, Visiting 30%, Final Report 20%, Logbook 10%) are completed yet (Business Rule 8).');
        }

        header('Location: ' . url('evaluations/lock'));
        exit;
    }

    // FR-EVAL-03: Student View Own Final Grade
    public function myGrade(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();

        $placement = Database::fetchOne(
            "SELECT p.*, c.name as company_name, u_sup.name as supervisor_name
             FROM placements p
             JOIN companies c ON p.company_id = c.id
             LEFT JOIN users u_sup ON p.university_supervisor_id = u_sup.id
             WHERE p.student_user_id = :uid AND p.status IN ('internship_active', 'completed')
             ORDER BY p.id DESC LIMIT 1",
            ['uid' => $user['id']]
        );

        if (!$placement) {
            View::render('evaluations/no_active', ['title' => 'My Final Grade'], 'layouts/main');
            return;
        }

        $gradeData = GradeEngine::calculateForPlacement((int)$placement['id']);

        View::render('evaluations/my_grade', [
            'title' => 'My Final Internship Grade',
            'placement' => $placement,
            'gradeData' => $gradeData,
        ], 'layouts/main');
    }
}
