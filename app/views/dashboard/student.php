<?php
$step = 1;
if (!empty($user['profile_complete'])) {
    $step = 2;
}
if (!empty($placement)) {
    if ($placement['status'] === 'university_approved' || $placement['status'] === 'internship_active') {
        $step = 3;
    } elseif ($placement['status'] === 'completed') {
        $step = 4;
    }
}
?>

<div class="page-header">
    <div>
        <h1>Welcome, <?= e($user['name']) ?></h1>
        <p>UNITEN Student Portal &bull; <?= e($user['student_id'] ?? 'Student') ?> &bull; <?= e($user['programme'] ?? 'Bachelor of Computer Science') ?></p>
    </div>
    <?php if (!$placement): ?>
        <a href="<?= url('vacancies') ?>" class="btn btn-primary">Find Internship Placements &rarr;</a>
    <?php endif; ?>
</div>

<!-- 1. INTERNSHIP LIFECYCLE STEPPER -->
<div class="card">
    <div class="card-title">
        <h3>Internship Lifecycle Progress</h3>
        <?php if ($placement): ?>
            <?= renderStatusBadge($placement['status']) ?>
        <?php else: ?>
            <span class="badge badge-warning">Placement Needed</span>
        <?php endif; ?>
    </div>
    <div class="stepper">
        <div class="step-item <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">
            <div class="step-number"><?= $step > 1 ? '✓' : '1' ?></div>
            <div class="step-title">Profile &amp; CV Upload</div>
        </div>
        <div class="step-item <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">
            <div class="step-number"><?= $step > 2 ? '✓' : '2' ?></div>
            <div class="step-title">Placement Approval</div>
        </div>
        <div class="step-item <?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>">
            <div class="step-number"><?= $step > 3 ? '✓' : '3' ?></div>
            <div class="step-title">12-Week Training</div>
        </div>
        <div class="step-item <?= $step >= 4 ? 'completed active' : '' ?>">
            <div class="step-number"><?= $step === 4 ? '✓' : '4' ?></div>
            <div class="step-title">Final Assessment &amp; Grade</div>
        </div>
    </div>
</div>

<!-- 2. STAT TILES -->
<div class="stats-grid">
    <div class="stat-tile">
        <div class="stat-label">Verified Attendance</div>
        <div class="stat-value" style="<?= !empty($attendanceStats['is_alert']) ? 'color: var(--c-status-danger);' : '' ?>">
            <?= $attendanceStats['percentage'] ?>%
        </div>
        <div style="font-size: 0.8rem; margin-top: 0.35rem; color: var(--c-text-muted);">
            <?= $attendanceStats['verified'] ?> / <?= $attendanceStats['expected'] ?? 0 ?> expected work days
            <?php if (!empty($attendanceStats['is_alert'])): ?>
                <span class="badge badge-danger" style="margin-left: 0.3rem;">Below 80%</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Weekly Logbooks</div>
        <div class="stat-value"><?= $logbookStats['approved'] ?> / 12</div>
        <div style="font-size: 0.8rem; margin-top: 0.35rem; color: var(--c-text-muted);">
            <?= $logbookStats['submitted'] ?> submitted for review
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Placement Status</div>
        <div style="margin-top: 0.6rem;">
            <?= renderStatusBadge($placement['status'] ?? 'pending') ?>
        </div>
        <div style="font-size: 0.8rem; margin-top: 0.6rem; color: var(--c-text-muted);">
            <?= $placement ? e($placement['company_name']) : 'No active placement' ?>
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Final Grade (100%)</div>
        <div class="stat-value" style="color: var(--c-forest);">
            <?= $finalGrade ? e($finalGrade['total']) . '%' : 'Pending' ?>
        </div>
        <div style="font-size: 0.8rem; margin-top: 0.35rem; color: var(--c-text-muted);">
            <?= $finalGrade ? 'Locked by Coordinator' : 'Dual evaluation pending' ?>
        </div>
    </div>
</div>

<!-- 3. THE FOUR INTERNSHIP RESOURCES DASHBOARD CARDS (FR-IRM-01, Section 17) -->
<div style="margin-bottom: 0.75rem;">
    <h3 style="color: var(--c-forest-dark);">Internship Resources &amp; Preparation</h3>
    <p style="color: var(--c-text-muted); font-size: 0.88rem;">
        Essential guidelines, downloadable forms, briefing slides and timeline roadmap accessible at all times.
    </p>
</div>

