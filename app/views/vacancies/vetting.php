<div class="page-header">
    <div>
        <h1>Vacancy Vetting &amp; Moderation</h1>
        <p>Review, flag, or deactivate inappropriate listings across all partner organisations (FR-ILM-03)</p>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>System Vacancies Review (<?= count($vacancies) ?> Total)</h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Title &amp; Company</th>
                    <th>Arrangement &amp; Allowance</th>
                    <th>Deadline</th>
                    <th>Company Status</th>
                    <th>Listing Status</th>
                    <th>Moderation Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($vacancies)): ?>
                    <?php foreach ($vacancies as $vac): ?>
                        <tr>
                            <td>
                                <strong><a href="<?= url('vacancies/view', ['id' => $vac['id']]) ?>"><?= e($vac['title']) ?></a></strong><br>
                                <small style="color: var(--c-text-muted);"><?= e($vac['company_name']) ?> &bull; <?= e($vac['location']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= strtoupper(e($vac['working_arrangement'])) ?></span><br>
                                <small>RM <?= number_format((float)$vac['allowance'], 2) ?></small>
                            </td>
                            <td style="font-size: 0.84rem;"><?= date('d M Y', strtotime($vac['deadline'])) ?></td>
                            <td><?= renderStatusBadge($vac['company_status']) ?></td>
                            <td><?= renderStatusBadge($vac['status']) ?></td>
                            <td>
                                <form action="<?= url('vacancies/vet') ?>" method="POST" style="display: flex; gap: 0.35rem;">
                                    <?= \App\Core\Csrf::field() ?>
                                    <input type="hidden" name="vacancy_id" value="<?= $vac['id'] ?>">

                                    <?php if ($vac['status'] !== 'flagged'): ?>
                                        <button type="submit" name="action" value="flag" class="btn btn-secondary btn-sm" style="color: var(--c-status-warning); border-color: rgba(141,92,11,0.3);" title="Flag for Review">Flag</button>
                                    <?php endif; ?>

                                    <?php if ($vac['status'] !== 'deactivated'): ?>
                                        <button type="submit" name="action" value="deactivate" class="btn btn-danger btn-sm" title="Deactivate Listing" data-confirm="Deactivate this vacancy? Students will no longer see it.">Deactivate</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="republish" class="btn btn-primary btn-sm">Reinstate</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--c-text-muted);">
                            No vacancies found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
