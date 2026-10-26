<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Role-Based Access Control (RBAC) and Authentication Engine (Table 3.5, NFR-SEC-03)
 */
class Auth
{
    private const SESSION_USER_ID = '_auth_user_id';
    private static ?array $currentUser = null;

    /**
     * RBAC Permission Matrix according to Table 3.5 of the specification
     */
    private static array $permissions = [
        'student' => [
            'dashboard' => true,
            'profile.view_own' => true,
            'profile.edit_own' => true,
            'documents.manage' => true,
            'vacancies.search' => true,
            'vacancies.apply' => true,
            'applications.view_own' => true,
            'offers.respond' => true,
            'placements.self_source' => true,
            'placements.view_own' => true,
            'attendance.log_daily' => true,
            'attendance.view_own' => true,
            'logbook.submit' => true,
            'logbook.view_own' => true,
            'monthly_reports.submit' => true,
            'final_reports.submit' => true,
            'evaluations.view_own_grade' => true,
            'resources.view' => true,
            'announcements.view' => true,
            'chat.participate' => true,
        ],
        'company' => [
            'dashboard' => true,
            'company.manage_profile' => true,
            'vacancies.manage_own' => true,
            'applications.screen' => true,
            'offers.issue' => true,
            'placements.view_interns' => true,
            'attendance.batch_verify' => true,
            'logbook.review_comment' => true,
            'monthly_reports.view' => true,
            'final_reports.view' => true,
            'evaluations.submit_host' => true,
            'chat.participate' => true,
        ],
        'university_supervisor' => [
            'dashboard' => true,
            'students.view_assigned' => true,
            'placements.view_assigned' => true,
            'attendance.monitor' => true,
            'logbook.grade_return' => true,
            'monthly_reports.view_assigned' => true,
            'final_reports.mark' => true,
            'evaluations.submit_visiting' => true,
            'chat.participate' => true,
        ],
        'coordinator' => [
            'dashboard' => true,
            'cohort.bulk_import' => true,
            'users.manage' => true,
            'companies.manage' => true,
            'vacancies.vet' => true,
            'placements.approve' => true,
            'supervisors.assign' => true,
            'attendance.audit' => true,
            'logbook.audit' => true,
            'monthly_reports.overview' => true,
            'final_reports.overview' => true,
            'evaluations.overview_lock' => true,
            'resources.manage' => true,
            'announcements.manage' => true,
            'settings.manage' => true,
            'exports.run' => true,
            'chat.monitor' => true,
        ],
    ];

    /**
     * Check if a user is currently authenticated
     */
    public static function check(): bool
    {
        return Session::has(self::SESSION_USER_ID);
    }

    /**
     * Retrieve the current logged-in user record (cached in request)
     */
    public static function user(): ?array
    {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        $userId = Session::get(self::SESSION_USER_ID);
        if (!$userId) {
            return null;
        }

        $user = Database::fetchOne(
            "SELECT u.*, s.student_id, s.programme, s.intake, s.cgpa, s.verified, s.profile_complete, s.photo_path
             FROM users u
             LEFT JOIN students s ON u.id = s.user_id
             WHERE u.id = :id AND u.status != 'inactive'",
            ['id' => $userId]
        );

        if (!$user) {
            self::logout();
            return null;
        }

        // Check if company user
        if ($user['role'] === 'company') {
            $company = Database::fetchOne(
                "SELECT c.* FROM companies c
                 JOIN company_users cu ON c.id = cu.company_id
                 WHERE cu.user_id = :user_id",
                ['user_id' => $userId]
            );
            $user['company'] = $company ?: null;
        }

        self::$currentUser = $user;
        return self::$currentUser;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['id'] : null;
    }

    public static function role(): ?string
    {
        $u = self::user();
        return $u ? (string) $u['role'] : null;
    }

    /**
     * Log in a user by setting session and recording timestamp
     */
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set(self::SESSION_USER_ID, (int) $user['id']);
        self::$currentUser = $user;

