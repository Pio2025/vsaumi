<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<form method="post" action="<?= site_url('admin/payouts/run') ?>">
    <?= csrf_field() ?>
    <button type="submit" class="kt-btn kt-btn-primary">Run Payout Batch</button>
</form>
<a href="<?= site_url('admin') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Payouts (<?= count($payouts) ?>)</h3>
        <?php if (! empty($payouts)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input class="js-datatable-search" data-table="#payoutsTable" placeholder="Search payouts" type="text">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($payouts)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No payouts have been processed yet.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border" id="payoutsTable">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Merchant</th>
                            <th>Total</th>
                            <th>Fees</th>
                            <th>Net</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $payout): ?>
                            <tr>
                                <td>
                                    <div class="flex flex-col gap-0.5">
                                        <span class="leading-none font-medium text-sm text-mono"><?= esc($merchantNames[$payout['merchant_id']] ?? '—') ?></span>
                                        <span class="text-xs text-secondary-foreground font-normal mono">#<?= esc($payout['id']) ?></span>
                                    </div>
                                </td>
                                <td><?= esc(number_format((float) $payout['total_amount'], 2)) ?></td>
                                <td><?= esc(number_format((float) $payout['fee_amount'], 2)) ?></td>
                                <td><?= esc(number_format((float) $payout['net_amount'], 2)) ?></td>
                                <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($payout['status']) ?>"><?= esc(ucfirst($payout['status'])) ?></span></td>
                                <td><?= esc($payout['payout_date']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->section('scripts') ?>
<script>
    $(function () {
        $('#payoutsTable').DataTable({
            order: [[5, 'desc']],
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
