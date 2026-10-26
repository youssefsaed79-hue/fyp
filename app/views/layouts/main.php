<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?> — InternFlow UNITEN</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <script src="<?= asset('vendor/chart.umd.min.js') ?>"></script>
</head>
<body>
    <div class="app-container">
        <!-- Persistent Role Sidebar -->
        <?php \App\Core\View::partial('partials/sidebar'); ?>

        <!-- Main Content Area -->
        <div class="main-wrapper">
            <!-- Topbar -->
            <?php \App\Core\View::partial('partials/topbar'); ?>

            <!-- Page Canvas -->
            <main class="content-canvas">
                <!-- Flash Messages -->
                <?php \App\Core\View::partial('partials/flash'); ?>

                <!-- View Output -->
                <?= $content ?? '' ?>
            </main>

            <!-- Footer -->
            <footer style="padding: 1.25rem 1.75rem; font-size: 0.8rem; color: var(--c-text-muted); border-top: 1px solid var(--c-border); background: #FFFFFF; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div>&copy; <?= date('Y') ?> Universiti Tenaga Nasional (UNITEN) — College of Computing &amp; Informatics (CCI)</div>
                <div>InternFlow: Internship Placement, Monitoring &amp; Evaluation Platform</div>
            </footer>
        </div>
    </div>

    <!-- UI Scripts -->
    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (!empty($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
