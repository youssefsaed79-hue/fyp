<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Validator;
use App\Core\View;

/**
 * Internship Listing Management Controller
 * Realises FR-ILM-01, FR-ILM-02, FR-ILM-03
 */
class VacancyController
{
    // FR-ILM-02: Student Vacancy Search & Filter
    public function index(): void
    {
        $keyword = trim($_GET['q'] ?? '');
        $industry = trim($_GET['industry'] ?? '');
        $location = trim($_GET['location'] ?? '');
        $arrangement = trim($_GET['arrangement'] ?? '');

        $sql = "SELECT v.*, c.name as company_name, c.industry as company_industry, c.address as company_address
                FROM vacancies v
                JOIN companies c ON v.company_id = c.id
                WHERE v.status = 'published' AND c.status = 'approved' AND v.deadline >= CURDATE()";
        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (v.title LIKE :kw1 OR v.duties LIKE :kw2 OR v.requirements LIKE :kw3 OR c.name LIKE :kw4)";
            $params['kw1'] = "%{$keyword}%";
            $params['kw2'] = "%{$keyword}%";
            $params['kw3'] = "%{$keyword}%";
            $params['kw4'] = "%{$keyword}%";
        }

        if ($industry !== '') {
            $sql .= " AND c.industry LIKE :ind";
            $params['ind'] = "%{$industry}%";
        }

        if ($location !== '') {
            $sql .= " AND v.location LIKE :loc";
            $params['loc'] = "%{$location}%";
        }

        if ($arrangement !== '' && in_array($arrangement, ['onsite', 'remote', 'hybrid'], true)) {
            $sql .= " AND v.working_arrangement = :arr";
            $params['arr'] = $arrangement;
        }

        $sql .= " ORDER BY v.created_at DESC";
        $vacancies = Database::fetchAll($sql, $params);

        // Fetch distinct industries for filter dropdown
        $industries = Database::fetchAll("SELECT DISTINCT industry FROM companies WHERE status = 'approved' AND industry != ''");

