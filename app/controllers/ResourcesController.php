<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\FileStore;
use App\Core\View;

/**
 * Student Internship Resources Controller
 * Realises FR-IRM-01 (Resource Access, Tabs: Kit, Journey, Guidelines, Announcements)
 * Realises FR-IRM-02 (Document Viewing & Download, grouped by category)
 * Realises FR-IRM-03 (Journey timeline/stepper and Guidelines viewing)
 * Realises FR-IRM-04 (Published Announcements viewing, newest first)
 * Implements Business Rule 9: Resources are visible only when published and do not depend on placement status.
 */
class ResourcesController
{
    // FR-IRM-01, FR-IRM-02, FR-IRM-03, FR-IRM-04: Student Resources Hub
    public function index(): void
    {
        Auth::requireAuth(); // Available to students with or without approved placement (Business Rule 9)

        // 1. Categories and Published Kit Documents
        $categories = Database::fetchAll("SELECT * FROM resource_categories ORDER BY sort_order ASC, name ASC");
        $resources = Database::fetchAll(
            "SELECT r.*, c.name as category_name
             FROM resources r
             JOIN resource_categories c ON r.category_id = c.id
             WHERE r.status = 'published'
             ORDER BY c.sort_order ASC, r.title ASC"
        );

        $resourcesByCategory = [];
        foreach ($resources as $res) {
            $resourcesByCategory[(int)$res['category_id']][] = $res;
        }

        // 2. Published Journey Stages (FR-IRM-03)
        $journeyStages = Database::fetchAll(
            "SELECT * FROM journey_stages WHERE status = 'published' ORDER BY stage_no ASC"
        );

        // 3. Published Guidelines (FR-IRM-03)
        $guidelines = Database::fetchAll(
            "SELECT * FROM guidelines WHERE status = 'published' ORDER BY kind ASC, sort_order ASC, id ASC"
        );
        $guidelinesByKind = [
            'eligibility' => [],
            'procedure' => [],
            'responsibility' => [],
            'instruction' => [],
        ];
        foreach ($guidelines as $g) {
            $guidelinesByKind[$g['kind']][] = $g;
        }

        // 4. Published Announcements (FR-IRM-04)
        $announcements = Database::fetchAll(
            "SELECT a.*, u.name as author_name 
             FROM announcements a
             LEFT JOIN users u ON a.created_by = u.id
             WHERE a.status = 'published'
             ORDER BY COALESCE(a.published_at, a.created_at) DESC"
        );

        $activeTab = $_GET['tab'] ?? 'kit';

        View::render('resources/index', [
            'title' => 'Internship Resources Portal',
            'categories' => $categories,
            'resourcesByCategory' => $resourcesByCategory,
            'journeyStages' => $journeyStages,
            'guidelinesByKind' => $guidelinesByKind,
            'announcements' => $announcements,
            'activeTab' => $activeTab,
        ], 'layouts/main');
    }

    // FR-IRM-02: Download Published Resource
    public function download(): void
    {
        Auth::requireAuth();
        $id = (int) ($_GET['id'] ?? 0);

        $resource = Database::fetchOne(
            "SELECT * FROM resources WHERE id = :id AND status = 'published'",
            ['id' => $id]
        );

        if (!$resource || empty($resource['stored_name'])) {
            http_response_code(404);
            die("Published resource document not found.");
        }

        FileStore::serve(
            $resource['stored_name'],
            $resource['original_name'] ?: 'internship_document.pdf',
            $resource['mime'] ?: 'application/pdf',
            true
        );
    }
}
