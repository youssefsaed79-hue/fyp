<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

/**
 * Dashboard Controller (FR-DASH-01, FR-IRM-01)
 * Role-tailored dashboards for Student, Company, University Supervisor and Coordinator.
 */
class DashboardController
{
    // FR-DASH-01: Role Dashboards
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $role = $user['role'];

        switch ($role) {
            case 'student':
                $this->studentDashboard($user);
                break;
            case 'company':
                $this->companyDashboard($user);
                break;
            case 'university_supervisor':
                $this->supervisorDashboard($user);
                break;
            case 'coordinator':
                $this->coordinatorDashboard($user);
                break;
            default:
                Auth::abortForbidden("Unknown role.");
        }
    }

    private function studentDashboard(array $user): void
    {
        $userId = (int) $user['id'];

        // Get placement details
        $placement = Database::fetchOne(
            "SELECT p.*, c.name as company_name, c.address as company_address,
                    u_uni.name as uni_supervisor_name, u_uni.email as uni_supervisor_email
             FROM placements p
             LEFT JOIN companies c ON p.company_id = c.id
             LEFT JOIN users u_uni ON p.university_supervisor_id = u_uni.id
             WHERE p.student_user_id = :uid
             ORDER BY p.id DESC LIMIT 1",
            ['uid' => $userId]
        );

        $attendanceStats = ['total_logged' => 0, 'verified' => 0, 'percentage' => 0];
        $logbookStats = ['total_weeks' => 12, 'submitted' => 0, 'approved' => 0];
        $finalGrade = null;

        if ($placement) {
            $plcId = (int) $placement['id'];

            // Attendance calculation
            $totalDays = (int) Database::fetchValue(
                "SELECT COUNT(*) FROM attendance WHERE placement_id = :pid AND status = 'present'",
                ['pid' => $plcId]
            );
            $verifiedDays = (int) Database::fetchValue(
                "SELECT COUNT(*) FROM attendance WHERE placement_id = :pid AND status = 'present' AND verification = 'verified'",
                ['pid' => $plcId]
            );
            // Working days elapsed in placement
            $start = new \DateTime($placement['start_date']);
            $end = new \DateTime(min($placement['end_date'], date('Y-m-d')));
            $expectedWorkDays = 0;
            if ($start <= $end) {
                $period = new \DatePeriod($start, new \DateInterval('P1D'), $end->modify('+1 day'));
                foreach ($period as $dt) {
                    if ((int) $dt->format('N') <= 5) {
                        $expectedWorkDays++;
                    }
                }
            }
            $expectedWorkDays = max($expectedWorkDays, 1);
            $attPct = min(100.0, round(($verifiedDays / $expectedWorkDays) * 100, 1));
            $attendanceStats = [
                'total_logged' => $totalDays,
                'verified' => $verifiedDays,
                'expected' => $expectedWorkDays,
                'percentage' => $attPct,
                'is_alert' => $attPct < 80.0 && $totalDays > 0,
            ];

            // Logbooks
            $submittedLogs = (int) Database::fetchValue(
                "SELECT COUNT(*) FROM logbooks WHERE placement_id = :pid AND status != 'draft'",
                ['pid' => $plcId]
            );
            $approvedLogs = (int) Database::fetchValue(
                "SELECT COUNT(*) FROM logbooks WHERE placement_id = :pid AND status = 'approved'",
                ['pid' => $plcId]
            );
            $logbookStats = [
                'total_weeks' => 12,
                'submitted' => $submittedLogs,
                'approved' => $approvedLogs,
            ];

            // Final Grade
            $finalGrade = Database::fetchOne(
                "SELECT * FROM final_grades WHERE placement_id = :pid AND locked = 1",
                ['pid' => $plcId]
            );
        }

        // Recent published announcements (FR-IRM-04)
        $recentAnnouncements = Database::fetchAll(
            "SELECT * FROM announcements WHERE status = 'published' ORDER BY published_at DESC LIMIT 3"
        );

        View::render('dashboard/student', [
            'title' => 'Student Dashboard',
            'user' => $user,
            'placement' => $placement,
            'attendanceStats' => $attendanceStats,
            'logbookStats' => $logbookStats,
            'finalGrade' => $finalGrade,
            'recentAnnouncements' => $recentAnnouncements,
        ], 'layouts/main');
    }

    private function companyDashboard(array $user): void
    {
        $userId = (int) $user['id'];
        $companyId = $user['company']['id'] ?? null;
        $scopePlacementId = $user['scope_placement_id'] ?? null;

        $placementWhere = "1=1";
        $params = [];
        if ($scopePlacementId) {
            $placementWhere = "p.id = :scope_pid";
            $params['scope_pid'] = $scopePlacementId;
        } elseif ($companyId) {
            $placementWhere = "p.company_id = :cid";
            $params['cid'] = $companyId;
        } else {
            $placementWhere = "p.company_supervisor_user_id = :cuid";
            $params['cuid'] = $userId;
        }

        // Current interns
        $interns = Database::fetchAll(
            "SELECT p.*, u.name as student_name, u.email as student_email, s.student_id, s.programme
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             WHERE {$placementWhere} AND p.status IN ('internship_active', 'completed')
             ORDER BY p.status ASC, p.start_date DESC",
            $params
        );

        // Pending attendance batches (records pending verification)
        $pendingAttendanceCount = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM attendance a
             JOIN placements p ON a.placement_id = p.id
             WHERE {$placementWhere} AND a.verification = 'pending_verification'",
            $params
        );

        // Logbooks awaiting review
        $pendingLogbooksCount = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM logbooks l
             JOIN placements p ON l.placement_id = p.id
             WHERE {$placementWhere} AND l.status IN ('submitted', 'resubmitted')",
            $params
        );

        // Pending evaluations
        $pendingEvaluationsCount = 0;
        foreach ($interns as $intern) {
            $hasFinalEval = Database::fetchValue(
                "SELECT COUNT(*) FROM evaluations WHERE placement_id = :pid AND type = 'host_final'",
                ['pid' => $intern['id']]
            );
            if (!$hasFinalEval) {
                $pendingEvaluationsCount++;
            }
        }

        View::render('dashboard/company', [
            'title' => 'Company Dashboard',
            'user' => $user,
            'interns' => $interns,
            'pendingAttendanceCount' => $pendingAttendanceCount,
            'pendingLogbooksCount' => $pendingLogbooksCount,
            'pendingEvaluationsCount' => $pendingEvaluationsCount,
        ], 'layouts/main');
    }

    private function supervisorDashboard(array $user): void
    {
        $userId = (int) $user['id'];

        // Assigned students
        $students = Database::fetchAll(
            "SELECT p.*, u.name as student_name, u.email as student_email, s.student_id, s.programme, s.cgpa,
                    c.name as company_name, c.address as company_address
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             WHERE p.university_supervisor_id = :uid
             ORDER BY p.status ASC, u.name ASC",
            ['uid' => $userId]
        );

        // Pending logbooks to grade
        $pendingLogbooksCount = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM logbooks l
             JOIN placements p ON l.placement_id = p.id
             WHERE p.university_supervisor_id = :uid AND l.status = 'company_reviewed'",
            ['uid' => $userId]
        );

        // Pending final reports to mark
        $pendingReportsCount = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM final_reports fr
             JOIN placements p ON fr.placement_id = p.id
             WHERE p.university_supervisor_id = :uid AND fr.mark IS NULL",
            ['uid' => $userId]
        );

        // Pending visiting lecturer assessments
        $pendingVisitsCount = 0;
        foreach ($students as $st) {
            $hasVisit = Database::fetchValue(
                "SELECT COUNT(*) FROM evaluations WHERE placement_id = :pid AND type = 'visiting_lecturer'",
                ['pid' => $st['id']]
            );
            if (!$hasVisit) {
                $pendingVisitsCount++;
            }
        }

        View::render('dashboard/supervisor', [
            'title' => 'University Supervisor Dashboard',
            'user' => $user,
            'students' => $students,
            'pendingLogbooksCount' => $pendingLogbooksCount,
            'pendingReportsCount' => $pendingReportsCount,
            'pendingVisitsCount' => $pendingVisitsCount,
        ], 'layouts/main');
    }

    private function coordinatorDashboard(array $user): void
    {
        // System Analytics for Chart.js
        $totalStudents = (int) Database::fetchValue("SELECT COUNT(*) FROM students");
        $verifiedStudents = (int) Database::fetchValue("SELECT COUNT(*) FROM students WHERE verified = 1");
        $activeInternships = (int) Database::fetchValue("SELECT COUNT(*) FROM placements WHERE status = 'internship_active'");
        $completedInternships = (int) Database::fetchValue("SELECT COUNT(*) FROM placements WHERE status = 'completed'");
        $pendingApprovals = (int) Database::fetchValue("SELECT COUNT(*) FROM placements WHERE status = 'placement_pending'");

        // Placement status breakdown for Chart.js
        $placementStatuses = Database::fetchAll(
            "SELECT status, COUNT(*) as count FROM placements GROUP BY status"
        );

        // Supervisor Workload
        $supervisorWorkload = Database::fetchAll(
            "SELECT u.id, u.name, COUNT(p.id) as assigned_count
             FROM users u
             LEFT JOIN placements p ON u.id = p.university_supervisor_id AND p.status IN ('university_approved', 'internship_active', 'completed')
             WHERE u.role = 'university_supervisor' AND u.status = 'active'
             GROUP BY u.id, u.name
             ORDER BY assigned_count DESC"
        );

        // Pending Placement Approvals Queue
        $approvalsQueue = Database::fetchAll(
            "SELECT p.*, u.name as student_name, s.student_id, s.programme, c.name as company_name, c.source as company_source
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             WHERE p.status = 'placement_pending'
             ORDER BY p.created_at ASC LIMIT 5"
        );

        // At-risk students (<80% attendance or overdue logbooks)
        $atRiskStudents = Database::fetchAll(
            "SELECT p.id as placement_id, u.name as student_name, s.student_id, c.name as company_name,
                    u_sup.name as supervisor_name,
                    COUNT(CASE WHEN a.verification = 'verified' AND a.status = 'present' THEN 1 END) as verified_days,
                    COUNT(a.id) as total_days
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             LEFT JOIN users u_sup ON p.university_supervisor_id = u_sup.id
             LEFT JOIN attendance a ON p.id = a.placement_id
             WHERE p.status = 'internship_active'
             GROUP BY p.id, u.name, s.student_id, c.name, u_sup.name
             HAVING total_days > 5 AND (verified_days / total_days) < 0.80"
        );

        // Grade Averages
        $gradeAverage = Database::fetchValue(
            "SELECT ROUND(AVG(total), 2) FROM final_grades WHERE locked = 1"
        );

        View::render('dashboard/coordinator', [
            'title' => 'Coordinator Analytics Dashboard',
            'user' => $user,
            'totalStudents' => $totalStudents,
            'verifiedStudents' => $verifiedStudents,
            'activeInternships' => $activeInternships,
            'completedInternships' => $completedInternships,
            'pendingApprovals' => $pendingApprovals,
            'placementStatuses' => $placementStatuses,
            'supervisorWorkload' => $supervisorWorkload,
            'approvalsQueue' => $approvalsQueue,
            'atRiskStudents' => $atRiskStudents,
            'gradeAverage' => $gradeAverage ?: 'N/A',
        ], 'layouts/main');
    }
}
