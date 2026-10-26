<div class="page-header">
    <div>
        <h1>University Supervisor Allocation</h1>
        <p>Assign visiting lecturers to approved placements and balance supervisory workload (FR-SVA-01, FR-SVA-02)</p>
    </div>
</div>

<!-- WORKLOAD BALANCE CARDS -->
<div style="margin-bottom: 1.5rem;">
    <h3 style="color: var(--c-forest-dark); margin-bottom: 0.75rem;">Supervisor Workload Capacity</h3>
    <div class="stats-grid">
        <?php foreach ($supervisors as $sup): ?>
            <div class="stat-tile">
                <div class="stat-label"><?= e($sup['name']) ?></div>
                <div class="stat-value" style="font-size: 1.5rem; margin-top: 0.25rem;">
                    <?= (int)$sup['current_load'] ?> <span style="font-size: 0.8rem; font-weight: normal; color: var(--c-text-muted);">Students Assigned</span>
                </div>
                <div style="font-size: 0.78rem; color: var(--c-text-muted); margin-top: 0.35rem;">
                    <?= e($sup['email']) ?>
                    <?php if ($suggestedSupervisor && $suggestedSupervisor['id'] === $sup['id']): ?>
                        <span class="badge badge-success" style="margin-left: 0.3rem;">Lowest Load (Suggested)</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- PLACEMENT ALLOCATION TABLE -->
<div class="card">
    <div class="card-title">
        <h3>Approved Placements Needing / Assigned Supervision</h3>
        <span class="badge badge-info"><?= count($placements) ?> Total</span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student Name &amp; ID</th>
                    <th>Host Company &amp; Location</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Current Supervisor</th>
                    <th>Assign / Re-allocate Supervisor</th>
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
                                <small style="color: var(--c-text-muted);"><?= e($plc['company_address']) ?></small>
                            </td>
                            <td><?= e($plc['position']) ?></td>
                            <td><?= renderStatusBadge($plc['status']) ?></td>
                            <td>
                                <?php if (!empty($plc['current_supervisor_name'])): ?>
                                    <span style="font-weight: 600; color: var(--c-forest);"><?= e($plc['current_supervisor_name']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="<?= url('supervisors/assign') ?>" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                    <?= \App\Core\Csrf::field() ?>
                                    <input type="hidden" name="placement_id" value="<?= $plc['id'] ?>">

                                    <select name="supervisor_id" class="form-select" style="min-width: 190px; font-size: 0.84rem;" required>
                                        <?php foreach ($supervisors as $sup): ?>
                                            <option value="<?= $sup['id'] ?>" <?= (int)($plc['university_supervisor_id'] ?? 0) === (int)$sup['id'] ? 'selected' : ($suggestedSupervisor && $suggestedSupervisor['id'] === $sup['id'] && empty($plc['university_supervisor_id']) ? 'selected' : '') ?>>
                                                <?= e($sup['name']) ?> (<?= (int)$sup['current_load'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.35rem 0.65rem;">
                                        <?= empty($plc['university_supervisor_id']) ? 'Assign' : 'Reassign' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--c-text-muted);">
                            No placements available for supervisor allocation.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
