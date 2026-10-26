<div class="card" style="max-width: 650px; margin: 3rem auto; text-align: center; padding: 3rem 2rem;">
    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🛡️</div>
    <h1 style="color: var(--c-status-danger); margin-bottom: 0.75rem;">403 — Access Forbidden</h1>
    <p style="font-size: 1.05rem; color: var(--c-text-muted); margin-bottom: 1.5rem;">
        <?= e($reason ?? 'You do not have permission to access the requested resource or page under the Role-Based Access Control policy.') ?>
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="<?= url('dashboard') ?>" class="btn btn-primary">Return to Dashboard</a>
        <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
    </div>
</div>
