<div class="page-header">
    <div>
        <h1>My Placement</h1>
        <p>Your approved host organisation, supervision arrangements, and training status</p>
    </div>
    <?php if (!$placement): ?>
        <div>
            <a href="<?= url('placements/self-source') ?>" class="btn btn-secondary">+ Register Self-Sourced Placement</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($placement): ?>
    <div class="card" style="border-left: 4px solid var(--c-forest);">
        <div class="card-title">
            <h3><?= e($placement['position']) ?></h3>
            <?= renderStatusBadge($placement['status']) ?>
        </div>

        <div class="form-row" style="margin-bottom: 1.5rem;">
            <div class="form-group" style="flex: 1.2;">
                <h4 style="color: var(--c-forest-dark); margin-bottom: 0.5rem;">Host Organisation</h4>
                <div style="font-size: 1.1rem; font-weight: 700; color: var(--c-forest);"><?= e($placement['company_name']) ?></div>
                <div style="font-size: 0.88rem; color: var(--c-text-muted); margin-top: 0.25rem;">
                    <strong>Industry:</strong> <?= e($placement['company_industry']) ?><br>
                    <strong>Address:</strong> <?= e($placement['company_address']) ?><br>
                    <strong>Placement Source:</strong> <?= strtoupper(e($placement['source'])) ?>
                </div>
            </div>

            <div class="form-group" style="flex: 1;">
                <h4 style="color: var(--c-forest-dark); margin-bottom: 0.5rem;">Approved Dates</h4>
                <div style="font-size: 0.92rem; color: var(--c-text-primary);">
                    <strong>Start Date:</strong> <?= date('d M Y', strtotime($placement['start_date'])) ?><br>
                    <strong>End Date:</strong> <?= date('d M Y', strtotime($placement['end_date'])) ?><br>
                    <strong>Duration:</strong> 12 Weeks (UNITEN CCI Industrial Training)
                </div>
            </div>
        </div>

        <div class="form-row" style="border-top: 1px solid var(--c-border); padding-top: 1.25rem;">
            <div class="form-group" style="flex: 1;">
                <div class="card-bluegray" style="margin-bottom: 0; padding: 1rem;">
                    <div style="font-weight: 700; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Host Workplace Supervisor</div>
                    <div style="font-size: 0.95rem; font-weight: 600;"><?= e($placement['supervisor_contact_name']) ?></div>
                    <div style="font-size: 0.84rem; color: var(--c-text-muted);"><?= e($placement['supervisor_contact_email']) ?></div>
                    <div style="font-size: 0.78rem; margin-top: 0.4rem; color: var(--c-forest);">
                        <?= !empty($placement['company_supervisor_user_id']) ? '✓ Registered Account Connected' : '⏳ Invitation Pending' ?>
                    </div>
                </div>
            </div>

            <div class="form-group" style="flex: 1;">
                <div class="card-bluegray" style="margin-bottom: 0; padding: 1rem;">
                    <div style="font-weight: 700; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Assigned Visiting Lecturer</div>
                    <?php if (!empty($placement['uni_supervisor_name'])): ?>
                        <div style="font-size: 0.95rem; font-weight: 600;"><?= e($placement['uni_supervisor_name']) ?></div>
                        <div style="font-size: 0.84rem; color: var(--c-text-muted);"><?= e($placement['uni_supervisor_email']) ?></div>
                        <div style="font-size: 0.78rem; margin-top: 0.4rem; color: var(--c-forest);">✓ Academic Supervisor Allocated</div>
                    <?php else: ?>
                        <div style="font-size: 0.9rem; color: var(--c-text-muted); padding: 0.5rem 0;">
                            To be assigned by the University Coordinator.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <?php if ($placement['status'] === 'internship_active'): ?>
                <a href="<?= url('attendance') ?>" class="btn btn-primary btn-sm">📅 Log Today's Attendance</a>
                <a href="<?= url('logbooks') ?>" class="btn btn-secondary btn-sm">📖 Weekly Logbooks</a>
            <?php endif; ?>
            <a href="<?= url('chat') ?>" class="btn btn-secondary btn-sm">💬 Open 4-Way Group Chat</a>
        </div>
    </div>
<?php else: ?>
    <div class="card" style="text-align: center; padding: 3rem 1rem;">
        <div style="font-size: 3rem; margin-bottom: 0.75rem;">🏢</div>
        <h3>No Internship Placement Registered</h3>
        <p style="color: var(--c-text-muted); font-size: 0.92rem; max-width: 500px; margin: 0.5rem auto 1.5rem;">
            You have not accepted a partner company offer or registered a self-sourced placement yet.
        </p>
        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?= url('vacancies') ?>" class="btn btn-primary">Find Internships on Platform</a>
            <a href="<?= url('placements/self-source') ?>" class="btn btn-secondary">Register Self-Sourced Placement</a>
        </div>
    </div>
<?php endif; ?>
