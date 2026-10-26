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
 * Application & Candidate Pipeline Controller
 * Realises FR-APM-01, FR-APM-02, FR-APM-04 (Offer Acceptance and Lock)
 */
class ApplicationController
{
    // FR-APM-01: Student Submits Application
    public function apply(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];
        $vacancyId = (int) ($_POST['vacancy_id'] ?? 0);
        $coverLetter = trim($_POST['cover_letter'] ?? '');

        // 1. Eligibility Gate Check (FR-SPD-03)
        $hasCv = Database::fetchValue(
            "SELECT COUNT(*) FROM documents WHERE student_user_id = :uid AND type = 'cv'",
            ['uid' => $userId]
        );
        if (empty($hasCv) || empty($user['verified'])) {
            Flash::danger('Eligibility Gate: You cannot apply until your profile is verified and your CV is uploaded in My Profile & Documents.');
            header('Location: ' . url('profile'));
            exit;
        }

        // 2. Check if student already has an active or approved placement
        $existingPlacement = Database::fetchOne(
            "SELECT id, status FROM placements WHERE student_user_id = :uid AND status IN ('placement_pending', 'university_approved', 'internship_active', 'completed')",
            ['uid' => $userId]
        );
        if ($existingPlacement) {
            Flash::warning('You already have a confirmed placement. Additional applications cannot be submitted.');
            header('Location: ' . url('placements/my'));
            exit;
        }

        // 3. Check duplicate application for same vacancy
        $existingApp = Database::fetchOne(
            "SELECT id FROM applications WHERE student_user_id = :uid AND vacancy_id = :vid",
            ['uid' => $userId, 'vid' => $vacancyId]
        );
        if ($existingApp) {
            Flash::info('You have already submitted an application for this vacancy.');
            header('Location: ' . url('applications'));
            exit;
        }

        // 4. Verify vacancy is open
        $vacancy = Database::fetchOne("SELECT * FROM vacancies WHERE id = :id AND status = 'published'", ['id' => $vacancyId]);
        if (!$vacancy) {
            Flash::danger('This internship listing is no longer accepting applications.');
            header('Location: ' . url('vacancies'));
            exit;
        }

        Database::execute(
            "INSERT INTO applications (student_user_id, vacancy_id, cover_letter, status, created_at)
             VALUES (:uid, :vid, :cl, 'submitted', NOW())",
            [
                'uid' => $userId,
                'vid' => $vacancyId,
                'cl' => $coverLetter,
            ]
        );

        // Notify company users
        $companyUsers = Database::fetchAll(
            "SELECT user_id FROM company_users WHERE company_id = :cid",
            ['cid' => $vacancy['company_id']]
        );
        foreach ($companyUsers as $cu) {
            Notifier::send(
                (int) $cu['user_id'],
                'new_application',
                'New Internship Candidate',
                "Student {$user['name']} applied for '{$vacancy['title']}'.",
                '/applications/screen?vacancy_id=' . $vacancyId
            );
        }

