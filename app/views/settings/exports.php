<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">CSV Data Export Centre</h1>
        <p class="text-muted" style="margin: 0;">
            Export key administrative datasets and reports for academic archiving, accreditation, and offline analysis (FR-DASH-01).
        </p>
    </div>
    <div>
        <a href="<?= url('settings') ?>" class="btn btn-secondary">
            ← Back to Settings
        </a>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    <!-- Student Cohort Roster Export -->
    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="font-size: 2rem; margin-bottom: 8px;">🎓</div>
            <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Student Cohort Roster</h3>
            <p style="font-size: 0.9rem; color: #555; line-height: 1.5; margin-bottom: 16px;">
                Complete list of all registered students including student ID, name, email, programme, intake, CGPA, and verification status.
            </p>
        </div>
        <a href="<?= url('settings/export-csv?type=students') ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
            📥 Export Students CSV
        </a>
    </div>

    <!-- Placements & Companies Export -->
    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="font-size: 2rem; margin-bottom: 8px;">🏢</div>
            <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Placements &amp; Companies</h3>
            <p style="font-size: 0.9rem; color: #555; line-height: 1.5; margin-bottom: 16px;">
                All student placements, assigned companies, positions, training dates, assigned supervisors, and placement statuses.
            </p>
        </div>
        <a href="<?= url('settings/export-csv?type=placements') ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
            📥 Export Placements CSV
        </a>
    </div>

    <!-- Official Final Grades Export -->
    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="font-size: 2rem; margin-bottom: 8px;">🏆</div>
            <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Final Grades &amp; Evaluations</h3>
            <p style="font-size: 0.9rem; color: #555; line-height: 1.5; margin-bottom: 16px;">
                Consolidated assessment results with Host (40%), Visiting (30%), Final Report (20%), Logbook (10%), total score, and letter grade.
            </p>
        </div>
        <a href="<?= url('settings/export-csv?type=grades') ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
            📥 Export Grades CSV
        </a>
    </div>

    <!-- Attendance Logs Export -->
    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="font-size: 2rem; margin-bottom: 8px;">📅</div>
            <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Attendance Audit Trail</h3>
            <p style="font-size: 0.9rem; color: #555; line-height: 1.5; margin-bottom: 16px;">
                Comprehensive daily logs with check-in, check-out, working hours, arrangement, verification status, and supervisor flag comments.
            </p>
        </div>
        <a href="<?= url('settings/export-csv?type=attendance') ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
            📥 Export Attendance CSV
        </a>
    </div>
</div>
