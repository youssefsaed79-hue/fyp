<?php
declare(strict_types=1);

/**
 * InternFlow: Internship Management Platform
 * Front Controller & Routing Dispatcher
 * Universiti Tenaga Nasional (UNITEN) - College of Computing & Informatics
 */

define('INTERNFLOW_START', microtime(true));
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', ROOT_PATH . '/storage');

// 1. PSR-4 Class Autoloader for plain PHP 8 (No Composer required)
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// 2. Load Global Helpers
require_once APP_PATH . '/core/helpers.php';
require_once APP_PATH . '/views/partials/badges.php';

// 3. Load Configuration
$configFile = APP_PATH . '/config/config.php';
if (!file_exists($configFile)) {
    die("Configuration file missing. Please copy app/config/config.sample.php to app/config/config.php");
}
$config = require $configFile;

// 3. Error Handling
if (!empty($config['app']['debug'])) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Kuala_Lumpur');

// 4. Initialize Core Services
use App\Core\Auth;
use App\Core\Database;
use App\Core\FileStore;
use App\Core\Mailer;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

Database::setConfig($config['db']);
Session::start($config['session'] ?? []);
View::init(APP_PATH . '/views', $config['app']['base_url'] ?? '');
FileStore::init($config['uploads'] ?? []);
Mailer::init($config['mail'] ?? []);

// 5. Register Routes
$router = new Router();

// --- Auth Routes ---
$router->get('/', 'AuthController@showLogin');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/force-change-password', 'AuthController@showForceChangePassword');
$router->post('/force-change-password', 'AuthController@forceChangePassword');

// --- Dashboard ---
$router->get('/dashboard', 'DashboardController@index');

// --- Module 1 & 2: Import & User & Company ---
$router->get('/import', 'ImportController@index');
$router->get('/import/template', 'ImportController@downloadTemplate');
$router->post('/import/preview', 'ImportController@preview');
$router->post('/import/confirm', 'ImportController@confirm');

$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users/store', 'UserController@store');
$router->get('/users/edit', 'UserController@edit');
$router->post('/users/update', 'UserController@update');
$router->post('/users/toggle-status', 'UserController@toggleStatus');

$router->get('/companies', 'CompanyController@index');
$router->get('/companies/register', 'CompanyController@registerForm');
$router->post('/companies/register', 'CompanyController@register');
$router->get('/companies/profile', 'CompanyController@profile');
$router->post('/companies/update-profile', 'CompanyController@updateProfile');
$router->post('/companies/update-status', 'CompanyController@updateStatus');

// System Invitation Token for External Supervisors (FR-UCM-02 / FR-UCM-03)
$router->get('/invite/accept', 'CompanyController@acceptInvitation');
$router->post('/invite/register', 'CompanyController@registerInvited');

// --- Module 3: Profile & Documents ---
$router->get('/profile', 'ProfileController@index');
$router->post('/profile/update', 'ProfileController@update');
$router->post('/profile/upload-photo', 'ProfileController@uploadPhoto');
$router->post('/documents/upload', 'ProfileController@uploadDocument');
$router->get('/documents/download', 'ProfileController@downloadDocument');
$router->post('/documents/delete', 'ProfileController@deleteDocument');

// --- Module 4: Vacancies ---
$router->get('/vacancies', 'VacancyController@index');
$router->get('/vacancies/view', 'VacancyController@view');
$router->get('/vacancies/manage', 'VacancyController@manage');
$router->get('/vacancies/create', 'VacancyController@create');
$router->post('/vacancies/store', 'VacancyController@store');
$router->get('/vacancies/edit', 'VacancyController@edit');
$router->post('/vacancies/update', 'VacancyController@update');
$router->post('/vacancies/status', 'VacancyController@changeStatus');
$router->get('/vacancies/vetting', 'VacancyController@vetting');
$router->post('/vacancies/vet', 'VacancyController@vet');

// --- Module 5: Applications & Placements ---
$router->get('/applications', 'ApplicationController@index');
$router->post('/applications/apply', 'ApplicationController@apply');
$router->get('/applications/screen', 'ApplicationController@screen');
$router->post('/applications/update-status', 'ApplicationController@updateStatus');
$router->post('/applications/offer', 'ApplicationController@issueOffer');
$router->post('/applications/respond-offer', 'ApplicationController@respondOffer');

$router->get('/placements/my', 'PlacementController@myPlacement');
$router->get('/placements/self-source', 'PlacementController@selfSourceForm');
$router->post('/placements/self-source', 'PlacementController@selfSource');
$router->get('/placements/approvals', 'PlacementController@approvals');
$router->post('/placements/approve', 'PlacementController@approve');
$router->post('/placements/activate', 'PlacementController@activate');
$router->get('/placements/interns', 'PlacementController@companyInterns');
$router->get('/placements/monitoring', 'PlacementController@monitoring');

