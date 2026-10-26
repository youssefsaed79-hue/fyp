<div class="page-header">
    <div>
        <h1>Coordinator Analytics Dashboard</h1>
        <p>UNITEN College of Computing &amp; Informatics &bull; Industrial Training Administration</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="<?= url('import') ?>" class="btn btn-primary">📥 Bulk Cohort Import</a>
        <a href="<?= url('supervisors/assignment') ?>" class="btn btn-secondary">⚖️ Supervisor Allocation</a>
        <a href="<?= url('evaluations/lock') ?>" class="btn btn-secondary">🔒 Lock Final Grades</a>
    </div>
</div>

<!-- KPI TILES -->
<div class="stats-grid">
    <div class="stat-tile">
        <div class="stat-label">Total Cohort Students</div>
        <div class="stat-value"><?= $totalStudents ?></div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            <?= $verifiedStudents ?> verified accounts
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Active Internships</div>
        <div class="stat-value" style="color: var(--c-forest);"><?= $activeInternships ?></div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Currently in 12-week training
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Pending Placement Approvals</div>
        <div class="stat-value" style="<?= $pendingApprovals > 0 ? 'color: var(--c-status-warning);' : '' ?>">
            <?= $pendingApprovals ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            <a href="<?= url('placements/approvals') ?>">Review approval queue &rarr;</a>
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Completed Internships</div>
        <div class="stat-value"><?= $completedInternships ?></div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Full grade endorsed
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Cohort Grade Average</div>
        <div class="stat-value" style="color: var(--c-forest-dark);">
            <?= is_numeric($gradeAverage) ? $gradeAverage . '%' : 'N/A' ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Combined weighted result (40/30/20/10)
        </div>
    </div>
</div>

<!-- ANALYTICS CHARTS (Chart.js) -->
<div class="form-row" style="margin-bottom: 1.5rem;">
    <div class="form-group" style="flex: 1.1;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>Placement Status Distribution</h3>
                <span style="font-size: 0.82rem; color: var(--c-text-muted);">Current cohort lifecycle</span>
            </div>
            <div style="height: 260px; position: relative;">
                <canvas id="placementStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="form-group" style="flex: 1.3;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>University Supervisor Workload</h3>
                <a href="<?= url('supervisors/assignment') ?>" class="btn btn-secondary btn-sm">Balance Load</a>
            </div>
            <div style="height: 260px; position: relative;">
                <canvas id="supervisorWorkloadChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ACTION QUEUES: PENDING APPROVALS & AT-RISK STUDENTS -->
<div class="form-row">
    <!-- PENDING APPROVALS -->
    <div class="form-group" style="flex: 1.2;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>Pending Placement Approvals Queue</h3>
                <a href="<?= url('placements/approvals') ?>" class="btn btn-secondary btn-sm">View All (<?= $pendingApprovals ?>)</a>
            </div>

            <?php if (!empty($approvalsQueue)): ?>
                <div class="table-responsive">
                    <table class="table" style="font-size: 0.86rem;">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Company</th>
                                <th>Source</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approvalsQueue as $appr): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($appr['student_name']) ?></strong><br>
                                        <small style="color: var(--c-text-muted);"><?= e($appr['student_id']) ?></small>
                                    </td>
                                    <td><?= e($appr['company_name']) ?></td>
                                    <td>
                                        <span class="badge <?= $appr['source'] === 'self_sourced' ? 'badge-warning' : 'badge-info' ?>">
                                            <?= e(str_replace('_', ' ', $appr['source'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form action="<?= url('placements/approve') ?>" method="POST" style="display: inline;">
                                            <?= \App\Core\Csrf::field() ?>
                                            <input type="hidden" name="placement_id" value="<?= $appr['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.55rem;">Approve</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem 1rem; color: var(--c-text-muted);">
                    ✓ No pending placement approvals in queue.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AT-RISK STUDENTS & ATTENDANCE ALERTS -->
    <div class="form-group" style="flex: 1;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>Students At Risk (&lt;80% Attendance)</h3>
                <span class="badge badge-danger"><?= count($atRiskStudents) ?> Alerts</span>
            </div>

            <?php if (!empty($atRiskStudents)): ?>
                <div class="table-responsive">
                    <table class="table" style="font-size: 0.86rem;">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Company</th>
                                <th>Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($atRiskStudents as $ar): ?>
                                <?php $pct = round(($ar['verified_days'] / max(1, $ar['total_days'])) * 100, 1); ?>
                                <tr>
                                    <td>
                                        <strong><?= e($ar['student_name']) ?></strong><br>
                                        <small><?= e($ar['student_id']) ?></small>
                                    </td>
                                    <td><?= e($ar['company_name']) ?></td>
                                    <td>
                                        <span class="badge badge-danger"><?= $pct ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem 1rem; color: var(--c-text-muted);">
                    ✓ All active students currently maintain attendance at or above 80%.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js Initialization using local library and Brand Colors (Section 17) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Brand Color Palette tokens
    const cForestDark = '#1D3E2F';
    const cForest = '#2F5F48';
    const cBlueGray = '#E7EEF0';
    const cBorder = '#D5DFE2';
    const cWarning = '#8D5C0B';
    const cSuccess = '#1E6B47';

    // 1. Placement Status Doughnut Chart
    <?php
    $statusLabels = [];
    $statusCounts = [];
    foreach ($placementStatuses as $ps) {
        $statusLabels[] = ucwords(str_replace('_', ' ', $ps['status']));
        $statusCounts[] = (int) $ps['count'];
    }
    if (empty($statusLabels)) {
        $statusLabels = ['No Placements'];
        $statusCounts = [0];
    }
    ?>
    const ctxStatus = document.getElementById('placementStatusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusCounts) ?>,
                backgroundColor: [cForest, cForestDark, '#4A7C64', '#7BA893', '#A4C6B6', '#CBDED4'],
                borderColor: '#FFFFFF',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: cForestDark, font: { family: 'inherit', size: 12 } }
                }
            }
        }
    });

    // 2. Supervisor Workload Horizontal Bar Chart
    <?php
    $supNames = [];
    $supCounts = [];
    foreach ($supervisorWorkload as $sw) {
        $supNames[] = $sw['name'];
        $supCounts[] = (int) $sw['assigned_count'];
    }
    ?>
    const ctxWorkload = document.getElementById('supervisorWorkloadChart').getContext('2d');
    new Chart(ctxWorkload, {
        type: 'bar',
        data: {
            labels: <?= json_encode($supNames) ?>,
            datasets: [{
                label: 'Assigned Students',
                data: <?= json_encode($supCounts) ?>,
                backgroundColor: cForest,
                borderColor: cForestDark,
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: cForestDark },
                    grid: { color: cBorder }
                },
                y: {
                    ticks: { color: cForestDark },
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
