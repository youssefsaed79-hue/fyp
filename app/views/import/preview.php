<div class="page-header">
    <div>
        <h1>Cohort Validation Report &amp; Preview</h1>
        <p>File: <strong><?= e($fileName) ?></strong> &bull; Review error report before confirming database insertion</p>
    </div>
    <div>
        <a href="<?= url('import') ?>" class="btn btn-secondary">&larr; Upload Different File</a>
    </div>
</div>

<!-- VALIDATION SUMMARY -->
<div class="stats-grid">
    <div class="stat-tile">
        <div class="stat-label">Total Rows Analyzed</div>
        <div class="stat-value"><?= $report['total_processed'] ?></div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Valid Rows Ready to Import</div>
        <div class="stat-value" style="color: var(--c-status-success);"><?= $report['valid_count'] ?></div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Will be auto-provisioned as verified
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Rejected Rows (Excluded)</div>
        <div class="stat-value" style="<?= $report['rejected_count'] > 0 ? 'color: var(--c-status-danger);' : '' ?>">
            <?= $report['rejected_count'] ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            <?= $report['rejected_count'] > 0 ? 'Errors identified below' : 'Zero validation errors' ?>
        </div>
    </div>
</div>

<!-- 1. ERROR REPORT (REJECTED ROWS) -->
<?php if (!empty($report['rejected_rows'])): ?>
    <div class="card" style="border-left: 4px solid var(--c-status-danger);">
        <div class="card-title">
            <h3 style="color: var(--c-status-danger);">Validation Error Report (<?= count($report['rejected_rows']) ?> Rejected Rows)</h3>
            <span class="badge badge-danger">Excluded from Import</span>
        </div>
        <p style="color: var(--c-text-muted); font-size: 0.88rem; margin-bottom: 1rem;">
            The following rows contained missing fields, invalid email syntax, or duplicates. Per specification (FR-AUTH-03), these rows will <strong>not</strong> be imported into the database.
        </p>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Row #</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Reason(s) for Rejection</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['rejected_rows'] as $rej): ?>
                        <tr>
                            <td><strong><?= $rej['row'] ?></strong></td>
                            <td><code><?= e($rej['student_id'] ?: '[EMPTY]') ?></code></td>
                            <td><?= e($rej['name'] ?: '[EMPTY]') ?></td>
                            <td><?= e($rej['email'] ?: '[EMPTY]') ?></td>
                            <td>
                                <ul style="margin: 0; padding-left: 1.2rem; color: var(--c-status-danger); font-size: 0.84rem;">
                                    <?php foreach ($rej['errors'] as $err): ?>
                                        <li><?= e($err) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- 2. VALID ROWS PREVIEW -->
<div class="card">
    <div class="card-title">
        <h3 style="color: var(--c-forest-dark);">Valid Students Preview (<?= count($report['valid_rows']) ?> Ready)</h3>
        <span class="badge badge-success">Passed All Checks</span>
    </div>

    <?php if (!empty($report['valid_rows'])): ?>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Row #</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Programme</th>
                        <th>Intake</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['valid_rows'] as $row): ?>
                        <tr>
                            <td><?= $row['row'] ?></td>
                            <td><strong><?= e($row['student_id']) ?></strong></td>
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e($row['programme']) ?></td>
                            <td><?= e($row['intake']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <form action="<?= url('import/confirm') ?>" method="POST" style="margin-top: 1.5rem;" data-confirm="Are you sure you want to provision these <?= count($report['valid_rows']) ?> student accounts? Activation emails will be dispatched.">
            <?= \App\Core\Csrf::field() ?>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem; font-size: 1rem;">
                    Confirm &amp; Auto-Provision <?= count($report['valid_rows']) ?> Accounts &rarr;
                </button>
                <a href="<?= url('import') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div style="text-align: center; padding: 2rem 1rem; color: var(--c-text-muted);">
            No valid rows found in the uploaded file. Please correct the errors in the CSV file and re-upload.
        </div>
    <?php endif; ?>
</div>
