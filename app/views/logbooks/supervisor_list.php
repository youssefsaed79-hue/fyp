<div class="page-header">
    <div>
        <h1>Logbook Academic Grading Queue (10%)</h1>
        <p>Review weekly training logs, evaluate learning reflections, and assign academic grades (FR-LOG-03)</p>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Supervised Logbooks (<?= count($logbooks) ?> Entries)</h3>
        <span class="badge badge-info">10% Final Grade Weightage</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name &amp; ID</th>
                    <th>Host Company</th>
                    <th>Week &amp; Period</th>
                    <th>Status</th>
                    <th>Current Grade</th>
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
                            <td><?= e($log['company_name']) ?></td>
                            <td>
                                <strong>Week <?= $log['week_no'] ?></strong><br>
                                <small style="color: var(--c-text-muted);">
                                    <?= date('d M Y', strtotime($log['week_start'])) ?> &ndash; <?= date('d M Y', strtotime($log['week_end'])) ?>
                                </small>
                            </td>
                            <td><?= renderStatusBadge($log['status']) ?></td>
                            <td>
                                <?php if ($log['grade'] !== null): ?>
                                    <strong style="color: var(--c-forest);"><?= number_format((float)$log['grade'], 1) ?>%</strong>
                                <?php else: ?>
                                    <span class="badge badge-warning">Needs Grading</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('logbooks/view', ['id' => $log['id']]) ?>" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.65rem;">
                                    <?= $log['grade'] !== null ? 'View / Update Grade' : '✏️ Grade Entry (10%)' ?> &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--c-text-muted);">
                            No logbooks currently available for academic grading.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
