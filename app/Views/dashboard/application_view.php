<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<?php if (! $hasActive): ?>
    <button type="button" class="kt-btn kt-btn-primary" data-kt-modal-toggle="#subscribe_modal">Subscribe</button>
<?php endif; ?>
<a href="<?= site_url('dashboard/applications') ?>" class="kt-btn kt-btn-outline">Back to applications</a>
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
                            <th>Invoice</th>
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
                                <td>
                                    <a href="<?= site_url('dashboard/applications/' . $application['id'] . '/subscriptions/' . $sub['id'] . '/invoice') ?>" class="kt-btn kt-btn-sm kt-btn-outline">
                                        <i class="ki-filled ki-file-down"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (! $hasActive): ?>
    <div class="kt-modal" data-kt-modal="true" id="subscribe_modal">
        <div class="kt-modal-dialog kt-modal-open:!flex">
            <div class="kt-modal-content max-w-[480px]">
                <div class="kt-modal-header">
                    <h3 class="kt-modal-title">Subscribe "<?= esc($application['name']) ?>"</h3>
                    <button class="kt-modal-close" data-kt-modal-dismiss="true" type="button">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <form method="post" action="<?= site_url('dashboard/applications/' . $application['id'] . '/subscribe') ?>" onsubmit="KTModal.getInstance(this.closest('.kt-modal')).hide()">
                    <?= csrf_field() ?>
                    <div class="kt-modal-body flex flex-col gap-3.5">
                        <p class="text-2sm text-secondary-foreground mb-0">This charges nothing real — it's a simulated payment that activates this application's API access for 30 days.</p>
                        <?php foreach ($plans as $key => $plan): ?>
                            <label class="kt-card cursor-pointer has-[:checked]:border-primary p-4">
                                <input type="radio" name="plan" value="<?= esc($key) ?>" class="kt-radio mb-2" <?= $key === 'starter' ? 'checked' : '' ?>>
                                <div class="font-medium text-mono"><?= esc($plan['label']) ?> — $<?= esc($plan['price']) ?>/mo</div>
                                <div class="text-xs text-secondary-foreground"><?= esc($plan['note']) ?></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="kt-modal-footer">
                        <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Cancel</button>
                        <button type="submit" class="kt-btn kt-btn-mono">Simulate Payment &amp; Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
