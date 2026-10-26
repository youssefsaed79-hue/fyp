<div class="card" style="max-width: 650px; margin: 3rem auto; text-align: center; padding: 3rem 2rem;">
    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🔍</div>
    <h1 style="color: var(--c-forest-dark); margin-bottom: 0.75rem;">404 — Page Not Found</h1>
    <p style="font-size: 1.05rem; color: var(--c-text-muted); margin-bottom: 1.5rem;">
        The page you are looking for does not exist or may have been moved.
    </p>
    <?php if (!empty($requestedUrl)): ?>
        <p style="font-family: monospace; font-size: 0.9rem; color: var(--c-text-muted); margin-bottom: 1.5rem;">
            <?= e($requestedUrl) ?>
        </p>
    <?php endif; ?>
    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="<?= url('dashboard') ?>" class="btn btn-primary">Go to Dashboard</a>
        <a href="<?= url('login') ?>" class="btn btn-secondary">Login Page</a>
    </div>
</div>
