<?php
$user = \App\Core\Auth::user();
$role = $user['role'] ?? 'guest';
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $scriptDir);
if ($scriptDir !== '/' && str_starts_with($currentUri, $scriptDir)) {
    $currentUri = substr($currentUri, strlen($scriptDir));
}
$currentUri = '/' . trim($currentUri ?: '/', '/');

function isNavActive(string $path, string $currentUri): string
{
    $p = '/' . trim($path, '/');
    if ($p === '/' && $currentUri === '/') {
        return 'active';
    }
    if ($p !== '/' && str_starts_with($currentUri, $p)) {
        return 'active';
    }
    return '';
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?= url('dashboard') ?>" class="brand-title">
            InternFlow
            <div class="brand-subtitle">UNITEN CCI Internship</div>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Navigation</div>
        <a href="<?= url('dashboard') ?>" class="nav-link <?= isNavActive('/dashboard', $currentUri) ?>">
            <span class="icon">📊</span>
            <span>Dashboard</span>
        </a>

        <?php if ($role === 'student'): ?>
            <div class="nav-section-title">Preparation</div>
            <a href="<?= url('profile') ?>" class="nav-link <?= isNavActive('/profile', $currentUri) ?>">
                <span class="icon">👤</span>
                <span>Profile &amp; Documents</span>
            </a>
            <a href="<?= url('resources') ?>" class="nav-link <?= isNavActive('/resources', $currentUri) ?>">
                <span class="icon">📁</span>
                <span>Internship Resources</span>
            </a>
            <a href="<?= url('vacancies') ?>" class="nav-link <?= isNavActive('/vacancies', $currentUri) ?>">
                <span class="icon">🔍</span>
                <span>Find Internships</span>
            </a>
            <a href="<?= url('applications') ?>" class="nav-link <?= isNavActive('/applications', $currentUri) ?>">
                <span class="icon">📝</span>
                <span>My Applications</span>
            </a>

            <div class="nav-section-title">Internship Lifecycle</div>
            <a href="<?= url('placements/my') ?>" class="nav-link <?= isNavActive('/placements', $currentUri) ?>">
                <span class="icon">🏢</span>
                <span>My Placement</span>
            </a>
            <a href="<?= url('attendance') ?>" class="nav-link <?= isNavActive('/attendance', $currentUri) ?>">
                <span class="icon">📅</span>
                <span>Daily Attendance</span>
            </a>
            <a href="<?= url('logbooks') ?>" class="nav-link <?= isNavActive('/logbooks', $currentUri) ?>">
                <span class="icon">📖</span>
                <span>Weekly Logbook</span>
            </a>
            <a href="<?= url('monthly-reports') ?>" class="nav-link <?= isNavActive('/monthly-reports', $currentUri) ?>">
                <span class="icon">📑</span>
                <span>Monthly Reports</span>
            </a>
            <a href="<?= url('final-reports') ?>" class="nav-link <?= isNavActive('/final-reports', $currentUri) ?>">
                <span class="icon">🎓</span>
                <span>Final Report</span>
            </a>
            <a href="<?= url('evaluations/my-grade') ?>" class="nav-link <?= isNavActive('/evaluations', $currentUri) ?>">
                <span class="icon">🏆</span>
                <span>My Final Grade</span>
            </a>

        <?php elseif ($role === 'company'): ?>
            <div class="nav-section-title">Recruitment</div>
            <a href="<?= url('companies/profile') ?>" class="nav-link <?= isNavActive('/companies/profile', $currentUri) ?>">
                <span class="icon">🏢</span>
                <span>Company Profile</span>
            </a>
            <a href="<?= url('vacancies/manage') ?>" class="nav-link <?= isNavActive('/vacancies', $currentUri) ?>">
                <span class="icon">💼</span>
                <span>Internship Listings</span>
            </a>
            <a href="<?= url('applications/screen') ?>" class="nav-link <?= isNavActive('/applications', $currentUri) ?>">
                <span class="icon">👥</span>
                <span>Candidate Pipeline</span>
            </a>

            <div class="nav-section-title">Supervision &amp; Assessment</div>
            <a href="<?= url('placements/interns') ?>" class="nav-link <?= isNavActive('/placements', $currentUri) ?>">
                <span class="icon">🎓</span>
                <span>Current Interns</span>
            </a>
            <a href="<?= url('attendance/batch') ?>" class="nav-link <?= isNavActive('/attendance', $currentUri) ?>">
                <span class="icon">📅</span>
                <span>Batch Attendance</span>
            </a>
            <a href="<?= url('logbooks/review') ?>" class="nav-link <?= isNavActive('/logbooks', $currentUri) ?>">
                <span class="icon">📖</span>
                <span>Logbook Review</span>
            </a>
            <a href="<?= url('evaluations/host') ?>" class="nav-link <?= isNavActive('/evaluations', $currentUri) ?>">
                <span class="icon">📋</span>
                <span>Host Evaluations (40%)</span>
            </a>

        <?php elseif ($role === 'university_supervisor'): ?>
            <div class="nav-section-title">Supervision</div>
            <a href="<?= url('supervisors/students') ?>" class="nav-link <?= isNavActive('/supervisors/students', $currentUri) ?>">
                <span class="icon">👥</span>
                <span>Assigned Students</span>
            </a>
            <a href="<?= url('logbooks/supervisor') ?>" class="nav-link <?= isNavActive('/logbooks', $currentUri) ?>">
                <span class="icon">📖</span>
                <span>Grade Logbooks (10%)</span>
            </a>
            <a href="<?= url('monthly-reports/supervisor') ?>" class="nav-link <?= isNavActive('/monthly-reports', $currentUri) ?>">
                <span class="icon">📑</span>
                <span>Monthly Reports</span>
            </a>
            <a href="<?= url('final-reports/marking') ?>" class="nav-link <?= isNavActive('/final-reports', $currentUri) ?>">
                <span class="icon">🎓</span>
                <span>Mark Final Report (20%)</span>
            </a>
            <a href="<?= url('evaluations/visiting') ?>" class="nav-link <?= isNavActive('/evaluations', $currentUri) ?>">
                <span class="icon">📋</span>
                <span>Visiting Assessment (30%)</span>
            </a>

        <?php elseif ($role === 'coordinator'): ?>
            <div class="nav-section-title">Cohort &amp; Users</div>
            <a href="<?= url('import') ?>" class="nav-link <?= isNavActive('/import', $currentUri) ?>">
                <span class="icon">📥</span>
                <span>Bulk Cohort Import</span>
            </a>
            <a href="<?= url('users') ?>" class="nav-link <?= isNavActive('/users', $currentUri) ?>">
                <span class="icon">👥</span>
                <span>User Management</span>
            </a>
            <a href="<?= url('companies') ?>" class="nav-link <?= isNavActive('/companies', $currentUri) ?>">
                <span class="icon">🏢</span>
                <span>Company Verification</span>
            </a>

            <div class="nav-section-title">Placement Administration</div>
            <a href="<?= url('vacancies/vetting') ?>" class="nav-link <?= isNavActive('/vacancies/vetting', $currentUri) ?>">
                <span class="icon">🛡️</span>
                <span>Vacancy Vetting</span>
            </a>
            <a href="<?= url('placements/approvals') ?>" class="nav-link <?= isNavActive('/placements/approvals', $currentUri) ?>">
                <span class="icon">✅</span>
                <span>Placement Approvals</span>
            </a>
            <a href="<?= url('supervisors/assignment') ?>" class="nav-link <?= isNavActive('/supervisors/assignment', $currentUri) ?>">
                <span class="icon">⚖️</span>
                <span>Supervisor Allocation</span>
            </a>
            <a href="<?= url('placements/monitoring') ?>" class="nav-link <?= isNavActive('/placements/monitoring', $currentUri) ?>">
                <span class="icon">⚠️</span>
                <span>Monitoring &amp; Late Submissions</span>
            </a>
            <a href="<?= url('evaluations/lock') ?>" class="nav-link <?= isNavActive('/evaluations/lock', $currentUri) ?>">
                <span class="icon">🔒</span>
                <span>Final Grade Overview &amp; Lock</span>
            </a>

            <div class="nav-section-title">Portal &amp; System</div>
            <a href="<?= url('resource-admin') ?>" class="nav-link <?= isNavActive('/resource-admin', $currentUri) ?>">
                <span class="icon">📁</span>
                <span>Internship Resources</span>
            </a>
            <a href="<?= url('resource-admin/announcements') ?>" class="nav-link <?= isNavActive('/resource-admin/announcements', $currentUri) ?>">
                <span class="icon">📢</span>
                <span>Announcements</span>
            </a>
            <a href="<?= url('settings') ?>" class="nav-link <?= isNavActive('/settings', $currentUri) ?>">
                <span class="icon">⚙️</span>
                <span>Assessment Weights &amp; Rules</span>
            </a>
            <a href="<?= url('settings/exports') ?>" class="nav-link <?= isNavActive('/settings/exports', $currentUri) ?>">
                <span class="icon">📤</span>
                <span>CSV Data Exports</span>
            </a>
        <?php endif; ?>

        <div class="nav-section-title">Communication</div>
        <a href="<?= url('chat') ?>" class="nav-link <?= isNavActive('/chat', $currentUri) ?>">
            <span class="icon">💬</span>
            <span>4-Way Group Chat</span>
        </a>
    </nav>
</aside>
