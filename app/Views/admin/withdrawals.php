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
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($request['status']) ?>"><?= esc(ucfirst($request['status'])) ?></span>
                                            <?php if ($request['status'] === 'rejected' && ! empty($request['fund_disposition'])): ?>
                                                <span class="text-xs text-secondary-foreground"><?= $request['fund_disposition'] === 'forfeited' ? 'Funds forfeited' : 'Returned to balance' ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= esc($request['created_at']) ?></td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="View transaction details" data-kt-modal-toggle="#view_withdrawal_modal_<?= esc($request['id'], 'attr') ?>">
                                                <i class="ki-filled ki-eye"></i>
                                            </button>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <form method="post" action="<?= site_url('admin/withdrawals/' . $request['id'] . '/approve') ?>" class="js-approve-form" data-confirm-name="<?= esc($request['merchant_name'], 'attr') ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">Approve</button>
                                                </form>
                                                <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" data-kt-modal-toggle="#reject_withdrawal_modal_<?= esc($request['id'], 'attr') ?>">Reject</button>
                                            <?php endif; ?>
                                        </div>
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

<?php foreach ($requests as $request): ?>
    <?php $requestTransactions = $transactionsByRequest[$request['id']] ?? []; ?>
    <div class="kt-modal" data-kt-modal="true" id="view_withdrawal_modal_<?= esc($request['id'], 'attr') ?>">
        <div class="kt-modal-dialog kt-modal-open:!flex">
            <div class="kt-modal-content max-w-[640px]">
                <div class="kt-modal-header">
                    <h3 class="kt-modal-title">Withdrawal request #<?= esc($request['id']) ?> — <?= esc($request['merchant_name']) ?></h3>
                    <button class="kt-modal-close" data-kt-modal-dismiss="true" type="button">
                        <i class="ki-filled ki-cross"></i>
                    </button>
                </div>
                <div class="kt-modal-body flex flex-col gap-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <span class="text-xs text-secondary-foreground block mb-1">Amount requested</span>
                            <span class="text-sm font-medium text-mono"><?= esc(number_format((float) $request['amount'], 2)) ?> FJD</span>
                        </div>
                        <div>
                            <span class="text-xs text-secondary-foreground block mb-1">Status</span>
                            <span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($request['status']) ?>"><?= esc(ucfirst($request['status'])) ?></span>
                        </div>
                        <div>
                            <span class="text-xs text-secondary-foreground block mb-1">Requested</span>
                            <span class="text-sm text-mono"><?= esc($request['created_at']) ?></span>
                        </div>
                        <div>
                            <span class="text-xs text-secondary-foreground block mb-1">Transactions</span>
                            <span class="text-sm text-mono"><?= count($requestTransactions) ?></span>
                        </div>
                    </div>

                    <?php if ($request['status'] === 'rejected' && ! empty($request['admin_note'])): ?>
                        <div class="kt-alert kt-alert-outline <?= $request['fund_disposition'] === 'forfeited' ? 'kt-alert-destructive' : 'kt-alert-warning' ?>">
                            <span class="kt-alert-icon"><i class="ki-filled ki-information-2"></i></span>
                            <div>
                                <span class="kt-alert-title block"><?= $request['fund_disposition'] === 'forfeited' ? 'Rejected — funds forfeited' : 'Rejected — funds returned to balance' ?></span>
                                <span class="text-2sm"><?= esc($request['admin_note']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($requestTransactions)): ?>
                        <p class="text-2sm text-secondary-foreground mb-0">No transaction details are available for this request.</p>
                    <?php else: ?>
                        <div class="kt-scrollable-x-auto">
                            <table class="kt-table kt-table-border text-2sm">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Product / service</th>
                                        <th>Amount</th>
                                        <th>Fee</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requestTransactions as $transaction): ?>
                                        <tr>
                                            <td class="mono"><?= esc($transaction['reference']) ?></td>
                                            <td><?= esc($transaction['product_name'] ?? '—') ?></td>
                                            <td><?= esc(number_format((float) $transaction['amount'], 2)) ?> <?= esc($transaction['currency']) ?></td>
                                            <td><?= esc(number_format((float) $transaction['fee_amount'], 2)) ?> <?= esc($transaction['currency']) ?></td>
                                            <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($transaction['status']) ?>"><?= esc(ucfirst($transaction['status'])) ?></span></td>
                                            <td><?= esc($transaction['created_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="kt-modal-footer">
                    <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($request['status'] === 'pending'): ?>
        <div class="kt-modal" data-kt-modal="true" id="reject_withdrawal_modal_<?= esc($request['id'], 'attr') ?>">
            <div class="kt-modal-dialog kt-modal-open:!flex">
                <div class="kt-modal-content max-w-[560px]">
                    <div class="kt-modal-header">
                        <h3 class="kt-modal-title">Reject withdrawal — <?= esc($request['merchant_name']) ?></h3>
                        <button class="kt-modal-close" data-kt-modal-dismiss="true" type="button">
                            <i class="ki-filled ki-cross"></i>
                        </button>
                    </div>
                    <form method="post" action="<?= site_url('admin/withdrawals/' . $request['id'] . '/reject') ?>">
                        <?= csrf_field() ?>
                        <div class="kt-modal-body flex flex-col gap-4">
                            <div>
                                <label class="kt-form-label mb-1.5">Reason for rejection</label>
                                <textarea name="admin_note" class="kt-textarea" rows="3" placeholder="Explain why this withdrawal request is being rejected…" required></textarea>
                            </div>

                            <div>
                                <label class="kt-form-label mb-2">What happens to the funds?</label>
                                <div class="flex flex-col gap-2.5">
                                    <label class="kt-card cursor-pointer has-[:checked]:border-primary p-3.5">
                                        <div class="flex items-start gap-3">
                                            <input type="radio" name="disposition" value="returned" class="kt-radio mt-0.5" checked>
                                            <div>
                                                <div class="flex items-center gap-2 font-medium text-mono text-sm">
                                                    <i class="ki-filled ki-wallet text-success"></i>
                                                    Return to available balance
                                                </div>
                                                <div class="text-xs text-secondary-foreground mt-0.5">The transactions stay as earned. The merchant will see this amount in their Available balance again and can request a withdrawal later.</div>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="kt-card cursor-pointer has-[:checked]:border-primary p-3.5">
                                        <div class="flex items-start gap-3">
                                            <input type="radio" name="disposition" value="forfeited" class="kt-radio mt-0.5">
                                            <div>
                                                <div class="flex items-center gap-2 font-medium text-mono text-sm">
                                                    <i class="ki-filled ki-cross-circle text-destructive"></i>
                                                    Remove funds
                                                </div>
                                                <div class="text-xs text-secondary-foreground mt-0.5">The transactions backing this request are voided and permanently removed from the merchant's balance. This cannot be undone.</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="kt-modal-footer">
                            <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Cancel</button>
                            <button type="submit" class="kt-btn kt-btn-destructive">Reject request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?= $this->endSection() ?>
