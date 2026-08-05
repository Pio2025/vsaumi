<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card">
    <?php if (empty($merchants)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No merchants have signed up yet.</p></div>
    <?php else: ?>
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border" id="merchantsTable">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Plan</th>
                            <th>Signed up</th>
                            <th class="w-[220px]"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($merchants as $merchant): ?>
                            <tr>
                                <td class="text-foreground font-medium"><?= esc($merchant['business_name']) ?></td>
                                <td><?= esc($merchant['contact_email']) ?></td>
                                <td><span class="kt-badge kt-badge-sm <?= status_badge_class($merchant['status']) ?>"><?= esc(ucfirst($merchant['status'])) ?></span></td>
                                <td><?= esc(ucfirst($latestPlans[$merchant['id']] ?? '—')) ?></td>
                                <td><?= esc($merchant['created_at']) ?></td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <?php if ($merchant['status'] === 'pending'): ?>
                                            <form method="post" action="<?= site_url('admin/merchants/' . $merchant['id'] . '/approve') ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">Approve</button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="<?= site_url('admin/merchants/' . $merchant['id'] . '/edit') ?>" class="kt-btn kt-btn-sm kt-btn-outline">Edit</a>
                                        <form method="post" action="<?= site_url('admin/merchants/' . $merchant['id'] . '/delete') ?>" class="js-delete-form" data-confirm-name="<?= esc($merchant['business_name'], 'attr') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="kt-btn kt-btn-sm kt-btn-destructive">Delete</button>
                                        </form>
                                    </div>
                                </td>
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
        $('#merchantsTable').DataTable({
            order: [[4, 'desc']],
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
