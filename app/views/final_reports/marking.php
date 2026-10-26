<div class="page-header" style="margin-bottom: 24px;">
    <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Industrial Training Final Report Assessment</h1>
    <p class="text-muted" style="margin: 0;">
        Review student final reports and assign academic marks (FR-EVAL-02). Final Report carries <strong>20% weight</strong> in the final grade (Table 3.8).
    </p>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Company</th>
                    <?php if ($isCoordinator): ?><th>Supervisor</th><?php endif; ?>
                    <th>Report Status</th>
                    <th>Submitted On</th>
                    <th>Mark (0 – 100)</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($placements)): ?>
                    <tr>
                        <td colspan="<?= $isCoordinator ? 8 : 7 ?>" class="text-center py-6 text-muted">
                            No students currently requiring final report evaluation.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($placements as $p): ?>
                        <tr>
                            <td>
                                <strong><?= e($p['student_name']) ?></strong><br>
                                <small class="text-muted"><?= e($p['position']) ?></small>
                            </td>
                            <td><?= e($p['student_id'] ?? '—') ?></td>
                            <td><?= e($p['company_name']) ?></td>
                            <?php if ($isCoordinator): ?>
                                <td><?= e($p['supervisor_name'] ?? 'Unassigned') ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if (!empty($p['report_id'])): ?>
                                    <span class="badge badge-success">✓ Uploaded</span>
                                    <br><small class="text-muted"><?= e($p['original_name'] ?: 'Report') ?></small>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending Upload</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= !empty($p['submitted_at']) ? date('d M Y, h:i A', strtotime($p['submitted_at'])) : '—' ?>
                            </td>
                            <td>
                                <?php if ($p['report_mark'] !== null): ?>
                                    <strong style="color: var(--c-forest); font-size: 1.05rem;">
                                        <?= number_format((float)$p['report_mark'], 1) ?>%
                                    </strong>
                                    <br><small class="text-muted">(20% component = <?= number_format(((float)$p['report_mark'] * 0.20), 2) ?>)</small>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Not Graded</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <?php if (!empty($p['report_id'])): ?>
                                    <a href="<?= url('final-report/download?id=' . $p['report_id']) ?>" class="btn btn-secondary btn-sm" style="margin-right: 6px;">
                                        📥 Download
                                    </a>

                                    <button type="button" class="btn btn-primary btn-sm" onclick="openGradingModal(<?= (int)$p['id'] ?>, '<?= e(addslashes($p['student_name'])) ?>', <?= $p['report_mark'] !== null ? (float)$p['report_mark'] : 'null' ?>)">
                                        <?= $p['report_mark'] !== null ? 'Edit Mark' : 'Grade Report' ?>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.85rem;">Awaiting Report</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Report Grading Modal -->
<div id="gradingModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 480px; margin: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="color: var(--c-forest-dark); margin: 0;" id="modalStudentTitle">Grade Final Report</h3>
            <button type="button" onclick="closeGradingModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #888;">&times;</button>
        </div>

        <form action="<?= url('final-report/mark') ?>" method="POST">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="placement_id" id="modalPlacementId" value="">

            <p style="font-size: 0.9rem; color: #555; margin-bottom: 16px;">
                Enter the academic mark based on the UNITEN CCI Industrial Training Report Marking Scheme (0.00 – 100.00).
            </p>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-weight: 600;">Report Mark (%) <span class="text-danger">*</span></label>
                <input type="number" step="0.5" min="0" max="100" name="mark" id="modalMarkInput" class="form-control" required placeholder="e.g. 85.0">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeGradingModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Academic Mark</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGradingModal(placementId, studentName, currentMark) {
    document.getElementById('modalPlacementId').value = placementId;
    document.getElementById('modalStudentTitle').innerText = 'Grade Report: ' + studentName;
    document.getElementById('modalMarkInput').value = (currentMark !== null) ? currentMark : '';
    document.getElementById('gradingModal').style.display = 'flex';
}
function closeGradingModal() {
    document.getElementById('gradingModal').style.display = 'none';
}
</script>
