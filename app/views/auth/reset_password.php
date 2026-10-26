<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.5rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Set New Password</h2>
    <p style="color: var(--c-text-muted); font-size: 0.92rem;">
        Please create a strong new password for your InternFlow account.
    </p>
</div>

<form action="<?= url('reset-password') ?>" method="POST" data-validate>
    <?= \App\Core\Csrf::field() ?>
    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

    <div class="form-group">
        <label for="password" class="form-label">New Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="At least 8 characters" required autofocus minlength="8">
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Re-type new password" required minlength="8">
    </div>

    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">
            Update Password &amp; Sign In
        </button>
    </div>
</form>
