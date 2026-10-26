<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Visiting Lecturer Assessments</h1>
        <p class="text-muted" style="margin: 0;">
            Conduct academic supervision visits and record visiting evaluations (FR-EVAL-02). Constitutes <strong>30%</strong> of the final grade.
        </p>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Company &amp; Position</th>
                    <?php if ($isCoordinator): ?><th>Supervisor</th><?php endif; ?>
                    <th>Visit Assessment (30%)</th>
                    <th>Assessment Status (All 4)</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($placements)): ?>
                    <tr>
                        <td colspan="<?= $isCoordinator ? 7 : 6 ?>" class="text-center py-6 text-muted">
                            No students currently assigned for academic supervision.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($placements as $p):
                        $vEval = $p['visiting_eval'];
                        $calc = $p['grade_calc'];
                    ?>
                        <tr>
                            <td>
                                <strong><?= e($p['student_name']) ?></strong>
                            </td>
                            <td><?= e($p['student_id'] ?? '—') ?></td>
                            <td>
                                <strong><?= e($p['company_name']) ?></strong><br>
                                <small class="text-muted"><?= e($p['position']) ?></small>
                            </td>
                            <?php if ($isCoordinator): ?>
                                <td><?= e($p['supervisor_name'] ?? 'Unassigned') ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if ($vEval && $vEval['total_pct'] !== null): ?>
                                    <span class="badge badge-success" style="font-size: 0.9rem;">
                                        ✓ Completed: <?= number_format((float)$vEval['total_pct'], 1) ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending Visit</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($calc['all_ready']): ?>
                                    <span class="badge badge-success">All 4 Components Ready</span>
                                    <br><small class="text-muted">Total: <?= number_format($calc['total'], 1) ?>% (<?= $calc['letter_grade'] ?>)</small>
                                <?php else: ?>
                                    <span class="badge badge-neutral">In Progress</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="<?= url("evaluations/visiting-rubric?placement_id={$p['id']}") ?>" class="btn btn-primary btn-sm">
                                    <?= ($vEval && $vEval['total_pct'] !== null) ? 'Edit Assessment' : 'Conduct Assessment' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