        View::render('vacancies/index', [
            'title' => 'Find Internship Vacancies',
            'vacancies' => $vacancies,
            'industries' => array_column($industries, 'industry'),
            'keyword' => $keyword,
            'currentIndustry' => $industry,
            'currentLocation' => $location,
            'currentArrangement' => $arrangement,
        ], 'layouts/main');
    }

    // FR-ILM-02: View Single Vacancy Details
    public function view(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $vacancy = Database::fetchOne(
            "SELECT v.*, c.name as company_name, c.industry as company_industry, c.address as company_address,
                    c.representative_name, c.representative_email
             FROM vacancies v
             JOIN companies c ON v.company_id = c.id
             WHERE v.id = :id",
            ['id' => $id]
        );

        if (!$vacancy) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Listing Not Found'], 'layouts/main');
            return;
        }

        // Check if student has already applied
        $user = Auth::user();
        $hasApplied = false;
        $application = null;
        if ($user && $user['role'] === 'student') {
            $application = Database::fetchOne(
                "SELECT * FROM applications WHERE student_user_id = :uid AND vacancy_id = :vid",
                ['uid' => $user['id'], 'vid' => $id]
            );
            $hasApplied = ($application !== null);
        }

        View::render('vacancies/view', [
            'title' => $vacancy['title'],
            'vacancy' => $vacancy,
            'hasApplied' => $hasApplied,
            'application' => $application,
            'user' => $user,
        ], 'layouts/main');
    }

    // FR-ILM-01: Company Manage Own Vacancies Screen
    public function manage(): void
    {
        Auth::requireRole('company');
        $user = Auth::user();
        $companyId = $user['company']['id'] ?? 0;

        $vacancies = Database::fetchAll(
            "SELECT v.*, (SELECT COUNT(*) FROM applications a WHERE a.vacancy_id = v.id) as applicants_count
             FROM vacancies v
             WHERE v.company_id = :cid
             ORDER BY v.created_at DESC",
            ['cid' => $companyId]
        );

        View::render('vacancies/manage', [
            'title' => 'Manage Internship Listings',
            'vacancies' => $vacancies,
            'company' => $user['company'] ?? null,
        ], 'layouts/main');
    }

    // FR-ILM-01: Create Vacancy Form
    public function create(): void
    {
        Auth::requireRole('company');
        $user = Auth::user();

        // Enforce rule: Only approved companies can publish vacancies (FR-UCM-01)
        if (($user['company']['status'] ?? '') !== 'approved') {
            Flash::danger('Your company profile is pending approval. You cannot post vacancies until verified by the coordinator.');
            header('Location: ' . url('vacancies/manage'));
            exit;
        }

        View::render('vacancies/create', [
            'title' => 'Post New Internship Vacancy',
        ], 'layouts/main');
    }

    // FR-ILM-01: Store Vacancy
    public function store(): void
    {
        Auth::requireRole('company');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $companyId = (int) ($user['company']['id'] ?? 0);

        if (($user['company']['status'] ?? '') !== 'approved') {
            Flash::danger('Your company profile must be approved before posting listings.');
            header('Location: ' . url('vacancies/manage'));
            exit;
        }

        $rules = [
            'title' => 'required|min:5',
            'duties' => 'required|min:10',
            'requirements' => 'required|min:10',
            'slots' => 'required|integer|min:1',
            'location' => 'required',
            'working_arrangement' => 'required|in:onsite,remote,hybrid',
            'deadline' => 'required|date',
        ];

        $validator = Validator::make($_POST, $rules);
        if ($validator->fails()) {
            Flash::danger($validator->firstError() ?? 'Validation failed.');
            header('Location: ' . url('vacancies/create'));
            exit;
        }

        $status = isset($_POST['publish']) ? 'published' : 'draft';

        Database::execute(
            "INSERT INTO vacancies (company_id, title, duties, requirements, slots, allowance, location, working_arrangement, duration_weeks, deadline, status, created_at)
             VALUES (:cid, :title, :duties, :req, :slots, :allow, :loc, :arr, 12, :dead, :status, NOW())",
            [
                'cid' => $companyId,
                'title' => trim($_POST['title']),
                'duties' => trim($_POST['duties']),
                'req' => trim($_POST['requirements']),
                'slots' => (int) $_POST['slots'],
                'allow' => (float) ($_POST['allowance'] ?? 0),
                'loc' => trim($_POST['location']),
                'arr' => trim($_POST['working_arrangement']),
                'dead' => trim($_POST['deadline']),
                'status' => $status,
            ]
        );

        Flash::success("Vacancy '" . htmlspecialchars($_POST['title']) . "' saved as {$status}.");
        header('Location: ' . url('vacancies/manage'));
        exit;
    }

    // FR-ILM-01: Change Vacancy Status (Publish / Close)
    public function changeStatus(): void
    {
        Auth::requireRole('company');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $companyId = (int) ($user['company']['id'] ?? 0);
        $vacancyId = (int) ($_POST['vacancy_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');

        if (!in_array($newStatus, ['draft', 'published', 'closed'], true)) {
            Flash::danger('Invalid status specified.');
            header('Location: ' . url('vacancies/manage'));
            exit;
        }

        Database::execute(
            "UPDATE vacancies SET status = :status WHERE id = :id AND company_id = :cid",
            ['status' => $newStatus, 'id' => $vacancyId, 'cid' => $companyId]
        );

        Flash::success("Listing status updated to '{$newStatus}'.");
        header('Location: ' . url('vacancies/manage'));
        exit;
    }

    // FR-ILM-03: Coordinator Vacancy Vetting Directory
    public function vetting(): void
    {
        Auth::requireRole('coordinator');

        $vacancies = Database::fetchAll(
            "SELECT v.*, c.name as company_name, c.status as company_status,
                    (SELECT COUNT(*) FROM applications a WHERE a.vacancy_id = v.id) as applicants_count
             FROM vacancies v
             JOIN companies c ON v.company_id = c.id
             ORDER BY v.status = 'flagged' DESC, v.created_at DESC"
        );

        View::render('vacancies/vetting', [
            'title' => 'Vacancy Vetting & Moderation',
            'vacancies' => $vacancies,
        ], 'layouts/main');
    }

    // FR-ILM-03: Coordinator Flag or Deactivate Inappropriate Vacancy
    public function vet(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $vacancyId = (int) ($_POST['vacancy_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');

        if (!in_array($action, ['flag', 'deactivate', 'republish'], true)) {
            Flash::danger('Invalid moderation action.');
            header('Location: ' . url('vacancies/vetting'));
            exit;
        }

        $newStatus = match ($action) {
            'flag' => 'flagged',
            'deactivate' => 'deactivated',
            'republish' => 'published',
        };

        Database::execute(
            "UPDATE vacancies SET status = :status WHERE id = :id",
            ['status' => $newStatus, 'id' => $vacancyId]
        );

        Flash::success("Vacancy has been marked as '{$newStatus}'.");
        header('Location: ' . url('vacancies/vetting'));
        exit;
    }
}
