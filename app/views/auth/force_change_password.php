<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.5rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">First Time Login</h2>
    <p style="color: var(--c-text-muted); font-size: 0.92rem;">
        Your student account was imported by the coordinator with a temporary password. For your security, you must set a new password before accessing your account.
    </p>
</div>

<form action="<?= url('force-change-password') ?>" method="POST" data-validate>
    <?= \App\Core\Csrf::field() ?>

    <div class="form-group">
        <label for="current_password" class="form-label">Temporary Password</label>
        <input type="password" id="current_password" name="current_password" class="form-control" required autofocus>
    </div>

    <div class="form-group">
        <label for="new_password" class="form-label">New Password</label>
        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="At least 8 characters" required minlength="8">
    </div>

    <div class="form-group">
        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" placeholder="Re-type new password" required minlength="8">
    </div>

    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">
            Set New Password &amp; Continue
        </button>
    </div>
</form>
