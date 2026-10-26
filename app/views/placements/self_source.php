<div class="page-header">
    <div>
        <h1>Register Self-Sourced Placement</h1>
        <p>Submit placement details secured outside InternFlow to generate an automated supervisor invitation (FR-APM-03, FR-UCM-02)</p>
    </div>
    <div>
        <a href="<?= url('placements/my') ?>" class="btn btn-secondary">&larr; Back to Placement</a>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-bluegray" style="margin-bottom: 1.5rem;">
        <h4 style="color: var(--c-forest-dark); margin-bottom: 0.35rem;">How Self-Sourced Placement Works</h4>
        <p style="font-size: 0.88rem; color: var(--c-text-muted); margin: 0; line-height: 1.5;">
            Once you submit this form, InternFlow will automatically issue a <strong>System Invitation Token</strong> to your named host supervisor. Your supervisor can register a scoped account in seconds, and your placement will proceed for University Coordinator approval.
        </p>
    </div>

    <form action="<?= url('placements/self-source') ?>" method="POST" data-validate>
        <?= \App\Core\Csrf::field() ?>

        <h3 style="font-size: 1.1rem; color: var(--c-forest-dark); margin-bottom: 1rem;">1. Host Organisation Information</h3>
        <div class="form-row">
            <div class="form-group" style="flex: 1.2;">
                <label for="company_name" class="form-label">Company / Organisation Name</label>
                <input type="text" id="company_name" name="company_name" class="form-control" required placeholder="e.g. Acme Technologies Sdn Bhd">
            </div>

            <div class="form-group" style="flex: 1;">
                <label for="registration_no" class="form-label">Company Registration No. (SSM)</label>
                <input type="text" id="registration_no" name="registration_no" class="form-control" required placeholder="e.g. 202101099887">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label for="industry" class="form-label">Industry / Sector</label>
                <input type="text" id="industry" name="industry" class="form-control" required placeholder="e.g. IT &amp; Software Development">
            </div>

            <div class="form-group" style="flex: 1.5;">
                <label for="address" class="form-label">Company Physical Address</label>
                <input type="text" id="address" name="address" class="form-control" required placeholder="Full office address">
            </div>
        </div>

        <h3 style="font-size: 1.1rem; color: var(--c-forest-dark); margin: 1.5rem 0 1rem; border-top: 1px solid var(--c-border); padding-top: 1.25rem;">2. Host Workplace Supervisor Contact</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="supervisor_name" class="form-label">Supervisor Full Name</label>
                <input type="text" id="supervisor_name" name="supervisor_name" class="form-control" required placeholder="e.g. Ir. Ahmad Zaki">
            </div>

            <div class="form-group">
                <label for="supervisor_email" class="form-label">Supervisor Work Email</label>
                <input type="email" id="supervisor_email" name="supervisor_email" class="form-control" required placeholder="supervisor@company.com">
                <div class="form-help">The invitation token link will be sent to this email address.</div>
            </div>
        </div>

        <h3 style="font-size: 1.1rem; color: var(--c-forest-dark); margin: 1.5rem 0 1rem; border-top: 1px solid var(--c-border); padding-top: 1.25rem;">3. Training Position &amp; Approved Dates</h3>
        <div class="form-group">
            <label for="position" class="form-label">Job / Training Title</label>
            <input type="text" id="position" name="position" class="form-control" required placeholder="e.g. Trainee Software Engineer / IT Intern">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
            </div>

            <div class="form-group">
                <label for="end_date" class="form-label">End Date (12 Weeks)</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+98 days')) ?>" required>
            </div>
        </div>

        <div style="margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem;">
                Register Placement &amp; Dispatch Supervisor Invitation &rarr;
            </button>
        </div>
    </form>
</div>
