<div class="page-header" style="margin-bottom: 24px;">
    <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Industrial Training Final Report</h1>
    <p class="text-muted" style="margin: 0;">
        Upload your comprehensive internship report once before the deadline (FR-FRS-01). The report constitutes <strong>20%</strong> of your overall assessment grade (FR-EVAL-02).
    </p>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Left Column: Guidelines & Declaration -->
    <div>
        <div class="card" style="margin-bottom: 20px;">
            <h3 style="color: var(--c-forest-dark); margin: 0 0 12px;">Submission Guidelines</h3>
            <ul style="padding-left: 20px; line-height: 1.6; color: #444; margin: 0 0 16px;">
                <li>The final report must adhere strictly to the <strong>UNITEN CCI Industrial Training Report Guideline</strong>.</li>
                <li>Single submission: upload your complete report once. Ensure all executive summaries, technical chapters, and appendices are compiled.</li>
                <li>Accepted formats: <strong>PDF (.pdf)</strong> or <strong>Word Document (.docx)</strong>.</li>
                <li>Maximum file size: <strong>10 MB</strong>.</li>
                <li>Upon upload, your report will be automatically routed to your University Supervisor for academic marking and made available to your Host Supervisor.</li>
            </ul>
            <div class="alert alert-info" style="font-size: 0.9rem; margin: 0;">
                ℹ️ Official report templates and user manuals can be downloaded at any time from the <a href="<?= url('resources') ?>" style="color: var(--c-forest); font-weight: 600;">Internship Kit</a>.
            </div>
        </div>

        <div class="card" style="background: var(--c-bluegray); border-left: 4px solid var(--c-forest);">
            <h4 style="color: var(--c-forest-dark); margin: 0 0 8px;">Supervisor Information</h4>
            <p style="margin: 0 0 4px; font-size: 0.95rem;">
                <strong>Host Company:</strong> <?= e($placement['company_name']) ?>
            </p>
            <p style="margin: 0; font-size: 0.95rem;">
                <strong>University Supervisor:</strong> <?= e($placement['supervisor_name'] ?? 'Pending Assignment') ?>
            </p>
        </div>
    </div>

    <!-- Right Column: Submission Form & Status -->
    <div>
        <div class="card">
            <h3 style="color: var(--c-forest-dark); margin: 0 0 16px;">Report Status</h3>

            <?php if (!empty($report)): ?>
                <div style="background: #fdfdfd; border: 1px solid rgba(0,0,0,0.08); border-radius: 8px; padding: 18px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span class="badge badge-success" style="font-size: 0.9rem; padding: 4px 10px;">✓ Final Report Uploaded</span>
                        <?php if ($report['mark'] !== null): ?>
                            <span class="badge badge-success" style="font-size: 0.9rem; padding: 4px 10px;">
                                Graded: <?= number_format((float)$report['mark'], 1) ?>% (Weight: 20%)
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning" style="font-size: 0.9rem; padding: 4px 10px;">Awaiting Grading</span>
                        <?php endif; ?>
                    </div>

                    <div style="font-size: 0.9rem; color: #444; line-height: 1.6;">
                        <p style="margin: 0 0 4px;"><strong>Original File:</strong> <?= e($report['original_name'] ?: 'Final_Report.pdf') ?></p>
                        <p style="margin: 0 0 4px;"><strong>Submitted On:</strong> <?= date('d M Y, h:i A', strtotime($report['submitted_at'])) ?></p>
                        <p style="margin: 0;"><strong>Originality Declaration:</strong> <span style="color: var(--c-forest); font-weight: 600;">✓ Confirmed &amp; Signed</span></p>
                    </div>

                    <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid #eee;">
                        <a href="<?= url('final-report/download?id=' . $report['id']) ?>" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 8px;">
                            📥 Download Submitted Report
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" style="margin-bottom: 20px;">
                    ⚠️ You have not yet submitted your Industrial Training Final Report.
                </div>
            <?php endif; ?>

            <form action="<?= url('final-report/upload') ?>" method="POST" enctype="multipart/form-data">
                <?= \App\Core\Csrf::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600;">Upload Final Report File <span class="text-danger">*</span></label>
                    <input type="file" name="final_report_file" class="form-control" accept=".pdf,.docx" required>
                    <small class="text-muted">PDF or Word document (.pdf, .docx), maximum 10 MB.</small>
                </div>

                <!-- Academic Integrity & Originality Declaration Checkbox (Mandatory FR-FRS-01 / Scope Boundary 3) -->
                <div style="background: var(--c-bluegray); padding: 14px; border-radius: 6px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin: 0; font-size: 0.9rem; line-height: 1.4;">
                        <input type="checkbox" name="originality_confirmed" value="1" required style="margin-top: 3px; accent-color: var(--c-forest);">
                        <span>
                            <strong>Student Originality Declaration:</strong>
                            I hereby declare that this final report is entirely my own original work conducted during my 12-week industrial training, and that all sources and contributions have been accurately cited and acknowledged in accordance with UNITEN academic integrity standards.
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <?= !empty($report) ? 'Replace & Resubmit Final Report' : 'Submit Final Report' ?>
                </button>
            </form>
        </div>
    </div>
</div>
