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
 * University Supervisor Assignment & Workload Controller
 * Realises FR-SVA-01, FR-SVA-02
 */
class SupervisorController
{
    // FR-SVA-01 & FR-SVA-02: Supervisor Allocation Administration & Workload View
    public function assignment(): void
    {
        Auth::requireRole('coordinator');

        // Placements needing supervisor assignment or already approved
        $placements = Database::fetchAll(
            "SELECT p.*, u_st.name as student_name, s.student_id, s.programme,
                    c.name as company_name, c.address as company_address,
                    u_uni.name as current_supervisor_name
             FROM placements p
             JOIN users u_st ON p.student_user_id = u_st.id
             JOIN students s ON u_st.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             LEFT JOIN users u_uni ON p.university_supervisor_id = u_uni.id
             WHERE p.status IN ('university_approved', 'internship_active', 'placement_pending')
             ORDER BY (p.university_supervisor_id IS NULL) DESC, p.created_at DESC"
        );

        // Supervisors and their current workload
        $supervisors = Database::fetchAll(
            "SELECT u.id, u.name, u.email,
                    COUNT(p.id) as current_load
             FROM users u
             LEFT JOIN placements p ON u.id = p.university_supervisor_id AND p.status IN ('university_approved', 'internship_active', 'completed')
             WHERE u.role = 'university_supervisor' AND u.status = 'active'
             GROUP BY u.id, u.name, u.email
             ORDER BY current_load ASC, u.name ASC"
        );

        // Suggested supervisor: lowest current load (FR-SVA-01)
        $suggestedSupervisor = $supervisors[0] ?? null;

        View::render('supervisors/assignment', [
            'title' => 'Supervisor Allocation & Workload',
            'placements' => $placements,
            'supervisors' => $supervisors,
            'suggestedSupervisor' => $suggestedSupervisor,
        ], 'layouts/main');
    }

    // FR-SVA-01: Assign University Supervisor to Placement
    public function assign(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $placementId = (int) ($_POST['placement_id'] ?? 0);
        $supervisorId = (int) ($_POST['supervisor_id'] ?? 0);

        $placement = Database::fetchOne("SELECT * FROM placements WHERE id = :id", ['id' => $placementId]);
        $supervisor = Database::fetchOne("SELECT * FROM users WHERE id = :id AND role = 'university_supervisor'", ['id' => $supervisorId]);

        if (!$placement || !$supervisor) {
            Flash::danger('Invalid placement or supervisor selection.');
            header('Location: ' . url('supervisors/assignment'));
            exit;
        }

        Database::execute(
            "UPDATE placements SET university_supervisor_id = :sid WHERE id = :pid",
            ['sid' => $supervisorId, 'pid' => $placementId]
        );

        // Notify supervisor
        $student = Database::fetchOne("SELECT name FROM users WHERE id = :id", ['id' => $placement['student_user_id']]);
        Notifier::send(
            $supervisorId,
            'student_assigned',
            'New Student Intern Assigned',
            "You have been assigned as the visiting lecturer for student {$student['name']}.",
            '/supervisors/students'
        );

        // Notify student
        Notifier::send(
            (int) $placement['student_user_id'],
            'supervisor_assigned',
            'Visiting Lecturer Allocated',
            "{$supervisor['name']} has been assigned as your university supervisor.",
            '/placements/my'
        );

        Flash::success("Assigned {$supervisor['name']} to placement #{$placementId}.");
        header('Location: ' . url('supervisors/assignment'));
        exit;
    }

    // University Supervisor: Assigned Students Roster
    public function assignedStudents(): void
    {
        Auth::requireRole('university_supervisor');
        $user = Auth::user();
        $userId = (int) $user['id'];

        $students = Database::fetchAll(
            "SELECT p.*, u.name as student_name, u.email as student_email, s.student_id, s.programme, s.cgpa,
                    c.name as company_name, c.address as company_address, c.representative_name, c.representative_email,
                    (SELECT COUNT(*) FROM attendance a WHERE a.placement_id = p.id AND a.verification = 'verified') as verified_att,
                    (SELECT COUNT(*) FROM logbooks l WHERE l.placement_id = p.id AND l.status = 'approved') as approved_logs,
                    (SELECT COUNT(*) FROM logbooks l WHERE l.placement_id = p.id AND l.status = 'company_reviewed') as pending_grades
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN students s ON u.id = s.user_id
             JOIN companies c ON p.company_id = c.id
             WHERE p.university_supervisor_id = :uid
             ORDER BY p.status ASC, u.name ASC",
            ['uid' => $userId]
        );

        View::render('supervisors/students', [
            'title' => 'Assigned Student Interns',
            'students' => $students,
            'user' => $user,
        ], 'layouts/main');
    }
}
