<div class="page-header">
    <div>
        <h1>Current Interns</h1>
        <p>UNITEN CCI student interns currently undergoing 12-week industrial training at your organization</p>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Interns Roster (<?= count($interns) ?> Total)</h3>
    </div>

    <?php if (!empty($interns)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name &amp; ID</th>
                        <th>Programme</th>
                        <th>Position</th>
                        <th>Training Dates</th>
                        <th>Status</th>
                        <th>Supervision Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interns as $intern): ?>
                        <tr>
                            <td>
                                <strong><?= e($intern['student_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($intern['student_id']) ?></small>
                            </td>
                            <td><?= e($intern['programme']) ?></td>
                            <td><?= e($intern['position']) ?></td>
                            <td style="font-size: 0.84rem;">
                                <?= date('d M Y', strtotime($intern['start_date'])) ?> &ndash;<br>
                                <?= date('d M Y', strtotime($intern['end_date'])) ?>
                            </td>
                            <td><?= renderStatusBadge($intern['status']) ?></td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                    <a href="<?= url('attendance/batch', ['placement_id' => $intern['id']]) ?>" class="btn btn-secondary btn-sm">Verify Attendance</a>
                                    <a href="<?= url('logbooks/review', ['placement_id' => $intern['id']]) ?>" class="btn btn-secondary btn-sm">Review Logbooks</a>
                                    <a href="<?= url('evaluations/host-rubric', ['placement_id' => $intern['id']]) ?>" class="btn btn-primary btn-sm">Evaluate (40%)</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--c-text-muted);">
            No interns currently active.
        </div>
    <?php endif; ?>
</div>