<div class="resource-cards-grid">
    <a href="<?= url('resources', ['tab' => 'kit']) ?>" class="resource-card">
        <div>
            <div class="resource-card-icon">📁</div>
            <div class="resource-card-title">Internship Kit</div>
            <div class="resource-card-desc">Download official forms (F1, F2), templates, logbook format and evaluation rubrics.</div>
        </div>
        <div style="font-size: 0.8rem; font-weight: 700; margin-top: 0.75rem;">View Documents &rarr;</div>
    </a>

    <a href="<?= url('resources', ['tab' => 'journey']) ?>" class="resource-card">
        <div>
            <div class="resource-card-icon">🗺️</div>
            <div class="resource-card-title">Internship Journey</div>
            <div class="resource-card-desc">4-stage workflow across semesters: briefing, placement search, training and completion.</div>
        </div>
        <div style="font-size: 0.8rem; font-weight: 700; margin-top: 0.75rem;">Explore Roadmap &rarr;</div>
    </a>

    <a href="<?= url('resources', ['tab' => 'guidelines']) ?>" class="resource-card">
        <div>
            <div class="resource-card-icon">📜</div>
            <div class="resource-card-title">Internship Guidelines</div>
            <div class="resource-card-desc">Academic eligibility requirements (60 credits Degree, 45 Diploma), procedures and responsibilities.</div>
        </div>
        <div style="font-size: 0.8rem; font-weight: 700; margin-top: 0.75rem;">Read Guidelines &rarr;</div>
    </a>

    <a href="<?= url('resources', ['tab' => 'announcements']) ?>" class="resource-card">
        <div>
            <div class="resource-card-icon">📢</div>
            <div class="resource-card-title">Announcements</div>
            <div class="resource-card-desc">Latest cohort updates, upcoming briefings, placement registration notices and deadlines.</div>
        </div>
        <div style="font-size: 0.8rem; font-weight: 700; margin-top: 0.75rem;">Check Notices &rarr;</div>
    </a>
</div>

<!-- 4. ACTIVE PLACEMENT INFORMATION & RECENT ANNOUNCEMENTS -->
<div class="form-row">
    <div class="form-group" style="flex: 1.3;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>My Placement Details</h3>
                <?php if ($placement): ?>
                    <a href="<?= url('placements/my') ?>" class="btn btn-secondary btn-sm">Full Details</a>
                <?php endif; ?>
            </div>

            <?php if ($placement): ?>
                <table class="table" style="font-size: 0.88rem;">
                    <tr>
                        <th style="width: 35%;">Company</th>
                        <td><strong><?= e($placement['company_name']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Position</th>
                        <td><?= e($placement['position']) ?></td>
                    </tr>
                    <tr>
                        <th>Training Dates</th>
                        <td><?= e($placement['start_date']) ?> to <?= e($placement['end_date']) ?> (12 Weeks)</td>
                    </tr>
                    <tr>
                        <th>Host Supervisor</th>
                        <td><?= e($placement['supervisor_contact_name']) ?> (<?= e($placement['supervisor_contact_email']) ?>)</td>
                    </tr>
                    <tr>
                        <th>Visiting Lecturer</th>
                        <td><?= e($placement['uni_supervisor_name'] ?? 'To be assigned by Coordinator') ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem 1rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">🏢</div>
                    <h4>No Approved Placement Yet</h4>
                    <p style="color: var(--c-text-muted); font-size: 0.88rem; margin: 0.5rem 0 1.25rem;">
                        You have not secured or registered a placement yet. Make sure your profile and CV are uploaded, then browse listings or register an external offer.
                    </p>
                    <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                        <a href="<?= url('vacancies') ?>" class="btn btn-primary btn-sm">Browse Listings</a>
                        <a href="<?= url('placements/self-source') ?>" class="btn btn-secondary btn-sm">Register Self-Sourced Placement</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group" style="flex: 1;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>Recent Announcements</h3>
                <a href="<?= url('resources', ['tab' => 'announcements']) ?>" class="btn btn-secondary btn-sm">All</a>
            </div>
            <?php if (!empty($recentAnnouncements)): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($recentAnnouncements as $ann): ?>
                        <div class="card-bluegray" style="padding: 0.85rem; margin-bottom: 0;">
                            <div style="font-weight: 700; color: var(--c-forest-dark); font-size: 0.92rem; margin-bottom: 0.3rem;">
                                <?= e($ann['title']) ?>
                            </div>
                            <div style="font-size: 0.82rem; color: var(--c-text-muted); line-height: 1.4;">
                                <?= mb_strimwidth(strip_tags($ann['body']), 0, 140, '...') ?>
                            </div>
                            <div style="font-size: 0.72rem; color: var(--c-text-muted); margin-top: 0.4rem;">
                                Published: <?= date('d M Y', strtotime($ann['published_at'] ?? $ann['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--c-text-muted); font-size: 0.88rem;">No announcements published yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
