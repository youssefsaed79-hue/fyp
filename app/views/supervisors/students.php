<div class="page-header">
    <div>
        <h1>Assigned Students Roster</h1>
        <p>UNITEN CCI student interns under your academic supervision and evaluation (FR-SVA-01)</p>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Supervisory Roster (<?= count($students) ?> Students)</h3>
    </div>

    <?php if (!empty($students)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name &amp; ID</th>
                        <th>Host Company &amp; Contact</th>
                        <th>Position</th>
                        <th>Training Dates</th>
                        <th>Verified Attendance</th>
                        <th>Logbooks Approved</th>
                        <th>Status</th>
                        <th>Supervision Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $st): ?>
                        <tr>
                            <td>
                                <strong><?= e($st['student_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($st['student_id']) ?> &bull; <?= e($st['programme']) ?> (CGPA: <?= e($st['cgpa'] ?? 'N/A') ?>)</small>
                            </td>
                            <td>
                                <strong><?= e($st['company_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($st['representative_name']) ?> (<?= e($st['representative_email']) ?>)</small>
                            </td>
                            <td><?= e($st['position']) ?></td>
                            <td style="font-size: 0.84rem;">
                                <?= date('d M Y', strtotime($st['start_date'])) ?> &ndash;<br>
                                <?= date('d M Y', strtotime($st['end_date'])) ?>
                            </td>
                            <td>
                                <strong><?= (int)$st['verified_att'] ?></strong> days
                            </td>
                            <td>
                                <strong><?= (int)$st['approved_logs'] ?></strong> / 12
                                <?php if ((int)$st['pending_grades'] > 0): ?>
                                    <span class="badge badge-warning" style="margin-left: 0.2rem;"><?= (int)$st['pending_grades'] ?> to grade</span>
                                <?php endif; ?>
                            </td>
                            <td><?= renderStatusBadge($st['status']) ?></td>
                            <td>
                                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                    <a href="<?= url('logbooks/supervisor', ['placement_id' => $st['id']]) ?>" class="btn btn-secondary btn-sm" title="Grade Weekly Logbooks">Logbooks (10%)</a>
                                    <a href="<?= url('monthly-reports/supervisor', ['placement_id' => $st['id']]) ?>" class="btn btn-secondary btn-sm" title="Review Progress">Reports</a>
                                    <a href="<?= url('evaluations/visiting-rubric', ['placement_id' => $st['id']]) ?>" class="btn btn-primary btn-sm" title="Visiting Evaluation">Visit (30%)</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--c-text-muted);">
            No students currently assigned to your supervision.
        </div>
    <?php endif; ?>
</div>
