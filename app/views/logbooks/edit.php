<div class="page-header">
    <div>
        <h1>Week <?= $logbook['week_no'] ?> Training Logbook</h1>
        <p>Period: <?= date('d M Y', strtotime($logbook['week_start'])) ?> to <?= date('d M Y', strtotime($logbook['week_end'])) ?> &bull; Single-Submission Entry (FR-LOG-02)</p>
    </div>
    <div>
        <a href="<?= url('logbooks') ?>" class="btn btn-secondary">&larr; Back to Schedule</a>
    </div>
</div>

<?php if ($logbook['status'] === 'returned'): ?>
    <div class="alert alert-danger" style="border-left: 4px solid var(--c-status-danger);">
        <div>
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem;">⚠️ Returned for Correction (FR-LOG-04)</div>
            <p style="margin: 0; font-size: 0.88rem;">
                This logbook was returned by your supervisor. Please review the comments, update the required sections, and click <strong>"Submit Corrected Logbook"</strong>. A snapshot will be saved to the revision history.
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 850px;">
    <form action="<?= url('logbooks/save') ?>" method="POST" enctype="multipart/form-data" data-validate>
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="logbook_id" value="<?= $logbook['id'] ?>">

        <div class="form-group">
            <label for="tasks" class="form-label">1. Key Tasks &amp; Activities Performed <span style="color: var(--c-status-danger);">*</span></label>
            <textarea id="tasks" name="tasks" class="form-control" rows="4" required placeholder="Detail the specific workplace tasks, software development modules, or operational duties performed this week..."><?= e($logbook['tasks'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="skills" class="form-label">2. Technical Skills &amp; Tools Developed</label>
            <textarea id="skills" name="skills" class="form-control" rows="2" placeholder="e.g. PHP 8, MySQL indexing, Git versioning, Docker deployment, Linux server commands..."><?= e($logbook['skills'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="challenges" class="form-label">3. Challenges &amp; Obstacles Encountered</label>
                <textarea id="challenges" name="challenges" class="form-control" rows="3" placeholder="Technical bottlenecks, configuration errors, integration hurdles..."><?= e($logbook['challenges'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="solutions" class="form-label">4. Solutions Applied &amp; Workarounds</label>
                <textarea id="solutions" name="solutions" class="form-control" rows="3" placeholder="How did you resolve the issues? Debugging steps, documentation consulted..."><?= e($logbook['solutions'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="reflection" class="form-label">5. Personal &amp; Professional Learning Reflection <span style="color: var(--c-status-danger);">*</span></label>
            <textarea id="reflection" name="reflection" class="form-control" rows="3" required placeholder="Reflect on your workplace learning, team collaboration, professional habits, and communication progress this week..."><?= e($logbook['reflection'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex: 1;">
                <label for="hours" class="form-label">Total Working Hours Completed</label>
                <input type="number" step="0.5" min="0" max="80" id="hours" name="hours" class="form-control" value="<?= e($logbook['hours'] > 0 ? (string)$logbook['hours'] : '40.0') ?>" required>
            </div>

            <div class="form-group" style="flex: 1.5;">
                <label for="attachment" class="form-label">Upload Evidence / Screenshot / Diagram (Optional)</label>
                <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.docx">
                <div class="form-help">Max 10 MB &bull; Architecture diagrams, output screenshots, evidence</div>
            </div>
        </div>

        <!-- Attached files list if any -->
        <?php if (!empty($attachments)): ?>
            <div style="margin: 1rem 0; padding: 0.75rem; background: var(--c-bluegray); border-radius: var(--radius-sm);">
                <div style="font-weight: 600; font-size: 0.84rem; margin-bottom: 0.35rem;">Previously Attached Files:</div>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.82rem;">
                    <?php foreach ($attachments as $att): ?>
                        <li><?= e($att['original_name']) ?> (<?= round($att['size'] / 1024, 1) ?> KB)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <button type="submit" name="submit_entry" value="1" class="btn btn-primary" style="padding: 0.75rem 1.75rem; font-size: 0.95rem;">
                ✓ <?= $logbook['status'] === 'returned' ? 'Submit Corrected Logbook' : 'Submit Weekly Logbook' ?> &rarr;
            </button>
            <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                💾 Save as Draft
            </button>
            <a href="<?= url('logbooks') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
