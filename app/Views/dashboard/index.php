<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<span class="kt-badge kt-badge-sm <?= status_badge_class($merchant['status']) ?>"><?= esc(ucfirst($merchant['status'])) ?></span>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if ($apiSecretOnce): ?>
    <div class="kt-card border-primary mb-5 lg:mb-7.5">
        <div class="kt-card-content p-6">
            <h3 class="text-base font-medium text-mono mb-1.5">Your API credentials</h3>
            <p class="text-2sm text-secondary-foreground mb-3">Save your API secret now — it will not be shown again.</p>
            <div class="credential-box">
                <div class="row"><span class="text-2sm text-secondary-foreground">API Key</span><span class="mono"><?= esc($merchant['api_key']) ?></span></div>
                <div class="row"><span class="text-2sm text-secondary-foreground">API Secret</span><span class="mono"><?= esc($apiSecretOnce) ?></span></div>
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
            <h3 class="text-base font-medium text-mono mb-1.5"><?= $merchant['status'] === 'suspended' ? 'Renew your subscription' : "You're approved — choose a plan" ?></h3>
            <p class="text-2sm text-secondary-foreground mb-5">This charges nothing real — it's a simulated payment that activates your API access for 30 days, standing in for the real subscription billing described in the design doc.</p>
            <form method="post" action="<?= site_url('dashboard/subscribe') ?>">
                <?= csrf_field() ?>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <?php foreach ($plans as $key => $plan): ?>
                        <label class="kt-card cursor-pointer has-[:checked]:border-primary p-4">
                            <input type="radio" name="plan" value="<?= esc($key) ?>" class="kt-radio mb-2" <?= $key === 'starter' ? 'checked' : '' ?>>
                            <div class="font-medium text-mono"><?= esc($plan['label']) ?> — $<?= esc($plan['price']) ?>/mo</div>
                            <div class="text-xs text-secondary-foreground"><?= esc($plan['note']) ?></div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="kt-btn kt-btn-mono mt-5">Simulate Payment &amp; Activate</button>
            </form>
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

    <div class="kt-card mt-5 lg:mt-7.5">
        <div class="kt-card-content p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-medium text-mono">Subscription</h3>
                <a href="<?= site_url('dashboard/subscriptions') ?>" class="kt-btn kt-btn-sm kt-btn-outline">View history</a>
            </div>
            <p class="text-2sm text-secondary-foreground mb-0">
                Plan: <strong class="text-foreground"><?= esc(ucfirst($subscription['plan'] ?? '—')) ?></strong>
                · Renews/expires: <strong class="text-foreground"><?= esc($subscription['expires_at'] ?? '—') ?></strong>
            </p>
        </div>
    </div>

    <div class="kt-card mt-5 lg:mt-7.5">
        <div class="kt-card-content p-6">
            <h3 class="text-base font-medium text-mono mb-1.5">Try a demo checkout</h3>
            <p class="text-2sm text-secondary-foreground mb-4">This simulates a customer landing on your storefront's "Pay with VSaumi" button — pick an amount and open it.</p>
            <form method="get" action="<?= site_url('checkout/' . $merchant['api_key']) ?>" target="_blank" class="flex flex-wrap items-end gap-3.5">
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="amount">Amount (FJD)</label>
                    <input class="kt-input w-40" type="number" id="amount" name="amount" value="50.00" min="1" step="0.01">
                </div>
                <button type="submit" class="kt-btn kt-btn-primary">Open Demo Checkout</button>
            </form>
        </div>
    </div>

    <div class="kt-card mt-5 lg:mt-7.5">
        <div class="kt-card-content p-6">
            <h3 class="text-base font-medium text-mono mb-3">API Credentials</h3>
            <div class="credential-box">
                <div class="row"><span class="text-2sm text-secondary-foreground">API Key</span><span class="mono"><?= esc($merchant['api_key']) ?></span></div>
            </div>
            <p class="text-xs text-secondary-foreground mt-2 mb-0">Your API secret was shown once at signup and is stored encrypted — it can't be displayed again. Contact support to rotate it.</p>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
