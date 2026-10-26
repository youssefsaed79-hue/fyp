<div class="page-header">
    <div>
        <h1>Weekly Digital Logbooks</h1>
        <p>12-Week Industrial Training Digital Logbook System &bull; Single-Submission Workflow (FR-LOG-01, FR-LOG-02)</p>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Training Logbooks Schedule (12 Weeks)</h3>
        <span class="badge badge-info">10% Academic Assessment Component</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">Week</th>
                    <th>Date Period</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                    <th>Academic Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logbooks as $log): ?>
                    <tr>
                        <td><strong>Week <?= $log['week_no'] ?></strong></td>
                        <td>
                            <?= date('d M Y', strtotime($log['week_start'])) ?> &ndash;
                            <?= date('d M Y', strtotime($log['week_end'])) ?>
                        </td>
                        <td>
                            <?= renderStatusBadge($log['status']) ?>
                        </td>
                        <td style="font-size: 0.84rem; color: var(--c-text-muted);">
                            <?= !empty($log['submitted_at']) ? date('d M Y, H:i', strtotime($log['submitted_at'])) : 'Not submitted' ?>
                        </td>
                        <td>
                            <?php if ($log['grade'] !== null): ?>
                                <strong style="color: var(--c-forest);"><?= number_format((float)$log['grade'], 1) ?>%</strong>
                            <?php else: ?>
                                <span style="color: var(--c-text-muted);">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (in_array($log['status'], ['draft', 'returned'], true)): ?>
                                <a href="<?= url('logbooks/edit', ['id' => $log['id']]) ?>" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.65rem;">
                                    <?= $log['status'] === 'returned' ? '✏️ Resubmit Correction' : '✏️ Complete &amp; Submit' ?>
                                </a>
                            <?php else: ?>
                                <a href="<?= url('logbooks/view', ['id' => $log['id']]) ?>" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.65rem;">
                                    View Entry &rarr;
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
