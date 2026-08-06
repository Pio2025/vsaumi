<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin/merchants/' . $merchant['id'] . '/edit') ?>" class="kt-btn kt-btn-outline">Edit</a>
<a href="<?= site_url('admin/merchants') ?>" class="kt-btn kt-btn-outline">Back to merchants</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card mb-5 lg:mb-7.5">
    <div class="kt-card-content p-6">
        <div class="flex flex-wrap items-center justify-between gap-2.5 mb-4">
            <h3 class="text-base font-medium text-mono mb-0"><?= esc($merchant['business_name']) ?></h3>
            <span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($merchant['status']) ?>"><?= esc(ucfirst($merchant['status'])) ?></span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Contact email</span>
                <span class="text-2sm text-mono"><?= esc($merchant['contact_email']) ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Contact phone</span>
                <span class="text-2sm text-mono"><?= $merchant['contact_phone'] ? esc($merchant['contact_phone']) : '—' ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Signed up</span>
                <span class="text-2sm text-mono"><?= esc($merchant['created_at']) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="kt-card kt-card-grid mb-5 lg:mb-7.5">
    <div class="kt-card-header py-5">
        <h3 class="kt-card-title">Applications &amp; active subscriptions (<?= count($applications) ?>)</h3>
    </div>
    <?php if (empty($applications)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">This merchant has no applications yet.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Application</th>
                            <th class="min-w-[200px]">API Key</th>
                            <th>Status</th>
                            <th>Subscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="leading-none font-medium text-sm text-mono"><?= esc($app['name']) ?></span>
                                        <span class="text-xs text-secondary-foreground font-normal"><?= $app['website_url'] ? esc($app['website_url']) : '—' ?></span>
                                    </div>
                                </td>
                                <td><span class="mono text-2sm"><?= esc($app['api_key']) ?></span></td>
                                <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($app['status']) ?>"><?= esc(ucfirst($app['status'])) ?></span></td>
                                <td>
                                    <?php if ($app['has_active_subscription']): ?>
                                        <span class="text-2sm text-mono"><?= esc(ucfirst($app['latest_subscription']['plan'])) ?></span>
                                        <span class="text-xs text-secondary-foreground block">Expires <?= esc($app['latest_subscription']['expires_at']) ?></span>
                                    <?php else: ?>
                                        <span class="text-2sm text-secondary-foreground">No active subscription</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5">
        <h3 class="kt-card-title">Other subscriptions (<?= count($otherSubscriptions) ?>)</h3>
    </div>
    <?php if (empty($otherSubscriptions)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No expired or canceled subscriptions.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Application</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Expired</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($otherSubscriptions as $sub): ?>
                            <tr>
                                <td><span class="text-2sm text-mono"><?= esc($sub['application_name']) ?></span></td>
                                <td><span class="text-2sm"><?= esc(ucfirst($sub['plan'])) ?></span></td>
                                <td><span class="text-2sm">$<?= esc(number_format((float) $sub['amount'], 2)) ?></span></td>
                                <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($sub['status']) ?>"><?= esc(ucfirst($sub['status'])) ?></span></td>
                                <td><span class="text-2sm"><?= esc($sub['started_at']) ?></span></td>
                                <td><span class="text-2sm"><?= esc($sub['expires_at']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
