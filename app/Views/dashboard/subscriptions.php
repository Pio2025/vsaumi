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
                <input data-kt-datatable-search="#subscriptionsTable" placeholder="Search subscriptions" type="text" value="">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($subscriptions)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No subscriptions yet.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10" id="subscriptionsTable">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true">
                        <thead>
                            <tr>
                                <th class="min-w-[160px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Application</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Plan</span>
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
                                        <span class="kt-table-col-label">Started</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Expires</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Invoice</span>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscriptions as $sub): ?>
                                <tr>
                                    <td class="text-mono font-medium"><?= esc($sub['application_name'] ?? '—') ?></td>
                                    <td class="text-mono font-medium"><?= esc(ucfirst($sub['plan'])) ?></td>
                                    <td><?= esc(number_format((float) $sub['amount'], 2)) ?> FJD</td>
                                    <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($sub['status']) ?>"><?= esc(ucfirst($sub['status'])) ?></span></td>
                                    <td><?= esc($sub['started_at']) ?></td>
                                    <td><?= esc($sub['expires_at']) ?></td>
                                    <td>
                                        <a href="<?= site_url('dashboard/applications/' . $sub['application_id'] . '/subscriptions/' . $sub['id'] . '/invoice') ?>" class="kt-btn kt-btn-sm kt-btn-outline">
                                            <i class="ki-filled ki-file-down"></i> PDF
                                        </a>
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