// --- Module 6: Supervisor Assignment ---
$router->get('/supervisors/assignment', 'SupervisorController@assignment');
$router->post('/supervisors/assign', 'SupervisorController@assign');
$router->get('/supervisors/students', 'SupervisorController@assignedStudents');

// --- Module 7: Attendance ---
$router->get('/attendance', 'AttendanceController@index');
$router->post('/attendance/log', 'AttendanceController@logDaily');
$router->get('/attendance/batch', 'AttendanceController@batchView');
$router->post('/attendance/batch-verify', 'AttendanceController@batchVerify');

// --- Module 8: Logbooks ---
$router->get('/logbooks', 'LogbookController@index');
$router->get('/logbooks/view', 'LogbookController@view');
$router->get('/logbooks/edit', 'LogbookController@edit');
$router->post('/logbooks/save', 'LogbookController@save');
$router->get('/logbooks/review', 'LogbookController@companyReviewList');
$router->get('/logbooks/supervisor', 'LogbookController@supervisorList');
$router->post('/logbooks/action', 'LogbookController@reviewAction');

// --- Module 9: Monthly Progress Reports ---
$router->get('/monthly-reports', 'MonthlyReportController@index');
$router->post('/monthly-reports/submit', 'MonthlyReportController@submit');
$router->get('/monthly-reports/supervisor', 'MonthlyReportController@supervisorView');
$router->get('/monthly-reports/download', 'MonthlyReportController@download');

// --- Module 10: Evaluation & Marking ---
$router->get('/evaluations/host', 'EvaluationController@hostList');
$router->get('/evaluations/host-rubric', 'EvaluationController@hostRubric');
$router->post('/evaluations/host-submit', 'EvaluationController@hostSubmit');
$router->get('/evaluations/visiting', 'EvaluationController@visitingList');
$router->get('/evaluations/visiting-rubric', 'EvaluationController@visitingRubric');
$router->post('/evaluations/visiting-submit', 'EvaluationController@visitingSubmit');
$router->get('/evaluations/lock', 'EvaluationController@lockOverview');
$router->post('/evaluations/lock-grade', 'EvaluationController@lockGrade');
$router->get('/evaluations/my-grade', 'EvaluationController@myGrade');

// --- Module 11: Final Reports ---
$router->get('/final-reports', 'FinalReportController@index');
$router->get('/final-report', 'FinalReportController@index');
$router->post('/final-reports/upload', 'FinalReportController@upload');
$router->post('/final-report/upload', 'FinalReportController@upload');
$router->get('/final-reports/marking', 'FinalReportController@markingList');
$router->get('/final-report/marking', 'FinalReportController@markingList');
$router->post('/final-reports/mark', 'FinalReportController@markReport');
$router->post('/final-report/mark', 'FinalReportController@markReport');
$router->get('/final-reports/download', 'FinalReportController@download');
$router->get('/final-report/download', 'FinalReportController@download');

// --- Module 12: Communication & Notifications ---
$router->get('/chat', 'ChatController@index');
$router->post('/chat/send', 'ChatController@send');
$router->get('/chat/messages', 'ChatController@getMessages');

$router->get('/notifications', 'NotificationController@index');
$router->post('/notifications/read', 'NotificationController@markRead');
$router->post('/notifications/read-all', 'NotificationController@markAllRead');

// --- Module 13 & Settings: Dashboards & Config ---
$router->get('/settings', 'SettingsController@index');
$router->post('/settings/update', 'SettingsController@update');
$router->get('/settings/exports', 'SettingsController@exports');
$router->get('/settings/export-csv', 'SettingsController@exportCsv');

// --- Module 14: Internship Resources Management ---
$router->get('/resources', 'ResourcesController@index');
$router->get('/resources/download', 'ResourcesController@download');

$router->get('/resource-admin', 'ResourceAdminController@index');
$router->post('/resource-admin/categories/store', 'ResourceAdminController@storeCategory');
$router->post('/resource-admin/categories/delete', 'ResourceAdminController@deleteCategory');
$router->post('/resource-admin/resources/store', 'ResourceAdminController@storeResource');
$router->post('/resource-admin/resources/toggle', 'ResourceAdminController@toggleResource');
$router->post('/resource-admin/resources/delete', 'ResourceAdminController@deleteResource');

$router->post('/resource-admin/journey/store', 'ResourceAdminController@storeJourneyStage');
$router->post('/resource-admin/journey/delete', 'ResourceAdminController@deleteJourneyStage');

$router->post('/resource-admin/guidelines/store', 'ResourceAdminController@storeGuideline');
$router->post('/resource-admin/guidelines/delete', 'ResourceAdminController@deleteGuideline');

$router->get('/resource-admin/announcements', 'ResourceAdminController@announcements');
$router->post('/resource-admin/announcements/store', 'ResourceAdminController@storeAnnouncement');
$router->post('/resource-admin/announcements/toggle', 'ResourceAdminController@toggleAnnouncement');
$router->post('/resource-admin/announcements/delete', 'ResourceAdminController@deleteAnnouncement');

// 6. Dispatch Request
$router->dispatch();
