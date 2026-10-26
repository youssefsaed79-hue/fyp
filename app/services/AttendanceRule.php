<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Notifier;

/**
 * Attendance Rule Engine (FR-ATT-03)
 * Calculates monthly verified attendance percentage and raises automated alerts
 * when attendance falls below 80% (configurable in settings).
 */
class AttendanceRule
{
    /**
     * Compute monthly attendance percentage for a placement
     */
    public static function computeMonthlyAttendance(int $placementId, ?int $year = null, ?int $month = null): array
    {
        $year = $year ?? (int) date('Y');
        $month = $month ?? (int) date('n');

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $placement = Database::fetchOne("SELECT * FROM placements WHERE id = :id", ['id' => $placementId]);
        if (!$placement) {
            return ['percentage' => 0.0, 'is_alert' => false];
        }

        // Clamp dates to placement range
        $effectiveStart = max($startDate, $placement['start_date']);
        $effectiveEnd = min($endDate, $placement['end_date'], date('Y-m-d'));

        // Calculate expected weekdays (Monday to Friday)
        $expectedWorkDays = 0;
        if ($effectiveStart <= $effectiveEnd) {
            $period = new \DatePeriod(
                new \DateTime($effectiveStart),
                new \DateInterval('P1D'),
                (new \DateTime($effectiveEnd))->modify('+1 day')
            );
            foreach ($period as $dt) {
                if ((int) $dt->format('N') <= 5) {
                    $expectedWorkDays++;
                }
            }
        }

        // Count verified present/leave days
        $verifiedDays = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM attendance
             WHERE placement_id = :pid
               AND work_date BETWEEN :start AND :end
               AND verification = 'verified'
               AND status IN ('present', 'leave')",
            ['pid' => $placementId, 'start' => $startDate, 'end' => $endDate]
        );

        $flaggedDays = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM attendance
             WHERE placement_id = :pid
               AND work_date BETWEEN :start AND :end
               AND verification = 'flagged'",
            ['pid' => $placementId, 'start' => $startDate, 'end' => $endDate]
        );

        $threshold = (float) (Database::fetchValue("SELECT setting_value FROM settings WHERE setting_key = 'attendance_alert_pct'") ?: 80.0);

        $percentage = ($expectedWorkDays > 0) ? min(100.0, round(($verifiedDays / $expectedWorkDays) * 100, 1)) : 100.0;
        $isAlert = ($expectedWorkDays >= 5 && $percentage < $threshold);

        return [
            'year' => $year,
            'month' => $month,
            'expected_days' => $expectedWorkDays,
            'verified_days' => $verifiedDays,
            'flagged_days' => $flaggedDays,
            'percentage' => $percentage,
            'threshold' => $threshold,
            'is_alert' => $isAlert,
        ];
    }

    /**
     * Trigger alert notification if attendance drops below threshold (FR-ATT-03)
     */
    public static function checkAndAlert(int $placementId): void
    {
        $stats = self::computeMonthlyAttendance($placementId);
        if (!$stats['is_alert']) {
            return;
        }

        $placement = Database::fetchOne(
            "SELECT p.*, u.name as student_name, c.name as company_name
             FROM placements p
             JOIN users u ON p.student_user_id = u.id
             JOIN companies c ON p.company_id = c.id
             WHERE p.id = :id",
            ['id' => $placementId]
        );

        if (!$placement) {
            return;
        }

        $title = "⚠️ Attendance Warning: Below 80%";
        $body = "Monthly verified attendance for student {$placement['student_name']} at {$placement['company_name']} is currently {$stats['percentage']}% (Threshold: {$stats['threshold']}%). Minimum 80% attendance is mandatory for academic completion.";

        // 1. Alert Student
        Notifier::send((int)$placement['student_user_id'], 'attendance_alert', $title, $body, '/attendance');

        // 2. Alert Company Supervisor
        if (!empty($placement['company_supervisor_user_id'])) {
            Notifier::send((int)$placement['company_supervisor_user_id'], 'attendance_alert', $title, $body, '/attendance/batch?placement_id=' . $placementId);
        }

        // 3. Alert University Supervisor
        if (!empty($placement['university_supervisor_id'])) {
            Notifier::send((int)$placement['university_supervisor_id'], 'attendance_alert', $title, $body, '/supervisors/students');
        }

        // 4. Alert Coordinator
        $coordinators = Database::fetchAll("SELECT id FROM users WHERE role = 'coordinator'");
        foreach ($coordinators as $coord) {
            Notifier::send((int)$coord['id'], 'attendance_alert', $title, $body, '/placements/monitoring');
        }
    }
}
