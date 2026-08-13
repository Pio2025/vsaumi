<?= $this->extend('layout/public') ?>
<?= $this->section('content') ?>

<div class="kt-container-fixed max-w-[440px] pb-16">
    <div class="kt-card mt-7.5">
        <div class="text-center pt-7.5 px-5">
            <p class="text-2sm text-secondary-foreground mb-1">Paying <strong class="text-foreground"><?= esc($merchant['business_name']) ?></strong> with <?= esc($methodInfo['label']) ?></p>
            <h2 class="text-xl font-semibold text-mono"><?= esc(number_format($amount, 2)) ?> FJD</h2>
        </div>

        <form method="post" action="<?= site_url('checkout/' . $application['api_key'] . '/pay/' . $method) ?>" class="flex flex-col gap-4 p-7.5">
            <?= csrf_field() ?>
            <input type="hidden" name="amount" value="<?= esc($amount) ?>">
            <?php foreach ($product as $field => $value): ?>
                <?php if ($value !== null && $value !== ''): ?>
                    <input type="hidden" name="<?= esc($field, 'attr') ?>" value="<?= esc($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($methodInfo['kind'] === 'card'): ?>
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="card_number">Card number</label>
                    <input class="kt-input" type="text" id="card_number" name="card_number" value="4242 4242 4242 4242" maxlength="19" required>
                </div>
                <div class="grid grid-cols-2 gap-3.5">
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="card_expiry">Expiry</label>
                        <input class="kt-input" type="text" id="card_expiry" name="card_expiry" value="12/29" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="kt-form-label font-normal text-mono" for="card_cvv">CVV</label>
                        <input class="kt-input" type="text" id="card_cvv" name="card_cvv" value="123" maxlength="4" required>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="email">Email for receipt</label>
                    <input class="kt-input" type="email" id="email" name="email" placeholder="you@example.com">
                </div>
                <span class="text-2sm text-secondary-foreground">No real card is charged — any test number works in this demo.</span>
            <?php else: ?>
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="msisdn">Mobile number</label>
                    <input class="kt-input" type="tel" id="msisdn" name="msisdn" value="9791234567" required>
                </div>
                <?php if ($method === 'mpaisa'): ?>
                    <span class="text-2sm text-secondary-foreground">You'll be redirected to M-PAiSA to log in and approve this payment.</span>
                <?php else: ?>
                    <span class="text-2sm text-secondary-foreground">You'll be sent a demo approval prompt on the next screen, simulating the <?= esc($methodInfo['label']) ?> app popup on your phone.</span>
                <?php endif; ?>
            <?php endif; ?>

            <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">Pay <?= esc(number_format($amount, 2)) ?> FJD</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
