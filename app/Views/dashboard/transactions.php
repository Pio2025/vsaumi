<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Transactions (<?= count($transactions) ?>)</h3>
        <?php if (! empty($transactions)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input class="js-datatable-search" data-table="#transactionsTable" placeholder="Search transactions" type="text">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($transactions)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No transactions yet. Try the demo checkout link from your dashboard.</p></div>
    <?php else: ?>
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border" id="transactionsTable">
                    <thead>
                        <tr>
                            <th class="min-w-[200px]">Reference</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td class="mono"><?= esc($tx['reference']) ?></td>
                                <td><?= esc(strtoupper($tx['payment_method'])) ?></td>
                                <td><?= esc(number_format((float) $tx['amount'], 2)) ?> <?= esc($tx['currency']) ?></td>
                                <td><?= esc(number_format((float) ($tx['fee_amount'] ?? 0), 2)) ?></td>
                                <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($tx['status']) ?>"><?= esc(ucfirst($tx['status'])) ?></span></td>
                                <td><?= esc($tx['created_at']) ?></td>
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
        $('#transactionsTable').DataTable({
            order: [[5, 'desc']],
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
