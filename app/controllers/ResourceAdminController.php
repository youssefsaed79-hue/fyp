<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\FileStore;
use App\Core\Flash;
use App\Core\Notifier;
use App\Core\View;

/**
 * Coordinator Internship Resources Management Controller
 * Realises FR-IRM-05 (Resource Upload & Management, Journey & Guidelines CRUD)
 * Realises FR-IRM-06 (Announcement Management, In-App Student Notifications)
 * Realises FR-IRM-07 (Resource Categorisation, Versioning, Publish/Unpublish)
 */
class ResourceAdminController
{
    // FR-IRM-05, FR-IRM-07: Coordinator Resources Admin Dashboard
    public function index(): void
    {
        Auth::requireRole('coordinator');

        $categories = Database::fetchAll("SELECT * FROM resource_categories ORDER BY sort_order ASC, name ASC");
        
        $resources = Database::fetchAll(
            "SELECT r.*, c.name as category_name
             FROM resources r
             JOIN resource_categories c ON r.category_id = c.id
             ORDER BY c.sort_order ASC, r.title ASC"
        );

        $journeyStages = Database::fetchAll("SELECT * FROM journey_stages ORDER BY stage_no ASC");
        $guidelines = Database::fetchAll("SELECT * FROM guidelines ORDER BY kind ASC, sort_order ASC");

        View::render('resource_admin/index', [
            'title' => 'Internship Resources Administration',
            'categories' => $categories,
            'resources' => $resources,
            'journeyStages' => $journeyStages,
            'guidelines' => $guidelines,
        ], 'layouts/main');
    }

