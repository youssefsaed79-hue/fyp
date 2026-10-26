<div class="page-header">
    <div>
        <h1>My Applications</h1>
        <p>Track your submitted internship applications, interview notices, and offers (FR-APM-01, FR-APM-04)</p>
    </div>
    <div>
        <a href="<?= url('vacancies') ?>" class="btn btn-primary">Find More Vacancies &rarr;</a>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <h3>Submitted Applications (<?= count($applications) ?> Total)</h3>
    </div>

    <?php if (!empty($applications)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Position &amp; Company</th>
                        <th>Location &amp; Arrangement</th>
                        <th>Allowance</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th>Interview / Offer Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <strong><a href="<?= url('vacancies/view', ['id' => $app['vacancy_id']]) ?>"><?= e($app['vacancy_title']) ?></a></strong><br>
                                <span style="color: var(--c-forest); font-weight: 600;"><?= e($app['company_name']) ?></span>
                            </td>
                            <td>
                                <?= e($app['location']) ?><br>
                                <span class="badge badge-info"><?= strtoupper(e($app['working_arrangement'])) ?></span>
                            </td>
                            <td>RM <?= number_format((float)$app['allowance'], 2) ?></td>
                            <td style="font-size: 0.84rem;"><?= date('d M Y', strtotime($app['created_at'])) ?></td>
                            <td>
                                <?= renderStatusBadge($app['status']) ?>
                            </td>
                            <td>
                                <?php if (!empty($app['offer_issued']) && $app['status'] !== 'accepted' && $app['status'] !== 'withdrawn'): ?>
                                    <!-- FORMAL OFFER RECEIVED (FR-APM-04) -->
                                    <div class="card-bluegray" style="padding: 0.65rem; margin-bottom: 0.5rem; font-size: 0.82rem;">
                                        <div style="font-weight: 700; color: var(--c-status-success);">🎉 Offer Extended!</div>
                                        <div><?= e($app['offer_text'] ?? '12-Week Industrial Training Placement Offer') ?></div>
                                    </div>
                                    <form action="<?= url('applications/respond-offer') ?>" method="POST" style="display: flex; gap: 0.4rem;" data-confirm="Accept this placement offer? Accepting will automatically withdraw all your other active applications!">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                        <button type="submit" name="response" value="accept" class="btn btn-primary btn-sm" style="background-color: var(--c-status-success); border-color: var(--c-status-success);">Accept Offer</button>
                                        <button type="submit" name="response" value="decline" class="btn btn-secondary btn-sm" style="color: var(--c-status-danger); border-color: rgba(163,40,40,0.3);">Decline</button>
                                    </form>
                                <?php elseif (!empty($app['interview_details'])): ?>
                                    <div style="font-size: 0.82rem; color: var(--c-forest-dark); background: var(--c-status-info-bg); padding: 0.4rem 0.6rem; border-radius: 4px;">
                                        <strong>Interview Scheduled:</strong><br>
                                        <?= e($app['interview_details']) ?>
                                    </div>
                                <?php elseif ($app['status'] === 'withdrawn'): ?>
                                    <small style="color: var(--c-text-muted);">Withdrawn upon offer lock (FR-APM-04)</small>
                                <?php else: ?>
                                    <span style="color: var(--c-text-muted); font-size: 0.84rem;">Awaiting Review</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2.5rem 1rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">📝</div>
            <h4>No Applications Submitted Yet</h4>
            <p style="color: var(--c-text-muted); font-size: 0.9rem; margin: 0.5rem 0 1.25rem;">
                Browse accredited internship vacancies posted by partner organizations to submit your application.
            </p>
            <a href="<?= url('vacancies') ?>" class="btn btn-primary btn-sm">Browse Vacancies</a>
        </div>
    <?php endif; ?>
</div>
