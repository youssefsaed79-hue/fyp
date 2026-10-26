<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Host Supervisor Evaluations</h1>
        <p class="text-muted" style="margin: 0;">
            Assess student interns using the UNITEN CCI digital evaluation rubric (FR-EVAL-01). Constitutes <strong>40%</strong> of the student's overall grade.
        </p>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Intern Name</th>
                    <th>Student ID</th>
                    <th>Position &amp; Period</th>
                    <th>Monthly Evaluations</th>
                    <th>Final Evaluation</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($placements)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-muted">
                            No active interns found under your supervision.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($placements as $p):
                        $monthlyEvals = array_filter($p['evaluations'], fn($e) => $e['type'] === 'host_monthly');
                        $finalEval = current(array_filter($p['evaluations'], fn($e) => $e['type'] === 'host_final'));
                    ?>
                        <tr>
                            <td>
                                <strong><?= e($p['student_name']) ?></strong>
                            </td>
                            <td><?= e($p['student_id'] ?? '—') ?></td>
                            <td>
                                <strong><?= e($p['position']) ?></strong><br>
                                <small class="text-muted"><?= date('d M', strtotime($p['start_date'])) ?> – <?= date('d M Y', strtotime($p['end_date'])) ?></small>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                    <?php for ($m = 1; $m <= 3; $m++):
                                        $evalM = current(array_filter($monthlyEvals, fn($e) => (int)$e['period_no'] === $m));
                                    ?>
                                        <?php if ($evalM): ?>
                                            <a href="<?= url("evaluations/host-rubric?placement_id={$p['id']}&type=host_monthly&period_no={$m}") ?>" class="badge badge-success" style="text-decoration: none;" title="Score: <?= $evalM['total_pct'] ?>%">
                                                M<?= $m ?>: <?= number_format((float)$evalM['total_pct'], 0) ?>%
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= url("evaluations/host-rubric?placement_id={$p['id']}&type=host_monthly&period_no={$m}") ?>" class="badge badge-warning" style="text-decoration: none;">
                                                M<?= $m ?>: Pending
                                            </a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($finalEval): ?>
                                    <span class="badge badge-success" style="font-size: 0.9rem;">
                                        ✓ Final: <?= number_format((float)$finalEval['total_pct'], 1) ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Not Done</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="<?= url("evaluations/host-rubric?placement_id={$p['id']}&type=host_final") ?>" class="btn btn-primary btn-sm">
                                    <?= $finalEval ? 'Edit Final Rubric' : 'Complete Final Rubric' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
