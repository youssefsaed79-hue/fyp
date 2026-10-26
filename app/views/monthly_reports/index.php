<div class="page-header" style="margin-bottom: 24px;">
    <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Monthly Progress Reports</h1>
    <p class="text-muted" style="margin: 0;">
        Submit a monthly progress summary once per 4-week period (FR-MPR-01). Both your Company Supervisor and University Supervisor will be notified automatically.
    </p>
</div>

<div class="alert alert-info" style="margin-bottom: 24px;">
    <strong>ℹ️ Monitoring &amp; Feedback Notice:</strong> As specified in UNITEN CCI Industrial Training guidelines, monthly reports are used for supervision, monitoring, and formative feedback and carry <em>no separate numerical mark</em>.
</div>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
    <?php
    $periods = [
        1 => ['title' => 'Month 1 Progress Summary', 'weeks' => 'Weeks 1 – 4', 'desc' => 'Initial induction, role onboarding, baseline technical tasks.'],
        2 => ['title' => 'Month 2 Progress Summary', 'weeks' => 'Weeks 5 – 8', 'desc' => 'Core development / project execution, challenges encountered and overcome.'],
        3 => ['title' => 'Month 3 Progress Summary', 'weeks' => 'Weeks 9 – 12', 'desc' => 'Project wrap-up, handover, testing, reflection on learning outcomes.'],
    ];

    foreach ($periods as $periodNo => $info):
        $report = $reportsByPeriod[$periodNo] ?? null;
        $isSubmitted = !empty($report);
    ?>
    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div>
                    <h3 style="color: var(--c-forest-dark); margin: 0 0 4px;"><?= e($info['title']) ?></h3>
                    <span class="badge badge-neutral"><?= e($info['weeks']) ?></span>
                </div>
                <?php if ($isSubmitted): ?>
                    <span class="badge badge-success">✓ Submitted</span>
                <?php else: ?>
                    <span class="badge badge-warning">Pending</span>
                <?php endif; ?>
            </div>

            <p style="font-size: 0.9rem; color: #555; margin-bottom: 16px;">
                <?= e($info['desc']) ?>
            </p>

            <?php if ($isSubmitted): ?>
                <div style="background: var(--c-bluegray); padding: 12px 14px; border-radius: 6px; margin-bottom: 16px;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--c-forest-dark); margin-bottom: 4px;">
                        SUBMITTED ON: <?= date('d M Y, h:i A', strtotime($report['submitted_at'])) ?>
                    </div>
                    <p style="margin: 0; font-size: 0.9rem; white-space: pre-wrap; color: #222;"><?= e($report['summary']) ?></p>
                    
                    <?php if (!empty($report['stored_name'])): ?>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(0,0,0,0.08);">
                            <a href="<?= url('monthly-reports/download?id=' . $report['id']) ?>" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                📎 <?= e($report['original_name'] ?: 'Download Attachment') ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <details style="margin-top: 10px;">
                <summary style="cursor: pointer; font-weight: 600; color: var(--c-forest); padding: 6px 0;">
                    <?= $isSubmitted ? '✎ Update Month ' . $periodNo . ' Summary' : '＋ Write & Submit Month ' . $periodNo . ' Summary' ?>
                </summary>
                <form action="<?= url('monthly-reports/submit') ?>" method="POST" enctype="multipart/form-data" style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed #ccc;">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="period_no" value="<?= $periodNo ?>">

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 600;">Monthly Progress Summary <span class="text-danger">*</span></label>
                        <textarea name="summary" class="form-control" rows="4" required placeholder="Summarise your key achievements, deliverables, and challenges for <?= strtolower($info['weeks']) ?>..."><?= e($report['summary'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" style="font-weight: 600;">Optional Supporting Evidence / Report (PDF / DOCX)</label>
                        <input type="file" name="report_file" class="form-control" accept=".pdf,.docx">
                        <small class="text-muted">Max file size: 10 MB. PDF or Word document.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                        <?= $isSubmitted ? 'Save Updated Summary' : 'Submit Month ' . $periodNo . ' Report' ?>
                    </button>
                </form>
            </details>
        </div>
    </div>
    <?php endforeach; ?>
</div>
