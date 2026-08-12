<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin/payouts') ?>" class="kt-btn kt-btn-outline">Cancel</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$modeLabels = ['bank' => 'Bank Transfer', 'digital_wallet' => 'Digital Wallet'];
$totalItems = count($groups['bank']) + count($groups['digital_wallet']);
?>

<?php if ($totalItems === 0): ?>
    <div class="kt-card">
        <div class="p-5"><p class="text-secondary-foreground mb-0">No processed payouts are currently waiting for disbursement.</p></div>
    </div>
<?php else: ?>
    <form method="post" action="<?= site_url('admin/disbursements/run') ?>" id="disbursement_run_form">
        <?= csrf_field() ?>

        <div class="kt-alert kt-alert-outline kt-alert-primary mb-5">
            <span class="kt-alert-icon"><i class="ki-filled ki-information-2"></i></span>
            <div>
                <span class="kt-alert-title block">Select the payouts to disburse</span>
                <span class="text-2sm">Grouped by the merchant's chosen payment option. Uncheck any item to leave it out of this batch — it will remain available for a future disbursement run.</span>
            </div>
        </div>

        <?php foreach (['bank', 'digital_wallet'] as $mode): ?>
            <?php $payouts = $groups[$mode]; ?>
            <?php if (empty($payouts)) continue; ?>
            <div class="kt-card kt-card-grid mb-5">
                <div class="kt-card-header py-4 flex-wrap gap-2">
                    <h3 class="kt-card-title"><?= esc($modeLabels[$mode]) ?> (<?= count($payouts) ?>)</h3>
                    <label class="kt-form-label flex items-center gap-2 font-normal text-2sm">
                        <input type="checkbox" class="kt-checkbox kt-checkbox-sm js-group-toggle" data-group="<?= esc($mode, 'attr') ?>" checked>
                        Select all
                    </label>
                </div>
                <div class="kt-card-content">
                    <div class="kt-scrollable-x-auto">
                        <table class="kt-table kt-table-border">
                            <thead>
                                <tr>
                                    <th class="w-10"></th>
                                    <th>Merchant</th>
                                    <th>Provider</th>
                                    <th>Account</th>
                                    <th>Payout</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payouts as $payout): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="kt-checkbox kt-checkbox-sm js-item-checkbox js-group-<?= esc($mode, 'attr') ?>" name="payout_ids[]" value="<?= esc($payout['id'], 'attr') ?>" data-amount="<?= esc($payout['net_amount'], 'attr') ?>" checked>
                                        </td>
                                        <td class="font-medium text-mono"><?= esc($payout['business_name']) ?></td>
                                        <td><?= esc($payout['provider_name'] ?? 'Not on file') ?><?= $payout['provider_bsb_code'] ? ' <span class="text-xs text-secondary-foreground">BSB ' . esc($payout['provider_bsb_code']) . '</span>' : '' ?></td>
                                        <td><span class="text-2sm text-mono"><?= esc($payout['account_name'] ?? '—') ?></span><br><span class="text-xs text-secondary-foreground mono"><?= esc($payout['account_number'] ?? '—') ?></span></td>
                                        <td class="text-2sm">#<?= esc($payout['id']) ?><br><span class="text-xs text-secondary-foreground"><?= esc($payout['payout_date']) ?></span></td>
                                        <td class="text-right font-medium text-mono"><?= esc(number_format((float) $payout['net_amount'], 2)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="kt-card">
            <div class="kt-card-content flex flex-wrap items-center justify-between gap-4 p-5">
                <div>
                    <span class="text-2sm text-secondary-foreground block">Selected for disbursement</span>
                    <span class="text-lg font-semibold text-mono"><span id="selected_count">0</span> item(s) — $<span id="selected_total">0.00</span></span>
                </div>
                <div class="flex items-center gap-2.5">
                    <a href="<?= site_url('admin/payouts') ?>" class="kt-btn kt-btn-outline">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary">Run disbursement batch</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        (function () {
            var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.js-item-checkbox'));
            var groupToggles = Array.prototype.slice.call(document.querySelectorAll('.js-group-toggle'));
            var countEl = document.getElementById('selected_count');
            var totalEl = document.getElementById('selected_total');

            function refreshSummary() {
                var count = 0;
                var total = 0;

                checkboxes.forEach(function (checkbox) {
                    if (checkbox.checked) {
                        count += 1;
                        total += parseFloat(checkbox.dataset.amount) || 0;
                    }
                });

                countEl.textContent = count;
                totalEl.textContent = total.toFixed(2);
            }

            groupToggles.forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    document.querySelectorAll('.js-group-' + toggle.dataset.group).forEach(function (checkbox) {
                        checkbox.checked = toggle.checked;
                    });
                    refreshSummary();
                });
            });

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', refreshSummary);
            });

            refreshSummary();
        })();
    </script>
<?php endif; ?>

<?= $this->endSection() ?>
