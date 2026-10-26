<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Notifier;

/**
 * GradeEngine Service
 * Realises FR-EVAL-03 (Weighted Final Grade Combination, Configurable Weights, Grade Locking)
 * Implements Business Rule 8: The final grade is calculated only when all four assessment components are available.
 * Implements Table 3.8: Host Supervisor 40%, Visiting Lecturer 30%, Final Report 20%, Logbook 10%.
 */
class GradeEngine
{
    /**
     * Compute assessment components and weighted total for a placement.
     */
    public static function calculateForPlacement(int $placementId): array
    {
        // 1. Fetch configurable weights from settings
        $settingsRows = Database::fetchAll("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = (float)$row['setting_value'];
        }

        $wHost = $settings['weight_host'] ?? 40.0;
        $wVisiting = $settings['weight_visiting'] ?? 30.0;
        $wReport = $settings['weight_report'] ?? 20.0;
        $wLogbook = $settings['weight_logbook'] ?? 10.0;

        // 2. Component 1: Host Supervisor Evaluation (Average of submitted host evaluations)
        $hostEvals = Database::fetchAll(
            "SELECT total_pct, type FROM evaluations 
             WHERE placement_id = :pid AND type IN ('host_monthly', 'host_final')",
            ['pid' => $placementId]
        );
        $hostScore = null;
        if (!empty($hostEvals)) {
            $sum = 0.0;
            foreach ($hostEvals as $ev) {
                $sum += (float)$ev['total_pct'];
            }
            $hostScore = round($sum / count($hostEvals), 2);
        }

        // 3. Component 2: Visiting Lecturer Assessment
        $visitingEval = Database::fetchOne(
            "SELECT total_pct FROM evaluations 
             WHERE placement_id = :pid AND type = 'visiting_lecturer'",
            ['pid' => $placementId]
        );
        $visitingScore = ($visitingEval && $visitingEval['total_pct'] !== null)
            ? round((float)$visitingEval['total_pct'], 2)
            : null;

        // 4. Component 3: Industrial Training Final Report
        $finalReport = Database::fetchOne(
            "SELECT mark FROM final_reports WHERE placement_id = :pid",
            ['pid' => $placementId]
        );
        $reportScore = ($finalReport && $finalReport['mark'] !== null)
            ? round((float)$finalReport['mark'], 2)
            : null;

        // 5. Component 4: Logbook Component (Average of weekly logbook grades)
        $logbookRows = Database::fetchAll(
            "SELECT grade FROM logbooks WHERE placement_id = :pid AND grade IS NOT NULL",
            ['pid' => $placementId]
        );
        $logbookScore = null;
        if (!empty($logbookRows)) {
            $sumLog = 0.0;
            foreach ($logbookRows as $lb) {
                $sumLog += (float)$lb['grade'];
            }
            $logbookScore = round($sumLog / count($logbookRows), 2);
        }

        // Check if all four components exist (Business Rule 8)
        $allReady = ($hostScore !== null && $visitingScore !== null && $reportScore !== null && $logbookScore !== null);

        $weightedTotal = null;
        $letterGrade = null;
        if ($allReady) {
            $weightedTotal = round(
                ($hostScore * ($wHost / 100.0)) +
                ($visitingScore * ($wVisiting / 100.0)) +
                ($reportScore * ($wReport / 100.0)) +
                ($logbookScore * ($wLogbook / 100.0)),
                2
            );
            $letterGrade = self::determineLetterGrade($weightedTotal);
        }

        // Check existing locked final grade record
        $lockedRecord = Database::fetchOne(
            "SELECT fg.*, u.name as locked_by_name 
             FROM final_grades fg
             LEFT JOIN users u ON fg.locked_by = u.id
             WHERE fg.placement_id = :pid",
            ['pid' => $placementId]
        );

        $finalTotal = $lockedRecord ? (float)$lockedRecord['total'] : $weightedTotal;
        if ($finalTotal !== null && $letterGrade === null) {
            $letterGrade = self::determineLetterGrade($finalTotal);
        }

        return [
            'placement_id' => $placementId,
            'weights' => [
                'host' => $wHost,
                'visiting' => $wVisiting,
                'report' => $wReport,
                'logbook' => $wLogbook,
            ],
            'scores' => [
                'host' => $hostScore,
                'visiting' => $visitingScore,
                'report' => $reportScore,
                'logbook' => $logbookScore,
            ],
            'weighted_components' => [
                'host' => $hostScore !== null ? round($hostScore * ($wHost / 100.0), 2) : null,
                'visiting' => $visitingScore !== null ? round($visitingScore * ($wVisiting / 100.0), 2) : null,
                'report' => $reportScore !== null ? round($reportScore * ($wReport / 100.0), 2) : null,
                'logbook' => $logbookScore !== null ? round($logbookScore * ($wLogbook / 100.0), 2) : null,
            ],
            'all_ready' => $allReady,
            'total' => $finalTotal,
            'letter_grade' => $letterGrade,
            'is_locked' => !empty($lockedRecord['locked']),
            'locked_record' => $lockedRecord,
        ];
    }

