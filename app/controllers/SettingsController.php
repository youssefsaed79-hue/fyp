<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\View;

/**
 * System Settings & Data Export Controller
 * Realises Table 3.8 / FR-EVAL-03 (Configurable Assessment Weights and Thresholds)
 * Realises FR-DASH-01 (Coordinator CSV data exports of key tables)
 */
class SettingsController
{
    // Coordinator Settings Screen
    public function index(): void
    {
        Auth::requireRole('coordinator');

        $settingsRows = Database::fetchAll("SELECT * FROM settings ORDER BY setting_key ASC");
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        View::render('settings/index', [
            'title' => 'System Settings & Assessment Configuration',
            'settings' => $settings,
        ], 'layouts/main');
    }

    // Save Updated Settings
    public function update(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $wHost = (float) ($_POST['weight_host'] ?? 40);
        $wVisiting = (float) ($_POST['weight_visiting'] ?? 30);
        $wReport = (float) ($_POST['weight_report'] ?? 20);
        $wLogbook = (float) ($_POST['weight_logbook'] ?? 10);

        $totalWeight = $wHost + $wVisiting + $wReport + $wLogbook;
        if (abs($totalWeight - 100.0) > 0.01) {
            Flash::danger("The four assessment component weights must sum exactly to 100% (Current sum: {$totalWeight}%).");
            header('Location: ' . url('settings'));
            exit;
        }

        $attThreshold = (float) ($_POST['attendance_alert_pct'] ?? 80);
        $tokenExpiry = (int) ($_POST['token_expiry_hours'] ?? 72);
        $sessionTimeout = (int) ($_POST['session_timeout_min'] ?? 30);
        $maxUpload = (int) ($_POST['max_upload_mb'] ?? 10);

        $updates = [
            'weight_host' => (string)$wHost,
            'weight_visiting' => (string)$wVisiting,
            'weight_report' => (string)$wReport,
            'weight_logbook' => (string)$wLogbook,
            'attendance_alert_pct' => (string)$attThreshold,
            'token_expiry_hours' => (string)$tokenExpiry,
            'session_timeout_min' => (string)$sessionTimeout,
            'max_upload_mb' => (string)$maxUpload,
        ];

        Database::transaction(function($pdo) use ($updates) {
            $stmt = $pdo->prepare(
                "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
            );
            foreach ($updates as $k => $v) {
                $stmt->execute(['k' => $k, 'v' => $v]);
            }
        });

        Flash::success('System configuration and assessment weights saved successfully.');
        header('Location: ' . url('settings'));
        exit;
    }

    // Coordinator Exports Hub
    public function exports(): void
    {
        Auth::requireRole('coordinator');

        View::render('settings/exports', [
            'title' => 'CSV Data Export Centre',
        ], 'layouts/main');
    }

    // FR-DASH-01: Export Key Tables to CSV
    public function exportCsv(): void
    {
        Auth::requireRole('coordinator');
        $type = $_GET['type'] ?? 'students';

        $filename = "internflow_{$type}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        switch ($type) {
            case 'students':
                fputcsv($out, ['User ID', 'Student ID', 'Name', 'Email', 'Programme', 'Intake', 'CGPA', 'Status', 'Verified']);
                $rows = Database::fetchAll(
                    "SELECT u.id, s.student_id, u.name, u.email, s.programme, s.intake, s.cgpa, u.status, s.verified
                     FROM users u
                     JOIN students s ON u.id = s.user_id
                     ORDER BY s.student_id ASC"
                );
                foreach ($rows as $r) {
                    fputcsv($out, $r);
                }
                break;

            case 'placements':
                fputcsv($out, ['Placement ID', 'Student ID', 'Student Name', 'Company Name', 'Position', 'Start Date', 'End Date', 'Supervisor', 'Status', 'Approved At']);
                $rows = Database::fetchAll(
                    "SELECT p.id, s.student_id, u.name as student_name, c.name as company_name, p.position,
                            p.start_date, p.end_date, u_sup.name as supervisor_name, p.status, p.approved_at
                     FROM placements p
                     JOIN users u ON p.student_user_id = u.id
                     JOIN students s ON u.id = s.user_id
                     JOIN companies c ON p.company_id = c.id
                     LEFT JOIN users u_sup ON p.university_supervisor_id = u_sup.id
                     ORDER BY p.id DESC"
                );
                foreach ($rows as $r) {
                    fputcsv($out, $r);
                }
                break;

            case 'grades':
                fputcsv($out, ['Placement ID', 'Student ID', 'Student Name', 'Company', 'Host (40%)', 'Visiting (30%)', 'Report (20%)', 'Logbook (10%)', 'Total', 'Letter Grade', 'Locked At']);
                $rows = Database::fetchAll(
                    "SELECT fg.placement_id, s.student_id, u.name as student_name, c.name as company_name,
                            fg.host_score, fg.visiting_score, fg.report_score, fg.logbook_score, fg.total,
                            '' as letter_grade, fg.locked_at
                     FROM final_grades fg
                     JOIN placements p ON fg.placement_id = p.id
                     JOIN users u ON p.student_user_id = u.id
                     JOIN students s ON u.id = s.user_id
                     JOIN companies c ON p.company_id = c.id
                     WHERE fg.locked = 1
                     ORDER BY fg.placement_id ASC"
                );
                foreach ($rows as $r) {
                    $r['letter_grade'] = \App\Services\GradeEngine::determineLetterGrade((float)$r['total']);
                    fputcsv($out, $r);
                }
                break;

            case 'attendance':
                fputcsv($out, ['Attendance ID', 'Placement ID', 'Student Name', 'Work Date', 'Check In', 'Check Out', 'Hours', 'Arrangement', 'Status', 'Verification', 'Flag Comment']);
                $rows = Database::fetchAll(
                    "SELECT a.id, a.placement_id, u.name as student_name, a.work_date, a.check_in, a.check_out, a.hours,
                            a.work_arrangement, a.status, a.verification, a.flag_comment
                     FROM attendance a
                     JOIN placements p ON a.placement_id = p.id
                     JOIN users u ON p.student_user_id = u.id
                     ORDER BY a.work_date DESC LIMIT 5000"
                );
                foreach ($rows as $r) {
                    fputcsv($out, $r);
                }
                break;

            default:
                fputcsv($out, ['Error: Invalid export table requested.']);
        }

        fclose($out);
        exit;
    }
}
