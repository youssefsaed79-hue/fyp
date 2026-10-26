<div class="page-header">
    <div>
        <h1>User Account Management</h1>
        <p>Coordinate student cohorts, host company supervisors, and university supervisors (FR-AUTH-04)</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= url('users/create') ?>" class="btn btn-primary">+ Create User Account</a>
        <a href="<?= url('import') ?>" class="btn btn-secondary">📥 Bulk Import Cohort</a>
    </div>
</div>

<!-- FILTERS -->
<div class="card" style="padding: 1.15rem; margin-bottom: 1.25rem;">
    <form method="GET" action="<?= url('users') ?>" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-size: 0.8rem;">Search Name, Email, or Student ID</label>
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= e($currentSearch) ?>">
        </div>

        <div style="width: 180px;">
            <label class="form-label" style="font-size: 0.8rem;">Filter by Role</label>
            <select name="role" class="form-select">
                <option value="">All Roles</option>
                <option value="student" <?= $currentRole === 'student' ? 'selected' : '' ?>>Students</option>
                <option value="company" <?= $currentRole === 'company' ? 'selected' : '' ?>>Companies</option>
                <option value="university_supervisor" <?= $currentRole === 'university_supervisor' ? 'selected' : '' ?>>Uni Supervisors</option>
                <option value="coordinator" <?= $currentRole === 'coordinator' ? 'selected' : '' ?>>Coordinators</option>
            </select>
        </div>

        <div style="width: 150px;">
            <label class="form-label" style="font-size: 0.8rem;">Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= $currentStatus === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.6rem 1.1rem;">Filter</button>
            <a href="<?= url('users') ?>" class="btn btn-secondary btn-sm" style="padding: 0.6rem 1rem;">Reset</a>
        </div>
    </form>
</div>

<!-- USERS TABLE -->
<div class="card">
    <div class="card-title">
        <h3>User Accounts Directory (<?= count($users) ?> Total)</h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Name &amp; Email</th>
                    <th>Student ID / Organisation</th>
                    <th>Verification</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th>Account Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <span class="badge badge-info"><?= e(str_replace('_', ' ', $u['role'])) ?></span>
                            </td>
                            <td>
                                <strong><?= e($u['name']) ?></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($u['email']) ?></small>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'student'): ?>
                                    <strong><?= e($u['student_id'] ?? 'N/A') ?></strong><br>
                                    <small style="color: var(--c-text-muted);"><?= e($u['programme'] ?? '') ?></small>
                                <?php elseif ($u['role'] === 'company'): ?>
                                    <?= e($u['company_name'] ?? 'Invited External Supervisor') ?>
                                <?php else: ?>
                                    <span style="color: var(--c-text-muted);">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'student'): ?>
                                    <?php if (!empty($u['verified'])): ?>
                                        <span class="badge badge-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending Gate</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--c-text-muted);">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= renderStatusBadge($u['status']) ?>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--c-text-muted);">
                                <?= !empty($u['last_login_at']) ? date('d M Y, H:i', strtotime($u['last_login_at'])) : 'Never' ?>
                            </td>
                            <td>
                                <?php if ($u['id'] !== \App\Core\Auth::id()): ?>
                                    <form action="<?= url('users/toggle-status') ?>" method="POST" style="display: inline;">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <?php if ($u['status'] === 'active'): ?>
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--c-status-danger); border-color: rgba(163,40,40,0.3);" title="Suspend Account" data-confirm="Suspend account for <?= e($u['name']) ?>?">Suspend</button>
                                        <?php else: ?>
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--c-status-success); border-color: rgba(30,107,71,0.3);" title="Activate Account">Activate</button>
                                        <?php endif; ?>
                                    </form>
                                <?php else: ?>
                                    <small style="color: var(--c-text-muted);">(Current User)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--c-text-muted);">
                            No user accounts match the current filter criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