    /**
     * Lock the final grade for a placement (Coordinator action, FR-EVAL-03).
     */
    public static function lockGrade(int $placementId, int $coordinatorUserId): bool
    {
        $calc = self::calculateForPlacement($placementId);
        if (!$calc['all_ready']) {
            return false;
        }

        Database::transaction(function($pdo) use ($placementId, $coordinatorUserId, $calc) {
            $stmt = $pdo->prepare(
                "INSERT INTO final_grades 
                    (placement_id, host_score, visiting_score, report_score, logbook_score, total, locked, locked_by, locked_at, created_at, updated_at)
                 VALUES 
                    (:pid, :host, :visiting, :report, :logbook, :total, 1, :locked_by, NOW(), NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    host_score = VALUES(host_score),
                    visiting_score = VALUES(visiting_score),
                    report_score = VALUES(report_score),
                    logbook_score = VALUES(logbook_score),
                    total = VALUES(total),
                    locked = 1,
                    locked_by = VALUES(locked_by),
                    locked_at = NOW(),
                    updated_at = NOW()"
            );

            $stmt->execute([
                'pid' => $placementId,
                'host' => $calc['scores']['host'],
                'visiting' => $calc['scores']['visiting'],
                'report' => $calc['scores']['report'],
                'logbook' => $calc['scores']['logbook'],
                'total' => $calc['total'],
                'locked_by' => $coordinatorUserId,
            ]);

            // Mark placement status as 'completed'
            $pdo->prepare("UPDATE placements SET status = 'completed', updated_at = NOW() WHERE id = :pid")
                ->execute(['pid' => $placementId]);

            // Notify student of finalized grade
            $placement = Database::fetchOne("SELECT student_user_id FROM placements WHERE id = :pid", ['pid' => $placementId]);
            if ($placement) {
                Notifier::send(
                    (int)$placement['student_user_id'],
                    'grade_locked',
                    'Official Internship Grade Finalized',
                    "Your final internship assessment has been verified and locked by the University Coordinator with a score of {$calc['total']}%.",
                    '/evaluations/my-grade'
                );
            }
        });

        return true;
    }

    /**
     * Standard UNITEN academic grading scale.
     */
    public static function determineLetterGrade(float $score): string
    {
        if ($score >= 80.0) return 'A';
        if ($score >= 75.0) return 'A-';
        if ($score >= 70.0) return 'B+';
        if ($score >= 65.0) return 'B';
        if ($score >= 60.0) return 'B-';
        if ($score >= 55.0) return 'C+';
        if ($score >= 50.0) return 'C';
        if ($score >= 40.0) return 'D';
        return 'F';
    }
}
