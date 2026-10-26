<div class="page-header">
    <div>
        <h1>Internship Monitoring &amp; Compliance</h1>
        <p>Real-time audit of active internships, late weekly logbooks, and verified attendance days (FR-DASH-01)</p>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Active Internships Audit (<?= count($activePlacements) ?> Active)</h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name &amp; ID</th>
                    <th>Host Company</th>
                    <th>Visiting Lecturer</th>
                    <th>Training Dates</th>
                    <th>Verified Days</th>
                    <th>Logbooks Approved</th>
                    <th>Overdue / Late Logs</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($activePlacements)): ?>
                    <?php foreach ($activePlacements as $ap): ?>
                        <tr>
                            <td>
                                <strong><?= e($ap['student_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($ap['student_id']) ?></small>
                            </td>
                            <td><?= e($ap['company_name']) ?></td>
                            <td><?= e($ap['uni_supervisor_name'] ?? 'Unassigned') ?></td>
                            <td style="font-size: 0.84rem;">
                                <?= date('d M Y', strtotime($ap['start_date'])) ?> &ndash;<br>
                                <?= date('d M Y', strtotime($ap['end_date'])) ?>
                            </td>
                            <td>
                                <strong><?= (int)$ap['verified_att'] ?></strong> days
                            </td>
                            <td>
                                <strong><?= (int)$ap['approved_logs'] ?></strong> / 12
                            </td>
                            <td>
                                <?php if ((int)$ap['overdue_logs'] > 0): ?>
                                    <span class="badge badge-danger"><?= (int)$ap['overdue_logs'] ?> Overdue</span>
                                <?php else: ?>
                                    <span class="badge badge-success">On Schedule</span>
                                <?php endif; ?>
                            </td>
                            <td><?= renderStatusBadge($ap['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--c-text-muted);">
                            No active internships currently undergoing training.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
