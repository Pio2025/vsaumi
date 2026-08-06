<?= $this->extend('layout/public') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-center grow py-10">
    <div class="kt-card max-w-[440px] w-full">
        <form method="post" action="<?= site_url('signup') ?>" class="kt-card-content flex flex-col gap-5 p-10">
            <?= csrf_field() ?>
            <div class="text-center mb-2.5">
                <h3 class="text-lg font-medium text-mono leading-none mb-2.5">Create your merchant account</h3>
                <div class="flex items-center justify-center font-medium">
                    <span class="text-sm text-secondary-foreground me-1.5">Already have an account?</span>
                    <a class="text-sm kt-link" href="<?= site_url('login') ?>">Log in</a>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="business_name">Business name</label>
                <input class="kt-input" type="text" id="business_name" name="business_name" value="<?= esc(old('business_name'), 'attr') ?>" required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="contact_email">Email</label>
                <input class="kt-input" type="email" id="contact_email" name="contact_email" value="<?= esc(old('contact_email'), 'attr') ?>" required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="contact_phone">Phone (optional)</label>
                <input class="kt-input" type="tel" id="contact_phone" name="contact_phone" value="<?= esc(old('contact_phone'), 'attr') ?>" placeholder="9791234567">
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="business_address">Business address</label>
                <textarea class="kt-textarea" id="business_address" name="business_address" rows="2" required><?= esc(old('business_address')) ?></textarea>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="payout_provider_id">How do you want to get paid?</label>
                <select class="kt-select" id="payout_provider_id" name="payout_provider_id" required>
                    <option value="">Select a payout method</option>
                    <?php if (! empty($banks)): ?>
                        <optgroup label="Bank">
                            <?php foreach ($banks as $bank): ?>
                                <option value="<?= esc($bank['id'], 'attr') ?>" data-type="bank" data-bsb="<?= esc($bank['bsb_code'], 'attr') ?>" <?= old('payout_provider_id') == $bank['id'] ? 'selected' : '' ?>><?= esc($bank['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                    <?php if (! empty($wallets)): ?>
                        <optgroup label="Digital Wallet">
                            <?php foreach ($wallets as $wallet): ?>
                                <option value="<?= esc($wallet['id'], 'attr') ?>" data-type="digital_wallet" <?= old('payout_provider_id') == $wallet['id'] ? 'selected' : '' ?>><?= esc($wallet['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
                <span class="text-2sm text-secondary-foreground">This is where you'll receive your payments. You can update it later in Settings.</span>
            </div>

            <div class="flex flex-col gap-1" id="bsb_field" style="display:none;">
                <label class="kt-form-label font-normal text-mono" for="bsb_code_display">BSB code</label>
                <input class="kt-input" type="text" id="bsb_code_display" readonly>
            </div>

            <div class="flex flex-col gap-1" id="account_type_field" style="display:none;">
                <label class="kt-form-label font-normal text-mono" for="account_type">Account type</label>
                <select class="kt-select" id="account_type" name="account_type">
                    <option value="savings" <?= old('account_type') === 'savings' ? 'selected' : '' ?>>Savings</option>
                    <option value="checking" <?= old('account_type') === 'checking' ? 'selected' : '' ?>>Checking</option>
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="account_number" id="account_number_label">Account number</label>
                <input class="kt-input" type="text" id="account_number" name="account_number" value="<?= esc(old('account_number'), 'attr') ?>" required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="account_name">Account name</label>
                <input class="kt-input" type="text" id="account_name" name="account_name" value="<?= esc(old('account_name'), 'attr') ?>" required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="app_name">App or website name</label>
                <input class="kt-input" type="text" id="app_name" name="app_name" value="<?= esc(old('app_name'), 'attr') ?>" placeholder="My Kava" required>
                <span class="text-2sm text-secondary-foreground">The first app that will call the payment API — you can add more later.</span>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="website_url">Website URL (optional)</label>
                <input class="kt-input" type="url" id="website_url" name="website_url" value="<?= esc(old('website_url'), 'attr') ?>" placeholder="https://mykava.fj">
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="password">Password</label>
                <input class="kt-input" type="password" id="password" name="password" required minlength="8">
                <span class="text-2sm text-secondary-foreground">At least 8 characters. This is for logging into your merchant dashboard — separate from your API secret.</span>
            </div>

            <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">Create account</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        var providerSelect   = document.getElementById('payout_provider_id');
        var bsbField          = document.getElementById('bsb_field');
        var bsbDisplay         = document.getElementById('bsb_code_display');
        var accountTypeField  = document.getElementById('account_type_field');
        var accountNumberLabel = document.getElementById('account_number_label');

        function syncPayoutFields() {
            var option = providerSelect.options[providerSelect.selectedIndex];
            var isBank = option && option.dataset.type === 'bank';

            bsbField.style.display         = isBank ? '' : 'none';
            accountTypeField.style.display = isBank ? '' : 'none';
            bsbDisplay.value               = isBank ? (option.dataset.bsb || '') : '';
            accountNumberLabel.textContent = isBank ? 'Account number' : 'Mobile / wallet number';
        }

        providerSelect.addEventListener('change', syncPayoutFields);
        syncPayoutFields();
    })();
</script>
<?= $this->endSection() ?>