    // FR-IRM-07: Store Category
    public function storeCategory(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $name = trim($_POST['name'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if (empty($name)) {
            Flash::danger('Category name is required.');
            header('Location: ' . url('resource-admin'));
            exit;
        }

        Database::query(
            "INSERT INTO resource_categories (name, sort_order, created_at, updated_at)
             VALUES (:name, :sort, NOW(), NOW())",
            ['name' => $name, 'sort' => $sortOrder]
        );

        Flash::success("Resource category '{$name}' created successfully (FR-IRM-07).");
        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-07: Delete Category
    public function deleteCategory(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $id = (int) ($_POST['id'] ?? 0);
        $count = (int) Database::fetchValue("SELECT COUNT(*) FROM resources WHERE category_id = :id", ['id' => $id]);
        if ($count > 0) {
            Flash::danger("Cannot delete category containing {$count} resource documents. Remove or move documents first.");
            header('Location: ' . url('resource-admin'));
            exit;
        }

        Database::query("DELETE FROM resource_categories WHERE id = :id", ['id' => $id]);
        Flash::success('Category removed successfully.');
        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-05 & FR-IRM-07: Store or Replace Resource Document
    public function storeResource(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $id = (int) ($_POST['id'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'published';

        if (empty($title) || $categoryId <= 0) {
            Flash::danger('Title and Category are required.');
            header('Location: ' . url('resource-admin'));
            exit;
        }

        if ($id > 0) {
            // Updating existing resource / replacing file (FR-IRM-07 version increment)
            $existing = Database::fetchOne("SELECT * FROM resources WHERE id = :id", ['id' => $id]);
            if (!$existing) {
                Flash::danger('Resource not found.');
                header('Location: ' . url('resource-admin'));
                exit;
            }

            $version = (int)$existing['version'];
            $storedName = $existing['stored_name'];
            $origName = $existing['original_name'];
            $mime = $existing['mime'];
            $size = $existing['size'];

            if (!empty($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
                try {
                    $stored = FileStore::store($_FILES['resource_file'], 'resources');
                    $storedName = $stored['stored_name'];
                    $origName = $stored['original_name'];
                    $mime = $stored['mime'];
                    $size = $stored['size'];
                    $version += 1; // Increment version on file replacement (FR-IRM-07)
                } catch (\Throwable $e) {
                    Flash::danger('File upload failed: ' . $e->getMessage());
                    header('Location: ' . url('resource-admin'));
                    exit;
                }
            }

            Database::query(
                "UPDATE resources SET 
                    category_id = :cid,
                    title = :title,
                    description = :desc,
                    stored_name = :sname,
                    original_name = :oname,
                    mime = :mime,
                    size = :size,
                    version = :ver,
                    status = :status,
                    published_at = CASE WHEN :status = 'published' AND published_at IS NULL THEN NOW() ELSE published_at END,
                    updated_at = NOW()
                 WHERE id = :id",
                [
                    'cid' => $categoryId,
                    'title' => $title,
                    'desc' => $description,
                    'sname' => $storedName,
                    'oname' => $origName,
                    'mime' => $mime,
                    'size' => $size,
                    'ver' => $version,
                    'status' => $status,
                    'id' => $id,
                ]
            );

            Flash::success("Resource '{$title}' updated successfully (Version {$version}).");
        } else {
            // New Resource Upload
            if (empty($_FILES['resource_file']) || $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
                Flash::danger('Please choose a valid document file (PDF, DOCX, PPTX).');
                header('Location: ' . url('resource-admin'));
                exit;
            }

            try {
                $stored = FileStore::store($_FILES['resource_file'], 'resources');
            } catch (\Throwable $e) {
                Flash::danger('File upload failed: ' . $e->getMessage());
                header('Location: ' . url('resource-admin'));
                exit;
            }

            Database::query(
                "INSERT INTO resources 
                    (category_id, title, description, stored_name, original_name, mime, size, version, status, published_at, created_by, created_at, updated_at)
                 VALUES 
                    (:cid, :title, :desc, :sname, :oname, :mime, :size, 1, :status, 
                     CASE WHEN :status = 'published' THEN NOW() ELSE NULL END, :uid, NOW(), NOW())",
                [
                    'cid' => $categoryId,
                    'title' => $title,
                    'desc' => $description,
                    'sname' => $stored['stored_name'],
                    'oname' => $stored['original_name'],
                    'mime' => $stored['mime'],
                    'size' => $stored['size'],
                    'status' => $status,
                    'uid' => $user['id'],
                ]
            );

            Flash::success("New internship document '{$title}' uploaded and saved (FR-IRM-05).");
        }

        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-07: Toggle Resource Published Status
    public function toggleResource(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $id = (int) ($_POST['id'] ?? 0);
        $res = Database::fetchOne("SELECT * FROM resources WHERE id = :id", ['id' => $id]);
        if (!$res) {
            Flash::danger('Resource not found.');
            header('Location: ' . url('resource-admin'));
            exit;
        }

        $newStatus = ($res['status'] === 'published') ? 'unpublished' : 'published';
        Database::query(
            "UPDATE resources SET status = :st, published_at = CASE WHEN :st = 'published' THEN NOW() ELSE published_at END, updated_at = NOW() WHERE id = :id",
            ['st' => $newStatus, 'id' => $id]
        );

        Flash::success("Resource '{$res['title']}' is now {$newStatus}.");
        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-05: Delete Resource
    public function deleteResource(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $id = (int) ($_POST['id'] ?? 0);
        $res = Database::fetchOne("SELECT * FROM resources WHERE id = :id", ['id' => $id]);
        if ($res) {
            FileStore::delete($res['stored_name']);
            Database::query("DELETE FROM resources WHERE id = :id", ['id' => $id]);
            Flash::success("Resource '{$res['title']}' deleted.");
        }

        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-05: Store Journey Stage
    public function storeJourneyStage(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $stageNo = (int) ($_POST['stage_no'] ?? 1);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'published';

        if (empty($title)) {
            Flash::danger('Journey stage title is required.');
            header('Location: ' . url('resource-admin'));
            exit;
        }

        Database::query(
            "INSERT INTO journey_stages (stage_no, title, description, status, created_at, updated_at)
             VALUES (:stage_no, :title, :desc, :status, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                description = VALUES(description),
                status = VALUES(status),
                updated_at = NOW()",
            [
                'stage_no' => $stageNo,
                'title' => $title,
                'desc' => $description,
                'status' => $status,
            ]
        );

        Flash::success("Journey Stage {$stageNo} saved successfully (FR-IRM-05).");
        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-05: Delete Journey Stage
    public function deleteJourneyStage(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $id = (int) ($_POST['id'] ?? 0);
        Database::query("DELETE FROM journey_stages WHERE id = :id", ['id' => $id]);
        Flash::success('Journey stage removed.');
        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-05: Store Guideline
    public function storeGuideline(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $kind = $_POST['kind'] ?? 'eligibility';
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'published';

        if (empty($title) || empty($body)) {
            Flash::danger('Guideline title and content body are required.');
            header('Location: ' . url('resource-admin'));
            exit;
        }

        Database::query(
            "INSERT INTO guidelines (kind, title, body, sort_order, status, created_at, updated_at)
             VALUES (:kind, :title, :body, :sort, :status, NOW(), NOW())",
            [
                'kind' => $kind,
                'title' => $title,
                'body' => $body,
                'sort' => $sortOrder,
                'status' => $status,
            ]
        );

        Flash::success("Guideline '{$title}' ({$kind}) added successfully (FR-IRM-05).");
        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-05: Delete Guideline
    public function deleteGuideline(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $id = (int) ($_POST['id'] ?? 0);
        Database::query("DELETE FROM guidelines WHERE id = :id", ['id' => $id]);
        Flash::success('Guideline removed.');
        header('Location: ' . url('resource-admin'));
        exit;
    }

    // FR-IRM-06: Announcements Management Screen
    public function announcements(): void
    {
        Auth::requireRole('coordinator');

        $announcements = Database::fetchAll(
            "SELECT a.*, u.name as author_name 
             FROM announcements a
             LEFT JOIN users u ON a.created_by = u.id
             ORDER BY a.id DESC"
        );

        View::render('resource_admin/announcements', [
            'title' => 'Announcements Management',
            'announcements' => $announcements,
        ], 'layouts/main');
    }

    // FR-IRM-06: Store Announcement (Sends in-app notifications if published)
    public function storeAnnouncement(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $user = Auth::user();
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $deadlineDate = !empty($_POST['deadline_date']) ? $_POST['deadline_date'] : null;
        $status = $_POST['status'] ?? 'published';

        if (empty($title) || empty($body)) {
            Flash::danger('Announcement title and body are required.');
            header('Location: ' . url('resource-admin/announcements'));
            exit;
        }

        Database::transaction(function($pdo) use ($title, $body, $deadlineDate, $status, $user) {
            $stmt = $pdo->prepare(
                "INSERT INTO announcements (title, body, deadline_date, status, published_at, created_by, created_at, updated_at)
                 VALUES (:title, :body, :deadline, :status, CASE WHEN :status = 'published' THEN NOW() ELSE NULL END, :uid, NOW(), NOW())"
            );
            $stmt->execute([
                'title' => $title,
                'body' => $body,
                'deadline' => $deadlineDate,
                'status' => $status,
                'uid' => $user['id'],
            ]);

            // If published, notify all active student users (FR-IRM-06)
            if ($status === 'published') {
                $students = Database::fetchAll("SELECT id FROM users WHERE role = 'student' AND status = 'active'");
                foreach ($students as $s) {
                    Notifier::send(
                        (int)$s['id'],
                        'announcement',
                        "New Announcement: {$title}",
                        substr(strip_tags($body), 0, 160) . '...',
                        '/resources?tab=announcements'
                    );
                }
            }
        });

        Flash::success("Announcement '{$title}' saved successfully! In-app notification sent to students (FR-IRM-06).");
        header('Location: ' . url('resource-admin/announcements'));
        exit;
    }

    // FR-IRM-06: Toggle Announcement Status
    public function toggleAnnouncement(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $id = (int) ($_POST['id'] ?? 0);
        $ann = Database::fetchOne("SELECT * FROM announcements WHERE id = :id", ['id' => $id]);
        if ($ann) {
            $newStatus = ($ann['status'] === 'published') ? 'draft' : 'published';
            Database::query(
                "UPDATE announcements SET status = :st, published_at = CASE WHEN :st = 'published' THEN NOW() ELSE published_at END, updated_at = NOW() WHERE id = :id",
                ['st' => $newStatus, 'id' => $id]
            );
            Flash::success("Announcement status updated to {$newStatus}.");
        }

        header('Location: ' . url('resource-admin/announcements'));
        exit;
    }

    // FR-IRM-06: Delete Announcement
    public function deleteAnnouncement(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $id = (int) ($_POST['id'] ?? 0);
        Database::query("DELETE FROM announcements WHERE id = :id", ['id' => $id]);
        Flash::success('Announcement removed.');
        header('Location: ' . url('resource-admin/announcements'));
        exit;
    }
}
