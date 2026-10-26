<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Announcements Administration</h1>
        <p class="text-muted" style="margin: 0;">
            Create, publish, and schedule student announcements and deadlines (FR-IRM-06).
        </p>
    </div>
    <div>
        <a href="<?= url('resource-admin') ?>" class="btn btn-secondary">
            ← Back to Resources Admin
        </a>
    </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Create Announcement Form -->
    <div class="card">
        <h3 style="color: var(--c-forest-dark); margin: 0 0 16px;">Post Announcement</h3>
        <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 16px;">
            Publishing an announcement automatically dispatches in-app notifications to all active students (FR-IRM-06).
        </p>

        <form action="<?= url('resource-admin/announcements/store') ?>" method="POST">
            <?= \App\Core\Csrf::field() ?>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" style="font-weight: 600;">Announcement Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="e.g. Industrial Training Briefing 1 Registration">
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" style="font-weight: 600;">Announcement Body <span class="text-danger">*</span></label>
                <textarea name="body" rows="5" class="form-control" required placeholder="Full announcement text, instructions, and dates..."></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Optional Deadline Date</label>
                <input type="date" name="deadline_date" class="form-control">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Publication Status</label>
                <select name="status" class="form-control">
                    <option value="published">Published (Send Alerts Now)</option>
                    <option value="draft">Draft (Save for Later)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                📢 Save &amp; Post Announcement
            </button>
        </form>
    </div>

    <!-- Announcements List -->
    <div class="card">
        <h3 style="color: var(--c-forest-dark); margin: 0 0 16px;">Existing Announcements</h3>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title &amp; Excerpt</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($announcements)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-6 text-muted">No announcements found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): ?>
                            <tr>
                                <td>
                                    <strong><?= e($ann['title']) ?></strong>
                                    <div style="font-size: 0.85rem; color: #555;"><?= substr(e($ann['body']), 0, 90) ?>...</div>
                                </td>
                                <td>
                                    <?= !empty($ann['deadline_date']) ? date('d M Y', strtotime($ann['deadline_date'])) : '—' ?>
                                </td>
                                <td>
                                    <span class="badge <?= $ann['status'] === 'published' ? 'badge-success' : 'badge-neutral' ?>">
                                        <?= e(ucfirst($ann['status'])) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($ann['created_at'])) ?></td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <form action="<?= url('resource-admin/announcements/toggle') ?>" method="POST" style="display: inline;">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$ann['id'] ?>">
                                        <button type="submit" class="btn btn-secondary btn-sm" style="margin-right: 4px;">
                                            <?= $ann['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                        </button>
                                    </form>

                                    <form action="<?= url('resource-admin/announcements/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Delete announcement?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$ann['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
