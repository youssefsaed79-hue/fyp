<div class="page-header">
    <div>
        <a href="<?= url('vacancies') ?>" class="btn btn-secondary btn-sm" style="margin-bottom: 0.5rem;">&larr; Back to Vacancies</a>
        <h1><?= e($vacancy['title']) ?></h1>
        <p>
            <strong><?= e($vacancy['company_name']) ?></strong> &bull; <?= e($vacancy['location']) ?> &bull; <?= e($vacancy['company_industry']) ?>
        </p>
    </div>
    <div>
        <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
            <?= strtoupper(e($vacancy['working_arrangement'])) ?> &bull; 12 WEEKS
        </span>
    </div>
</div>

<div class="form-row">
    <!-- LEFT: VACANCY DETAILS -->
    <div class="form-group" style="flex: 1.5;">
        <div class="card">
            <div class="card-title">
                <h3>Job Description &amp; Responsibilities</h3>
            </div>
            <div style="font-size: 0.95rem; line-height: 1.6; white-space: pre-line; color: var(--c-text-primary); margin-bottom: 1.5rem;">
                <?= e($vacancy['duties']) ?>
            </div>

            <div class="card-title">
                <h3>Candidate Requirements &amp; Skills</h3>
            </div>
            <div style="font-size: 0.95rem; line-height: 1.6; white-space: pre-line; color: var(--c-text-primary);">
                <?= e($vacancy['requirements']) ?>
            </div>
        </div>
    </div>

    <!-- RIGHT: APPLICATION BOX & COMPANY INFO -->
    <div class="form-group" style="flex: 1;">
        <!-- APPLICATION ACTION CARD -->
        <div class="card">
            <div class="card-title">
                <h3>Apply for Internship</h3>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="font-size: 0.82rem; color: var(--c-text-muted);">Monthly Allowance</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: var(--c-forest-dark);">
                    RM <?= number_format((float)$vacancy['allowance'], 2) ?>
                </div>
            </div>

            <div style="font-size: 0.84rem; color: var(--c-text-muted); margin-bottom: 1.25rem;">
                <div><strong>Available Slots:</strong> <?= (int)$vacancy['slots'] ?></div>
                <div><strong>Application Deadline:</strong> <?= date('d M Y', strtotime($vacancy['deadline'])) ?></div>
            </div>

            <?php if ($user && $user['role'] === 'student'): ?>
                <?php if ($hasApplied): ?>
                    <div class="alert alert-info" style="margin-bottom: 0;">
                        <div>
                            <strong>Application Status:</strong>
                            <div style="margin-top: 0.35rem;"><?= renderStatusBadge($application['status']) ?></div>
                            <div style="font-size: 0.8rem; margin-top: 0.5rem; color: var(--c-text-muted);">
                                Applied on <?= date('d M Y, H:i', strtotime($application['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php elseif (empty($user['profile_complete']) || empty($user['verified'])): ?>
                    <!-- ELIGIBILITY GATE ACTIVE (FR-SPD-03) -->
                    <div class="alert alert-warning" style="margin-bottom: 1rem; border-left: 4px solid var(--c-status-warning);">
                        <div>
                            <strong>Application Blocked (Eligibility Gate)</strong>
                            <p style="font-size: 0.82rem; margin: 0.3rem 0 0.6rem;">
                                You must upload your Curriculum Vitae (CV) in your profile before applying to vacancies.
                            </p>
                            <a href="<?= url('profile') ?>" class="btn btn-secondary btn-sm" style="display: inline-block;">
                                Complete Profile &amp; Upload CV &rarr;
                            </a>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-block" disabled style="opacity: 0.6; cursor: not-allowed;">
                        Apply Now (Blocked)
                    </button>
                <?php else: ?>
                    <!-- ELIGIBLE: APPLY FORM -->
                    <form action="<?= url('applications/apply') ?>" method="POST" data-confirm="Submit application for this internship position?">
                        <?= \App\Core\Csrf::field() ?>
                        <input type="hidden" name="vacancy_id" value="<?= $vacancy['id'] ?>">

                        <div class="form-group">
                            <label for="cover_letter" class="form-label">Cover Letter / Note to Employer (Optional)</label>
                            <textarea id="cover_letter" name="cover_letter" class="form-control" rows="4" placeholder="Highlight relevant skills, projects, and availability..."></textarea>
                        </div>

                        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-bottom: 1rem;">
                            ✓ Your verified profile and stored CV will be transmitted to the hiring supervisor automatically.
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">
                            Submit Application &rarr;
                        </button>
                    </form>
                <?php endif; ?>
            <?php elseif (!$user): ?>
                <a href="<?= url('login') ?>" class="btn btn-primary btn-block">Sign In to Apply</a>
            <?php else: ?>
                <div style="font-size: 0.85rem; color: var(--c-text-muted);">
                    Logged in as <strong><?= e($user['role']) ?></strong>. Only verified student accounts may submit applications.
                </div>
            <?php endif; ?>
        </div>

        <!-- COMPANY PROFILE CARD -->
        <div class="card">
            <div class="card-title">
                <h3>About Host Company</h3>
            </div>
            <div style="font-size: 0.9rem; line-height: 1.5; color: var(--c-text-primary);">
                <strong><?= e($vacancy['company_name']) ?></strong><br>
                <span style="color: var(--c-text-muted);"><?= e($vacancy['company_industry']) ?></span>
                <p style="margin-top: 0.75rem; font-size: 0.85rem; color: var(--c-text-muted);">
                    <?= e($vacancy['company_address']) ?>
                </p>
                <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--c-bluegray); font-size: 0.84rem;">
                    Representative: <strong><?= e($vacancy['representative_name']) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>
