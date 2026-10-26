<div style="margin-bottom: 1.5rem;">
    <span class="badge badge-success" style="margin-bottom: 0.5rem;">Verified Invitation</span>
    <h2 style="font-size: 1.5rem; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Host Supervisor Registration</h2>
    <p style="color: var(--c-text-muted); font-size: 0.9rem;">
        You have been invited by <strong><?= e($invite['student_name']) ?></strong> to register as their official host supervisor for the UNITEN CCI 12-Week Industrial Training.
    </p>
</div>

<div class="card-bluegray" style="margin-bottom: 1.5rem; font-size: 0.86rem;">
    <div style="font-weight: 700; color: var(--c-forest-dark); margin-bottom: 0.4rem;">Internship Placement Details</div>
    <div><strong>Student:</strong> <?= e($invite['student_name']) ?> (<?= e($invite['student_id']) ?>)</div>
    <div><strong>Programme:</strong> <?= e($invite['programme']) ?></div>
    <div><strong>Position:</strong> <?= e($invite['position']) ?></div>
    <div><strong>Company:</strong> <?= e($invite['company_name']) ?></div>
    <div><strong>Duration:</strong> <?= date('d M Y', strtotime($invite['start_date'])) ?> to <?= date('d M Y', strtotime($invite['end_date'])) ?></div>
</div>

<form action="<?= url('invite/register') ?>" method="POST" data-validate>
    <?= \App\Core\Csrf::field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="form-group">
        <label for="name" class="form-label">Your Full Name</label>
        <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Engr. David Tan" autofocus>
    </div>

    <div class="form-group">
        <label class="form-label">Email Address (Locked to Invitation)</label>
        <input type="email" class="form-control" value="<?= e($invite['invited_email']) ?>" readonly style="background-color: var(--c-bluegray);">
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Create Password</label>
        <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="At least 8 characters">
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" placeholder="Re-type password">
    </div>

    <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary btn-block" style="padding: 0.75rem;">
            Complete Supervisor Registration &rarr;
        </button>
    </div>
</form>

<div class="form-help" style="margin-top: 1rem; text-align: center; font-size: 0.8rem;">
    By registering, your account will have scoped access to confirm placement, batch-verify attendance, and evaluate this student's internship.
</div>
