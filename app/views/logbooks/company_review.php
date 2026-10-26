<div class="page-header">
    <div>
        <h1>Company Logbook Review Queue</h1>
        <p>Review and approve weekly digital logbooks submitted by student interns (FR-LOG-03)</p>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Submitted Logbooks (<?= count($logbooks) ?> Entries)</h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Intern Name &amp; ID</th>
                    <th>Week &amp; Period</th>
                    <th>Hours</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logbooks)): ?>
                    <?php foreach ($logbooks as $log): ?>
                        <tr>
                            <td>
                                <strong><?= e($log['student_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($log['student_id']) ?></small>
                            </td>
                            <td>
                                <strong>Week <?= $log['week_no'] ?></strong><br>
                                <small style="color: var(--c-text-muted);">
                                    <?= date('d M Y', strtotime($log['week_start'])) ?> &ndash; <?= date('d M Y', strtotime($log['week_end'])) ?>
                                </small>
                            </td>
                            <td><?= (float)$log['hours'] ?> hrs</td>
                            <td><?= renderStatusBadge($log['status']) ?></td>
                            <td style="font-size: 0.84rem;"><?= !empty($log['submitted_at']) ? date('d M Y, H:i', strtotime($log['submitted_at'])) : 'Draft' ?></td>
                            <td>
                                <a href="<?= url('logbooks/view', ['id' => $log['id']]) ?>" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.65rem;">
                                    Review &amp; Sign Off &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--c-text-muted);">
                            No logbooks currently awaiting company review.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