        Database::execute(
            "UPDATE users SET last_login_at = NOW() WHERE id = :id",
            ['id' => $user['id']]
        );
    }

    /**
     * Terminate the session
     */
    public static function logout(): void
    {
        self::$currentUser = null;
        Session::destroy();
    }

    /**
     * Check whether current user role has a specific permission
     */
    public static function can(string $permission): bool
    {
        $role = self::role();
        if (!$role) {
            return false;
        }

        // Coordinator has global supervisory bypass for viewing/monitoring
        if ($role === 'coordinator' && isset(self::$permissions['coordinator'][$permission])) {
            return true;
        }

        return !empty(self::$permissions[$role][$permission]);
    }

    /**
     * Enforce authentication. Redirects to login if guest.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            Flash::warning('Please sign in to access this page.');
            header('Location: /login');
            exit;
        }

        $user = self::user();
        if ($user && $user['status'] === 'suspended') {
            self::logout();
            Flash::danger('Your account has been suspended. Please contact the coordinator.');
            header('Location: /login');
            exit;
        }

        // Force password change on first login (FR-AUTH-05)
        if ($user && !empty($user['must_change_password'])) {
            $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $queryR = $_GET['r'] ?? '';
            if ($currentUri !== '/force-change-password' && $queryR !== 'auth/forceChangePassword' && $currentUri !== '/logout' && $queryR !== 'auth/logout') {
                Flash::info('You must change your temporary password before proceeding.');
                header('Location: /force-change-password');
                exit;
            }
        }
    }

    /**
     * Enforce a specific role or list of roles. Returns 403 on denial.
     */
    public static function requireRole(string|array $roles): void
    {
        self::requireAuth();
        $allowed = is_array($roles) ? $roles : [$roles];
        $currentRole = self::role();

        if (!in_array($currentRole, $allowed, true)) {
            self::abortForbidden("Role '{$currentRole}' is not authorized to access this section.");
        }
    }

    /**
     * Enforce an RBAC permission
     */
    public static function requirePermission(string $permission): void
    {
        self::requireAuth();
        if (!self::can($permission)) {
            self::abortForbidden("You do not have permission: {$permission}");
        }
    }

    /**
     * Row-Level Security Check for Placements
     * Checks if current user is allowed to access/view a given placement
     */
    public static function checkPlacementAccess(int|array $placement): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        if ($user['role'] === 'coordinator') {
            return true;
        }

        $plc = is_array($placement) ? $placement : Database::fetchOne(
            "SELECT * FROM placements WHERE id = :id",
            ['id' => $placement]
        );

        if (!$plc) {
            return false;
        }

        // Student check
        if ($user['role'] === 'student') {
            return (int) $plc['student_user_id'] === (int) $user['id'];
        }

        // University supervisor check
        if ($user['role'] === 'university_supervisor') {
            return (int) $plc['university_supervisor_id'] === (int) $user['id'];
        }

        // Company check
        if ($user['role'] === 'company') {
            // If scoped token supervisor
            if (!empty($user['scope_placement_id'])) {
                return (int) $user['scope_placement_id'] === (int) $plc['id'];
            }

            // Partner company user check
            if (!empty($user['company']['id'])) {
                return (int) $user['company']['id'] === (int) $plc['company_id'];
            }
            if ((int) $plc['company_supervisor_user_id'] === (int) $user['id']) {
                return true;
            }
        }

        return false;
    }

    public static function requirePlacementAccess(int|array $placement): void
    {
        if (!self::checkPlacementAccess($placement)) {
            self::abortForbidden("You are not authorized to view or manage this placement record.");
        }
    }

    /**
     * Abort with HTTP 403 Forbidden page
     */
    public static function abortForbidden(string $reason = 'Unauthorized access'): void
    {
        http_response_code(403);
        error_log("[InternFlow Security] 403 Forbidden for User #" . (self::id() ?? 0) . " - " . $reason);
        View::render('errors/403', [
            'reason' => $reason,
            'title' => '403 Forbidden - Access Denied'
        ], 'layouts/main');
        exit;
    }
}
