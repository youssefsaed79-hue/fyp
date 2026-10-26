<div class="page-header" style="margin-bottom: 24px;">
    <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Internship Resources Portal</h1>
    <p class="text-muted" style="margin: 0;">
        Official documents, guidelines, process timeline, and announcements published by the UNITEN CCI Internship Coordinator (Module 14 IRM).
    </p>
</div>

<!-- Tab Navigation -->
<div style="display: flex; gap: 8px; border-bottom: 2px solid var(--c-bluegray); margin-bottom: 24px; flex-wrap: wrap;">
    <a href="<?= url('resources?tab=kit') ?>" class="btn <?= $activeTab === 'kit' ? 'btn-primary' : 'btn-secondary' ?>" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
        📁 Internship Kit
    </a>
    <a href="<?= url('resources?tab=journey') ?>" class="btn <?= $activeTab === 'journey' ? 'btn-primary' : 'btn-secondary' ?>" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
        🗺️ Internship Journey
    </a>
    <a href="<?= url('resources?tab=guidelines') ?>" class="btn <?= $activeTab === 'guidelines' ? 'btn-primary' : 'btn-secondary' ?>" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
        📋 Guidelines &amp; Policies
    </a>
    <a href="<?= url('resources?tab=announcements') ?>" class="btn <?= $activeTab === 'announcements' ? 'btn-primary' : 'btn-secondary' ?>" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
        📢 Announcements
    </a>
</div>

