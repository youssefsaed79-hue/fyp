<div class="page-header" style="margin-bottom: 24px;">
    <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">My Final Internship Grade</h1>
    <p class="text-muted" style="margin: 0;">
        Official industrial training assessment breakdown following the UNITEN College of Computing &amp; Informatics evaluation scheme (Table 3.8).
    </p>
</div>

<?php
$scores = $gradeData['scores'] ?? [];
$weights = $gradeData['weights'] ?? [];
$weighted = $gradeData['weighted_components'] ?? [];
$isLocked = !empty($gradeData['is_locked']);
?>

<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Left Column: Component Breakdown Table -->
    <div class="card">
        <h3 style="color: var(--c-forest-dark); margin: 0 0 16px;">Assessment Components Breakdown</h3>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Assessment Component</th>
                        <th>Assessed By</th>
                        <th>Weight</th>
                        <th>Raw Score</th>
                        <th>Weighted Contribution</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Host Supervisor Evaluation</strong></td>
                        <td>Company Host Supervisor</td>
                        <td><?= (int)$weights['host'] ?>%</td>
                        <td>
                            <?php if ($scores['host'] !== null): ?>
                                <strong><?= number_format($scores['host'], 1) ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $weighted['host'] !== null ? number_format($weighted['host'], 2) . '%' : '—' ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Visiting Lecturer Assessment</strong></td>
                        <td>University Supervisor</td>
                        <td><?= (int)$weights['visiting'] ?>%</td>
                        <td>
                            <?php if ($scores['visiting'] !== null): ?>
                                <strong><?= number_format($scores['visiting'], 1) ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $weighted['visiting'] !== null ? number_format($weighted['visiting'], 2) . '%' : '—' ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Industrial Training Final Report</strong></td>
                        <td>University Supervisor</td>
                        <td><?= (int)$weights['report'] ?>%</td>
                        <td>
                            <?php if ($scores['report'] !== null): ?>
                                <strong><?= number_format($scores['report'], 1) ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $weighted['report'] !== null ? number_format($weighted['report'], 2) . '%' : '—' ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Weekly Training Logbook</strong></td>
                        <td>University Supervisor (with host review)</td>
                        <td><?= (int)$weights['logbook'] ?>%</td>
                        <td>
                            <?php if ($scores['logbook'] !== null): ?>
                                <strong><?= number_format($scores['logbook'], 1) ?>%</strong>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $weighted['logbook'] !== null ? number_format($weighted['logbook'], 2) . '%' : '—' ?>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: var(--c-bluegray); font-weight: bold;">
                        <td colspan="2">TOTAL CONSOLIDATED EVALUATION</td>
                        <td>100%</td>
                        <td>—</td>
                        <td style="color: var(--c-forest-dark); font-size: 1.1rem;">
                            <?= ($gradeData['total'] !== null) ? number_format($gradeData['total'], 2) . '%' : 'Incomplete' ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Right Column: Final Result Card -->
    <div>
        <div class="card text-center" style="background: #fff; border-top: 4px solid var(--c-forest); padding: 24px;">
            <div style="font-size: 0.9rem; font-weight: 600; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                Final Assessment Outcome
            </div>

            <?php if ($isLocked): ?>
                <div style="font-size: 3.5rem; font-weight: 800; color: var(--c-forest); line-height: 1; margin-bottom: 4px;">
                    <?= number_format($gradeData['total'], 1) ?><span style="font-size: 1.8rem;">%</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: var(--c-forest-dark); margin-bottom: 12px;">
                    Grade <?= e($gradeData['letter_grade'] ?? '—') ?>
                </div>

                <div class="badge badge-success" style="font-size: 0.9rem; padding: 6px 12px; margin-bottom: 16px;">
                    🔒 Official Grade Locked
                </div>

                <p style="font-size: 0.85rem; color: #555; margin: 0; line-height: 1.5;">
                    Verified and locked by University Coordinator on <strong><?= date('d M Y', strtotime($gradeData['locked_record']['locked_at'])) ?></strong>.
                </p>
            <?php elseif ($gradeData['all_ready']): ?>
                <div style="font-size: 3.5rem; font-weight: 800; color: var(--c-forest); line-height: 1; margin-bottom: 4px;">
                    <?= number_format($gradeData['total'], 1) ?><span style="font-size: 1.8rem;">%</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: var(--c-forest-dark); margin-bottom: 12px;">
                    Grade <?= e($gradeData['letter_grade'] ?? '—') ?>
                </div>

                <div class="badge badge-warning" style="font-size: 0.9rem; padding: 6px 12px; margin-bottom: 16px;">
                    Provisional (Awaiting Coordinator Lock)
                </div>

                <p style="font-size: 0.85rem; color: #555; margin: 0; line-height: 1.5;">
                    All four components are completed. Official locking is pending University Coordinator verification.
                </p>
            <?php else: ?>
                <div style="font-size: 3rem; margin: 16px 0; color: #aaa;">⏳</div>
                <h4 style="color: var(--c-forest-dark); margin-bottom: 8px;">In Progress</h4>
                <p style="font-size: 0.9rem; color: #555; line-height: 1.5; margin: 0;">
                    Your final grade will be automatically calculated when your Host Supervisor, Visiting Lecturer, Final Report, and Logbooks have all been assessed.
                </p>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 16px; background: var(--c-bluegray); padding: 16px;">
            <div style="font-size: 0.85rem; color: #444; line-height: 1.6;">
                <strong>Placement Details:</strong><br>
                Company: <?= e($placement['company_name']) ?><br>
                Supervisor: <?= e($placement['supervisor_name'] ?? 'Assigned Lecturer') ?><br>
                Status: <?= e(ucwords(str_replace('_', ' ', $placement['status']))) ?>
            </div>
        </div>
    </div>
</div>
