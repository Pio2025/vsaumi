<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card">
    <?php if (empty($subscriptions)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No subscriptions yet.</p></div>
    <?php else: ?>
        <div class="kt-card-table">
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
                                <td><?= esc(ucfirst($sub['plan'])) ?></td>
                                <td><?= esc(number_format((float) $sub['amount'], 2)) ?> FJD</td>
                                <td><span class="kt-badge kt-badge-sm <?= status_badge_class($sub['status']) ?>"><?= esc(ucfirst($sub['status'])) ?></span></td>
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
