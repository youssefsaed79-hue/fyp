<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Sign In') ?> — InternFlow UNITEN</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body style="background-color: var(--c-offwhite);">
    <div class="auth-wrapper">
        <!-- Hero Information Panel -->
        <div class="auth-hero-panel">
            <div>
                <a href="<?= url() ?>" style="text-decoration: none; color: var(--c-text-inverse);">
                    <div style="font-size: 2rem; font-weight: 800; letter-spacing: -0.5px;">InternFlow</div>
                    <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; margin-top: 4px;">
                        Industrial Training Management System
                    </div>
                </a>
                <div style="margin-top: 3.5rem; max-width: 480px;">
                    <h1 style="color: var(--c-text-inverse); font-size: 2.2rem; font-weight: 700; line-height: 1.25; margin-bottom: 1.25rem;">
                        Centralised Internship Lifecycle Platform
                    </h1>
                    <p style="font-size: 1.05rem; line-height: 1.6; opacity: 0.9; margin-bottom: 1.5rem;">
                        Connecting UNITEN College of Computing &amp; Informatics (CCI) students, host companies, visiting lecturers, and internship coordinators in one unified environment.
                    </p>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.95rem; opacity: 0.85;">
                        <li style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="color: #6ee7b7; font-weight: bold;">✓</span> 12-Week Industrial Training Digital Logbooks &amp; Daily Attendance
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="color: #6ee7b7; font-weight: bold;">✓</span> Batch Verification &amp; Dual-Supervisor Evaluation (40/30/20/10)
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="color: #6ee7b7; font-weight: bold;">✓</span> Official Internship Kit, Journey Stepper &amp; Guidelines
                        </li>
                    </ul>
                </div>
            </div>

            <div style="font-size: 0.82rem; opacity: 0.7; padding-top: 2rem; border-top: 1px solid rgba(243, 242, 236, 0.15);">
                &copy; <?= date('Y') ?> Universiti Tenaga Nasional. College of Computing and Informatics (CCI).
            </div>
        </div>

        <!-- Auth Form Panel -->
        <div class="auth-form-panel">
            <div class="auth-card">
                <?php \App\Core\View::partial('partials/flash'); ?>
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
