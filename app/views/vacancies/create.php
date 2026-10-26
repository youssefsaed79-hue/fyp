<div class="page-header">
    <div>
        <h1>Post New Internship Vacancy</h1>
        <p>Define position duties, candidate requirements, allowance, and application deadline (FR-ILM-01)</p>
    </div>
    <div>
        <a href="<?= url('vacancies/manage') ?>" class="btn btn-secondary">&larr; Back to Listings</a>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <form action="<?= url('vacancies/store') ?>" method="POST" data-validate>
        <?= \App\Core\Csrf::field() ?>

        <div class="form-group">
            <label for="title" class="form-label">Position / Job Title</label>
            <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Software Engineering Intern (Web &amp; Cloud)">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="slots" class="form-label">Available Slots</label>
                <input type="number" id="slots" name="slots" class="form-control" value="2" min="1" required>
            </div>

            <div class="form-group">
                <label for="allowance" class="form-label">Monthly Allowance (RM)</label>
                <input type="number" step="0.01" id="allowance" name="allowance" class="form-control" value="1000.00" required>
            </div>

            <div class="form-group">
                <label for="working_arrangement" class="form-label">Working Arrangement</label>
                <select name="working_arrangement" id="working_arrangement" class="form-select">
                    <option value="onsite">On-site</option>
                    <option value="hybrid" selected>Hybrid</option>
                    <option value="remote">Remote</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="location" class="form-label">Office Location / City</label>
                <input type="text" id="location" name="location" class="form-control" required placeholder="e.g. Bangsar, Kuala Lumpur / Cyberjaya">
            </div>

            <div class="form-group">
                <label for="deadline" class="form-label">Application Deadline</label>
                <input type="date" id="deadline" name="deadline" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="duties" class="form-label">Key Duties &amp; Daily Responsibilities</label>
            <textarea id="duties" name="duties" class="form-control" rows="5" required placeholder="Describe the day-to-day responsibilities, projects, and learning objectives..."></textarea>
        </div>

        <div class="form-group">
            <label for="requirements" class="form-label">Candidate Requirements &amp; Technical Skills</label>
            <textarea id="requirements" name="requirements" class="form-control" rows="4" required placeholder="Specify preferred academic programme, programming languages, tools, and minimum CGPA..."></textarea>
        </div>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" name="publish" value="1" class="btn btn-primary" style="padding: 0.7rem 1.5rem;">
                Publish Listing Immediately &rarr;
            </button>
            <button type="submit" name="draft" value="1" class="btn btn-secondary">
                Save as Draft
            </button>
            <a href="<?= url('vacancies/manage') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
