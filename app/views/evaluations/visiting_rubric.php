<div class="page-header" style="margin-bottom: 24px;">
    <a href="<?= url('evaluations/visiting') ?>" class="text-muted" style="text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        ← Back to Visiting Assessments
    </a>
    <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Visiting Lecturer Assessment (30%)</h1>
    <p class="text-muted" style="margin: 0;">
        Academic supervision evaluation for <strong><?= e($placement['student_name']) ?></strong> (<?= e($placement['student_id'] ?? '') ?>) placed at <strong><?= e($placement['company_name']) ?></strong> (FR-EVAL-02).
    </p>
</div>

<div class="card" style="margin-bottom: 24px; background: var(--c-bluegray); border-left: 4px solid var(--c-forest);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h4 style="color: var(--c-forest-dark); margin: 0 0 4px;">Visiting Lecturer Weight: 30% of Overall Grade</h4>
            <div style="font-size: 0.9rem; color: #444;">
                Supervision visit conducted between Weeks 8 and 12. Evaluate student technical presentation, host feedback, and academic alignment.
            </div>
        </div>
        <?php if (!empty($existing)): ?>
            <div class="badge badge-success" style="font-size: 0.95rem; padding: 6px 12px;">
                Current Score: <?= number_format((float)$existing['total_pct'], 1) ?>%
            </div>
        <?php endif; ?>
    </div>
</div>

<form action="<?= url('evaluations/visiting-submit') ?>" method="POST">
    <?= \App\Core\Csrf::field() ?>
    <input type="hidden" name="placement_id" value="<?= (int)$placement['id'] ?>">

    <div class="card" style="margin-bottom: 24px;">
        <h3 style="color: var(--c-forest-dark); margin: 0 0 16px;">Supervision Visit Details</h3>

        <div class="form-group" style="margin-bottom: 20px; max-width: 300px;">
            <label class="form-label" style="font-weight: 600;">Visit / Consultation Date <span class="text-danger">*</span></label>
            <input type="date" name="visit_date" class="form-control" required value="<?= e($scores['visit_date'] ?? date('Y-m-d')) ?>">
        </div>

        <h3 style="color: var(--c-forest-dark); margin: 24px 0 16px;">Evaluation Rubric Criteria (1 to 5)</h3>

        <?php
        $criteria = [
            'technical_scope' => [
                'title' => '1. Technical Competence & Scope of Work',
                'desc' => 'Depth and complexity of software engineering tasks performed, suitability to degree level curriculum.',
            ],
            'presentation_defense' => [
                'title' => '2. Student Presentation & Technical Defense',
                'desc' => 'Ability of the student to explain architecture, technologies used, and answer technical questions.',
            ],
            'employer_consultation' => [
                'title' => '3. Host Employer Consultation & Workplace Feedback',
                'desc' => 'Feedback received from the company supervisor regarding professionalism, reliability, and work quality.',
            ],
            'academic_alignment' => [
                'title' => '4. Academic Alignment & Logbook Verification',
                'desc' => 'Quality and completeness of weekly reflections, documentation rigor, and attainment of learning outcomes.',
            ],
        ];

        foreach ($criteria as $key => $c):
            $val = (int)($scores[$key] ?? 5);
        ?>
            <div style="padding: 16px; border-bottom: 1px solid rgba(0,0,0,0.06); background: #fafafa; border-radius: 8px; margin-bottom: 14px;">
                <div style="margin-bottom: 10px;">
                    <strong style="color: var(--c-forest-dark); font-size: 1.05rem;"><?= e($c['title']) ?></strong>
                    <div style="font-size: 0.88rem; color: #666; margin-top: 2px;"><?= e($c['desc']) ?></div>
                </div>

                <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                    <?php for ($s = 1; $s <= 5; $s++):
                        $labels = [1 => '1 - Poor', 2 => '2 - Fair', 3 => '3 - Good', 4 => '4 - Very Good', 5 => '5 - Excellent'];
                    ?>
                        <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; background: #fff; padding: 6px 12px; border-radius: 6px; border: 1px solid #ddd; font-size: 0.9rem;">
                            <input type="radio" name="score_<?= $key ?>" value="<?= $s ?>" <?= $val === $s ? 'checked' : '' ?> required style="accent-color: var(--c-forest);">
                            <span><?= e($labels[$s]) ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="form-group" style="margin-top: 20px;">
            <label class="form-label" style="font-weight: 600;">Visiting Lecturer Observation &amp; Recommendations</label>
            <textarea name="comments" rows="4" class="form-control" placeholder="Provide qualitative feedback, visit observations, notes on industry engagement, and advice to the student..."><?= e($existing['comments'] ?? '') ?></textarea>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="<?= url('evaluations/visiting') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                <?= !empty($existing) ? 'Save Updated Assessment' : 'Submit Visiting Assessment (30%)' ?>
            </button>
        </div>
    </div>
</form>
