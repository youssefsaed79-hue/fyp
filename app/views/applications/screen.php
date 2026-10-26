<div class="page-header">
    <div>
        <h1>Candidate Screening Pipeline</h1>
        <p>Review student applicants, schedule interviews, and issue placement offers (FR-APM-02)</p>
    </div>
</div>

<!-- VACANCY FILTER -->
<div class="card" style="padding: 1rem; margin-bottom: 1.25rem;">
    <form method="GET" action="<?= url('applications/screen') ?>" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <label style="font-size: 0.88rem; font-weight: 600;">Filter by Vacancy:</label>
        <select name="vacancy_id" class="form-select" style="max-width: 380px;" onchange="this.form.submit()">
            <option value="">All Vacancies</option>
            <?php foreach ($vacancies as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $selectedVacancyId === (int)$v['id'] ? 'selected' : '' ?>>
                    <?= e($v['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($selectedVacancyId > 0): ?>
            <a href="<?= url('applications/screen') ?>" class="btn btn-secondary btn-sm">Show All</a>
        <?php endif; ?>
    </form>
</div>

<!-- CANDIDATE LIST -->
<div class="card">
    <div class="card-title">
        <h3>Applicants (<?= count($candidates) ?> Total)</h3>
    </div>

    <?php if (!empty($candidates)): ?>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <?php foreach ($candidates as $cand): ?>
                <div class="card" style="margin-bottom: 0; padding: 1.35rem; border-left: 4px solid var(--c-forest);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <?= renderStatusBadge($cand['status']) ?>
                                <?php if (!empty($cand['offer_issued'])): ?>
                                    <span class="badge badge-success">Offer Issued</span>
                                <?php endif; ?>
                                <span style="font-size: 0.8rem; color: var(--c-text-muted);">Applied: <?= date('d M Y, H:i', strtotime($cand['created_at'])) ?></span>
                            </div>

                            <h3 style="font-size: 1.2rem; margin-bottom: 0.25rem;"><?= e($cand['student_name']) ?></h3>
                            <div style="font-size: 0.88rem; color: var(--c-text-muted); margin-bottom: 0.5rem;">
                                <strong>ID:</strong> <?= e($cand['student_id']) ?> &bull;
                                <strong>Programme:</strong> <?= e($cand['programme']) ?> &bull;
                                <strong>CGPA:</strong> <?= e($cand['cgpa'] ?? 'N/A') ?>
                            </div>

                            <div style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                                <strong>Applied For:</strong> <span style="color: var(--c-forest); font-weight: 600;"><?= e($cand['vacancy_title']) ?></span>
                            </div>

                            <?php if (!empty($cand['skills'])): ?>
                                <div style="font-size: 0.84rem; color: var(--c-text-muted); margin-bottom: 0.5rem;">
                                    <strong>Skills:</strong> <?= e($cand['skills']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($cand['cover_letter'])): ?>
                                <div class="card-bluegray" style="padding: 0.65rem 0.85rem; margin-top: 0.5rem; font-size: 0.84rem;">
                                    <strong>Cover Note:</strong> <?= e($cand['cover_letter']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.5rem; min-width: 140px;">
                            <?php if (!empty($cand['cv_doc_id'])): ?>
                                <a href="<?= url('documents/download', ['id' => $cand['cv_doc_id']]) ?>" class="btn btn-secondary btn-sm" target="_blank">
                                    📄 Download Student CV
                                </a>
                            <?php else: ?>
                                <span class="badge badge-warning">No CV Uploaded</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- SCREENING ACTIONS & OFFER FORM -->
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--c-border); display: flex; gap: 1rem; flex-wrap: wrap;">
                        <!-- Pipeline Status Form -->
                        <form action="<?= url('applications/update-status') ?>" method="POST" style="flex: 1.2; display: flex; gap: 0.5rem; align-items: flex-end; flex-wrap: wrap;">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="application_id" value="<?= $cand['id'] ?>">

                            <div style="flex: 1; min-width: 140px;">
                                <label class="form-label" style="font-size: 0.8rem;">Update Pipeline Stage</label>
                                <select name="status" class="form-select" onchange="toggleInterviewField(this, <?= $cand['id'] ?>)">
                                    <option value="submitted" <?= $cand['status'] === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                                    <option value="under_review" <?= $cand['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                                    <option value="shortlisted" <?= $cand['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                                    <option value="interview" <?= $cand['status'] === 'interview' ? 'selected' : '' ?>>Schedule Interview</option>
                                    <option value="rejected" <?= $cand['status'] === 'rejected' ? 'selected' : '' ?>>Reject</option>
                                </select>
                            </div>

                            <div id="interview_box_<?= $cand['id'] ?>" style="display: <?= $cand['status'] === 'interview' ? 'block' : 'none' ?>; flex: 2; min-width: 200px;">
                                <label class="form-label" style="font-size: 0.8rem;">Interview Details / Zoom Link</label>
                                <input type="text" name="interview_details" class="form-control" placeholder="e.g. 24 Oct 10:00 AM on Zoom (Link...)" value="<?= e($cand['interview_details'] ?? '') ?>">
                            </div>

                            <div>
                                <button type="submit" class="btn btn-secondary btn-sm">Update Stage</button>
                            </div>
                        </form>

                        <!-- Issue Digital Offer Form -->
                        <?php if (empty($cand['offer_issued']) && $cand['status'] !== 'rejected'): ?>
                            <div style="border-left: 1px solid var(--c-border); padding-left: 1rem;">
                                <form action="<?= url('applications/offer') ?>" method="POST" data-confirm="Issue formal placement offer to <?= e($cand['student_name']) ?>?">
                                    <?= \App\Core\Csrf::field() ?>
                                    <input type="hidden" name="application_id" value="<?= $cand['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        🎉 Issue Placement Offer
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2.5rem 1rem; color: var(--c-text-muted);">
            No candidates have applied for this listing yet.
        </div>
    <?php endif; ?>
</div>

<script>
function toggleInterviewField(select, id) {
    const box = document.getElementById('interview_box_' + id);
    if (select.value === 'interview') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
}
</script>
