<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Notification Centre</h1>
        <p class="text-muted" style="margin: 0;">
            Stay updated with real-time system alerts, evaluations, submission feedback, and announcements (FR-COM-02).
        </p>
    </div>
    <?php if ($unreadCount > 0): ?>
        <form action="<?= url('notifications/read-all') ?>" method="POST">
            <?= \App\Core\Csrf::field() ?>
            <button type="submit" class="btn btn-secondary btn-sm">
                ✓ Mark All as Read (<?= $unreadCount ?>)
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <?php if (empty($notifications)): ?>
        <div style="text-align: center; padding: 40px 20px; color: var(--c-text-muted);">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">🔔</div>
            <h3>No notifications yet</h3>
            <p>You have no notifications or activity alerts at this time.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column;">
            <?php foreach ($notifications as $n):
                $isUnread = empty($n['read_at']);
            ?>
                <div style="padding: 16px; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; <?= $isUnread ? 'background: var(--c-bluegray); border-left: 4px solid var(--c-forest);' : '' ?>">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <strong style="color: var(--c-forest-dark); font-size: 1rem;"><?= e($n['title']) ?></strong>
                            <?php if ($isUnread): ?>
                                <span class="badge badge-warning" style="font-size: 0.72rem; padding: 2px 6px;">New</span>
                            <?php endif; ?>
                            <span style="font-size: 0.78rem; color: #777;">
                                <?= date('d M Y, h:i A', strtotime($n['created_at'])) ?>
                            </span>
                        </div>

                        <div style="font-size: 0.9rem; color: #444; line-height: 1.5;">
                            <?= e($n['body']) ?>
                        </div>

                        <?php if (!empty($n['link'])): ?>
                            <div style="margin-top: 8px;">
                                <a href="<?= url(ltrim($n['link'], '/')) ?>" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 4px;">
                                    View Details &rarr;
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isUnread): ?>
                        <form action="<?= url('notifications/read') ?>" method="POST" style="margin-left: 10px;">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                            <button type="submit" class="btn btn-secondary btn-sm" title="Mark as Read" style="padding: 4px 8px; font-size: 0.8rem;">
                                ✓
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
