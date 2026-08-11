<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<span class="kt-badge kt-badge-sm <?= status_badge_class($merchant['status']) ?>"><?= esc(ucfirst($merchant['status'])) ?></span>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .kt-stat-card { display: flex; align-items: center; gap: 0.875rem; }
    .kt-stat-icon { width: 2.75rem; height: 2.75rem; min-width: 2.75rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .kt-stat-icon.is-primary { background: color-mix(in oklch, var(--primary) 14%, transparent); color: var(--primary); }
    .kt-stat-icon.is-warning { background: rgba(245, 158, 11, .14); color: #d97706; }
    .kt-stat-icon.is-success { background: rgba(34, 197, 94, .14); color: #16a34a; }
    .kt-stat-icon.is-destructive { background: color-mix(in oklch, var(--destructive) 14%, transparent); color: var(--destructive); }
    .kt-stat-icon.is-violet { background: rgba(139, 92, 246, .14); color: #7c3aed; }
    .kt-stat-icon.is-orange { background: rgba(249, 115, 22, .14); color: #ea580c; }
</style>

<?php if ($newApplicationCredentials): ?>
    <div class="kt-card border-primary mb-5 lg:mb-7.5">
        <div class="kt-card-content p-6">
            <h3 class="text-base font-medium text-mono mb-1.5">Credentials for "<?= esc($newApplicationCredentials['name']) ?>"</h3>
            <p class="text-2sm text-secondary-foreground mb-3">Save your API secret now — it will not be shown again.</p>
            <div class="credential-box">
                <div class="row"><span class="text-2sm text-secondary-foreground">API Key</span><span class="mono"><?= esc($newApplicationCredentials['api_key']) ?></span></div>
                <div class="row"><span class="text-2sm text-secondary-foreground">API Secret</span><span class="mono"><?= esc($newApplicationCredentials['api_secret']) ?></span></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($merchant['status'] === 'pending'): ?>
    <div class="kt-card">
        <div class="kt-card-content p-6">
            <h3 class="text-base font-medium text-mono mb-1.5">Waiting for approval</h3>
            <p class="text-2sm text-secondary-foreground mb-0">An admin needs to review and approve your account before you can subscribe and go live. This usually happens quickly — check back soon, or ask an admin to approve merchant #<?= esc($merchant['id']) ?> in the admin panel.</p>
        </div>
    </div>

<?php elseif (in_array($merchant['status'], ['approved', 'suspended'], true)): ?>
    <div class="kt-card">
        <div class="kt-card-content p-6">
            <h3 class="text-base font-medium text-mono mb-1.5"><?= $merchant['status'] === 'suspended' ? 'Renew your subscription' : "You're approved — activate an application" ?></h3>
            <p class="text-2sm text-secondary-foreground mb-5">Subscribe one of your applications to a plan to activate your API access. This charges nothing real — it's a simulated payment, standing in for the real subscription billing described in the design doc.</p>
            <a href="<?= site_url('dashboard/applications') ?>" class="kt-btn kt-btn-mono">Go to Applications</a>
        </div>
    </div>

<?php else: ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card p-5">
            <div class="kt-stat-card">
                <div class="kt-stat-icon is-primary"><i class="ki-filled ki-chart-simple"></i></div>
                <div>
                    <span class="text-2sm text-secondary-foreground font-medium">Total transactions</span>
                    <div class="text-2xl font-semibold text-mono mt-1"><?= esc($stats['total']) ?></div>
                </div>
            </div>
        </div>
        <div class="kt-card p-5">
            <div class="kt-stat-card">
                <div class="kt-stat-icon is-warning"><i class="ki-filled ki-time"></i></div>
                <div>
                    <span class="text-2sm text-secondary-foreground font-medium">In progress</span>
                    <div class="text-2xl font-semibold text-mono mt-1"><?= esc($stats['pending']) ?></div>
                </div>
            </div>
        </div>
        <div class="kt-card p-5">
            <div class="kt-stat-card">
                <div class="kt-stat-icon is-success"><i class="ki-filled ki-check-circle"></i></div>
                <div>
                    <span class="text-2sm text-secondary-foreground font-medium">Settled</span>
                    <div class="text-2xl font-semibold text-mono mt-1"><?= esc($stats['settled']) ?></div>
                </div>
            </div>
        </div>
        <div class="kt-card p-5">
            <div class="kt-stat-card">
                <div class="kt-stat-icon is-destructive"><i class="ki-filled ki-cross-circle"></i></div>
                <div>
                    <span class="text-2sm text-secondary-foreground font-medium">Failed</span>
                    <div class="text-2xl font-semibold text-mono mt-1"><?= esc($stats['failed']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-7.5 mt-5 lg:mt-7.5">
        <div class="kt-card p-5">
            <div class="kt-stat-card mb-3">
                <div class="kt-stat-icon is-violet"><i class="ki-filled ki-wallet"></i></div>
                <div>
                    <span class="text-2sm text-secondary-foreground font-medium">Available balance</span>
                    <div class="text-2xl font-semibold text-mono mt-1">$<?= esc(number_format($availableBalance, 2)) ?> FJD</div>
                </div>
            </div>
            <p class="text-xs text-secondary-foreground mt-1 mb-3">Settled funds not yet paid out.</p>
            <?php if ($pendingWithdrawal): ?>
                <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-warning">Withdrawal of $<?= esc(number_format((float) $pendingWithdrawal['amount'], 2)) ?> pending review</span>
            <?php elseif ($availableBalance > 0): ?>
                <form method="post" action="<?= site_url('dashboard/withdrawals/request') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">Withdraw funds</button>
                </form>
            <?php endif; ?>
        </div>
        <div class="kt-card p-5">
            <div class="kt-stat-card">
                <div class="kt-stat-icon is-orange"><i class="ki-filled ki-chart-line-up"></i></div>
                <div>
                    <span class="text-2sm text-secondary-foreground font-medium">Total revenue</span>
                    <div class="text-2xl font-semibold text-mono mt-1">$<?= esc(number_format($totalRevenue, 2)) ?> FJD</div>
                </div>
            </div>
            <p class="text-xs text-secondary-foreground mt-1 mb-0">Total settled while using VSaumi.</p>
        </div>
    </div>

    <div class="kt-card mt-5 lg:mt-7.5">
        <div class="kt-card-content p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-medium text-mono">Subscriptions</h3>
                <a href="<?= site_url('dashboard/subscriptions') ?>" class="kt-btn kt-btn-sm kt-btn-outline">View history</a>
            </div>
            <?php if (empty($subscriptions)): ?>
                <p class="text-2sm text-secondary-foreground mb-0">No subscriptions yet.</p>
            <?php else: ?>
                <div class="flex flex-col gap-2.5">
                    <?php foreach ($subscriptions as $sub): ?>
                        <div class="flex items-center justify-between border border-border rounded-lg px-3.5 py-2.5">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-medium text-mono"><?= esc($sub['application_name'] ?? '—') ?></span>
                                <span class="text-xs text-secondary-foreground">
                                    Plan: <?= esc(ucfirst($sub['plan'])) ?> · Renews/expires: <?= esc($sub['expires_at']) ?>
                                </span>
                            </div>
                            <span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($sub['status']) ?>"><?= esc(ucfirst($sub['status'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="kt-card mt-5 lg:mt-7.5">
        <div class="kt-card-content p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-medium text-mono">Applications</h3>
                <a href="<?= site_url('dashboard/applications') ?>" class="kt-btn kt-btn-sm kt-btn-outline">Manage applications</a>
            </div>
            <?php if (empty($applications)): ?>
                <p class="text-2sm text-secondary-foreground mb-0">No applications yet.</p>
            <?php else: ?>
                <div class="flex flex-col gap-2.5">
                    <?php foreach ($applications as $app): ?>
                        <div class="flex items-center justify-between border border-border rounded-lg px-3.5 py-2.5">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-medium text-mono"><?= esc($app['name']) ?></span>
                                <span class="text-xs text-secondary-foreground mono"><?= esc($app['api_key']) ?></span>
                            </div>
                            <span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($app['status']) ?>"><?= esc(ucfirst($app['status'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (! empty($applications)): ?>
        <div class="kt-card mt-5 lg:mt-7.5">
            <div class="kt-card-content p-6">
                <h3 class="text-base font-medium text-mono mb-1.5">Try a demo checkout</h3>
                <p class="text-2sm text-secondary-foreground mb-4">This simulates a customer landing on your storefront's "Pay with VSaumi" button — pick an application, an amount, and open it.</p>
                <form method="get" action="<?= site_url('checkout/' . $applications[0]['api_key']) ?>" target="_blank" id="demoCheckoutForm" class="flex flex-wrap items-end gap-3.5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="app_picker">Application</label>
                        <select class="kt-select w-52" id="app_picker">
                            <?php foreach ($applications as $app): ?>
                                <option value="<?= esc($app['api_key'], 'attr') ?>"><?= esc($app['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="amount">Amount (FJD)</label>
                        <input class="kt-input w-40" type="number" id="amount" name="amount" value="50.00" min="1" step="0.01">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="product_name">Product / service</label>
                        <input class="kt-input w-52" type="text" id="product_name" name="product_name" placeholder="e.g. Consulting session" value="Demo item" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="quantity">Quantity</label>
                        <input class="kt-input w-24" type="number" id="quantity" name="quantity" value="1" min="0" step="0.01">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="unit_of_measure">Unit</label>
                        <input class="kt-input w-24" type="text" id="unit_of_measure" name="unit_of_measure" placeholder="pcs">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="unit_price">Unit price (FJD)</label>
                        <input class="kt-input w-32" type="number" id="unit_price" name="unit_price" min="0" step="0.01">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="product_description">Description</label>
                        <input class="kt-input w-60" type="text" id="product_description" name="product_description" placeholder="Any other detail (optional)">
                    </div>
                    <button type="submit" class="kt-btn kt-btn-primary">Open Demo Checkout</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('app_picker')?.addEventListener('change', function () {
        document.getElementById('demoCheckoutForm').action = '<?= site_url('checkout') ?>/' + this.value;
    });
</script>
<?= $this->endSection() ?>
