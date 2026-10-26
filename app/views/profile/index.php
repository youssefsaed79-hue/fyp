<div class="page-header">
    <div>
        <h1>My Profile &amp; Documents</h1>
        <p>Maintain your student academic profile, emergency contact, and internship documents (FR-SPD-01, FR-SPD-02)</p>
    </div>
</div>

<!-- ELIGIBILITY GATE BANNER (FR-SPD-03) -->
<?php if (!$hasCv || empty($student['verified'])): ?>
    <div class="alert alert-warning" style="border-left: 4px solid var(--c-status-warning);">
        <div>
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem;">Eligibility Gate Active (Action Required)</div>
            <p style="margin: 0; font-size: 0.88rem;">
                Under university regulation (FR-SPD-03), you are blocked from applying to internship vacancies until your profile is complete.
                <strong>Please upload your Curriculum Vitae (CV) below to unlock applications.</strong>
            </p>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success" style="border-left: 4px solid var(--c-status-success);">
        <div>
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem;">✓ Eligibility Gate Passed</div>
            <p style="margin: 0; font-size: 0.88rem;">
                Your student profile is verified and your CV is on file. You are eligible to search and apply for internship vacancies.
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="form-row">
    <!-- 1. PERSONAL & ACADEMIC DETAILS -->
    <div class="form-group" style="flex: 1.3;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>Student Profile Details</h3>
                <?php if (!empty($student['verified'])): ?>
                    <span class="badge badge-success">Verified Student</span>
                <?php else: ?>
                    <span class="badge badge-warning">Unverified</span>
                <?php endif; ?>
            </div>

            <form action="<?= url('profile/update') ?>" method="POST" data-validate>
                <?= \App\Core\Csrf::field() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-control" value="<?= e($student['student_id'] ?? '') ?>" readonly style="background-color: var(--c-bluegray);">
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Academic Programme</label>
                        <input type="text" class="form-control" value="<?= e($student['programme'] ?? '') ?>" readonly style="background-color: var(--c-bluegray);">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Intake</label>
                        <input type="text" class="form-control" value="<?= e($student['intake'] ?? '') ?>" readonly style="background-color: var(--c-bluegray);">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cgpa" class="form-label">Cumulative GPA (CGPA)</label>
                        <input type="number" step="0.01" min="0.00" max="4.00" id="cgpa" name="cgpa" class="form-control" value="<?= e($student['cgpa'] ?? '') ?>" placeholder="e.g. 3.75">
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact" class="form-label">Emergency Contact</label>
                        <input type="text" id="emergency_contact" name="emergency_contact" class="form-control" value="<?= e($student['emergency_contact'] ?? '') ?>" placeholder="e.g. Next of Kin (+6012-3456789)">
                    </div>
                </div>

                <div class="form-group">
                    <label for="skills" class="form-label">Technical Skills</label>
                    <textarea id="skills" name="skills" class="form-control" rows="2" placeholder="e.g. PHP, MySQL, Python, JavaScript, Docker, Git"><?= e($student['skills'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="interests" class="form-label">Career Interests &amp; Training Goals</label>
                    <textarea id="interests" name="interests" class="form-control" rows="2" placeholder="e.g. Cloud engineering, distributed web systems, software quality assurance"><?= e($student['interests'] ?? '') ?></textarea>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Save Profile Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. DOCUMENT UPLOADS & MANAGEMENT -->
    <div class="form-group" style="flex: 1.1;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>Internship Documents</h3>
                <span class="badge badge-info">PDF &bull; DOCX &bull; Max 10MB</span>
            </div>

            <!-- Upload Form -->
            <form action="<?= url('documents/upload') ?>" method="POST" enctype="multipart/form-data" data-validate style="margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--c-border);">
                <?= \App\Core\Csrf::field() ?>

                <div class="form-group">
                    <label for="doc_type" class="form-label">Document Type</label>
                    <select name="type" id="doc_type" class="form-select" required>
                        <option value="cv" <?= !$hasCv ? 'selected' : '' ?>>Curriculum Vitae (CV) &mdash; Required for Gate</option>
                        <option value="verification_letter">Industrial Training Verification Letter</option>
                        <option value="transcript">Academic Examination Transcript</option>
                        <option value="other">Other Supporting Document</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="document_file" class="form-label">Select Document File</label>
                    <input type="file" id="document_file" name="document_file" class="form-control" accept=".pdf,.docx,.doc,.png,.jpg" required>
                    <div class="form-help">Private storage &bull; Only authorized supervisors can view.</div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm" style="margin-top: 0.5rem;">
                    Upload Document
                </button>
            </form>

            <!-- Uploaded Documents List -->
            <h4 style="font-size: 0.95rem; margin-bottom: 0.75rem; color: var(--c-forest-dark);">Stored Documents</h4>
            <?php if (!empty($documents)): ?>
                <div class="table-responsive">
                    <table class="table" style="font-size: 0.84rem;">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $doc['type'] === 'cv' ? 'badge-success' : 'badge-neutral' ?>">
                                            <?= strtoupper(str_replace('_', ' ', $doc['type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= e($doc['original_name']) ?></strong><br>
                                        <small style="color: var(--c-text-muted);"><?= date('d M Y', strtotime($doc['created_at'])) ?></small>
                                    </td>
                                    <td><?= round($doc['size'] / 1024, 1) ?> KB</td>
                                    <td>
                                        <div style="display: flex; gap: 0.3rem;">
                                            <a href="<?= url('documents/download', ['id' => $doc['id']]) ?>" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.45rem;" title="Download File">📥</a>
                                            <form action="<?= url('documents/delete') ?>" method="POST" style="display: inline;" data-confirm="Delete this document?">
                                                <?= \App\Core\Csrf::field() ?>
                                                <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.45rem;" title="Delete File">✕</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem 1rem; color: var(--c-text-muted); font-size: 0.88rem;">
                    No documents uploaded yet. Please upload your CV above.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
