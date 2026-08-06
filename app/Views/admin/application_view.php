<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin/applications/' . $application['id'] . '/edit') ?>" class="kt-btn kt-btn-outline">Edit</a>
<?php if ($hasActive): ?>
    <form method="post" action="<?= site_url('admin/applications/' . $application['id'] . '/cancel') ?>" class="js-cancel-form" data-confirm-name="<?= esc($application['name'], 'attr') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="kt-btn kt-btn-outline kt-btn-destructive">Cancel subscription</button>
    </form>
<?php endif; ?>
<a href="<?= site_url('admin/applications') ?>" class="kt-btn kt-btn-outline">Back to applications</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card mb-5 lg:mb-7.5">
    <div class="kt-card-content p-6">
        <div class="flex flex-wrap items-center justify-between gap-2.5 mb-4">
            <h3 class="text-base font-medium text-mono mb-0"><?= esc($application['name']) ?></h3>
            <span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($application['status']) ?>"><?= esc(ucfirst($application['status'])) ?></span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Company</span>
                <span class="text-2sm text-mono">
                    <?php if ($merchant !== null): ?>
                        <a class="text-primary" href="<?= site_url('admin/merchants/' . $merchant['id']) ?>"><?= esc($merchant['business_name']) ?></a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Website</span>
                <span class="text-2sm text-mono"><?= $application['website_url'] ? esc($application['website_url']) : '—' ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">API key</span>
                <span class="mono text-2sm"><?= esc($application['api_key']) ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Created</span>
                <span class="text-2sm text-mono"><?= esc($application['created_at']) ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">API access</span>
                <span class="text-2sm text-mono"><?= $hasActive ? 'Live' : 'On hold (no active subscription)' ?></span>
            </div>
        </div>
    </div>
</div>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5">
        <h3 class="kt-card-title">Subscription history (<?= count($subscriptions) ?>)</h3>
    </div>
    <?php if (empty($subscriptions)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No subscriptions for this application yet.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Expires</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $sub): ?>
                            <tr>
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
