<div class="page-header">
    <div>
        <h1>Company Supervisor Dashboard</h1>
        <p><?= e($user['company']['name'] ?? 'Host Organisation') ?> &bull; Supervision &amp; Recruitment Portal</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('attendance/batch') ?>" class="btn btn-primary">Batch Verify Attendance &rarr;</a>
        <a href="<?= url('vacancies/create') ?>" class="btn btn-secondary">+ Post Internship</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-tile">
        <div class="stat-label">Active Interns</div>
        <div class="stat-value"><?= count($interns) ?></div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Supervised in your organisation
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Pending Attendance Days</div>
        <div class="stat-value" style="<?= $pendingAttendanceCount > 0 ? 'color: var(--c-status-warning);' : '' ?>">
            <?= $pendingAttendanceCount ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Days awaiting batch verification
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Logbooks Awaiting Review</div>
        <div class="stat-value" style="<?= $pendingLogbooksCount > 0 ? 'color: var(--c-status-warning);' : '' ?>">
            <?= $pendingLogbooksCount ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Weekly logbook submissions
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Pending Evaluations (40%)</div>
        <div class="stat-value" style="<?= $pendingEvaluationsCount > 0 ? 'color: var(--c-status-danger);' : '' ?>">
            <?= $pendingEvaluationsCount ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Host supervisor rubrics
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Assigned Interns Under Supervision</h3>
        <span style="font-size: 0.84rem; color: var(--c-text-muted);">Batch verification and evaluations can be conducted per intern</span>
    </div>

    <?php if (!empty($interns)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name &amp; ID</th>
                        <th>Programme</th>
                        <th>Position</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                            <td>
                                <?= date('d M Y', strtotime($intern['start_date'])) ?> &ndash;<br>
                                <?= date('d M Y', strtotime($intern['end_date'])) ?>
                            </td>
                            <td><?= renderStatusBadge($intern['status']) ?></td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                    <a href="<?= url('attendance/batch', ['placement_id' => $intern['id']]) ?>" class="btn btn-secondary btn-sm" title="Batch Verify Attendance">Attendance</a>
                                    <a href="<?= url('logbooks/review', ['placement_id' => $intern['id']]) ?>" class="btn btn-secondary btn-sm" title="Review Logbooks">Logbooks</a>
                                    <a href="<?= url('evaluations/host-rubric', ['placement_id' => $intern['id']]) ?>" class="btn btn-primary btn-sm" title="Host Rubric Evaluation">Evaluate (40%)</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2.5rem 1rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">👥</div>
            <h4>No Interns Currently Assigned</h4>
            <p style="color: var(--c-text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
                Publish vacancies and review candidate applications to place interns.
            </p>
            <a href="<?= url('vacancies/manage') ?>" class="btn btn-primary btn-sm" style="margin-top: 1rem;">Manage Listings</a>
        </div>
    <?php endif; ?>
</div>
