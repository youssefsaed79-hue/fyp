<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.6rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Sign In</h2>
    <p style="color: var(--c-text-muted); font-size: 0.92rem;">
        Access the InternFlow portal with your credentials.
    </p>
</div>

<form action="<?= url('login') ?>" method="POST" data-validate>
    <?= \App\Core\Csrf::field() ?>

    <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="name@uniten.edu.my" required autofocus value="<?= e($email ?? '') ?>">
    </div>

    <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
            <label for="password" class="form-label" style="margin-bottom: 0;">Password</label>
            <a href="<?= url('forgot-password') ?>" style="font-size: 0.8rem;">Forgot password?</a>
        </div>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required>
    </div>

    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">
            Sign In to InternFlow
        </button>
    </div>
</form>

<div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--c-border); text-align: center; font-size: 0.88rem;">
    <p style="color: var(--c-text-muted); margin-bottom: 0.75rem;">Are you a host company partner?</p>
    <a href="<?= url('companies/register') ?>" class="btn btn-secondary btn-sm" style="display: inline-block;">
        Register Partner Company Profile
    </a>
</div>

<!-- Quick Demo Account Switcher (For evaluators and testing) -->
<div class="card-bluegray" style="margin-top: 1.5rem; padding: 0.85rem; font-size: 0.82rem;">
    <div style="font-weight: 700; color: var(--c-forest-dark); margin-bottom: 0.4rem;">
        Demo Credentials (Password: <code>Password@123</code>)
    </div>
    <div style="display: flex; flex-direction: column; gap: 0.3rem;">
        <div><strong>Coordinator:</strong> <a href="javascript:void(0)" onclick="fillLogin('coordinator@internflow.test')">coordinator@internflow.test</a></div>
        <div><strong>Supervisor:</strong> <a href="javascript:void(0)" onclick="fillLogin('dr.ahmed@uniten.edu.my')">dr.ahmed@uniten.edu.my</a></div>
        <div><strong>Partner Company:</strong> <a href="javascript:void(0)" onclick="fillLogin('hr@petronas.test')">hr@petronas.test</a></div>
        <div><strong>Student:</strong> <a href="javascript:void(0)" onclick="fillLogin('aly.youssef@student.uniten.edu.my')">aly.youssef@student.uniten.edu.my</a></div>
    </div>
</div>

<script>
function fillLogin(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'Password@123';
}
</script>
