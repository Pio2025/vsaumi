<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin/disbursements/run') ?>" class="kt-btn kt-btn-primary">Run Disbursement Batch</a>
<a href="<?= site_url('admin/payouts') ?>" class="kt-btn kt-btn-outline">Back to payouts</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Disbursement batches (<?= count($batches) ?>)</h3>
        <?php if (! empty($batches)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input data-kt-datatable-search="#disbursementsTable" placeholder="Search batches" type="text" value="">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($batches)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No disbursement batches have been run yet.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10" id="disbursementsTable">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true">
                        <thead>
                            <tr>
                                <th class="min-w-[160px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Reference</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Items</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Total</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Created</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($batches as $batch): ?>
                                <tr>
                                    <td class="font-medium text-mono mono"><?= esc($batch['reference']) ?></td>
                                    <td><?= esc($batch['item_count']) ?></td>
                                    <td><?= esc(number_format((float) $batch['total_amount'], 2)) ?> FJD</td>
                                    <td><?= esc($batch['created_at']) ?></td>
                                    <td>
                                        <a href="<?= site_url('admin/disbursements/' . $batch['id'] . '/report') ?>" target="_blank" class="kt-btn kt-btn-sm kt-btn-outline">
                                            <i class="ki-filled ki-document"></i>
                                            View Report
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
