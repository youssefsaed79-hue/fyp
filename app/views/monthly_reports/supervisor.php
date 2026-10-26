<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Student Monthly Progress Reports</h1>
        <p class="text-muted" style="margin: 0;">
            Review student progress summaries across 4-week periods for monitoring, academic guidance and feedback (FR-MPR-01).
        </p>
    </div>
</div>

<?php if (!$placement): ?>
    <div class="card p-6 text-center" style="max-width: 600px; margin: 40px auto;">
        <h3 style="color: var(--c-forest-dark);">No Placement Selected</h3>
        <p class="text-muted">No assigned active internship placement was found.</p>
    </div>
<?php else: ?>
    <div class="card" style="margin-bottom: 24px; background: var(--c-bluegray); border-left: 4px solid var(--c-forest);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="color: var(--c-forest-dark); margin: 0 0 4px;"><?= e($placement['student_name']) ?> (<?= e($placement['student_id'] ?? '') ?>)</h3>
                <div style="font-size: 0.9rem; color: #444;">
                    <strong>Company:</strong> <?= e($placement['company_name']) ?> &nbsp;|&nbsp;
                    <strong>Position:</strong> <?= e($placement['position']) ?> &nbsp;|&nbsp;
                    <strong>Duration:</strong> <?= date('d M Y', strtotime($placement['start_date'])) ?> to <?= date('d M Y', strtotime($placement['end_date'])) ?>
                </div>
            </div>
            <div>
                <span class="badge badge-success">Internship Active</span>
            </div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        <?php for ($p = 1; $p <= 3; $p++):
            $report = null;
            foreach ($reports as $r) {
                if ((int)$r['period_no'] === $p) {
                    $report = $r;
                    break;
                }
            }
            $weeks = "Weeks " . (($p - 1) * 4 + 1) . " – " . ($p * 4);
        ?>
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div>
                    <h3 style="color: var(--c-forest-dark); margin: 0 0 4px;">Month <?= $p ?> Progress</h3>
                    <span class="badge badge-neutral"><?= $weeks ?></span>
                </div>
                <?php if ($report): ?>
                    <span class="badge badge-success">✓ Submitted</span>
                <?php else: ?>
                    <span class="badge badge-warning">Awaiting Submission</span>
                <?php endif; ?>
            </div>

            <?php if ($report): ?>
                <div style="margin-top: 12px;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--c-forest-dark); margin-bottom: 6px;">
                        SUBMITTED: <?= date('d M Y, h:i A', strtotime($report['submitted_at'])) ?>
                    </div>
                    <div style="background: #fff; padding: 12px; border-radius: 6px; border: 1px solid rgba(0,0,0,0.06); font-size: 0.9rem; white-space: pre-wrap; line-height: 1.5; color: #222;">
                        <?= e($report['summary']) ?>
                    </div>

                    <?php if (!empty($report['stored_name'])): ?>
                        <div style="margin-top: 12px;">
                            <a href="<?= url('monthly-reports/download?id=' . $report['id']) ?>" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                📥 Download Attachment (<?= e($report['original_name'] ?: 'File') ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-muted" style="margin-top: 12px; font-size: 0.9rem;">
                    The student has not yet submitted their progress summary for this period.
                </p>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>
<?php endif; ?>
