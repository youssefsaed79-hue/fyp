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

/**
 * Digital Logbook Management Controller
 * Realises FR-LOG-01, FR-LOG-02, FR-LOG-03, FR-LOG-04
 */
class LogbookController
{
    // FR-LOG-02: Student Weekly Logbook Index Screen
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
            View::render('logbooks/no_active', ['title' => 'Weekly Logbooks'], 'layouts/main');
            return;
        }

        $logbooks = Database::fetchAll(
            "SELECT * FROM logbooks WHERE placement_id = :pid ORDER BY week_no ASC",
            ['pid' => $placement['id']]
        );

        View::render('logbooks/index', [
            'title' => 'Weekly Digital Logbooks',
            'placement' => $placement,
            'logbooks' => $logbooks,
        ], 'layouts/main');
    }

    // FR-LOG-02 / FR-LOG-04: View Detailed Logbook & Revision History
    public function view(): void
    {
        Auth::requireAuth();
        $id = (int) ($_GET['id'] ?? 0);

        $logbook = Database::fetchOne(
            "SELECT l.*, p.student_user_id, p.company_id, p.university_supervisor_id, p.company_supervisor_user_id,
                    u_st.name as student_name, s.student_id, c.name as company_name
             FROM logbooks l
             JOIN placements p ON l.placement_id = p.id
             JOIN users u_st ON p.student_user_id = u_st.id
             JOIN students s ON u_st.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             WHERE l.id = :id",
            ['id' => $id]
        );

        if (!$logbook) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Logbook Not Found'], 'layouts/main');
            return;
        }

        Auth::requirePlacementAccess((int) $logbook['placement_id']);

        // Fetch attachments
        $attachments = Database::fetchAll(
            "SELECT * FROM logbook_attachments WHERE logbook_id = :id",
            ['id' => $id]
        );

        // Fetch dual-party reviews
        $reviews = Database::fetchAll(
            "SELECT lr.*, u.name as reviewer_name
             FROM logbook_reviews lr
             JOIN users u ON lr.reviewer_id = u.id
             WHERE lr.logbook_id = :id
             ORDER BY lr.created_at ASC",
            ['id' => $id]
        );

        // Fetch revision snapshots (FR-LOG-04)
        $revisions = Database::fetchAll(
            "SELECT * FROM logbook_revisions WHERE logbook_id = :id ORDER BY created_at DESC",
            ['id' => $id]
        );

        View::render('logbooks/view', [
            'title' => "Week {$logbook['week_no']} Logbook",
            'logbook' => $logbook,
            'attachments' => $attachments,
            'reviews' => $reviews,
            'revisions' => $revisions,
            'currentUser' => Auth::user(),
        ], 'layouts/main');
    }

    // FR-LOG-02 / FR-LOG-04: Edit / Complete Logbook Entry Form
    public function edit(): void
    {
        Auth::requireRole('student');
        $id = (int) ($_GET['id'] ?? 0);
        $user = Auth::user();

        $logbook = Database::fetchOne(
            "SELECT l.*, p.student_user_id FROM logbooks l
             JOIN placements p ON l.placement_id = p.id
             WHERE l.id = :id",
            ['id' => $id]
        );

        if (!$logbook || (int)$logbook['student_user_id'] !== (int)$user['id']) {
            Flash::danger('Logbook not found or access denied.');
            header('Location: ' . url('logbooks'));
            exit;
        }

        // Cannot edit if already approved or company reviewed (unless returned)
        if (in_array($logbook['status'], ['approved', 'company_reviewed'], true)) {
            Flash::info('This logbook has already been reviewed/approved and cannot be edited.');
            header('Location: ' . url('logbooks/view', ['id' => $id]));
            exit;
        }

        $attachments = Database::fetchAll("SELECT * FROM logbook_attachments WHERE logbook_id = :id", ['id' => $id]);

        View::render('logbooks/edit', [
            'title' => "Edit Week {$logbook['week_no']} Logbook",
            'logbook' => $logbook,
            'attachments' => $attachments,
        ], 'layouts/main');
    }

    // FR-LOG-02 / FR-LOG-04: Save or Submit Logbook
    public function save(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $id = (int) ($_POST['logbook_id'] ?? 0);

        $logbook = Database::fetchOne(
            "SELECT l.*, p.student_user_id, p.company_supervisor_user_id, p.university_supervisor_id,
                    p.company_id, u_st.name as student_name
             FROM logbooks l
             JOIN placements p ON l.placement_id = p.id
             JOIN users u_st ON p.student_user_id = u_st.id
             WHERE l.id = :id",
            ['id' => $id]
        );

        if (!$logbook || (int)$logbook['student_user_id'] !== (int)$user['id']) {
            Flash::danger('Logbook record not found or access denied.');
            header('Location: ' . url('logbooks'));
            exit;
        }

        $isSubmit = isset($_POST['submit_entry']);
        $tasks = trim($_POST['tasks'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        $challenges = trim($_POST['challenges'] ?? '');
        $solutions = trim($_POST['solutions'] ?? '');
        $reflection = trim($_POST['reflection'] ?? '');
        $hours = (float) ($_POST['hours'] ?? 40.0);

        if ($isSubmit && (empty($tasks) || empty($reflection))) {
            Flash::danger('Tasks performed and weekly reflection are mandatory to submit the logbook.');
            header('Location: ' . url('logbooks/edit', ['id' => $id]));
            exit;
        }

        // Status transition: if draft -> submitted; if returned -> resubmitted; if saving draft -> draft
        $newStatus = $logbook['status'];
        if ($isSubmit) {
            $newStatus = ($logbook['status'] === 'returned') ? 'resubmitted' : 'submitted';
        }

        Database::transaction(function($pdo) use ($id, $tasks, $skills, $challenges, $solutions, $reflection, $hours, $newStatus, $isSubmit, $logbook) {
            $pdo->prepare(
                "UPDATE logbooks
                 SET tasks = :tasks, skills = :skills, challenges = :ch, solutions = :sol,
                     reflection = :ref, hours = :hrs, status = :st,
                     submitted_at = IF(:is_sub = 1, NOW(), submitted_at)
                 WHERE id = :id"
            )->execute([
                'tasks' => $tasks,
                'skills' => $skills,
                'ch' => $challenges,
                'sol' => $solutions,
                'ref' => $reflection,
                'hrs' => $hours,
                'st' => $newStatus,
                'is_sub' => $isSubmit ? 1 : 0,
                'id' => $id,
            ]);

            // Handle optional file attachments
            if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                $stored = FileStore::store($_FILES['attachment'], 'evidence');
                $pdo->prepare(
                    "INSERT INTO logbook_attachments (logbook_id, original_name, stored_name, mime, size, created_at)
                     VALUES (?, ?, ?, ?, ?, NOW())"
                )->execute([$id, $stored['original_name'], $stored['stored_name'], $stored['mime'], $stored['size']]);
            }

            // FR-LOG-04: Revision Snapshot on Submit / Resubmit
            if ($isSubmit) {
                $snapshot = [
                    'week_no' => $logbook['week_no'],
                    'tasks' => $tasks,
                    'skills' => $skills,
                    'challenges' => $challenges,
                    'solutions' => $solutions,
                    'reflection' => $reflection,
                    'hours' => $hours,
                    'timestamp' => date('Y-m-d H:i:s'),
                ];
                $pdo->prepare(
                    "INSERT INTO logbook_revisions (logbook_id, snapshot_json, created_at) VALUES (?, ?, NOW())"
                )->execute([$id, json_encode($snapshot)]);

                // Route automatically to company supervisor and university supervisor (FR-LOG-02)
                $title = "Week {$logbook['week_no']} Logbook Submitted";
                $body = "Student {$logbook['student_name']} has submitted their Week {$logbook['week_no']} training logbook for review.";

                if (!empty($logbook['company_supervisor_user_id'])) {
                    Notifier::send((int)$logbook['company_supervisor_user_id'], 'logbook_submitted', $title, $body, "/logbooks/view?id={$id}");
                }
                if (!empty($logbook['university_supervisor_id'])) {
                    Notifier::send((int)$logbook['university_supervisor_id'], 'logbook_submitted', $title, $body, "/logbooks/view?id={$id}");
                }
            }
        });

        if ($isSubmit) {
            Flash::success("Week {$logbook['week_no']} logbook submitted successfully! Routed to company and university supervisors.");
            header('Location: ' . url('logbooks/view', ['id' => $id]));
        } else {
            Flash::info("Draft saved successfully.");
            header('Location: ' . url('logbooks'));
        }
        exit;
    }

    // FR-LOG-03: Company Supervisor Review Queue
    public function companyReviewList(): void
    {
        Auth::requireRole('company');
        $user = Auth::user();
        $companyId = (int) ($user['company']['id'] ?? 0);
        $scopePid = $user['scope_placement_id'] ?? null;
        $placementId = (int) ($_GET['placement_id'] ?? 0);

        $where = "1=1";
        $params = [];
        if ($scopePid) {
            $where .= " AND p.id = :spid";
            $params['spid'] = $scopePid;
        } elseif ($companyId) {
            $where .= " AND p.company_id = :cid";
            $params['cid'] = $companyId;
        }

        if ($placementId > 0) {
            $where .= " AND l.placement_id = :pid";
            $params['pid'] = $placementId;
        }

        $logbooks = Database::fetchAll(
            "SELECT l.*, u_st.name as student_name, s.student_id, p.position
             FROM logbooks l
             JOIN placements p ON l.placement_id = p.id
             JOIN users u_st ON p.student_user_id = u_st.id
             JOIN students s ON u_st.id = s.user_id
             WHERE {$where} AND l.status IN ('submitted', 'company_reviewed', 'approved', 'returned', 'resubmitted')
             ORDER BY l.status = 'submitted' DESC, l.week_no DESC",
            $params
        );

        View::render('logbooks/company_review', [
            'title' => 'Company Logbook Review',
            'logbooks' => $logbooks,
        ], 'layouts/main');
    }

    // FR-LOG-03: University Supervisor Review & Grading Queue (10% component)
    public function supervisorList(): void
    {
        Auth::requireRole('university_supervisor');
        $user = Auth::user();
        $userId = (int) $user['id'];
        $placementId = (int) ($_GET['placement_id'] ?? 0);

        $where = "p.university_supervisor_id = :uid";
        $params = ['uid' => $userId];

        if ($placementId > 0) {
            $where .= " AND l.placement_id = :pid";
            $params['pid'] = $placementId;
        }

        $logbooks = Database::fetchAll(
            "SELECT l.*, u_st.name as student_name, s.student_id, c.name as company_name
             FROM logbooks l
             JOIN placements p ON l.placement_id = p.id
             JOIN users u_st ON p.student_user_id = u_st.id
             JOIN students s ON u_st.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             WHERE {$where} AND l.status IN ('submitted', 'company_reviewed', 'approved', 'returned', 'resubmitted')
             ORDER BY l.status = 'company_reviewed' DESC, l.status = 'submitted' DESC, l.week_no DESC",
            $params
        );

        View::render('logbooks/supervisor_list', [
            'title' => 'Grade Weekly Logbooks (10%)',
            'logbooks' => $logbooks,
        ], 'layouts/main');
    }

    // FR-LOG-03: Dual-Party Review Action (Comment, Approve, Return, Grade)
    public function reviewAction(): void
    {
        Auth::requireAuth();
        Csrf::checkOrAbort();

        $user = Auth::user();
        $logbookId = (int) ($_POST['logbook_id'] ?? 0);
        $action = trim($_POST['action'] ?? 'comment'); // 'comment', 'approve', 'return'
        $comment = trim($_POST['comment'] ?? '');
        $grade = !empty($_POST['grade']) ? (float) $_POST['grade'] : null;

        $logbook = Database::fetchOne(
            "SELECT l.*, p.student_user_id, p.university_supervisor_id, p.company_supervisor_user_id, p.company_id
             FROM logbooks l
             JOIN placements p ON l.placement_id = p.id
             WHERE l.id = :id",
            ['id' => $logbookId]
        );

        if (!$logbook) {
            Flash::danger('Logbook not found.');
            header('Location: ' . url('dashboard'));
            exit;
        }

        Auth::requirePlacementAccess((int) $logbook['placement_id']);

        if (empty($comment)) {
            Flash::danger('Please provide review feedback or comments.');
            header('Location: ' . url('logbooks/view', ['id' => $logbookId]));
            exit;
        }

        Database::transaction(function($pdo) use ($user, $logbookId, $action, $comment, $grade, $logbook) {
            $reviewerRole = $user['role'];

            // 1. Insert review record
            $stmtR = $pdo->prepare(
                "INSERT INTO logbook_reviews (logbook_id, reviewer_id, reviewer_role, action, comment, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmtR->execute([$logbookId, $user['id'], $reviewerRole, $action, $comment]);

            // 2. Status Transition Logic (FR-LOG-03)
            // Draft -> Submitted -> Company Reviewed -> Supervisor Reviewed and Approved, or Returned -> Resubmitted
            $newStatus = $logbook['status'];
            if ($action === 'return') {
                $newStatus = 'returned';
                Notifier::send(
                    (int) $logbook['student_user_id'],
                    'logbook_returned',
                    "Week {$logbook['week_no']} Logbook Returned for Correction",
                    "Your logbook was returned with feedback: '{$comment}'. Please correct and resubmit.",
                    "/logbooks/edit?id={$logbookId}"
                );
            } elseif ($action === 'approve') {
                if ($reviewerRole === 'company') {
                    $newStatus = 'company_reviewed';
                    Notifier::send(
                        (int) $logbook['student_user_id'],
                        'logbook_company_approved',
                        "Week {$logbook['week_no']} Approved by Company",
                        "Host supervisor verified your Week {$logbook['week_no']} logbook.",
                        "/logbooks/view?id={$logbookId}"
                    );
                    // Notify uni supervisor to grade
                    if (!empty($logbook['university_supervisor_id'])) {
                        Notifier::send(
                            (int) $logbook['university_supervisor_id'],
                            'logbook_ready_grade',
                            "Logbook Ready for Grading (Week {$logbook['week_no']})",
                            "Host supervisor approved Week {$logbook['week_no']} logbook. Ready for academic grading.",
                            "/logbooks/view?id={$logbookId}"
                        );
                    }
                } elseif ($reviewerRole === 'university_supervisor' || $reviewerRole === 'coordinator') {
                    $newStatus = 'approved';
                    Notifier::send(
                        (int) $logbook['student_user_id'],
                        'logbook_approved',
                        "Week {$logbook['week_no']} Logbook Endorsed & Graded",
                        "University supervisor approved Week {$logbook['week_no']} logbook with Grade: {$grade}%.",
                        "/logbooks/view?id={$logbookId}"
                    );
                }
            }

            // Update logbook status and grade if university supervisor graded
            $sql = "UPDATE logbooks SET status = :st";
            $params = ['st' => $newStatus, 'id' => $logbookId];
            if ($grade !== null && ($reviewerRole === 'university_supervisor' || $reviewerRole === 'coordinator')) {
                $sql .= ", grade = :grade";
                $params['grade'] = $grade;
            }
            $sql .= " WHERE id = :id";
            $pdo->prepare($sql)->execute($params);
        });

        Flash::success("Review action '{$action}' recorded successfully.");
        header('Location: ' . url('logbooks/view', ['id' => $logbookId]));
        exit;
    }
}
