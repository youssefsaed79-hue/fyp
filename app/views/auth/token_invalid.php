<div style="text-align: center; padding: 1.5rem 0;">
    <div style="font-size: 3rem; margin-bottom: 0.75rem;">⚠️</div>
    <h2 style="color: var(--c-status-danger); margin-bottom: 0.5rem;"><?= e($title ?? 'Invalid Invitation') ?></h2>
    <p style="color: var(--c-text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.5;">
        <?= e($message ?? 'This invitation token cannot be processed.') ?>
    </p>

    <div style="display: flex; gap: 0.75rem; justify-content: center;">
        <a href="<?= url('login') ?>" class="btn btn-primary">Go to Login</a>
    </div>
</div>
