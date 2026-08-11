<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard/transactions') ?>" class="kt-btn kt-btn-outline">Back to transactions</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $metadata = json_decode((string) ($transaction['metadata'] ?? ''), true) ?: [];
?>

<div class="kt-card mb-5 lg:mb-7.5">
    <div class="kt-card-content p-6">
        <div class="flex flex-wrap items-center justify-between gap-2.5 mb-4">
            <h3 class="text-base font-medium text-mono mb-0 mono"><?= esc($transaction['reference']) ?></h3>
            <span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($transaction['status']) ?>"><?= esc(ucfirst($transaction['status'])) ?></span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Amount</span>
                <span class="text-2sm text-mono"><?= esc(number_format((float) $transaction['amount'], 2)) ?> <?= esc($transaction['currency']) ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Fee</span>
                <span class="text-2sm text-mono"><?= esc(number_format((float) ($transaction['fee_amount'] ?? 0), 2)) ?> <?= esc($transaction['currency']) ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Payment method</span>
                <span class="text-2sm text-mono"><?= esc(strtoupper($transaction['payment_method'])) ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Application</span>
                <span class="text-2sm text-mono"><?= esc($transaction['application_name'] ?? '—') ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Customer email</span>
                <span class="text-2sm text-mono"><?= $transaction['customer_email'] ? esc($transaction['customer_email']) : '—' ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Customer mobile</span>
                <span class="text-2sm text-mono"><?= $transaction['customer_msisdn'] ? esc($transaction['customer_msisdn']) : '—' ?></span>
            </div>
            <?php if (! empty($metadata['card_last4'])): ?>
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-secondary-foreground">Card</span>
                    <span class="text-2sm text-mono">•••• <?= esc($metadata['card_last4']) ?></span>
                </div>
            <?php endif; ?>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">PSP reference</span>
                <span class="text-2sm text-mono"><?= $transaction['psp_reference'] ? esc($transaction['psp_reference']) : '—' ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Created</span>
                <span class="text-2sm text-mono"><?= esc($transaction['created_at']) ?></span>
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Last updated</span>
                <span class="text-2sm text-mono"><?= esc($transaction['updated_at']) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="kt-card">
    <div class="kt-card-content p-6">
        <h3 class="text-base font-medium text-mono mb-4">Product / service</h3>
        <?php if (empty($transaction['product_name'])): ?>
            <p class="text-2sm text-secondary-foreground mb-0">No product/service details were recorded for this transaction.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-secondary-foreground">Product / service</span>
                    <span class="text-2sm text-mono"><?= esc($transaction['product_name']) ?></span>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-secondary-foreground">Quantity</span>
                    <span class="text-2sm text-mono"><?= $transaction['quantity'] !== null ? esc(rtrim(rtrim(number_format((float) $transaction['quantity'], 2), '0'), '.')) : '—' ?></span>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-secondary-foreground">Unit of measure</span>
                    <span class="text-2sm text-mono"><?= $transaction['unit_of_measure'] ? esc($transaction['unit_of_measure']) : '—' ?></span>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-secondary-foreground">Unit price</span>
                    <span class="text-2sm text-mono"><?= $transaction['unit_price'] !== null ? esc(number_format((float) $transaction['unit_price'], 2)) . ' ' . esc($transaction['currency']) : '—' ?></span>
                </div>
                <div class="flex flex-col gap-0.5 sm:col-span-3">
                    <span class="text-xs text-secondary-foreground">Description</span>
                    <span class="text-2sm text-mono"><?= $transaction['product_description'] ? esc($transaction['product_description']) : '—' ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
