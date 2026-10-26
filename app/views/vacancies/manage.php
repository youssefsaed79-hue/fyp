<div class="page-header">
    <div>
        <h1>Manage Internship Listings</h1>
        <p><?= e($company['name'] ?? 'Organisation') ?> &bull; Post and manage 12-week internship opportunities (FR-ILM-01)</p>
    </div>
    <div>
        <a href="<?= url('vacancies/create') ?>" class="btn btn-primary">+ Post New Listing</a>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Company Listings (<?= count($vacancies) ?> Total)</h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Position Title</th>
                    <th>Arrangement</th>
                    <th>Slots</th>
                    <th>Allowance</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Applicants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($vacancies)): ?>
                    <?php foreach ($vacancies as $vac): ?>
                        <tr>
                            <td>
                                <strong><a href="<?= url('vacancies/view', ['id' => $vac['id']]) ?>"><?= e($vac['title']) ?></a></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($vac['location']) ?></small>
                            </td>
                            <td><span class="badge badge-info"><?= strtoupper(e($vac['working_arrangement'])) ?></span></td>
                            <td><?= (int)$vac['slots'] ?></td>
                            <td>RM <?= number_format((float)$vac['allowance'], 2) ?></td>
                            <td style="font-size: 0.84rem;"><?= date('d M Y', strtotime($vac['deadline'])) ?></td>
                            <td><?= renderStatusBadge($vac['status']) ?></td>
                            <td>
                                <a href="<?= url('applications/screen', ['vacancy_id' => $vac['id']]) ?>" class="btn btn-secondary btn-sm">
                                    👥 <?= (int)$vac['applicants_count'] ?> Candidates
                                </a>
                            </td>
                            <td>
                                <form action="<?= url('vacancies/status') ?>" method="POST" style="display: flex; gap: 0.3rem;">
                                    <?= \App\Core\Csrf::field() ?>
                                    <input type="hidden" name="vacancy_id" value="<?= $vac['id'] ?>">

                                    <?php if ($vac['status'] === 'draft'): ?>
                                        <button type="submit" name="status" value="published" class="btn btn-primary btn-sm" style="padding: 0.25rem 0.5rem;">Publish</button>
                                    <?php elseif ($vac['status'] === 'published'): ?>
                                        <button type="submit" name="status" value="closed" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem;">Close</button>
                                    <?php elseif ($vac['status'] === 'closed'): ?>
                                        <button type="submit" name="status" value="published" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem;">Re-open</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--c-text-muted);">
                            No internship vacancies posted yet. Click "+ Post New Listing" above to publish your opportunities.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
