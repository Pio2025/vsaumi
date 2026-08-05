<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Transactions (<?= count($transactions) ?>)</h3>
        <?php if (! empty($transactions)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input data-kt-datatable-search="#transactionsTable" placeholder="Search transactions" type="text" value="">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($transactions)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No transactions yet. Try the demo checkout link from your dashboard.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10" id="transactionsTable">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true">
                        <thead>
                            <tr>
                                <th class="min-w-[200px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Reference</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Method</span>
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
                                        <span class="kt-table-col-label">Fee</span>
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
                                        <span class="kt-table-col-label">Created</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
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
