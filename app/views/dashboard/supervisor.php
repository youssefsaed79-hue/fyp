<div class="page-header">
    <div>
        <h1>University Supervisor Dashboard</h1>
        <p><?= e($user['name']) ?> &bull; Visiting Lecturer Supervision Portal</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-tile">
        <div class="stat-label">Assigned Students</div>
        <div class="stat-value"><?= count($students) ?></div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Interns in your supervisory roster
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Logbooks to Grade (10%)</div>
        <div class="stat-value" style="<?= $pendingLogbooksCount > 0 ? 'color: var(--c-status-warning);' : '' ?>">
            <?= $pendingLogbooksCount ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Awaiting academic mark &amp; feedback
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Final Reports to Mark (20%)</div>
        <div class="stat-value" style="<?= $pendingReportsCount > 0 ? 'color: var(--c-status-warning);' : '' ?>">
            <?= $pendingReportsCount ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Submitted reports awaiting evaluation
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Visiting Evaluations (30%)</div>
        <div class="stat-value" style="<?= $pendingVisitsCount > 0 ? 'color: var(--c-status-danger);' : '' ?>">
            <?= $pendingVisitsCount ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Visiting lecturer evaluations pending
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Supervised Students Roster</h3>
        <span style="font-size: 0.84rem; color: var(--c-text-muted);">Assigned by the Coordinator for 12-week academic supervision</span>
    </div>

    <?php if (!empty($students)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name &amp; ID</th>
                        <th>Host Company</th>
                        <th>Position</th>
                        <th>Placement Dates</th>
                        <th>Status</th>
                        <th>Supervision Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $st): ?>
                        <tr>
                            <td>
                                <strong><?= e($st['student_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($st['student_id']) ?> &bull; <?= e($st['programme']) ?></small>
                            </td>
                            <td>
                                <strong><?= e($st['company_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($st['company_address']) ?></small>
                            </td>
                            <td><?= e($st['position']) ?></td>
                            <td>
                                <?= date('d M Y', strtotime($st['start_date'])) ?> &ndash;<br>
                                <?= date('d M Y', strtotime($st['end_date'])) ?>
                            </td>
                            <td><?= renderStatusBadge($st['status']) ?></td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                    <a href="<?= url('logbooks/supervisor', ['placement_id' => $st['id']]) ?>" class="btn btn-secondary btn-sm" title="Grade Weekly Logbooks">Logbooks (10%)</a>
                                    <a href="<?= url('monthly-reports/supervisor', ['placement_id' => $st['id']]) ?>" class="btn btn-secondary btn-sm" title="View Monthly Progress">Progress</a>
                                    <a href="<?= url('final-reports/marking', ['placement_id' => $st['id']]) ?>" class="btn btn-secondary btn-sm" title="Mark Final Report">Report (20%)</a>
                                    <a href="<?= url('evaluations/visiting-rubric', ['placement_id' => $st['id']]) ?>" class="btn btn-primary btn-sm" title="Visiting Evaluation">Visit (30%)</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2.5rem 1rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">🎓</div>
            <h4>No Students Assigned Yet</h4>
            <p style="color: var(--c-text-muted); font-size: 0.9rem;">
                The Coordinator will allocate students to you once placement offers are confirmed.
            </p>
        </div>
    <?php endif; ?>
</div>
