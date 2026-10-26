<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Session;
use App\Core\View;
use App\Services\BulkImportService;

/**
 * Controller for Bulk Student Cohort Import (FR-AUTH-03, FR-AUTH-04)
 */
class ImportController
{
    private BulkImportService $importService;

    public function __construct()
    {
        $this->importService = new BulkImportService();
    }

    // FR-AUTH-03: Bulk Cohort Import Wizard Screen
    public function index(): void
    {
        Auth::requireRole('coordinator');

        View::render('import/index', [
            'title' => 'Bulk Student Cohort Import',
        ], 'layouts/main');
    }

    // FR-AUTH-03: Download CSV Template
    public function downloadTemplate(): void
    {
        Auth::requireRole('coordinator');

        $headers = ['student_id', 'name', 'email', 'programme', 'intake'];
        $sampleRow = ['SW01084999', 'Sample Student Name', 'sample.student@student.uniten.edu.my', 'Bachelor of Computer Science (Software Engineering)', '2023/2024-01'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="internflow_cohort_template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        fputcsv($out, $sampleRow);
        fclose($out);
        exit;
    }

    // FR-AUTH-03: Process Uploaded CSV and Display Validation Preview & Error Report
    public function preview(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        if (empty($_FILES['cohort_file']) || $_FILES['cohort_file']['error'] !== UPLOAD_ERR_OK) {
            Flash::danger('Please select a valid CSV cohort file to upload.');
            header('Location: ' . url('import'));
            exit;
        }

        $fileTmp = $_FILES['cohort_file']['tmp_name'];
        $fileName = $_FILES['cohort_file']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($ext !== 'csv') {
            Flash::danger('Invalid file format. Only .csv files are supported for cohort import.');
            header('Location: ' . url('import'));
            exit;
        }

        try {
            $report = $this->importService->previewCsv($fileTmp);
            // Save valid rows in session for confirmation
            Session::set('import_pending_rows', $report['valid_rows']);

            View::render('import/preview', [
                'title' => 'Cohort Import Preview & Validation Report',
                'report' => $report,
                'fileName' => $fileName,
            ], 'layouts/main');
        } catch (\Throwable $e) {
            Flash::danger('Error processing CSV file: ' . $e->getMessage());
            header('Location: ' . url('import'));
            exit;
        }
    }

    // FR-AUTH-03: Confirm and Execute Bulk Import
    public function confirm(): void
    {
        Auth::requireRole('coordinator');
        Csrf::checkOrAbort();

        $validRows = Session::get('import_pending_rows', []);
        if (empty($validRows)) {
            Flash::warning('No pending valid cohort rows to import. Please upload a file first.');
            header('Location: ' . url('import'));
            exit;
        }

        try {
            $result = $this->importService->executeImport($validRows);
            Session::remove('import_pending_rows');

            Flash::success("Cohort import completed successfully! Created {$result['imported_count']} student accounts in {$result['elapsed_seconds']} seconds. Activation emails have been dispatched.");
            header('Location: ' . url('users', ['role' => 'student']));
            exit;
        } catch (\Throwable $e) {
            Flash::danger('Database transaction failed during bulk import: ' . $e->getMessage());
            header('Location: ' . url('import'));
            exit;
        }
    }
}
