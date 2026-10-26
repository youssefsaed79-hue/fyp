<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Batch Attendance Verification Service (FR-ATT-02, Assumption A4)
 * Allows host company supervisors to approve attendance records in ONE batch action,
 * or flag specific days with individual comments.
 */
class BatchVerificationService
{
    /**
     * Execute batch verification on attendance records
     *
     * @param int $verifierId Company supervisor user ID
     * @param int $placementId Placement ID
     * @param array $approvedRecordIds Array of record IDs to verify
     * @param array $flaggedData Array of [record_id => flag_comment]
     * @return int Number of verified records
     */
    public function processBatch(int $verifierId, int $placementId, array $approvedRecordIds, array $flaggedData = []): int
    {
        $verifiedCount = 0;

        Database::transaction(function($pdo) use ($verifierId, $placementId, $approvedRecordIds, $flaggedData, &$verifiedCount) {
            $stmtApprove = $pdo->prepare(
                "UPDATE attendance
                 SET verification = 'verified', flag_comment = NULL, verified_by = :vby, verified_at = NOW()
                 WHERE id = :id AND placement_id = :pid"
            );

            $stmtFlag = $pdo->prepare(
                "UPDATE attendance
                 SET verification = 'flagged', flag_comment = :comment, verified_by = :vby, verified_at = NOW()
                 WHERE id = :id AND placement_id = :pid"
            );

            // 1. Process flagged records first
            foreach ($flaggedData as $id => $comment) {
                $recId = (int) $id;
                $comment = trim((string) $comment);
                if ($comment !== '') {
                    $stmtFlag->execute([
                        'comment' => $comment,
                        'vby' => $verifierId,
                        'id' => $recId,
                        'pid' => $placementId,
                    ]);
                }
            }

            // 2. Process batch approval for unflagged selected records
            foreach ($approvedRecordIds as $id) {
                $recId = (int) $id;
                // Only approve if not flagged
                if (empty($flaggedData[$recId]) || trim((string)$flaggedData[$recId]) === '') {
                    $stmtApprove->execute([
                        'vby' => $verifierId,
                        'id' => $recId,
                        'pid' => $placementId,
                    ]);
                    $verifiedCount++;
                }
            }
        });

        // 3. Trigger 80% rule evaluation & automated warning check (FR-ATT-03)
        AttendanceRule::checkAndAlert($placementId);

        return $verifiedCount;
    }
}
