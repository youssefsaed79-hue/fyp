<div class="page-header">
    <div>
        <h1>Batch Attendance Verification</h1>
        <p>Approve intern daily attendance records in one single batch action, or flag individual discrepancies (FR-ATT-02)</p>
    </div>
</div>

<!-- INTERN SELECTOR -->
<div class="card" style="padding: 1rem; margin-bottom: 1.25rem;">
    <form method="GET" action="<?= url('attendance/batch') ?>" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <label style="font-size: 0.88rem; font-weight: 600;">Select Intern:</label>
        <select name="placement_id" class="form-select" style="max-width: 380px;" onchange="this.form.submit()">
            <?php foreach ($interns as $intern): ?>
                <option value="<?= $intern['id'] ?>" <?= $selectedPlacementId === (int)$intern['id'] ? 'selected' : '' ?>>
                    <?= e($intern['student_name']) ?> (<?= e($intern['student_id']) ?>) &mdash; <?= e($intern['position']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($monthlyStats): ?>
    <div class="stats-grid">
        <div class="stat-tile">
            <div class="stat-label">Current Month Verified Attendance</div>
            <div class="stat-value" style="<?= !empty($monthlyStats['is_alert']) ? 'color: var(--c-status-danger);' : 'color: var(--c-forest);' ?>">
                <?= $monthlyStats['percentage'] ?>%
            </div>
            <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
                <?= $monthlyStats['verified_days'] ?> verified of <?= $monthlyStats['expected_days'] ?> expected days
            </div>
        </div>

        <div class="stat-tile">
            <div class="stat-label">Days Awaiting Verification</div>
            <div class="stat-value" style="color: var(--c-status-warning);">
                <?= count(array_filter($records, fn($r) => $r['verification'] === 'pending_verification')) ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
                Select checkboxes below to approve in batch
            </div>
        </div>

        <div class="stat-tile">
            <div class="stat-label">Flagged Discrepancies</div>
            <div class="stat-value" style="color: var(--c-status-danger);">
                <?= (int)$monthlyStats['flagged_days'] ?>
            </div>
            <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
                Days with supervisor notes
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- BATCH VERIFICATION FORM -->
<div class="card">
    <div class="card-title">
        <h3>Attendance Days Register</h3>
        <span class="badge badge-info">Single Batch Action</span>
    </div>

    <?php if (!empty($records)): ?>
        <form action="<?= url('attendance/batch-verify') ?>" method="POST" data-confirm="Confirm batch verification for all selected attendance days?">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="placement_id" value="<?= $selectedPlacementId ?>">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleSelectAll(true)">Select All Pending</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleSelectAll(false)">Deselect All</button>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1.5rem;">
                    ✓ Approve Selected Batch &amp; Save Flags
                </button>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">Approve</th>
                            <th>Date &amp; Day</th>
                            <th>Status &amp; Arrangement</th>
                            <th>Check-In / Out</th>
                            <th>Hours</th>
                            <th>Verification</th>
                            <th style="min-width: 200px;">Flag Discrepancy Comment (Optional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $rec): ?>
                            <?php $isPending = ($rec['verification'] === 'pending_verification'); ?>
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="record_ids[]" value="<?= $rec['id'] ?>" class="att-checkbox" <?= $isPending ? 'checked' : '' ?> style="width: 17px; height: 17px; cursor: pointer;">
                                </td>
                                <td>
                                    <strong><?= date('d M Y', strtotime($rec['work_date'])) ?></strong><br>
                                    <small style="color: var(--c-text-muted);"><?= date('l', strtotime($rec['work_date'])) ?></small>
                                </td>
                                <td>
                                    <?= renderStatusBadge($rec['status']) ?><br>
                                    <small style="color: var(--c-text-muted);"><?= e(strtoupper($rec['work_arrangement'])) ?></small>
                                    <?php if (!empty($rec['absence_reason'])): ?>
                                        <div style="font-size: 0.78rem; color: var(--c-text-muted); margin-top: 0.2rem;">
                                            Note: <?= e($rec['absence_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= e($rec['check_in'] ?? '--:--') ?> &ndash; <?= e($rec['check_out'] ?? '--:--') ?>
                                </td>
                                <td><?= (float)$rec['hours'] ?> hrs</td>
                                <td>
                                    <?= renderStatusBadge($rec['verification']) ?>
                                </td>
                                <td>
                                    <input type="text" name="flag_comment[<?= $rec['id'] ?>]" class="form-control" style="font-size: 0.82rem; padding: 0.35rem 0.55rem;" placeholder="Leave comment to flag day..." value="<?= e($rec['flag_comment'] ?? '') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 1.25rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.75rem;">
                    ✓ Approve Selected Batch &amp; Save Flags
                </button>
            </div>
        </form>
    <?php else: ?>
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--c-text-muted);">
            No attendance records found for this student intern.
        </div>
    <?php endif; ?>
</div>

<script>
function toggleSelectAll(checked) {
    document.querySelectorAll('.att-checkbox').forEach(cb => {
        cb.checked = checked;
    });
}
</script>