        Flash::success("Application submitted successfully for '{$vacancy['title']}'! The hiring supervisor will review your CV.");
        header('Location: ' . url('applications'));
        exit;
    }

    // FR-APM-01 / FR-APM-04: Student Applications List
    public function index(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $userId = (int) $user['id'];

        $applications = Database::fetchAll(
            "SELECT a.*, v.title as vacancy_title, v.location, v.working_arrangement, v.allowance,
                    c.name as company_name, c.industry as company_industry
             FROM applications a
             JOIN vacancies v ON a.vacancy_id = v.id
             JOIN companies c ON v.company_id = c.id
             WHERE a.student_user_id = :uid
             ORDER BY a.created_at DESC",
            ['uid' => $userId]
        );

        View::render('applications/index', [
            'title' => 'My Applications',
            'applications' => $applications,
        ], 'layouts/main');
    }

    // FR-APM-02: Company Screening Pipeline
    public function screen(): void
    {
        Auth::requireRole('company');
        $user = Auth::user();
        $companyId = (int) ($user['company']['id'] ?? 0);
        $vacancyId = (int) ($_GET['vacancy_id'] ?? 0);

        // Fetch company vacancies for filter
        $vacancies = Database::fetchAll(
            "SELECT id, title FROM vacancies WHERE company_id = :cid ORDER BY created_at DESC",
            ['cid' => $companyId]
        );

        $sql = "SELECT a.*, v.title as vacancy_title, u.name as student_name, u.email as student_email,
                       s.student_id, s.programme, s.cgpa, s.skills,
                       (SELECT id FROM documents WHERE student_user_id = u.id AND type = 'cv' LIMIT 1) as cv_doc_id
                FROM applications a
                JOIN vacancies v ON a.vacancy_id = v.id
                JOIN users u ON a.student_user_id = u.id
                JOIN students s ON u.id = s.user_id
                WHERE v.company_id = :cid";
        $params = ['cid' => $companyId];

        if ($vacancyId > 0) {
            $sql .= " AND a.vacancy_id = :vid";
            $params['vid'] = $vacancyId;
        }

        $sql .= " ORDER BY a.status = 'submitted' DESC, a.created_at DESC";
        $candidates = Database::fetchAll($sql, $params);

        View::render('applications/screen', [
            'title' => 'Candidate Screening Pipeline',
            'candidates' => $candidates,
            'vacancies' => $vacancies,
            'selectedVacancyId' => $vacancyId,
        ], 'layouts/main');
    }

    // FR-APM-02: Update Screening Status (Under Review, Shortlisted, Interview, Reject)
    public function updateStatus(): void
    {
        Auth::requireRole('company');
        Csrf::checkOrAbort();

        $appId = (int) ($_POST['application_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $interviewDetails = trim($_POST['interview_details'] ?? '');

        if (!in_array($status, ['submitted', 'under_review', 'shortlisted', 'interview', 'rejected'], true)) {
            Flash::danger('Invalid application status.');
            header('Location: ' . url('applications/screen'));
            exit;
        }

        $app = Database::fetchOne("SELECT * FROM applications WHERE id = :id", ['id' => $appId]);
        if (!$app) {
            Flash::danger('Application not found.');
            header('Location: ' . url('applications/screen'));
            exit;
        }

        Database::execute(
            "UPDATE applications SET status = :status, interview_details = :details WHERE id = :id",
            [
                'status' => $status,
                'details' => ($status === 'interview') ? $interviewDetails : $app['interview_details'],
                'id' => $appId
            ]
        );

        // Notify student
        $msg = "Your application status has been updated to: " . ucwords(str_replace('_', ' ', $status));
        if ($status === 'interview') {
            $msg .= ". Interview Details: {$interviewDetails}";
        }
        Notifier::send((int)$app['student_user_id'], 'application_update', 'Internship Application Update', $msg, '/applications');

        Flash::success("Application status updated to '" . ucwords(str_replace('_', ' ', $status)) . "'.");
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('applications/screen')));
        exit;
    }

    // FR-APM-02: Company Issues Formal Digital Offer
    public function issueOffer(): void
    {
        Auth::requireRole('company');
        Csrf::checkOrAbort();

        $appId = (int) ($_POST['application_id'] ?? 0);
        $offerText = trim($_POST['offer_text'] ?? 'Congratulations! We are delighted to offer you a 12-week industrial training internship.');

        $app = Database::fetchOne("SELECT * FROM applications WHERE id = :id", ['id' => $appId]);
        if (!$app) {
            Flash::danger('Application record not found.');
            header('Location: ' . url('applications/screen'));
            exit;
        }

        Database::execute(
            "UPDATE applications SET offer_issued = 1, offer_text = :text, status = 'interview' WHERE id = :id",
            ['text' => $offerText, 'id' => $appId]
        );

        Notifier::send(
            (int) $app['student_user_id'],
            'placement_offer',
            '🎉 Internship Placement Offer Received!',
            "A host organisation has issued an internship placement offer to you. Please review and respond in your Applications portal.",
            '/applications'
        );

        Flash::success('Formal internship placement offer issued to candidate.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('applications/screen')));
        exit;
    }

    // FR-APM-04: Offer Acceptance and Automated Lock
    public function respondOffer(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];
        $appId = (int) ($_POST['application_id'] ?? 0);
        $response = trim($_POST['response'] ?? ''); // 'accept' or 'decline'

        $app = Database::fetchOne(
            "SELECT a.*, v.title as vacancy_title, v.company_id, v.location,
                    c.name as company_name, c.representative_name, c.representative_email
             FROM applications a
             JOIN vacancies v ON a.vacancy_id = v.id
             JOIN companies c ON v.company_id = c.id
             WHERE a.id = :id AND a.student_user_id = :uid",
            ['id' => $appId, 'uid' => $userId]
        );

        if (!$app || empty($app['offer_issued'])) {
            Flash::danger('No pending offer found for this application.');
            header('Location: ' . url('applications'));
            exit;
        }

        if ($response === 'decline') {
            Database::execute("UPDATE applications SET status = 'withdrawn', offer_issued = 0 WHERE id = :id", ['id' => $appId]);
            Flash::info('You have declined the internship placement offer.');
            header('Location: ' . url('applications'));
            exit;
        }

        if ($response === 'accept') {
            // AUTOMATED BUSINESS RULE 1 / FR-APM-04:
            // "When student accepts an offer the placement becomes 'Placement Pending' and all other active applications are automatically withdrawn."
            Database::transaction(function($pdo) use ($userId, $appId, $app) {
                // 1. Mark this application accepted
                $pdo->prepare("UPDATE applications SET status = 'accepted' WHERE id = ?")->execute([$appId]);

                // 2. AUTOMATICALLY WITHDRAW ALL OTHER ACTIVE APPLICATIONS (FR-APM-04 Rule 1)
                $pdo->prepare(
                    "UPDATE applications
                     SET status = 'withdrawn'
                     WHERE student_user_id = ? AND id != ? AND status NOT IN ('rejected', 'withdrawn')"
                )->execute([$userId, $appId]);

                // 3. Create placement record in 'placement_pending' status
                $startDate = date('Y-m-d', strtotime('+14 days'));
                $endDate = date('Y-m-d', strtotime('+98 days')); // 12 weeks = 84 days + 14 = 98

                $stmtPlc = $pdo->prepare(
                    "INSERT INTO placements (student_user_id, company_id, vacancy_id, application_id, source, position, start_date, end_date, supervisor_contact_name, supervisor_contact_email, status, created_at)
                     VALUES (:uid, :cid, :vid, :aid, 'platform', :pos, :start, :end, :sname, :semail, 'placement_pending', NOW())"
                );
                $stmtPlc->execute([
                    'uid' => $userId,
                    'cid' => $app['company_id'],
                    'vid' => $app['vacancy_id'],
                    'aid' => $appId,
                    'pos' => $app['vacancy_title'],
                    'start' => $startDate,
                    'end' => $endDate,
                    'sname' => $app['representative_name'],
                    'semail' => $app['representative_email'],
                ]);
                $placementId = (int) $pdo->lastInsertId();

                // 4. Notify Coordinator for university approval
                $coordinators = Database::fetchAll("SELECT id FROM users WHERE role = 'coordinator'");
                foreach ($coordinators as $coord) {
                    Notifier::send(
                        (int) $coord['id'],
                        'placement_pending',
                        'New Placement Pending Approval',
                        "Student confirmed offer at {$app['company_name']}. Placement requires university approval.",
                        '/placements/approvals'
                    );
                }
            });

            Flash::success("Offer accepted! Your placement is now 'Placement Pending' awaiting University Coordinator approval. All other active applications have been automatically withdrawn (FR-APM-04).");
            header('Location: ' . url('placements/my'));
            exit;
        }

        header('Location: ' . url('applications'));
        exit;
    }
}
