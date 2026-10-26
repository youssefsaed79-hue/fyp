<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Consolidated Assessment &amp; Final Grade Lock</h1>
        <p class="text-muted" style="margin: 0;">
            Review all 4 assessment components and lock finalized grades (FR-EVAL-03). Enforces Table 3.8 and Business Rule 8.
        </p>
    </div>
</div>

<div class="card" style="margin-bottom: 24px; background: var(--c-bluegray); border-left: 4px solid var(--c-forest);">
    <h4 style="color: var(--c-forest-dark); margin: 0 0 6px;">UNITEN Assessment Weighting (Table 3.8 — Configurable via Settings):</h4>
    <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 0.95rem;">
        <div>🏢 <strong>Host Supervisor:</strong> 40%</div>
        <div>👨‍🏫 <strong>Visiting Lecturer:</strong> 30%</div>
        <div>📄 <strong>Final Report:</strong> 20%</div>
        <div>📖 <strong>Logbooks:</strong> 10%</div>
    </div>
    <div style="font-size: 0.85rem; color: #555; margin-top: 8px;">
        * Business Rule 8: The final grade is calculated and can be locked <strong>only when all four assessment components are completed</strong>.
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name &amp; ID</th>
                    <th>Company &amp; Supervisor</th>
                    <th>Host (40%)</th>
                    <th>Visiting (30%)</th>
                    <th>Report (20%)</th>
                    <th>Logbook (10%)</th>
                    <th>Total / Grade</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($placements)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-6 text-muted">
                            No active or completed internship placements found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($placements as $p):
                        $calc = $gradeSummaries[$p['id']] ?? null;
                        $scores = $calc['scores'] ?? [];
                        $allReady = !empty($calc['all_ready']);
                        $isLocked = !empty($calc['is_locked']);
                    ?>
                        <tr>
                            <td>
                                <strong><?= e($p['student_name']) ?></strong><br>
                                <small class="text-muted"><?= e($p['student_id'] ?? '—') ?></small>
                            </td>
                            <td>
                                <?= e($p['company_name']) ?><br>
                                <small class="text-muted">Sup: <?= e($p['supervisor_name'] ?? 'Unassigned') ?></small>
                            </td>
                            <td>
                                <?php if ($scores['host'] !== null): ?>
                                    <span style="font-weight: 600; color: var(--c-forest);"><?= number_format($scores['host'], 1) ?>%</span>
                                    <br><small class="text-muted">(<?= number_format($calc['weighted_components']['host'], 1) ?>)</small>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($scores['visiting'] !== null): ?>
                                    <span style="font-weight: 600; color: var(--c-forest);"><?= number_format($scores['visiting'], 1) ?>%</span>
                                    <br><small class="text-muted">(<?= number_format($calc['weighted_components']['visiting'], 1) ?>)</small>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($scores['report'] !== null): ?>
                                    <span style="font-weight: 600; color: var(--c-forest);"><?= number_format($scores['report'], 1) ?>%</span>
                                    <br><small class="text-muted">(<?= number_format($calc['weighted_components']['report'], 1) ?>)</small>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($scores['logbook'] !== null): ?>
                                    <span style="font-weight: 600; color: var(--c-forest);"><?= number_format($scores['logbook'], 1) ?>%</span>
                                    <br><small class="text-muted">(<?= number_format($calc['weighted_components']['logbook'], 1) ?>)</small>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($allReady || $isLocked): ?>
                                    <strong style="color: var(--c-forest-dark); font-size: 1.1rem;">
                                        <?= number_format($calc['total'], 1) ?>%
                                    </strong>
                                    <span class="badge badge-success" style="margin-left: 4px;"><?= $calc['letter_grade'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.85rem;">Incomplete</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isLocked): ?>
                                    <span class="badge badge-success">🔒 Locked &amp; Official</span>
                                <?php elseif ($allReady): ?>
                                    <span class="badge badge-warning">Ready to Lock</span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Components Missing</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <?php if ($isLocked): ?>
                                    <span class="text-muted" style="font-size: 0.85rem;">Locked on <?= date('d M Y', strtotime($calc['locked_record']['locked_at'])) ?></span>
                                <?php elseif ($allReady): ?>
                                    <form action="<?= url('evaluations/lock-grade') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Lock this official final grade? This will finalize the result for the student and mark the internship as Completed.');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="placement_id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            🔒 Lock Final Grade
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.5;">
                                        Awaiting Components
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
