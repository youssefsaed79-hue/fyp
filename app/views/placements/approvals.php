<div class="page-header">
    <div>
        <h1>Placement Approvals Administration</h1>
        <p>Review student placement requests, grant university approvals, and activate internships (FR-APM-05)</p>
    </div>
</div>

<!-- STATUS FILTER -->
<div class="card" style="padding: 1rem; margin-bottom: 1.25rem;">
    <form method="GET" action="<?= url('placements/approvals') ?>" style="display: flex; gap: 1rem; align-items: center;">
        <label style="font-size: 0.88rem; font-weight: 600;">Placement Status:</label>
        <select name="status" class="form-select" style="width: 240px;" onchange="this.form.submit()">
            <option value="placement_pending" <?= $currentStatus === 'placement_pending' ? 'selected' : '' ?>>Pending Approval</option>
            <option value="university_approved" <?= $currentStatus === 'university_approved' ? 'selected' : '' ?>>University Approved</option>
            <option value="internship_active" <?= $currentStatus === 'internship_active' ? 'selected' : '' ?>>Active Internships</option>
            <option value="completed" <?= $currentStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="rejected" <?= $currentStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            <option value="all" <?= $currentStatus === 'all' ? 'selected' : '' ?>>All Placements</option>
        </select>
    </form>
</div>

<!-- PLACEMENTS TABLE -->
<div class="card">
    <div class="card-title">
        <h3>Placements Queue (<?= count($placements) ?> Total)</h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name &amp; ID</th>
                    <th>Host Organisation</th>
                    <th>Position</th>
                    <th>Source</th>
                    <th>Dates (12 Wks)</th>
                    <th>Status</th>
                    <th>Approval / Activation Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($placements)): ?>
                    <?php foreach ($placements as $plc): ?>
                        <tr>
                            <td>
                                <strong><?= e($plc['student_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($plc['student_id']) ?> &bull; <?= e($plc['programme']) ?></small>
                            </td>
                            <td>
                                <strong><?= e($plc['company_name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($plc['company_industry']) ?></small>
                            </td>
                            <td><?= e($plc['position']) ?></td>
                            <td>
                                <span class="badge <?= $plc['source'] === 'self_sourced' ? 'badge-warning' : 'badge-info' ?>">
                                    <?= e(str_replace('_', ' ', $plc['source'])) ?>
                                </span>
                            </td>
                            <td style="font-size: 0.84rem;">
                                <?= date('d M Y', strtotime($plc['start_date'])) ?> &ndash;<br>
                                <?= date('d M Y', strtotime($plc['end_date'])) ?>
                            </td>
                            <td>
                                <?= renderStatusBadge($plc['status']) ?>
                            </td>
                            <td>
                                <?php if ($plc['status'] === 'placement_pending'): ?>
                                    <form action="<?= url('placements/approve') ?>" method="POST" style="display: flex; gap: 0.35rem;">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="placement_id" value="<?= $plc['id'] ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.6rem;">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.6rem;" data-confirm="Reject placement for <?= e($plc['student_name']) ?>?">Reject</button>
                                    </form>
                                <?php elseif ($plc['status'] === 'university_approved'): ?>
                                    <form action="<?= url('placements/activate') ?>" method="POST" data-confirm="Activate internship #<?= $plc['id'] ?>? This will automatically generate 12 weekly logbooks and create the 4-way group chat (FR-LOG-01, FR-COM-01).">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="placement_id" value="<?= $plc['id'] ?>">
                                        <button type="submit" class="btn btn-primary btn-sm" style="background-color: var(--c-status-success); border-color: var(--c-status-success); padding: 0.25rem 0.6rem;">
                                            🚀 Activate Internship
                                        </button>
                                    </form>
                                <?php elseif ($plc['status'] === 'internship_active'): ?>
                                    <span style="font-size: 0.82rem; color: var(--c-status-success); font-weight: 600;">Active in Training</span>
                                <?php else: ?>
                                    <span style="color: var(--c-text-muted);">&mdash;</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--c-text-muted);">
                            No placements found with status: <strong><?= e($currentStatus) ?></strong>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
