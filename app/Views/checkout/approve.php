<?= $this->extend('layout/public') ?>
<?= $this->section('content') ?>

<div class="kt-container-fixed max-w-[440px] pb-16 mt-7.5">

    <?php if ($methodInfo['kind'] === 'mobile'): ?>
        <div class="phone-mock">
            <div class="carrier"><?= esc($methodInfo['label']) ?> · demo phone</div>
            <div class="prompt-box">
                <p class="font-semibold mb-1.5">Payment request</p>
                <p class="text-2sm text-secondary-foreground">
                    <strong class="text-foreground"><?= esc($merchant['business_name']) ?></strong> is requesting
                    <strong class="text-foreground"><?= esc(number_format((float) $transaction['amount'], 2)) ?> FJD</strong>
                    from <?= esc($transaction['customer_msisdn']) ?>.
                </p>
                <form method="post" action="<?= site_url('checkout/approve/' . $transaction['reference']) ?>">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-2 gap-3.5 mt-4">
                        <button type="submit" name="decision" value="decline" class="kt-btn kt-btn-outline justify-center">Decline</button>
                        <button type="submit" name="decision" value="approve" class="kt-btn kt-btn-primary justify-center">Approve</button>
                    </div>
                </form>
            </div>
        </div>
        <p class="text-xs text-secondary-foreground text-center mt-7.5 mb-0">Simulating the <?= esc($methodInfo['label']) ?> app prompt that would normally appear on the customer's own phone.</p>

    <?php else: ?>
        <div class="kt-card text-center p-7.5">
            <h3 class="text-base font-medium text-mono mb-1.5">Authorizing with your bank</h3>
            <p class="text-2sm text-secondary-foreground">
                Your issuer is confirming this <?= esc(number_format((float) $transaction['amount'], 2)) ?> FJD
                payment to <strong class="text-foreground"><?= esc($merchant['business_name']) ?></strong>.
            </p>
            <form method="post" action="<?= site_url('checkout/approve/' . $transaction['reference']) ?>">
                <?= csrf_field() ?>
                <div class="grid grid-cols-2 gap-3.5 mt-4">
                    <button type="submit" name="decision" value="decline" class="kt-btn kt-btn-outline justify-center">Decline</button>
                    <button type="submit" name="decision" value="approve" class="kt-btn kt-btn-primary justify-center">Approve</button>
                </div>
            </form>
        </div>
        <p class="text-xs text-secondary-foreground text-center mt-7.5 mb-0">Simulating the card issuer's authorization step (e.g. 3-D Secure).</p>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>
