<?php
declare(strict_types=1);

namespace App\Services;

require_once dirname(__DIR__) . '/core/helpers.php';

use App\Core\Database;
use App\Core\Mailer;

/**
 * Bulk Student Cohort Import Service (FR-AUTH-03, NFR-PERF-03)
 * Processes CSV using built-in fgetcsv, validates rows against missing fields,
 * regex email format, duplicates within file, and duplicates in database.
 * Auto-provisions verified student accounts and dispatches activation emails.
 */
class BulkImportService
{
    /**
     * Parse and validate CSV file, returning preview and error report
     */
    public function previewCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Uploaded file not found.");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Unable to open CSV file for reading.");
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new \RuntimeException("CSV file is empty.");
        }

        // Normalize header
        $cleanHeader = array_map(function ($col) {
            return strtolower(trim(preg_replace('/[\x{FEFF}]/u', '', (string)$col)));
        }, $header);

        $expectedCols = ['student_id', 'name', 'email', 'programme', 'intake'];
        $colMap = [];
        foreach ($expectedCols as $col) {
            $idx = array_search($col, $cleanHeader, true);
            if ($idx === false) {
                // Try fuzzy match
                foreach ($cleanHeader as $k => $h) {
                    if (str_contains($h, str_replace('_', '', $col)) || str_contains($h, $col)) {
                        $idx = $k;
                        break;
                    }
                }
            }
            if ($idx === false) {
                fclose($handle);
                throw new \RuntimeException("Missing required CSV header column: '{$col}'. Expected: " . implode(', ', $expectedCols));
            }
            $colMap[$col] = $idx;
        }

        $validRows = [];
        $rejectedRows = [];
        $seenStudentIds = [];
        $seenEmails = [];
        $rowNumber = 1; // 1 was header

        // Fetch existing DB student IDs and emails in one query for fast lookup (1000 rows in <60s)
        $existingStudents = Database::fetchAll("SELECT student_id FROM students");
        $existingStudentIds = array_column($existingStudents, 'student_id');
        $dbStudentIds = array_fill_keys(array_map('strtoupper', array_map('trim', $existingStudentIds)), true);

        $existingUsers = Database::fetchAll("SELECT email FROM users");
        $existingEmails = array_column($existingUsers, 'email');
        $dbEmails = array_fill_keys(array_map('strtolower', array_map('trim', $existingEmails)), true);

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;
            // Skip empty rows
            if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $studentId = trim((string)($data[$colMap['student_id']] ?? ''));
            $name = trim((string)($data[$colMap['name']] ?? ''));
            $email = trim((string)($data[$colMap['email']] ?? ''));
            $programme = trim((string)($data[$colMap['programme']] ?? ''));
            $intake = trim((string)($data[$colMap['intake']] ?? ''));

            $errors = [];

            // 1. Check missing fields
            if ($studentId === '') {
                $errors[] = "Missing Student ID";
            }
            if ($name === '') {
                $errors[] = "Missing Name";
            }
            if ($email === '') {
                $errors[] = "Missing Email";
            }
            if ($programme === '') {
                $errors[] = "Missing Programme";
            }
            if ($intake === '') {
                $errors[] = "Missing Intake";
            }

            // 2. Validate email format
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format: '{$email}'";
            }

            // 3. Check duplicate in file
            $sidKey = strtoupper($studentId);
            $emailKey = strtolower($email);

            if ($studentId !== '' && isset($seenStudentIds[$sidKey])) {
                $errors[] = "Duplicate Student ID '{$studentId}' inside this CSV (first seen at row {$seenStudentIds[$sidKey]})";
            }
            if ($email !== '' && isset($seenEmails[$emailKey])) {
                $errors[] = "Duplicate Email '{$email}' inside this CSV (first seen at row {$seenEmails[$emailKey]})";
            }

            // 4. Check duplicate in database
            if ($studentId !== '' && isset($dbStudentIds[$sidKey])) {
                $errors[] = "Student ID '{$studentId}' already exists in database";
            }
            if ($email !== '' && isset($dbEmails[$emailKey])) {
                $errors[] = "Email '{$email}' already registered in database";
            }

            $record = [
                'row' => $rowNumber,
                'student_id' => $studentId,
                'name' => $name,
                'email' => $email,
                'programme' => $programme,
                'intake' => $intake,
            ];

            if (!empty($errors)) {
                $record['errors'] = $errors;
                $rejectedRows[] = $record;
            } else {
                $seenStudentIds[$sidKey] = $rowNumber;
                $seenEmails[$emailKey] = $rowNumber;
                $validRows[] = $record;
            }
        }

        fclose($handle);

        return [
            'total_processed' => count($validRows) + count($rejectedRows),
            'valid_count' => count($validRows),
            'rejected_count' => count($rejectedRows),
            'valid_rows' => $validRows,
            'rejected_rows' => $rejectedRows,
        ];
    }

    /**
     * Execute cohort provisioning inside a transaction (FR-AUTH-03)
     */
    public function executeImport(array $validRows): array
    {
        $startTime = microtime(true);
        $importedCount = 0;

        Database::transaction(function ($pdo) use ($validRows, &$importedCount) {
            $stmtUser = $pdo->prepare(
                "INSERT INTO users (role, name, email, password_hash, must_change_password, status, created_at)
                 VALUES ('student', :name, :email, :pw, 1, 'active', NOW())"
            );

            $stmtStudent = $pdo->prepare(
                "INSERT INTO students (user_id, student_id, programme, intake, verified, profile_complete, created_at)
                 VALUES (:uid, :sid, :prog, :intake, 1, 0, NOW())"
            );

            foreach ($validRows as $row) {
                // Generate secure random temporary password (e.g. Uniten#2026! or random string)
                $tempPassword = 'Temp#' . bin2hex(random_bytes(3)) . '!';
                $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);

                $stmtUser->execute([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'pw' => $passwordHash,
                ]);
                $userId = (int) $pdo->lastInsertId();

                $stmtStudent->execute([
                    'uid' => $userId,
                    'sid' => $row['student_id'],
                    'prog' => $row['programme'],
                    'intake' => $row['intake'],
                ]);

                // Send activation email with temporary password & login link (FR-AUTH-03, FR-AUTH-05)
                $loginUrl = url('login');
                $emailSubject = "Welcome to InternFlow - UNITEN CCI Industrial Training Account";
                $emailBody = "<h3>Dear {$row['name']},</h3>
                    <p>Your student account for the UNITEN CCI Industrial Training platform has been created.</p>
                    <p><strong>Your Login Credentials:</strong><br>
                    Email: <code>{$row['email']}</code><br>
                    Temporary Password: <code>{$tempPassword}</code></p>
                    <p><a href='{$loginUrl}'>Click here to Sign In and Set Your New Password</a></p>
                    <p>Upon your first sign-in, you will be required to create your personal password and complete your student profile by uploading your CV.</p>
                    <hr><p><small>UNITEN College of Computing &amp; Informatics</small></p>";

                Mailer::send($row['email'], $emailSubject, $emailBody);
                $importedCount++;
            }
        });

        $elapsedSeconds = round(microtime(true) - $startTime, 2);

        return [
            'imported_count' => $importedCount,
            'elapsed_seconds' => $elapsedSeconds,
        ];
    }
}
