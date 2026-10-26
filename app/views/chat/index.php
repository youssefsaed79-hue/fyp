<div class="page-header" style="margin-bottom: 24px;">
    <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">4-Way Group Chat</h1>
    <p class="text-muted" style="margin: 0;">
        Direct communication channel linking the Student, Company Supervisor, University Supervisor, and Coordinator (FR-COM-01).
    </p>
</div>

<div class="grid" style="grid-template-columns: 280px 1fr; gap: 20px; min-height: 580px;">
    <!-- Left Column: Conversations List -->
    <div class="card" style="padding: 16px; display: flex; flex-direction: column;">
        <h4 style="color: var(--c-forest-dark); margin: 0 0 12px; font-size: 1rem;">Active Conversations</h4>

        <?php if (empty($placements)): ?>
            <div class="text-muted" style="font-size: 0.88rem; padding: 12px 0;">
                No active conversations available yet. Group conversations are automatically initiated upon placement activation.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 8px; overflow-y: auto;">
                <?php foreach ($placements as $pl):
                    $isActive = ((int)$pl['conversation_id'] === (int)$conversationId);
                ?>
                    <a href="<?= url('chat?conversation_id=' . $pl['conversation_id']) ?>" 
                       style="display: block; padding: 10px 12px; border-radius: 6px; text-decoration: none; border: 1px solid <?= $isActive ? 'var(--c-forest)' : 'rgba(0,0,0,0.06)' ?>; background: <?= $isActive ? 'var(--c-bluegray)' : '#fff' ?>;">
                        <div style="font-weight: 600; color: var(--c-forest-dark); font-size: 0.9rem;">
                            <?= e($pl['student_name'] ?? $pl['company_name'] ?? 'Internship') ?>
                        </div>
                        <div style="font-size: 0.8rem; color: #666; margin-top: 2px;">
                            <?= e($pl['position']) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Chat Window -->
    <div class="card" style="padding: 0; display: flex; flex-direction: column; overflow: hidden;">
        <?php if (!$activePlacement): ?>
            <div style="padding: 40px; text-align: center; color: var(--c-text-muted); margin: auto;">
                <div style="font-size: 2.5rem; margin-bottom: 12px;">💬</div>
                <h3>Select a conversation</h3>
                <p>Choose an internship group conversation from the left to start communicating.</p>
            </div>
        <?php else: ?>
            <!-- Chat Header -->
            <div style="background: var(--c-forest-dark); color: var(--c-offwhite); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; color: #fff;">
                        <?= e($activePlacement['student_name']) ?> &bull; <?= e($activePlacement['company_name']) ?>
                    </h3>
                    <div style="font-size: 0.82rem; color: rgba(255,255,255,0.8); margin-top: 2px;">
                        Position: <?= e($activePlacement['position']) ?> &nbsp;|&nbsp;
                        Host Sup: <?= e($activePlacement['company_supervisor_name'] ?: 'Host') ?> &nbsp;|&nbsp;
                        Visiting Lecturer: <?= e($activePlacement['uni_supervisor_name'] ?: 'Supervisor') ?>
                    </div>
                </div>
                <div>
                    <span class="badge" style="background: rgba(255,255,255,0.2); color: #fff;">4-Way Group Active</span>
                </div>
            </div>

            <!-- Messages Stream -->
            <div id="messagesContainer" style="flex: 1; padding: 20px; overflow-y: auto; max-height: 420px; background: #fafafa; display: flex; flex-direction: column; gap: 12px;">
                <?php if (empty($messages)): ?>
                    <div class="text-center text-muted" style="margin: 30px 0; font-size: 0.9rem;">
                        No messages in this conversation yet. Send a greeting or share a meeting link below.
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg):
                        $isMe = ((int)$msg['sender_id'] === (int)$user['id']);
                        $roleColors = [
                            'student' => 'var(--c-forest)',
                            'company' => '#8D5C0B',
                            'university_supervisor' => '#1D3E2F',
                            'coordinator' => '#8B263E',
                        ];
                        $roleColor = $roleColors[$msg['sender_role']] ?? '#444';
                    ?>
                        <div style="display: flex; flex-direction: column; align-items: <?= $isMe ? 'flex-end' : 'flex-start' ?>;">
                            <div style="font-size: 0.78rem; color: #777; margin-bottom: 2px;">
                                <strong style="color: <?= $roleColor ?>;"><?= e($msg['sender_name']) ?></strong> 
                                (<?= e(ucwords(str_replace('_', ' ', $msg['sender_role']))) ?>) &bull; <?= date('d M, h:i A', strtotime($msg['created_at'])) ?>
                            </div>
                            <div style="max-width: 75%; padding: 10px 14px; border-radius: 8px; font-size: 0.92rem; line-height: 1.4; word-break: break-word; white-space: pre-wrap; <?= $isMe ? 'background: var(--c-forest); color: #fff;' : 'background: #fff; border: 1px solid rgba(0,0,0,0.1); color: #222;' ?>">
                                <?php
                                    // Make links clickable
                                    $escapedBody = e($msg['body']);
                                    $linkedBody = preg_replace(
                                        '#(https?://[^\s]+)#i',
                                        '<a href="$1" target="_blank" rel="noopener noreferrer" style="color: ' . ($isMe ? '#bbf' : 'var(--c-forest)') . '; font-weight: 600; text-decoration: underline;">$1</a>',
                                        $escapedBody
                                    );
                                    echo $linkedBody;
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Message Input Area -->
            <div style="padding: 16px; background: #fff; border-top: 1px solid rgba(0,0,0,0.08);">
                <form action="<?= url('chat/send') ?>" method="POST">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="conversation_id" value="<?= (int)$conversationId ?>">

                    <div style="margin-bottom: 10px;">
                        <input type="url" name="meeting_link" class="form-control form-control-sm" placeholder="🔗 Optional Meeting Link (e.g. MS Teams, Google Meet, Zoom URL)">
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <textarea name="body" class="form-control" rows="2" required placeholder="Type your message to the group..." style="resize: none;"></textarea>
                        <button type="submit" class="btn btn-primary" style="padding: 0 20px; font-weight: 600;">
                            Send
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('messagesContainer');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});
</script>
