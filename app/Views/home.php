<?= $this->extend('layout/public') ?>
<?= $this->section('content') ?>

<section class="kt-container-fixed" style="padding-top: 72px; padding-bottom: 56px;">
    <div class="max-w-[640px] mx-auto text-center">
        <span class="kt-badge kt-badge-outline kt-badge-sm mb-4">Payments for Fiji businesses</span>
        <h1 class="text-4xl lg:text-5xl font-semibold text-mono leading-tight mb-4">Get paid, the Fijian way.</h1>
        <p class="text-lg text-secondary-foreground mb-7">A payment gateway API for Fijian businesses. Accept Visa, Mastercard, Vodafone M-PAiSA and Digicel MyCash from one integration, and get settled straight into your account.</p>
        <div class="flex flex-wrap items-center justify-center gap-2.5">
            <a href="<?= site_url('signup') ?>" class="kt-btn kt-btn-lg kt-btn-primary">Start now</a>
            <?php if ($demoApiKey): ?>
                <a href="<?= site_url('checkout/' . $demoApiKey) ?>?amount=50" class="kt-btn kt-btn-lg kt-btn-outline">Try a demo checkout</a>
            <?php endif; ?>
        </div>
        <p class="text-xs text-secondary-foreground mt-4 mb-0">No credit card required · Live in minutes</p>
    </div>
</section>

<section id="payments" class="bg-muted border-t border-b border-border" style="padding-top: 32px; padding-bottom: 32px;">
    <div class="kt-container-fixed">
        <p class="text-xs uppercase tracking-wide text-secondary-foreground text-center mb-5">Accept payments from</p>
        <div class="flex flex-wrap items-center justify-center gap-7.5">
            <div class="flex items-center gap-2">
                <span class="text-xl">💳</span>
                <span class="text-sm font-medium text-mono">Visa</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xl">💳</span>
                <span class="text-sm font-medium text-mono">Mastercard</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xl">📱</span>
                <span class="text-sm font-medium text-mono">M-PAiSA</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xl">📱</span>
                <span class="text-sm font-medium text-mono">MyCash</span>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="kt-container-fixed" style="padding-top: 56px; padding-bottom: 56px;">
    <div class="text-center mb-7.5">
        <h2 class="text-xl font-semibold text-mono mb-1.5">How the money moves</h2>
        <p class="text-2sm text-secondary-foreground mb-0">The same flow every transaction takes, from checkout to your bank account.</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="kt-card p-5">
            <div class="text-2xl mb-2">1️⃣</div>
            <h4 class="text-sm font-medium text-mono mb-1">Authorize</h4>
            <p class="text-2sm text-secondary-foreground mb-0">Customer pays at checkout. VSaumi sends the request to the card network or mobile wallet.</p>
        </div>
        <div class="kt-card p-5">
            <div class="text-2xl mb-2">2️⃣</div>
            <h4 class="text-sm font-medium text-mono mb-1">Capture</h4>
            <p class="text-2sm text-secondary-foreground mb-0">The provider confirms funds — a card is authorized, or the customer approves on their phone.</p>
        </div>
        <div class="kt-card p-5">
            <div class="text-2xl mb-2">3️⃣</div>
            <h4 class="text-sm font-medium text-mono mb-1">Settle</h4>
            <p class="text-2sm text-secondary-foreground mb-0">Captured funds are batched by the provider and land in VSaumi's master account.</p>
        </div>
        <div class="kt-card p-5">
            <div class="text-2xl mb-2">4️⃣</div>
            <h4 class="text-sm font-medium text-mono mb-1">Payout</h4>
            <p class="text-2sm text-secondary-foreground mb-0">VSaumi pays each merchant their net balance, minus the platform fee.</p>
        </div>
    </div>
</section>

<section id="pricing" class="bg-muted border-t border-b border-border" style="padding-top: 56px; padding-bottom: 56px;">
    <div class="kt-container-fixed">
        <div class="text-center mb-7.5">
            <h2 class="text-xl font-semibold text-mono mb-1.5">Simple, transparent pricing</h2>
            <p class="text-2sm text-secondary-foreground mb-0">Pick a plan and change it any time as you grow.</p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 max-w-[960px] mx-auto">
            <?php foreach ($plans as $key => $plan): ?>
                <div class="kt-card <?= $key === 'growth' ? 'border-2 border-primary' : '' ?> p-6">
                    <?php if ($key === 'growth'): ?>
                        <span class="kt-badge kt-badge-primary kt-badge-sm mb-3">Most popular</span>
                    <?php endif; ?>
                    <h3 class="text-base font-semibold text-mono mb-1"><?= esc($plan['label']) ?></h3>
                    <div class="flex items-baseline gap-1 mb-1.5">
                        <span class="text-3xl font-semibold text-mono">$<?= esc($plan['price']) ?></span>
                        <span class="text-2sm text-secondary-foreground">FJD/mo</span>
                    </div>
                    <p class="text-2sm text-secondary-foreground mb-5"><?= esc($plan['note']) ?></p>
                    <ul class="flex flex-col gap-2 mb-5">
                        <?php foreach ($plan['features'] as $feature): ?>
                            <li class="flex items-center gap-2 text-2sm text-mono">
                                <i class="ki-filled ki-check text-green-600"></i>
                                <?= esc($feature) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= site_url('signup') ?>" class="kt-btn <?= $key === 'growth' ? 'kt-btn-primary' : 'kt-btn-outline' ?> justify-center w-full">Get started</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="kt-container-fixed" style="padding-top: 56px; padding-bottom: 64px;">
    <div class="max-w-[440px] mx-auto text-center">
        <h2 class="text-xl font-semibold text-mono mb-1.5">Ready to accept payments?</h2>
        <p class="text-2sm text-secondary-foreground mb-5">Sign up, get your API keys, and start testing in minutes.</p>
        <a href="<?= site_url('signup') ?>" class="kt-btn kt-btn-primary">Create your merchant account</a>
    </div>
</section>

<?= $this->endSection() ?>
