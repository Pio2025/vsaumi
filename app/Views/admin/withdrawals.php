<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Withdrawal requests (<?= count($requests) ?>)</h3>
        <?php if (! empty($requests)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input data-kt-datatable-search="#withdrawalsTable" placeholder="Search requests" type="text" value="">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($requests)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No withdrawal requests yet.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10" id="withdrawalsTable">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true">
                        <thead>
                            <tr>
                                <th class="min-w-[200px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Merchant</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Amount</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Status</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Requested</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="leading-none font-medium text-sm text-mono"><?= esc($request['merchant_name']) ?></span>
                                            <span class="text-xs text-secondary-foreground font-normal mono">#<?= esc($request['id']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= esc(number_format((float) $request['amount'], 2)) ?> FJD</td>
                                    <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($request['status']) ?>"><?= esc(ucfirst($request['status'])) ?></span></td>
                                    <td><?= esc($request['created_at']) ?></td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <div class="flex items-center gap-2">
                                                <form method="post" action="<?= site_url('admin/withdrawals/' . $request['id'] . '/approve') ?>" class="js-approve-form" data-confirm-name="<?= esc($request['merchant_name'], 'attr') ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">Approve</button>
                                                </form>
                                                <form method="post" action="<?= site_url('admin/withdrawals/' . $request['id'] . '/reject') ?>" class="js-reject-form" data-confirm-name="<?= esc($request['merchant_name'], 'attr') ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-outline">Reject</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-secondary-foreground text-2sm">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                    <div class="flex items-center gap-2 order-2 md:order-1">
                        Show
                        <select class="kt-select w-16" data-kt-datatable-size="true" data-kt-select="" name="perpage"></select>
                        per page
                    </div>
                    <div class="flex items-center gap-4 order-1 md:order-2">
                        <span data-kt-datatable-info="true"></span>
                        <div class="kt-datatable-pagination" data-kt-datatable-pagination="true"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
