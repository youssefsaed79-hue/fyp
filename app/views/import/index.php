<div class="page-header">
    <div>
        <h1>Bulk Student Cohort Import</h1>
        <p>Auto-provision verified student accounts from CSV file (FR-AUTH-03, NFR-PERF-03)</p>
    </div>
    <div>
        <a href="<?= url('import/template') ?>" class="btn btn-secondary">
            📄 Download CSV Template (.csv)
        </a>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Step 1: Upload Cohort File</h3>
    </div>

    <div class="card-bluegray" style="margin-bottom: 1.5rem;">
        <h4 style="color: var(--c-forest-dark); margin-bottom: 0.5rem;">Cohort File Specifications</h4>
        <ul style="padding-left: 1.25rem; font-size: 0.88rem; color: var(--c-text-primary); line-height: 1.6;">
            <li>File format must be standard <strong>Comma-Separated Values (.csv)</strong>.</li>
            <li>Required columns: <code>student_id</code>, <code>name</code>, <code>email</code>, <code>programme</code>, <code>intake</code>.</li>
            <li>Each row is validated for missing fields, valid email syntax, and duplicates inside the file &amp; database.</li>
            <li><strong>Error Prevention:</strong> Rows containing errors are isolated into an error report; valid rows are previewed before confirmation.</li>
            <li>Accounts created will be automatically marked as <strong>verified</strong> with temporary passwords dispatched via email.</li>
        </ul>
    </div>

    <form action="<?= url('import/preview') ?>" method="POST" enctype="multipart/form-data" data-validate>
        <?= \App\Core\Csrf::field() ?>

        <div class="form-group" style="max-width: 600px;">
            <label for="cohort_file" class="form-label">Select Cohort CSV File</label>
            <input type="file" id="cohort_file" name="cohort_file" class="form-control" accept=".csv" required>
            <div class="form-help">Maximum upload size: 10 MB. Need a demo file? Use <code>database/sample_cohort.csv</code>.</div>
        </div>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                Upload &amp; Preview Validation Report &rarr;
            </button>
        </div>
    </form>
</div>
