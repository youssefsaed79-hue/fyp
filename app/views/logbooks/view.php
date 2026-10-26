<div class="page-header">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem;">
            <h1>Week <?= $logbook['week_no'] ?> Logbook</h1>
            <?= renderStatusBadge($logbook['status']) ?>
            <?php if ($logbook['grade'] !== null): ?>
                <span class="badge badge-success" style="font-size: 0.85rem;">Grade: <?= number_format((float)$logbook['grade'], 1) ?>%</span>
            <?php endif; ?>
        </div>
        <p>
            <strong><?= e($logbook['student_name']) ?></strong> (<?= e($logbook['student_id']) ?>) &bull;
            <?= e($logbook['company_name']) ?> &bull;
            Period: <?= date('d M Y', strtotime($logbook['week_start'])) ?> to <?= date('d M Y', strtotime($logbook['week_end'])) ?>
        </p>
    </div>
    <div>
        <a href="<?= url('logbooks') ?>" class="btn btn-secondary">&larr; Back to Logbooks</a>
    </div>
</div>

<div class="form-row">
    <!-- LEFT: LOGBOOK CONTENT -->
    <div class="form-group" style="flex: 1.5;">
        <div class="card">
            <div class="card-title">
                <h3>Weekly Training Activities &amp; Hours</h3>
                <span style="font-weight: 600; color: var(--c-forest);"><?= (float)$logbook['hours'] ?> Hours Worked</span>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <h4 style="font-size: 0.95rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Tasks &amp; Duties Performed</h4>
                <div style="font-size: 0.92rem; line-height: 1.6; white-space: pre-line; color: var(--c-text-primary); background: var(--c-offwhite); padding: 0.85rem; border-radius: var(--radius-sm);">
                    <?= e($logbook['tasks'] ?: 'No tasks entered.') ?>
                </div>
            </div>

            <?php if (!empty($logbook['skills'])): ?>
                <div style="margin-bottom: 1.25rem;">
                    <h4 style="font-size: 0.95rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Skills &amp; Technologies Applied</h4>
                    <div style="font-size: 0.92rem; color: var(--c-text-primary); background: var(--c-offwhite); padding: 0.75rem; border-radius: var(--radius-sm);">
                        <?= e($logbook['skills']) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-row" style="margin-bottom: 1.25rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <h4 style="font-size: 0.95rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Challenges Encountered</h4>
                    <div style="font-size: 0.88rem; line-height: 1.5; color: var(--c-text-primary); background: var(--c-offwhite); padding: 0.75rem; border-radius: var(--radius-sm);">
                        <?= e($logbook['challenges'] ?: 'None documented.') ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <h4 style="font-size: 0.95rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Solutions Applied</h4>
                    <div style="font-size: 0.88rem; line-height: 1.5; color: var(--c-text-primary); background: var(--c-offwhite); padding: 0.75rem; border-radius: var(--radius-sm);">
                        <?= e($logbook['solutions'] ?: 'None documented.') ?>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <h4 style="font-size: 0.95rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Personal Learning Reflection</h4>
                <div style="font-size: 0.92rem; line-height: 1.6; white-space: pre-line; color: var(--c-text-primary); background: var(--c-offwhite); padding: 0.85rem; border-radius: var(--radius-sm);">
                    <?= e($logbook['reflection'] ?: 'No reflection entered.') ?>
                </div>
            </div>

            <!-- Attachments -->
            <?php if (!empty($attachments)): ?>
                <div style="border-top: 1px solid var(--c-border); padding-top: 1rem; margin-top: 1.25rem;">
                    <h4 style="font-size: 0.95rem; color: var(--c-forest-dark); margin-bottom: 0.5rem;">Attached Evidence Files</h4>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <?php foreach ($attachments as $att): ?>
                            <a href="<?= url('documents/download', ['id' => $att['id']]) ?>" class="btn btn-secondary btn-sm" target="_blank">
                                📎 <?= e($att['original_name']) ?> (<?= round($att['size'] / 1024, 1) ?> KB)
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- REVISION HISTORY (FR-LOG-04) -->
        <?php if (!empty($revisions)): ?>
            <div class="card">
                <div class="card-title">
                    <h3>Revision History &amp; Snapshots (FR-LOG-04)</h3>
                    <span class="badge badge-neutral"><?= count($revisions) ?> Submissions</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php $revNo = count($revisions); foreach ($revisions as $rev): ?>
                        <?php $snap = json_decode($rev['snapshot_json'], true) ?: []; ?>
                        <div class="card-bluegray" style="padding: 0.75rem 1rem; margin-bottom: 0; font-size: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; font-weight: 700; color: var(--c-forest-dark); margin-bottom: 0.25rem;">
                                <span>Submission Revision #<?= $revNo-- ?></span>
                                <span style="font-weight: normal; color: var(--c-text-muted);"><?= date('d M Y, H:i:s', strtotime($rev['created_at'])) ?></span>
                            </div>
                            <div style="color: var(--c-text-muted); font-size: 0.8rem;">
                                <?= mb_strimwidth(e($snap['tasks'] ?? ''), 0, 150, '...') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT: DUAL REVIEWS & SUPERVISOR ACTIONS -->
    <div class="form-group" style="flex: 1;">
        <!-- DUAL-PARTY SUPERVISION FEEDBACK -->
        <div class="card">
            <div class="card-title">
                <h3>Supervision Feedback</h3>
                <span class="badge badge-info">Dual-Party Review</span>
            </div>

            <?php if (!empty($reviews)): ?>
                <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.5rem;">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="card-bluegray" style="margin-bottom: 0; padding: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <strong><?= e($rev['reviewer_name']) ?></strong>
                                <span class="badge <?= $rev['action'] === 'approve' ? 'badge-success' : ($rev['action'] === 'return' ? 'badge-danger' : 'badge-neutral') ?>">
                                    <?= strtoupper($rev['action']) ?>
                                </span>
                            </div>
                            <div style="font-size: 0.86rem; color: var(--c-text-primary); line-height: 1.45;">
                                <?= e($rev['comment']) ?>
                            </div>
                            <div style="font-size: 0.72rem; color: var(--c-text-muted); margin-top: 0.35rem;">
                                <?= date('d M Y, H:i', strtotime($rev['created_at'])) ?> &bull; <?= e(str_replace('_', ' ', $rev['reviewer_role'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--c-text-muted); font-size: 0.88rem; margin-bottom: 1.5rem;">
                    No feedback recorded yet.
                </p>
            <?php endif; ?>

            <!-- REVIEW ACTION FORM (FOR COMPANY & UNIVERSITY SUPERVISORS) -->
            <?php if (in_array($currentUser['role'], ['company', 'university_supervisor', 'coordinator'], true)): ?>
                <div style="border-top: 1px solid var(--c-border); padding-top: 1.25rem;">
                    <h4 style="color: var(--c-forest-dark); margin-bottom: 0.75rem;">Record Review Feedback</h4>

                    <form action="<?= url('logbooks/action') ?>" method="POST" data-validate>
                        <?= \App\Core\Csrf::field() ?>
                        <input type="hidden" name="logbook_id" value="<?= $logbook['id'] ?>">

                        <div class="form-group">
                            <label for="action" class="form-label">Review Action</label>
                            <select name="action" id="action" class="form-select" onchange="toggleGradeInput(this.value)">
                                <option value="approve">✓ Approve &amp; Sign Off</option>
                                <option value="return">⚠️ Return for Correction (Resubmission)</option>
                                <option value="comment">💬 Comment Only</option>
                            </select>
                        </div>

                        <!-- University Supervisor Grade Input (10% component, FR-LOG-03) -->
                        <?php if ($currentUser['role'] === 'university_supervisor' || $currentUser['role'] === 'coordinator'): ?>
                            <div class="form-group" id="grade_input_box">
                                <label for="grade" class="form-label">Academic Logbook Mark (0 &ndash; 100%) <span style="color: var(--c-forest); font-weight: 700;">[10% Weight]</span></label>
                                <input type="number" step="0.5" min="0" max="100" id="grade" name="grade" class="form-control" placeholder="e.g. 92.5" value="<?= e($logbook['grade'] !== null ? (string)$logbook['grade'] : '') ?>">
                                <div class="form-help">Enter academic score based on technical depth, reflection, and completeness.</div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="comment" class="form-label">Supervisor Comment &amp; Guidance</label>
                            <textarea id="comment" name="comment" class="form-control" rows="3" required placeholder="Write your workplace feedback, recommendations, or correction instructions..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            Submit Supervisor Review &rarr;
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleGradeInput(action) {
    const box = document.getElementById('grade_input_box');
    if (box) {
        if (action === 'approve') {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }
}
</script>
