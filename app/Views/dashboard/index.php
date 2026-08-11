<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<span class="kt-badge kt-badge-sm <?= status_badge_class($merchant['status']) ?>"><?= esc(ucfirst($merchant['status'])) ?></span>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

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
            <span class="text-2sm text-secondary-foreground font-medium">Total transactions</span>
            <div class="text-2xl font-semibold text-mono mt-1.5"><?= esc($stats['total']) ?></div>
        </div>
        <div class="kt-card p-5">
            <span class="text-2sm text-secondary-foreground font-medium">In progress</span>
            <div class="text-2xl font-semibold text-mono mt-1.5"><?= esc($stats['pending']) ?></div>
        </div>
        <div class="kt-card p-5">
            <span class="text-2sm text-secondary-foreground font-medium">Settled</span>
            <div class="text-2xl font-semibold text-mono mt-1.5"><?= esc($stats['settled']) ?></div>
        </div>
        <div class="kt-card p-5">
            <span class="text-2sm text-secondary-foreground font-medium">Failed</span>
            <div class="text-2xl font-semibold text-mono mt-1.5"><?= esc($stats['failed']) ?></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-7.5 mt-5 lg:mt-7.5">
        <div class="kt-card p-5">
            <span class="text-2sm text-secondary-foreground font-medium">Available balance</span>
            <div class="text-2xl font-semibold text-mono mt-1.5">$<?= esc(number_format($availableBalance, 2)) ?> FJD</div>
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
            <span class="text-2sm text-secondary-foreground font-medium">Total revenue</span>
            <div class="text-2xl font-semibold text-mono mt-1.5">$<?= esc(number_format($totalRevenue, 2)) ?> FJD</div>
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
