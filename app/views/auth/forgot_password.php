<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.5rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Password Recovery</h2>
    <p style="color: var(--c-text-muted); font-size: 0.92rem;">
        Enter your registered email address to receive a secure password reset link.
    </p>
</div>

<form action="<?= url('forgot-password') ?>" method="POST" data-validate>
    <?= \App\Core\Csrf::field() ?>

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="name@uniten.edu.my" required autofocus>
    </div>

    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">
            Send Password Reset Link
        </button>
    </div>
</form>

<div style="margin-top: 2rem; text-align: center;">
    <a href="<?= url('login') ?>" style="font-size: 0.88rem;">← Back to Sign In</a>
</div>
