<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin/applications/' . $application['id'] . '/edit') ?>" class="kt-btn kt-btn-outline">Edit</a>
<?php if ($hasActive): ?>
    <form method="post" action="<?= site_url('admin/applications/' . $application['id'] . '/cancel') ?>" class="js-cancel-form" data-confirm-name="<?= esc($application['name'], 'attr') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="kt-btn kt-btn-outline kt-btn-destructive">Cancel subscription</button>
    </form>
<?php else: ?>
    <button type="button" class="kt-btn kt-btn-primary" data-kt-modal-toggle="#activate_plan_modal">Activate plan</button>
<?php endif; ?>
<a href="<?= site_url('admin/applications') ?>" class="kt-btn kt-btn-outline">Back to applications</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if ($regeneratedCredentials): ?>
    <div class="kt-card border-primary mb-5 lg:mb-7.5">
        <div class="kt-card-content p-6">
            <h3 class="text-base font-medium text-mono mb-1.5">New API credentials for "<?= esc($application['name']) ?>"</h3>
            <p class="text-2sm text-secondary-foreground mb-3">Save the API secret now — it will not be shown again. The old key/secret no longer work.</p>
            <div class="credential-box">
                <div class="row"><span class="text-2sm text-secondary-foreground">API Key</span><span class="mono"><?= esc($regeneratedCredentials['api_key']) ?></span></div>
                <div class="row"><span class="text-2sm text-secondary-foreground">API Secret</span><span class="mono"><?= esc($regeneratedCredentials['api_secret']) ?></span></div>
            </div>
        </div>
    </div>
<?php endif; ?>

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

<?php if (! $hasActive): ?>
    <div class="kt-modal" data-kt-modal="true" id="activate_plan_modal">
        <div class="kt-modal-dialog kt-modal-open:!flex">
            <div class="kt-modal-content max-w-[480px]">
                <div class="kt-modal-header">
                    <h3 class="kt-modal-title">Activate plan for "<?= esc($application['name']) ?>"</h3>
                    <button class="kt-modal-close" data-kt-modal-dismiss="true" type="button">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <form method="post" action="<?= site_url('admin/applications/' . $application['id'] . '/activate') ?>" onsubmit="KTModal.getInstance(this.closest('.kt-modal')).hide()">
                    <?= csrf_field() ?>
                    <div class="kt-modal-body flex flex-col gap-3.5">
                        <p class="text-2sm text-secondary-foreground mb-0">This activates the application's API access directly, without the merchant going through the subscribe flow. If the merchant isn't active yet, this also requires payout details to already be on file.</p>
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
                        <button type="submit" class="kt-btn kt-btn-mono">Activate plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
