<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.5rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Partner Company Registration</h2>
    <p style="color: var(--c-text-muted); font-size: 0.92rem;">
        Register your organization with the UNITEN College of Computing &amp; Informatics (CCI) Industrial Training Programme.
    </p>
</div>

<form action="<?= url('companies/register') ?>" method="POST" data-validate>
    <?= \App\Core\Csrf::field() ?>

    <div class="form-group">
        <label for="company_name" class="form-label">Company / Organisation Name</label>
        <input type="text" id="company_name" name="company_name" class="form-control" required placeholder="e.g. InnoTech Solutions Sdn Bhd">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="registration_no" class="form-label">Business Registration No. (SSM)</label>
            <input type="text" id="registration_no" name="registration_no" class="form-control" required placeholder="e.g. 202101012345 (123456-X)">
        </div>

        <div class="form-group">
            <label for="industry" class="form-label">Industry / Tech Domain</label>
            <input type="text" id="industry" name="industry" class="form-control" required placeholder="e.g. Software, Fintech, Cyber Security">
        </div>
    </div>

    <div class="form-group">
        <label for="address" class="form-label">Official Office Address</label>
        <textarea id="address" name="address" class="form-control" rows="2" required placeholder="Office street address, city, postcode"></textarea>
    </div>

    <div style="border-top: 1px solid var(--c-border); padding-top: 1.25rem; margin-top: 1.25rem;">
        <h4 style="margin-bottom: 0.85rem; color: var(--c-forest-dark);">Representative / Supervisor Contact</h4>

        <div class="form-row">
            <div class="form-group">
                <label for="representative_name" class="form-label">Representative Name</label>
                <input type="text" id="representative_name" name="representative_name" class="form-control" required placeholder="Full Name">
            </div>

            <div class="form-group">
                <label for="representative_email" class="form-label">Official Corporate Email</label>
                <input type="email" id="representative_email" name="representative_email" class="form-control" required placeholder="name@company.com">
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Account Password</label>
            <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="At least 8 characters">
            <div class="form-help">Used to sign in to InternFlow once approved by the coordinator.</div>
        </div>
    </div>

    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">
            Submit Partner Registration
        </button>
    </div>
</form>

<div style="margin-top: 1.5rem; text-align: center;">
    <a href="<?= url('login') ?>" style="font-size: 0.88rem;">&larr; Already registered? Sign In</a>
</div>
