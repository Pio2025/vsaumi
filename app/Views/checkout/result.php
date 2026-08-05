<?= $this->extend('layout/public') ?>
<?= $this->section('content') ?>

<div class="kt-container-fixed max-w-[440px] pb-16 mt-7.5">
    <div class="kt-card text-center p-7.5">
        <?php if ($transaction['status'] === 'captured'): ?>
            <div class="text-3xl mb-2">✅</div>
            <h2 class="text-lg font-medium text-mono mb-1.5">Payment approved</h2>
            <p class="text-2sm text-secondary-foreground">Your payment to <strong class="text-foreground"><?= esc($merchant['business_name']) ?></strong> was successful.</p>
        <?php elseif ($transaction['status'] === 'failed'): ?>
            <div class="text-3xl mb-2">❌</div>
            <h2 class="text-lg font-medium text-mono mb-1.5">Payment declined</h2>
            <p class="text-2sm text-secondary-foreground">The payment to <strong class="text-foreground"><?= esc($merchant['business_name']) ?></strong> was not completed.</p>
        <?php else: ?>
            <div class="text-3xl mb-2">⏳</div>
            <h2 class="text-lg font-medium text-mono mb-1.5">Payment <?= esc($transaction['status']) ?></h2>
        <?php endif; ?>

        <div class="credential-box mt-5 text-start">
            <div class="row"><span class="text-2sm text-secondary-foreground">Reference</span><span class="mono"><?= esc($transaction['reference']) ?></span></div>
            <div class="row"><span class="text-2sm text-secondary-foreground">Amount</span><span class="mono"><?= esc(number_format((float) $transaction['amount'], 2)) ?> <?= esc($transaction['currency']) ?></span></div>
            <div class="row"><span class="text-2sm text-secondary-foreground">Method</span><span class="mono"><?= esc(strtoupper($transaction['payment_method'])) ?></span></div>
            <div class="row"><span class="text-2sm text-secondary-foreground">Status</span><span class="kt-badge kt-badge-sm <?= status_badge_class($transaction['status']) ?>"><?= esc(ucfirst($transaction['status'])) ?></span></div>
        </div>

        <a href="<?= site_url('/') ?>" class="kt-btn kt-btn-outline justify-center mt-5">Back to VSaumi</a>
    </div>
</div>

<?= $this->endSection() ?>
