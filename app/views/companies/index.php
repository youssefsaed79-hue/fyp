<div class="page-header">
    <div>
        <h1>Company Management &amp; Verification</h1>
        <p>Review partner company profiles and self-sourced supervisor invitations (FR-UCM-01, FR-UCM-03)</p>
    </div>
</div>

<!-- TABS: PARTNER COMPANIES & INVITATION TOKENS -->
<div class="tabs-container">
    <div class="nav-tabs">
        <button type="button" class="tab-btn active" data-tab="tab-companies">Partner Companies (<?= count($companies) ?>)</button>
        <button type="button" class="tab-btn" data-tab="tab-tokens">System Invitation Tokens (<?= count($tokens) ?>)</button>
    </div>

    <!-- TAB 1: COMPANIES -->
    <div id="tab-companies" class="tab-pane active">
        <div class="card" style="padding: 1rem; margin-bottom: 1.25rem;">
            <form method="GET" action="<?= url('companies') ?>" style="display: flex; gap: 1rem; align-items: center;">
                <label style="font-size: 0.88rem; font-weight: 600;">Filter by Status:</label>
                <select name="status" class="form-select" style="width: 200px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                    <option value="approved" <?= $currentStatus === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected" <?= $currentStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    <option value="suspended" <?= $currentStatus === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
                <?php if ($currentStatus): ?>
                    <a href="<?= url('companies') ?>" class="btn btn-secondary btn-sm">Clear Filter</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company Name &amp; Reg No</th>
                            <th>Industry &amp; Location</th>
                            <th>Representative</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Interns / Vacancies</th>
                            <th>Coordinator Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($companies)): ?>
                            <?php foreach ($companies as $comp): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($comp['name']) ?></strong><br>
                                        <small style="color: var(--c-text-muted);"><?= e($comp['registration_no']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= e($comp['industry']) ?></strong><br>
                                        <small style="color: var(--c-text-muted);"><?= mb_strimwidth(e($comp['address']), 0, 50, '...') ?></small>
                                    </td>
                                    <td>
                                        <?= e($comp['representative_name']) ?><br>
                                        <small style="color: var(--c-text-muted);"><?= e($comp['representative_email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $comp['source'] === 'partner' ? 'badge-info' : 'badge-warning' ?>">
                                            <?= e(str_replace('_', ' ', $comp['source'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= renderStatusBadge($comp['status']) ?>
                                    </td>
                                    <td style="font-size: 0.85rem;">
                                        <?= (int) $comp['interns_count'] ?> active interns<br>
                                        <small style="color: var(--c-text-muted);"><?= (int) $comp['vacancies_count'] ?> listings</small>
                                    </td>
                                    <td>
                                        <form action="<?= url('companies/update-status') ?>" method="POST" style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                            <?= \App\Core\Csrf::field() ?>
                                            <input type="hidden" name="company_id" value="<?= $comp['id'] ?>">

                                            <?php if ($comp['status'] !== 'approved'): ?>
                                                <button type="submit" name="status" value="approved" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.55rem;">Approve</button>
                                            <?php endif; ?>

                                            <?php if ($comp['status'] === 'pending'): ?>
                                                <button type="submit" name="status" value="rejected" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.55rem;" data-confirm="Reject registration for <?= e($comp['name']) ?>?">Reject</button>
                                            <?php endif; ?>

                                            <?php if ($comp['status'] === 'approved'): ?>
                                                <button type="submit" name="status" value="suspended" class="btn btn-secondary btn-sm" style="color: var(--c-status-danger); border-color: rgba(163,40,40,0.3); padding: 0.25rem 0.55rem;" data-confirm="Suspend company <?= e($comp['name']) ?>?">Suspend</button>
                                            <?php elseif ($comp['status'] === 'suspended'): ?>
                                                <button type="submit" name="status" value="approved" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.55rem;">Reinstate</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: var(--c-text-muted);">
                                    No companies match this filter.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: SYSTEM INVITATION TOKENS -->
    <div id="tab-tokens" class="tab-pane">
        <div class="card">
            <div class="card-title">
                <h3>System Invitation Tokens for Self-Sourced Placements</h3>
                <span class="badge badge-info">Single-Use &bull; 72h Lifespan</span>
            </div>
            <p style="color: var(--c-text-muted); font-size: 0.88rem; margin-bottom: 1rem;">
                When a student registers an external host supervisor, a single-use token is generated and emailed. Accounts created through these tokens are scoped strictly to the student's placement.
            </p>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invited Email</th>
                            <th>Student &amp; Position</th>
                            <th>Company</th>
                            <th>Expires At</th>
                            <th>Status</th>
                            <th>Direct Test Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tokens)): ?>
                            <?php foreach ($tokens as $tok): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($tok['invited_email']) ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= e($tok['student_name']) ?></strong> (<?= e($tok['student_id']) ?>)<br>
                                        <small style="color: var(--c-text-muted);"><?= e($tok['position']) ?></small>
                                    </td>
                                    <td><?= e($tok['company_name']) ?></td>
                                    <td style="font-size: 0.84rem;">
                                        <?= date('d M Y, H:i', strtotime($tok['expires_at'])) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($tok['used_at'])): ?>
                                            <span class="badge badge-success">Registered &amp; Used</span>
                                        <?php elseif (!empty($tok['revoked'])): ?>
                                            <span class="badge badge-danger">Revoked</span>
                                        <?php elseif (strtotime($tok['expires_at']) < time()): ?>
                                            <span class="badge badge-danger">Expired</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Active Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($tok['used_at']) && empty($tok['revoked']) && strtotime($tok['expires_at']) >= time()): ?>
                                            <a href="<?= url('invite/accept', ['token' => 'demo_token_nextech_2026']) ?>" target="_blank" class="btn btn-secondary btn-sm" style="font-size: 0.78rem;">
                                                Open Invitation Link &nearr;
                                            </a>
                                        <?php else: ?>
                                            <span style="color: var(--c-text-muted);">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem; color: var(--c-text-muted);">
                                    No invitation tokens issued yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
