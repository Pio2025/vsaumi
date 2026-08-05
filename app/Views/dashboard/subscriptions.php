<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Subscriptions (<?= count($subscriptions) ?>)</h3>
        <?php if (! empty($subscriptions)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input class="js-datatable-search" data-table="#subscriptionsTable" placeholder="Search subscriptions" type="text">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($subscriptions)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No subscriptions yet.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border" id="subscriptionsTable">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Expires</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $sub): ?>
                            <tr>
                                <td class="text-mono font-medium"><?= esc(ucfirst($sub['plan'])) ?></td>
                                <td><?= esc(number_format((float) $sub['amount'], 2)) ?> FJD</td>
                                <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($sub['status']) ?>"><?= esc(ucfirst($sub['status'])) ?></span></td>
                                <td><?= esc($sub['started_at']) ?></td>
                                <td><?= esc($sub['expires_at']) ?></td>
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
        $('#subscriptionsTable').DataTable({
            order: [[3, 'desc']],
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
