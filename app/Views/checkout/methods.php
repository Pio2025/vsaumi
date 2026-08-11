<?= $this->extend('layout/public') ?>
<?= $this->section('content') ?>

<div class="kt-container-fixed max-w-[640px] pb-16">
    <div class="text-center mt-7.5 mb-7.5">
        <p class="text-2sm text-secondary-foreground mb-1">Paying <strong class="text-foreground"><?= esc($merchant['business_name']) ?></strong></p>
        <h1 class="text-2xl font-semibold text-mono"><?= esc(number_format($amount, 2)) ?> FJD</h1>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
        <?php foreach ($methods as $key => $method): ?>
            <a class="kt-card items-center text-center p-5 hover:shadow-md transition-shadow" href="<?= site_url('checkout/' . $application['api_key'] . '/pay/' . $key) . '?amount=' . urlencode((string) $amount) . '&' . http_build_query(array_filter($product, fn ($v) => $v !== null && $v !== '')) ?>">
                <div class="text-2xl mb-2"><?= $method['kind'] === 'card' ? '💳' : '📱' ?></div>
                <div class="text-sm font-medium text-mono"><?= esc($method['label']) ?></div>
                <div class="text-xs text-secondary-foreground"><?= $method['kind'] === 'card' ? 'Credit / debit card' : 'Mobile money' ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <p class="text-xs text-secondary-foreground text-center mt-7.5 mb-0">This is a simulated checkout — no real card networks or mobile money providers are contacted.</p>
</div>

<?= $this->endSection() ?>
