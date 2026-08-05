<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
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
        <div class="p-5"><p class="text-secondary-foreground mb-0">No payouts yet. Payouts are created once your settled transactions are batched by an admin.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border" id="payoutsTable">
                    <thead>
                        <tr>
                            <th>Payout ID</th>
                            <th>Net Amount</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $payout): ?>
                            <tr>
                                <td class="mono">#<?= esc($payout['id']) ?></td>
                                <td><?= esc(number_format((float) $payout['net_amount'], 2)) ?></td>
                                <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($payout['status']) ?>"><?= esc(ucfirst($payout['status'])) ?></span></td>
                                <td><?= esc($payout['created_at']) ?></td>
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
            order: [[3, 'desc']],
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
