<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\FileStore;
use App\Core\Flash;
use App\Core\Validator;
use App\Core\View;

/**
 * Student Profile & Document Management Controller
 * Realises FR-SPD-01, FR-SPD-02, FR-SPD-03 (Eligibility Gate)
 */
class ProfileController
{
    // FR-SPD-01: Profile Overview & Documents Screen
    public function index(): void
    {
        Auth::requireRole('student');
        $user = Auth::user();
        $userId = (int) $user['id'];

        $student = Database::fetchOne(
            "SELECT * FROM students WHERE user_id = :uid",
            ['uid' => $userId]
        );

        $documents = Database::fetchAll(
            "SELECT * FROM documents WHERE student_user_id = :uid ORDER BY created_at DESC",
            ['uid' => $userId]
        );

        $hasCv = false;
        foreach ($documents as $doc) {
            if ($doc['type'] === 'cv') {
                $hasCv = true;
                break;
            }
        }

        View::render('profile/index', [
            'title' => 'My Profile & Documents',
            'user' => $user,
            'student' => $student,
            'documents' => $documents,
            'hasCv' => $hasCv,
        ], 'layouts/main');
    }

    // FR-SPD-01: Update Student Personal Profile Details
    public function update(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];

        $name = trim($_POST['name'] ?? '');
        $cgpa = !empty($_POST['cgpa']) ? (float) $_POST['cgpa'] : null;
        $skills = trim($_POST['skills'] ?? '');
        $interests = trim($_POST['interests'] ?? '');
        $emergency = trim($_POST['emergency_contact'] ?? '');

        if (empty($name)) {
            Flash::danger('Name cannot be empty.');
            header('Location: ' . url('profile'));
            exit;
        }

        Database::transaction(function($pdo) use ($userId, $name, $cgpa, $skills, $interests, $emergency) {
            $pdo->prepare("UPDATE users SET name = ? WHERE id = ?")->execute([$name, $userId]);
            $pdo->prepare("UPDATE students SET cgpa = ?, skills = ?, interests = ?, emergency_contact = ? WHERE user_id = ?")
                ->execute([$cgpa, $skills, $interests, $emergency, $userId]);
        });

        Flash::success('Profile details updated successfully.');
        header('Location: ' . url('profile'));
        exit;
    }

    // FR-SPD-02: Document Upload (CV, Verification Letter, Transcript)
    public function uploadDocument(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];

        $type = trim($_POST['type'] ?? 'other');
        if (!in_array($type, ['cv', 'verification_letter', 'transcript', 'other'], true)) {
            Flash::danger('Invalid document type selected.');
            header('Location: ' . url('profile'));
            exit;
        }

        if (empty($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            Flash::danger('Please choose a file to upload.');
            header('Location: ' . url('profile'));
            exit;
        }

        try {
            $stored = FileStore::store($_FILES['document_file'], 'documents');

            // If CV uploaded, check profile completeness (FR-SPD-03)
            Database::transaction(function($pdo) use ($userId, $type, $stored) {
                // If type is CV, delete previous stored CV record and file if any
                if ($type === 'cv') {
                    $oldCv = Database::fetchOne("SELECT * FROM documents WHERE student_user_id = :uid AND type = 'cv'", ['uid' => $userId]);
                    if ($oldCv) {
                        FileStore::delete($oldCv['stored_name']);
                        $pdo->prepare("DELETE FROM documents WHERE id = ?")->execute([$oldCv['id']]);
                    }
                }

                $stmt = $pdo->prepare(
                    "INSERT INTO documents (student_user_id, type, original_name, stored_name, mime, size, created_at)
                     VALUES (:uid, :type, :orig, :stored, :mime, :size, NOW())"
                );
                $stmt->execute([
                    'uid' => $userId,
                    'type' => $type,
                    'orig' => $stored['original_name'],
                    'stored' => $stored['stored_name'],
                    'mime' => $stored['mime'],
                    'size' => $stored['size'],
                ]);

                // Update profile_complete = 1 if CV is present
                if ($type === 'cv') {
                    $pdo->prepare("UPDATE students SET profile_complete = 1 WHERE user_id = ?")->execute([$userId]);
                }
            });

            Flash::success("Document '{$stored['original_name']}' uploaded successfully.");
        } catch (\Throwable $e) {
            Flash::danger('Upload failed: ' . $e->getMessage());
        }

        header('Location: ' . url('profile'));
        exit;
    }

    // FR-SPD-02 / NFR-PRIV-01: Authenticated Document Download
    public function downloadDocument(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        $docId = (int) ($_GET['id'] ?? 0);

        $doc = Database::fetchOne("SELECT * FROM documents WHERE id = :id", ['id' => $docId]);
        if (!$doc) {
            http_response_code(404);
            die("Document record not found.");
        }

        // Access check: Student owner, or Coordinator, or Company supervisor of that student, or University supervisor
        $isOwner = (int)$doc['student_user_id'] === (int)$user['id'];
        $isCoordinator = $user['role'] === 'coordinator';
        $isSupervisor = false;

        if ($user['role'] === 'university_supervisor') {
            $assigned = Database::fetchValue(
                "SELECT COUNT(*) FROM placements WHERE student_user_id = :st_uid AND university_supervisor_id = :sup_uid",
                ['st_uid' => $doc['student_user_id'], 'sup_uid' => $user['id']]
            );
            $isSupervisor = ((int)$assigned > 0);
        }

        if ($user['role'] === 'company') {
            // Check if applicant or active intern
            $isCompApplicant = Database::fetchValue(
                "SELECT COUNT(*) FROM applications a
                 JOIN vacancies v ON a.vacancy_id = v.id
                 JOIN company_users cu ON v.company_id = cu.company_id
                 WHERE a.student_user_id = :st_uid AND cu.user_id = :cuid",
                ['st_uid' => $doc['student_user_id'], 'cuid' => $user['id']]
            );
            $isSupervisor = ((int)$isCompApplicant > 0);
        }

        if (!$isOwner && !$isCoordinator && !$isSupervisor) {
            Auth::abortForbidden("You are not authorized to download this student document.");
        }

        FileStore::serve($doc['stored_name'], $doc['original_name'], $doc['mime'], true);
    }

    // FR-SPD-02: Delete Document
    public function deleteDocument(): void
    {
        Auth::requireRole('student');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $userId = (int) $user['id'];
        $docId = (int) ($_POST['document_id'] ?? 0);

        $doc = Database::fetchOne(
            "SELECT * FROM documents WHERE id = :id AND student_user_id = :uid",
            ['id' => $docId, 'uid' => $userId]
        );

        if (!$doc) {
            Flash::danger('Document not found or unauthorized.');
            header('Location: ' . url('profile'));
            exit;
        }

        FileStore::delete($doc['stored_name']);
        Database::execute("DELETE FROM documents WHERE id = :id", ['id' => $docId]);

        // If deleted CV, recheck profile completeness
        if ($doc['type'] === 'cv') {
            $hasAnotherCv = Database::fetchValue(
                "SELECT COUNT(*) FROM documents WHERE student_user_id = :uid AND type = 'cv'",
                ['uid' => $userId]
            );
            if (!$hasAnotherCv) {
                Database::execute("UPDATE students SET profile_complete = 0 WHERE user_id = :uid", ['uid' => $userId]);
            }
        }

        Flash::success('Document removed.');
        header('Location: ' . url('profile'));
        exit;
    }
}
