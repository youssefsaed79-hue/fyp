<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Assessment Weights &amp; System Settings</h1>
        <p class="text-muted" style="margin: 0;">
            Configure assessment component weighting (Table 3.8), attendance thresholds, and security parameters.
        </p>
    </div>
    <div>
        <a href="<?= url('settings/exports') ?>" class="btn btn-secondary">
            📤 CSV Data Exports
        </a>
    </div>
</div>

<form action="<?= url('settings/update') ?>" method="POST" style="max-width: 800px;">
    <?= \App\Core\Csrf::field() ?>

    <!-- Assessment Components Weighting -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Method of Evaluation Weightage (Table 3.8)</h3>
        <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 20px;">
            Configure the 4 component weights applied by the GradeEngine. The total must equal exactly 100%.
        </p>

        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="form-group">
                <label class="form-label" style="font-weight: 600;">Host Supervisor Evaluation Weight (%)</label>
                <input type="number" step="1" min="0" max="100" name="weight_host" class="form-control" required value="<?= e($settings['weight_host'] ?? '40') ?>">
                <small class="text-muted">Standard UNITEN: 40%</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 600;">Visiting Lecturer Assessment Weight (%)</label>
                <input type="number" step="1" min="0" max="100" name="weight_visiting" class="form-control" required value="<?= e($settings['weight_visiting'] ?? '30') ?>">
                <small class="text-muted">Standard UNITEN: 30%</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 600;">Final Report Weight (%)</label>
                <input type="number" step="1" min="0" max="100" name="weight_report" class="form-control" required value="<?= e($settings['weight_report'] ?? '20') ?>">
                <small class="text-muted">Standard UNITEN: 20%</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 600;">Logbook Component Weight (%)</label>
                <input type="number" step="1" min="0" max="100" name="weight_logbook" class="form-control" required value="<?= e($settings['weight_logbook'] ?? '10') ?>">
                <small class="text-muted">Standard UNITEN: 10%</small>
            </div>
        </div>
    </div>

    <!-- Operational Thresholds & Security -->
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Operational Thresholds &amp; Rules</h3>
        <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 20px;">
            System automated triggers and expiration timeouts.
        </p>

        <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label class="form-label" style="font-weight: 600;">Attendance Warning Threshold (%)</label>
                <input type="number" step="1" min="1" max="100" name="attendance_alert_pct" class="form-control" required value="<?= e($settings['attendance_alert_pct'] ?? '80') ?>">
                <small class="text-muted">Triggers at-risk warnings below this value (Default: 80%).</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 600;">Invitation Token Expiry (Hours)</label>
                <input type="number" step="1" min="1" max="720" name="token_expiry_hours" class="form-control" required value="<?= e($settings['token_expiry_hours'] ?? '72') ?>">
                <small class="text-muted">Self-sourced supervisor token lifetime (Default: 72 hours).</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 600;">Session Inactivity Timeout (Minutes)</label>
                <input type="number" step="1" min="5" max="120" name="session_timeout_min" class="form-control" required value="<?= e($settings['session_timeout_min'] ?? '30') ?>">
                <small class="text-muted">NFR-SEC-02 compliance (Default: 30 minutes).</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 600;">Max File Upload Limit (MB)</label>
                <input type="number" step="1" min="1" max="50" name="max_upload_mb" class="form-control" required value="<?= e($settings['max_upload_mb'] ?? '10') ?>">
                <small class="text-muted">NFR-PERF-02 compliance (Default: 10 MB).</small>
            </div>
        </div>
    </div>

    <div>
        <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-weight: 600;">
            💾 Save System Settings
        </button>
    </div>
</form>
