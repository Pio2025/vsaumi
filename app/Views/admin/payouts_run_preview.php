<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin/payouts') ?>" class="kt-btn kt-btn-outline">Cancel</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$grandTotal = array_sum(array_column($rows, 'total_amount'));
$grandFees  = array_sum(array_column($rows, 'total_fees'));
$grandNet   = array_sum(array_column($rows, 'net_amount'));
?>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Payout batch preview — <?= count($rows) ?> merchant(s)</h3>
    </div>
    <?php if (empty($rows)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No merchants currently have settled, unpaid funds — there's nothing to pay out.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="kt-alert kt-alert-outline kt-alert-primary mb-5">
                <span class="kt-alert-icon"><i class="ki-filled ki-information-2"></i></span>
                <div>
                    <span class="kt-alert-title block">Review before running</span>
                    <span class="text-2sm">These are the merchants and amounts that will be bundled into new payout batches. This does not disburse funds — it only creates payout records ready for a later disbursement run.</span>
                </div>
            </div>
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border">
                    <thead>
                        <tr>
                            <th>Merchant</th>
                            <th>Transactions</th>
                            <th>Total</th>
                            <th>Fees</th>
                            <th>Net payout</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td class="font-medium text-mono"><?= esc($row['business_name']) ?></td>
                                <td><?= esc($row['transaction_count']) ?></td>
                                <td><?= esc(number_format($row['total_amount'], 2)) ?></td>
                                <td><?= esc(number_format($row['total_fees'], 2)) ?></td>
                                <td class="font-medium text-mono"><?= esc(number_format($row['net_amount'], 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-medium text-mono">
                            <td>Total</td>
                            <td></td>
                            <td><?= esc(number_format($grandTotal, 2)) ?></td>
                            <td><?= esc(number_format($grandFees, 2)) ?></td>
                            <td><?= esc(number_format($grandNet, 2)) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="kt-card-footer justify-end gap-2.5">
            <a href="<?= site_url('admin/payouts') ?>" class="kt-btn kt-btn-outline">Cancel</a>
            <form method="post" action="<?= site_url('admin/payouts/run') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="kt-btn kt-btn-primary">Confirm &amp; run payout batch</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
