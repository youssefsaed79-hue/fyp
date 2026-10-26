<?php
$messages = \App\Core\Flash::get();
if (!empty($messages)): ?>
    <div class="flash-messages-container" style="margin-bottom: 1.25rem;">
        <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?= e($msg['type']) ?>" role="alert">
                <span class="alert-icon">
                    <?php if ($msg['type'] === 'success'): ?>✓
                    <?php elseif ($msg['type'] === 'danger'): ?>⚠
                    <?php elseif ($msg['type'] === 'warning'): ?>!
                    <?php else: ?>ℹ<?php endif; ?>
                </span>
                <span class="alert-text"><?= e($msg['message']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