<!-- SECTION 1: INTERNSHIP KIT (FR-IRM-02) -->
<?php if ($activeTab === 'kit'): ?>
    <div style="margin-bottom: 20px;">
        <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Internship Kit Downloads</h3>
        <p class="text-muted" style="margin: 0 0 20px;">
            Download official forms, templates, briefing slides, and marking rubrics for your 12-week industrial training.
        </p>

        <?php if (empty($categories)): ?>
            <div class="card p-6 text-center text-muted">No published resource categories available yet.</div>
        <?php else: ?>
            <?php foreach ($categories as $cat):
                $docs = $resourcesByCategory[$cat['id']] ?? [];
                if (empty($docs)) continue;
            ?>
                <div class="card" style="margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid rgba(0,0,0,0.06);">
                        <h4 style="color: var(--c-forest-dark); margin: 0;"><?= e($cat['name']) ?></h4>
                        <span class="badge badge-neutral"><?= count($docs) ?> file<?= count($docs) > 1 ? 's' : '' ?></span>
                    </div>

                    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                        <?php foreach ($docs as $doc): ?>
                            <div style="background: var(--c-bluegray); padding: 14px; border-radius: 8px; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div style="font-weight: 600; color: var(--c-forest-dark); font-size: 1rem; margin-bottom: 4px;">
                                        <?= e($doc['title']) ?>
                                    </div>
                                    <?php if (!empty($doc['description'])): ?>
                                        <p style="font-size: 0.85rem; color: #555; margin: 0 0 10px; line-height: 1.4;">
                                            <?= e($doc['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <div style="font-size: 0.8rem; color: #777; margin-bottom: 12px;">
                                        <span>v<?= (int)$doc['version'] ?></span> &nbsp;|&nbsp;
                                        <span><?= !empty($doc['size']) ? round((int)$doc['size'] / 1024, 1) . ' KB' : 'PDF' ?></span> &nbsp;|&nbsp;
                                        <span>Published: <?= date('d M Y', strtotime($doc['published_at'] ?: $doc['created_at'])) ?></span>
                                    </div>
                                </div>

                                <div>
                                    <a href="<?= url('resources/download?id=' . $doc['id']) ?>" class="btn btn-primary btn-sm" style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                        📥 Download File
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<!-- SECTION 2: INTERNSHIP JOURNEY (FR-IRM-03) -->
<?php elseif ($activeTab === 'journey'): ?>
    <div>
        <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Internship Journey Timeline</h3>
        <p class="text-muted" style="margin: 0 0 24px;">
            Four milestone stages from academic preparation through final completion at UNITEN CCI.
        </p>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php if (empty($journeyStages)): ?>
                <div class="card p-6 text-center text-muted">No journey milestones published yet.</div>
            <?php else: ?>
                <?php foreach ($journeyStages as $stage): ?>
                    <div class="card" style="display: flex; gap: 20px; align-items: flex-start;">
                        <div style="background: var(--c-forest); color: var(--c-offwhite); min-width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem;">
                            <?= (int)$stage['stage_no'] ?>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="color: var(--c-forest-dark); margin: 0 0 8px; font-size: 1.15rem;">
                                <?= e($stage['title']) ?>
                            </h4>
                            <div style="font-size: 0.95rem; color: #444; line-height: 1.6; white-space: pre-wrap;">
                                <?= e($stage['description']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<!-- SECTION 3: GUIDELINES & POLICIES (FR-IRM-03) -->
<?php elseif ($activeTab === 'guidelines'): ?>
    <div>
        <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Internship Guidelines &amp; Policies</h3>
        <p class="text-muted" style="margin: 0 0 24px;">
            Official university requirements, student eligibility, operational procedures, and responsibilities.
        </p>

        <?php
        $kindLabels = [
            'eligibility' => '📌 Eligibility Requirements',
            'procedure' => '📑 Operational Procedures & Documents to Send',
            'responsibility' => '🤝 Stakeholder Responsibilities',
            'instruction' => '📝 Important Instructions & Regulations',
        ];

        foreach ($kindLabels as $k => $label):
            $items = $guidelinesByKind[$k] ?? [];
            if (empty($items)) continue;
        ?>
            <div class="card" style="margin-bottom: 20px;">
                <h4 style="color: var(--c-forest-dark); margin: 0 0 14px; padding-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.06);">
                    <?= e($label) ?>
                </h4>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($items as $item): ?>
                        <details style="background: var(--c-bluegray); padding: 12px 16px; border-radius: 6px;" open>
                            <summary style="font-weight: 600; color: var(--c-forest-dark); cursor: pointer; font-size: 1rem;">
                                <?= e($item['title']) ?>
                            </summary>
                            <div style="margin-top: 10px; font-size: 0.92rem; color: #333; line-height: 1.6; white-space: pre-wrap;">
                                <?= e($item['body']) ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<!-- SECTION 4: ANNOUNCEMENTS (FR-IRM-04) -->
<?php elseif ($activeTab === 'announcements'): ?>
    <div>
        <h3 style="color: var(--c-forest-dark); margin: 0 0 8px;">Announcements &amp; Important Notices</h3>
        <p class="text-muted" style="margin: 0 0 24px;">
            Latest updates, briefing schedules, and deadlines from the Internship Coordinator.
        </p>

        <?php if (empty($announcements)): ?>
            <div class="card p-6 text-center text-muted">No announcements posted at this time.</div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($announcements as $ann): ?>
                    <div class="card" style="border-left: 4px solid var(--c-forest);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                            <h4 style="color: var(--c-forest-dark); margin: 0; font-size: 1.15rem;">
                                <?= e($ann['title']) ?>
                            </h4>
                            <span class="badge badge-neutral">
                                <?= date('d M Y, h:i A', strtotime($ann['published_at'] ?: $ann['created_at'])) ?>
                            </span>
                        </div>

                        <?php if (!empty($ann['deadline_date'])): ?>
                            <div class="badge badge-warning" style="margin-bottom: 12px; display: inline-block;">
                                ⏰ Deadline: <?= date('d M Y', strtotime($ann['deadline_date'])) ?>
                            </div>
                        <?php endif; ?>

                        <div style="font-size: 0.95rem; color: #333; line-height: 1.6; white-space: pre-wrap;">
                            <?= e($ann['body']) ?>
                        </div>

                        <?php if (!empty($ann['author_name'])): ?>
                            <div style="margin-top: 12px; font-size: 0.8rem; color: #777;">
                                Posted by: <?= e($ann['author_name']) ?> (University Coordinator)
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
