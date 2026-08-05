<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5">
    <div class="kt-card p-5">
        <span class="text-2sm text-secondary-foreground font-medium">Pending merchants</span>
        <div class="text-2xl font-semibold text-mono mt-1.5"><?= esc($stats['pending_merchants']) ?></div>
    </div>
    <div class="kt-card p-5">
        <span class="text-2sm text-secondary-foreground font-medium">Active merchants</span>
        <div class="text-2xl font-semibold text-mono mt-1.5"><?= esc($stats['active_merchants']) ?></div>
    </div>
    <div class="kt-card p-5">
        <span class="text-2sm text-secondary-foreground font-medium">Total merchants</span>
        <div class="text-2xl font-semibold text-mono mt-1.5"><?= esc($stats['total_merchants']) ?></div>
    </div>
    <div class="kt-card p-5">
        <span class="text-2sm text-secondary-foreground font-medium">Captured, awaiting settlement</span>
        <div class="text-2xl font-semibold text-mono mt-1.5"><?= esc($stats['captured_count']) ?></div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7.5 mt-5 lg:mt-7.5 items-stretch">
    <div class="kt-card">
        <div class="kt-card-content flex flex-col justify-between gap-4 p-6 h-full">
            <div>
                <h3 class="text-base font-medium text-mono mb-1.5">Settlement</h3>
                <p class="text-2sm text-secondary-foreground">Moves captured transactions to <span class="kt-badge kt-badge-sm kt-badge-primary">settled</span>, applying the platform fee.</p>
            </div>
            <form method="post" action="<?= site_url('admin/settlement/run') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="kt-btn kt-btn-primary justify-center w-full">Run Settlement Batch</button>
            </form>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-content flex flex-col justify-between gap-4 p-6 h-full">
            <div>
                <h3 class="text-base font-medium text-mono mb-1.5">Payouts</h3>
                <p class="text-2sm text-secondary-foreground"><?= esc($stats['settled_unpaid']) ?> merchant(s) currently have unpaid settled funds.</p>
            </div>
            <form method="post" action="<?= site_url('admin/payouts/run') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="kt-btn kt-btn-mono justify-center w-full">Run Payout Batch</button>
            </form>
        </div>
    </div>

    <a href="<?= site_url('admin/merchants') ?>" class="kt-card hover:shadow-md transition-shadow">
        <div class="kt-card-content flex flex-col justify-between gap-4 p-6 h-full">
            <div>
                <h3 class="text-base font-medium text-mono mb-1.5">Manage Merchants →</h3>
                <p class="text-2sm text-secondary-foreground mb-0">Review, approve, edit, or remove merchant accounts.</p>
            </div>
        </div>
    </a>

    <a href="<?= site_url('admin/payouts') ?>" class="kt-card hover:shadow-md transition-shadow">
        <div class="kt-card-content flex flex-col justify-between gap-4 p-6 h-full">
            <div>
                <h3 class="text-base font-medium text-mono mb-1.5">View Payouts →</h3>
                <p class="text-2sm text-secondary-foreground mb-0">See every payout batch that's been processed.</p>
            </div>
        </div>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5 mt-5 lg:mt-7.5">
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Merchants by Status</h3>
        </div>
        <div class="kt-card-content flex items-center gap-5 p-5">
            <div class="flex-1" id="merchantStatusChart"></div>
            <div class="flex-1 flex flex-col gap-3">
                <?php $statusColors = ['pending' => '#2f6fed', 'approved' => '#be1e2d', 'active' => '#1f9d55', 'suspended' => '#6b5f5e']; ?>
                <?php foreach ($statusCounts as $status => $count): ?>
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full shrink-0" style="background-color: <?= esc($statusColors[$status] ?? '#999', 'attr') ?>"></span>
                        <span class="text-2sm text-secondary-foreground"><?= esc(ucfirst($status)) ?></span>
                    </div>
                    <span class="text-sm font-medium text-mono"><?= esc($count) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">Transactions by Method</h3>
        </div>
        <div class="kt-card-content p-5">
            <div id="methodChart"></div>
        </div>
    </div>
</div>

<div class="kt-card mt-5 lg:mt-7.5">
    <div class="kt-card-header">
        <h3 class="kt-card-title">Transaction Volume — Last 14 Days</h3>
    </div>
    <div class="kt-card-content p-5">
        <div id="volumeChart"></div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    (function () {
        const brand = '#be1e2d';
        const accent = '#f7941e';
        const info = '#2f6fed';
        const success = '#1f9d55';
        const muted = '#6b5f5e';

        new ApexCharts(document.querySelector('#merchantStatusChart'), {
            chart: { type: 'donut', height: 220, fontFamily: 'inherit' },
            series: <?= json_encode(array_values($statusCounts)) ?>,
            labels: <?= json_encode(array_map('ucfirst', array_keys($statusCounts))) ?>,
            colors: [info, brand, success, muted],
            legend: { show: false },
            dataLabels: { enabled: false },
        }).render();

        new ApexCharts(document.querySelector('#methodChart'), {
            chart: { type: 'bar', height: 260, fontFamily: 'inherit', toolbar: { show: false } },
            series: [{ name: 'Transactions', data: <?= json_encode(array_values($methodCounts)) ?> }],
            xaxis: { categories: <?= json_encode(array_keys($methodCounts)) ?> },
            colors: [accent],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
            dataLabels: { enabled: false },
            legend: { show: false },
        }).render();

        const daily = <?= json_encode($dailySeries) ?>;
        new ApexCharts(document.querySelector('#volumeChart'), {
            chart: { type: 'area', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
            series: [{ name: 'Transaction count', data: daily.map(d => d.count) }],
            xaxis: { categories: daily.map(d => d.day) },
            colors: [brand],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0 } },
            dataLabels: { enabled: false },
            legend: { show: false },
        }).render();
    })();
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
