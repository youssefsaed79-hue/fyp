<div class="page-header">
    <div>
        <h1>Daily Attendance Register</h1>
        <p><?= e($placement['company_name']) ?> &bull; Log daily working hours and track verification status (FR-ATT-01)</p>
    </div>
</div>

<!-- ATTENDANCE 80% RULE ALERT & STATS (FR-ATT-03) -->
<?php if (!empty($monthlyStats['is_alert'])): ?>
    <div class="alert alert-danger" style="border-left: 4px solid var(--c-status-danger);">
        <div>
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem;">⚠️ Attendance Warning: Current Month Below 80%</div>
            <p style="margin: 0; font-size: 0.88rem;">
                Your verified attendance for <?= date('F Y') ?> is currently <strong><?= $monthlyStats['percentage'] ?>%</strong> (<?= $monthlyStats['verified_days'] ?> / <?= $monthlyStats['expected_days'] ?> expected days).
                University regulation requires an overall attendance rate of at least 80% to be eligible for final grading. Please ensure your host supervisor verifies your pending days.
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-tile">
        <div class="stat-label">Monthly Verified Attendance</div>
        <div class="stat-value" style="<?= !empty($monthlyStats['is_alert']) ? 'color: var(--c-status-danger);' : 'color: var(--c-forest);' ?>">
            <?= $monthlyStats['percentage'] ?>%
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            <?= $monthlyStats['verified_days'] ?> verified of <?= $monthlyStats['expected_days'] ?> expected weekdays
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Pending Verification</div>
        <div class="stat-value" style="color: var(--c-status-warning);">
            <?php
            $pendingCount = count(array_filter($records, fn($r) => $r['verification'] === 'pending_verification'));
            echo $pendingCount;
            ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Awaiting company supervisor batch sign-off
        </div>
    </div>

    <div class="stat-tile">
        <div class="stat-label">Flagged Days</div>
        <div class="stat-value" style="color: var(--c-status-danger);">
            <?= (int)$monthlyStats['flagged_days'] ?>
        </div>
        <div style="font-size: 0.8rem; color: var(--c-text-muted); margin-top: 0.35rem;">
            Requires clarification or evidence
        </div>
    </div>
</div>

<div class="form-row">
    <!-- 1. LOG TODAY'S ATTENDANCE FORM -->
    <div class="form-group" style="flex: 1.1;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>Log Attendance Record</h3>
                <span class="badge badge-info"><?= date('l, d M Y') ?></span>
            </div>

            <form action="<?= url('attendance/log') ?>" method="POST" enctype="multipart/form-data" data-validate>
                <?= \App\Core\Csrf::field() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="work_date" class="form-label">Work Date</label>
                        <input type="date" id="work_date" name="work_date" class="form-control" value="<?= e($todayStr) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Attendance Status</label>
                        <select name="status" id="status" class="form-select" onchange="toggleAbsenceFields(this.value)">
                            <option value="present" selected>Present (Working Day)</option>
                            <option value="leave">Approved Medical / College Leave</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </div>

                <div id="present_fields">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="check_in" class="form-label">Check-In Time</label>
                            <input type="time" id="check_in" name="check_in" class="form-control" value="08:30">
                        </div>

                        <div class="form-group">
                            <label for="check_out" class="form-label">Check-Out Time</label>
                            <input type="time" id="check_out" name="check_out" class="form-control" value="17:30">
                        </div>

                        <div class="form-group">
                            <label for="hours" class="form-label">Working Hours</label>
                            <input type="number" step="0.5" id="hours" name="hours" class="form-control" value="8.0" min="0" max="16">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="work_arrangement" class="form-label">Work Arrangement</label>
                        <select name="work_arrangement" id="work_arrangement" class="form-select">
                            <option value="onsite" selected>On-site Office</option>
                            <option value="remote">Remote / Work From Home</option>
                            <option value="hybrid">Field / Site Visit</option>
                        </select>
                    </div>
                </div>

                <div id="absence_fields" style="display: none;">
                    <div class="form-group">
                        <label for="absence_reason" class="form-label">Absence / Leave Justification</label>
                        <textarea id="absence_reason" name="absence_reason" class="form-control" rows="2" placeholder="State reason for medical leave, university event, or emergency..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="evidence" class="form-label">Supporting Medical Certificate / Evidence (PDF/Image)</label>
                        <input type="file" id="evidence" name="evidence" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                    </div>
                </div>

                <div style="margin-top: 1.25rem;">
                    <button type="submit" class="btn btn-primary">
                        Submit Daily Attendance &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. ATTENDANCE HISTORY TABLE -->
    <div class="form-group" style="flex: 1.4;">
        <div class="card" style="height: 100%;">
            <div class="card-title">
                <h3>Recorded Attendance Register (<?= count($records) ?> Days)</h3>
            </div>

            <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                <table class="table" style="font-size: 0.86rem;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status &amp; Hours</th>
                            <th>Time In/Out</th>
                            <th>Verification</th>
                            <th>Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $rec): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('d M Y', strtotime($rec['work_date'])) ?></strong><br>
                                        <small style="color: var(--c-text-muted);"><?= date('l', strtotime($rec['work_date'])) ?></small>
                                    </td>
                                    <td>
                                        <?= renderStatusBadge($rec['status']) ?><br>
                                        <small style="color: var(--c-text-muted);"><?= (float)$rec['hours'] ?> hrs (<?= e($rec['work_arrangement']) ?>)</small>
                                    </td>
                                    <td>
                                        <?= e($rec['check_in'] ?? '--:--') ?> &ndash; <?= e($rec['check_out'] ?? '--:--') ?>
                                    </td>
                                    <td>
                                        <?= renderStatusBadge($rec['verification']) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($rec['flag_comment'])): ?>
                                            <span style="color: var(--c-status-danger); font-size: 0.8rem; font-weight: 500;">
                                                <?= e($rec['flag_comment']) ?>
                                            </span>
                                        <?php elseif ($rec['verification'] === 'verified'): ?>
                                            <small style="color: var(--c-text-muted);">Signed off by supervisor</small>
                                        <?php else: ?>
                                            <small style="color: var(--c-text-muted);">&mdash;</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: var(--c-text-muted);">
                                    No attendance records logged yet. Use the form on the left to submit today's attendance.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAbsenceFields(status) {
    const presentFields = document.getElementById('present_fields');
    const absenceFields = document.getElementById('absence_fields');
    if (status === 'leave' || status === 'absent') {
        presentFields.style.display = 'none';
        absenceFields.style.display = 'block';
    } else {
        presentFields.style.display = 'block';
        absenceFields.style.display = 'none';
    }
}
</script>
