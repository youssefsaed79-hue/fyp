<div class="page-header">
    <div>
        <h1>Create New User Account</h1>
        <p>Coordinator-managed account creation (FR-AUTH-04)</p>
    </div>
    <div>
        <a href="<?= url('users') ?>" class="btn btn-secondary">&larr; Back to Users</a>
    </div>
</div>

<div class="card" style="max-width: 750px;">
    <form action="<?= url('users/store') ?>" method="POST" data-validate>
        <?= \App\Core\Csrf::field() ?>

        <div class="form-group">
            <label for="role" class="form-label">User Role</label>
            <select name="role" id="role" class="form-select" onchange="toggleStudentFields(this.value)">
                <option value="student" <?= ($role ?? '') === 'student' ? 'selected' : '' ?>>Student (Manual Exception)</option>
                <option value="university_supervisor" <?= ($role ?? '') === 'university_supervisor' ? 'selected' : '' ?>>University Supervisor (Visiting Lecturer)</option>
                <option value="coordinator" <?= ($role ?? '') === 'coordinator' ? 'selected' : '' ?>>Coordinator (Admin)</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Dr. John Doe / Student Name">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="e.g. user@uniten.edu.my">
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Initial Password</label>
            <input type="password" id="password" name="password" class="form-control" value="Password@123" required minlength="8">
            <div class="form-help">Default: <code>Password@123</code> (user will be forced to change it on first login).</div>
        </div>

        <!-- Student Specific Fields -->
        <div id="student_fields" style="border-top: 1px solid var(--c-border); padding-top: 1.25rem; margin-top: 1rem;">
            <h4 style="margin-bottom: 1rem; color: var(--c-forest-dark);">Student Information</h4>

            <div class="form-row">
                <div class="form-group">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" placeholder="e.g. SW01084999">
                </div>

                <div class="form-group">
                    <label for="intake" class="form-label">Intake</label>
                    <input type="text" id="intake" name="intake" class="form-control" placeholder="e.g. 2023/2024-01">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="programme" class="form-label">Academic Programme</label>
                    <select name="programme" id="programme" class="form-select">
                        <option value="Bachelor of Computer Science (Software Engineering)">Bachelor of Computer Science (Software Engineering)</option>
                        <option value="Bachelor of Computer Science (Cyber Security)">Bachelor of Computer Science (Cyber Security)</option>
                        <option value="Bachelor of Information Technology (Information Systems)">Bachelor of Information Technology (Information Systems)</option>
                        <option value="Diploma in Computer Science">Diploma in Computer Science</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="cgpa" class="form-label">CGPA (Optional)</label>
                    <input type="number" step="0.01" min="0.00" max="4.00" id="cgpa" name="cgpa" class="form-control" placeholder="e.g. 3.50">
                </div>
            </div>

            <div class="form-group" style="margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 500; cursor: pointer;">
                    <input type="checkbox" name="verified" value="1" checked>
                    <span>Mark student as verified immediately (enables applying once CV is uploaded)</span>
                </label>
            </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Create User Account</button>
            <a href="<?= url('users') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
function toggleStudentFields(role) {
    const fields = document.getElementById('student_fields');
    if (role === 'student') {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}
toggleStudentFields(document.getElementById('role').value);
</script>
