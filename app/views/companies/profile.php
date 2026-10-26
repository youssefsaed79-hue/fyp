<div class="page-header">
    <div>
        <h1>Company Profile</h1>
        <p>Manage your host organization details and representative contact</p>
    </div>
</div>

<div class="card" style="max-width: 750px;">
    <div class="card-title">
        <h3>Organization Information</h3>
        <?= renderStatusBadge($company['status']) ?>
    </div>

    <form action="<?= url('companies/update-profile') ?>" method="POST" data-validate>
        <?= \App\Core\Csrf::field() ?>

        <div class="form-group">
            <label for="name" class="form-label">Company Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= e($company['name']) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Registration No. (SSM)</label>
                <input type="text" class="form-control" value="<?= e($company['registration_no']) ?>" readonly style="background-color: var(--c-bluegray);">
            </div>

            <div class="form-group">
                <label for="industry" class="form-label">Industry / Domain</label>
                <input type="text" id="industry" name="industry" class="form-control" value="<?= e($company['industry']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="form-label">Office Address</label>
            <textarea id="address" name="address" class="form-control" rows="3" required><?= e($company['address']) ?></textarea>
        </div>

        <div class="card-bluegray" style="margin: 1.25rem 0;">
            <div style="font-weight: 700; color: var(--c-forest-dark); margin-bottom: 0.35rem;">Supervisor Representative Account</div>
            <div style="font-size: 0.88rem; color: var(--c-text-muted);">
                User: <strong><?= e($user['name']) ?></strong> (<?= e($user['email']) ?>)<br>
                Source: <strong><?= e(ucfirst($company['source'])) ?></strong>
            </div>
        </div>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">Save Profile Changes</button>
        </div>
    </form>
</div>
