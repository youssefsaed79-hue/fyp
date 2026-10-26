<div class="page-header" style="margin-bottom: 24px;">
    <a href="<?= url('evaluations/host') ?>" class="text-muted" style="text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        ← Back to Host Evaluations
    </a>
    <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;"><?= e($title) ?></h1>
    <p class="text-muted" style="margin: 0;">
        Evaluate <strong><?= e($placement['student_name']) ?></strong> (<?= e($placement['student_id'] ?? '') ?>) at <strong><?= e($placement['company_name']) ?></strong>.
    </p>
</div>

<div class="card" style="margin-bottom: 24px; background: var(--c-bluegray); border-left: 4px solid var(--c-forest);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h4 style="color: var(--c-forest-dark); margin: 0 0 4px;">Assessment Weight: 40% of Overall Grade (FR-EVAL-01)</h4>
            <div style="font-size: 0.9rem; color: #444;">
                Rate each of the 9 criteria on a scale of <strong>1 (Unsatisfactory)</strong> to <strong>5 (Excellent)</strong>. The system automatically converts the total score (out of 45) into a percentage.
            </div>
        </div>
        <?php if (!empty($existing)): ?>
            <div class="badge badge-success" style="font-size: 0.95rem; padding: 6px 12px;">
                Current Score: <?= number_format((float)$existing['total_pct'], 1) ?>%
            </div>
        <?php endif; ?>
    </div>
</div>

<form action="<?= url('evaluations/host-submit') ?>" method="POST">
    <?= \App\Core\Csrf::field() ?>
    <input type="hidden" name="placement_id" value="<?= (int)$placement['id'] ?>">
    <input type="hidden" name="type" value="<?= e($type) ?>">
    <?php if ($periodNo): ?>
        <input type="hidden" name="period_no" value="<?= (int)$periodNo ?>">
    <?php endif; ?>

    <div class="card" style="margin-bottom: 24px;">
        <h3 style="color: var(--c-forest-dark); margin: 0 0 16px;">Digital Evaluation Rubric (9 Criteria)</h3>

        <?php
        $criteriaList = [
            'attendance' => [
                'title' => '1. Attendance',
                'desc' => 'Regularity of reporting for work on designated working days and adherence to company calendar.',
            ],
            'punctuality' => [
                'title' => '2. Punctuality',
                'desc' => 'Promptness in arrival, meeting deadlines, and attending required technical sessions.',
            ],
            'communication' => [
                'title' => '3. Communication Skills',
                'desc' => 'Clarity in spoken and written communications, effective technical reporting, and responsiveness.',
            ],
            'technical_knowledge' => [
                'title' => '4. Technical Knowledge & Application',
                'desc' => 'Understanding of tools, programming concepts, architecture, and application to real-world tasks.',
            ],
            'work_quality' => [
                'title' => '5. Work Quality & Thoroughness',
                'desc' => 'Accuracy, neatness, code quality, testing rigor, and attention to detail in assigned deliverables.',
            ],
            'problem_solving' => [
                'title' => '6. Problem-Solving & Analytical Ability',
                'desc' => 'Initiative in troubleshooting bugs, proposing viable solutions, and critical thinking under ambiguity.',
            ],
            'teamwork' => [
                'title' => '7. Teamwork & Interpersonal Relations',
                'desc' => 'Constructive collaboration with colleagues, adaptability in team settings, and respect for others.',
            ],
            'professional_behaviour' => [
                'title' => '8. Professional Behaviour & Work Ethics',
                'desc' => 'Integrity, confidentiality of company IP, adherence to workplace policies, and maturity.',
            ],
            'training_objectives' => [
                'title' => '9. Progress Towards Training Objectives',
                'desc' => 'Overall learning trajectory, achievement of internship milestones, and receptiveness to feedback.',
            ],
        ];

        foreach ($criteriaList as $key => $c):
            $val = (int)($scores[$key] ?? 5);
        ?>
            <div style="padding: 16px; border-bottom: 1px solid rgba(0,0,0,0.06); background: #fafafa; border-radius: 8px; margin-bottom: 14px;">
                <div style="margin-bottom: 10px;">
                    <strong style="color: var(--c-forest-dark); font-size: 1.05rem;"><?= e($c['title']) ?></strong>
                    <div style="font-size: 0.88rem; color: #666; margin-top: 2px;"><?= e($c['desc']) ?></div>
                </div>

                <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                    <?php for ($s = 1; $s <= 5; $s++):
                        $labels = [1 => '1 - Unsatisfactory', 2 => '2 - Marginal', 3 => '3 - Satisfactory', 4 => '4 - Very Good', 5 => '5 - Excellent'];
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
            <label class="form-label" style="font-weight: 600;">Supervisor Comments &amp; Performance Feedback</label>
            <textarea name="comments" rows="4" class="form-control" placeholder="Provide constructive feedback on student performance, technical strengths, and areas for professional growth..."><?= e($existing['comments'] ?? '') ?></textarea>
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="<?= url('evaluations/host') ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                <?= !empty($existing) ? 'Save Updated Evaluation' : 'Submit Evaluation (40%)' ?>
            </button>
        </div>
    </div>
</form>
