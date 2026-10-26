<?php
$user = \App\Core\Auth::user();
$unreadNotifs = $user ? \App\Core\Notifier::getUnreadCount((int) $user['id']) : 0;
?>
<header class="topbar">
    <div class="topbar-left">
        <button type="button" class="mobile-menu-toggle" aria-label="Toggle menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <span style="font-size: 0.88rem; font-weight: 600; opacity: 0.9;">UNITEN College of Computing &amp; Informatics</span>
    </div>

    <div class="topbar-right">
        <?php if ($user): ?>
            <a href="<?= url('notifications') ?>" class="notifications-bell" title="Notifications">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($unreadNotifs > 0): ?>
                    <span class="notifications-count"><?= $unreadNotifs > 99 ? '99+' : $unreadNotifs ?></span>
                <?php endif; ?>
            </a>

            <div class="user-profile-badge">
                <span style="font-weight: 600; font-size: 0.92rem;"><?= e($user['name']) ?></span>
                <span class="user-role-pill"><?= e(str_replace('_', ' ', $user['role'])) ?></span>
            </div>

            <form action="<?= url('logout') ?>" method="POST" style="display: inline; margin: 0;">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--c-offwhite); border-color: rgba(243,242,236,0.3); padding: 0.25rem 0.6rem;">Sign Out</button>
            </form>
        <?php endif; ?>
    </div>
</header>
