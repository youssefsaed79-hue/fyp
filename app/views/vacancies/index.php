<div class="page-header">
    <div>
        <h1>Find Internship Opportunities</h1>
        <p>Explore accredited 12-week industrial training positions with partner companies (FR-ILM-02)</p>
    </div>
</div>

<!-- SEARCH & FILTER BAR -->
<div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
    <form method="GET" action="<?= url('vacancies') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1.5; min-width: 220px;">
            <label class="form-label" style="font-size: 0.82rem;">Search Keywords / Job Title / Skills</label>
            <input type="text" name="q" class="form-control" placeholder="e.g. Software, Cloud, Cyber Security, Python" value="<?= e($keyword) ?>">
        </div>

        <div style="flex: 1; min-width: 170px;">
            <label class="form-label" style="font-size: 0.82rem;">Industry</label>
            <select name="industry" class="form-select">
                <option value="">All Industries</option>
                <?php foreach ($industries as $ind): ?>
                    <option value="<?= e($ind) ?>" <?= $currentIndustry === $ind ? 'selected' : '' ?>><?= e($ind) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="flex: 1; min-width: 150px;">
            <label class="form-label" style="font-size: 0.82rem;">Work Arrangement</label>
            <select name="arrangement" class="form-select">
                <option value="">Any Arrangement</option>
                <option value="onsite" <?= $currentArrangement === 'onsite' ? 'selected' : '' ?>>On-site</option>
                <option value="hybrid" <?= $currentArrangement === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                <option value="remote" <?= $currentArrangement === 'remote' ? 'selected' : '' ?>>Remote</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem;">Search</button>
            <a href="<?= url('vacancies') ?>" class="btn btn-secondary" style="padding: 0.6rem 1rem;">Reset</a>
        </div>
    </form>
</div>

<!-- VACANCY CARDS LIST -->
<div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <h3 style="color: var(--c-forest-dark);">Available Positions (<?= count($vacancies) ?> Found)</h3>
</div>

<?php if (!empty($vacancies)): ?>
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <?php foreach ($vacancies as $vac): ?>
            <div class="card" style="margin-bottom: 0; padding: 1.35rem; transition: transform 0.15s ease, box-shadow 0.15s ease;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span class="badge badge-info"><?= strtoupper(e($vac['working_arrangement'])) ?></span>
                            <span style="font-size: 0.8rem; color: var(--c-text-muted);">12 Weeks &bull; <?= (int)$vac['slots'] ?> Slots</span>
                        </div>
                        <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">
                            <a href="<?= url('vacancies/view', ['id' => $vac['id']]) ?>"><?= e($vac['title']) ?></a>
                        </h2>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--c-forest); margin-bottom: 0.5rem;">
                            <?= e($vac['company_name']) ?> &bull; <span style="font-size: 0.85rem; color: var(--c-text-muted); font-weight: normal;"><?= e($vac['location']) ?> (<?= e($vac['company_industry']) ?>)</span>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <div style="font-size: 1.2rem; font-weight: 800; color: var(--c-forest-dark);">
                            RM <?= number_format((float)$vac['allowance'], 2) ?>
                            <span style="font-size: 0.75rem; font-weight: normal; color: var(--c-text-muted);">/ month</span>
                        </div>
                        <div style="font-size: 0.78rem; color: var(--c-text-muted); margin-top: 0.2rem;">
                            Deadline: <?= date('d M Y', strtotime($vac['deadline'])) ?>
                        </div>
                    </div>
                </div>

                <p style="font-size: 0.88rem; color: var(--c-text-primary); margin: 0.75rem 0; line-height: 1.45;">
                    <?= mb_strimwidth(strip_tags($vac['duties']), 0, 200, '...') ?>
                </p>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--c-bluegray); padding-top: 0.75rem; margin-top: 0.5rem;">
                    <div style="font-size: 0.8rem; color: var(--c-text-muted);">
                        <strong>Key Skills:</strong> <?= mb_strimwidth(e($vac['requirements']), 0, 75, '...') ?>
                    </div>
                    <a href="<?= url('vacancies/view', ['id' => $vac['id']]) ?>" class="btn btn-primary btn-sm">
                        View Details &amp; Apply &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card" style="text-align: center; padding: 3rem 1rem;">
        <div style="font-size: 3rem; margin-bottom: 0.75rem;">🔍</div>
        <h3>No Listings Match Your Filters</h3>
        <p style="color: var(--c-text-muted); font-size: 0.92rem; margin: 0.5rem 0 1.25rem;">
            Try adjusting your keyword query, industry selection, or clearing filters.
        </p>
        <a href="<?= url('vacancies') ?>" class="btn btn-primary btn-sm">Clear All Filters</a>
    </div>
<?php endif; ?>
