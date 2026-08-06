<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-[640px]">
    <div class="kt-card">
        <div class="kt-card-content flex flex-col gap-5 p-7.5">
            <h3 class="text-base font-medium text-mono mb-0">Payout details</h3>
            <p class="text-2sm text-secondary-foreground -mt-3.5 mb-0">This is where you'll receive your payments. You must add this before your account can be activated.</p>

            <form method="post" action="<?= site_url('dashboard/settings') ?>" class="flex flex-col gap-5">
                <?= csrf_field() ?>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="business_address">Business address</label>
                    <textarea class="kt-textarea" id="business_address" name="business_address" rows="2" required><?= esc(old('business_address', $merchant['business_address'] ?? '')) ?></textarea>
                </div>

                <?php $selectedProviderId = old('payout_provider_id', $payoutAccount['payout_provider_id'] ?? ''); ?>
                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="payout_provider_id">How do you want to get paid?</label>
                    <select class="kt-select" id="payout_provider_id" name="payout_provider_id" required>
                        <option value="">Select a payout method</option>
                        <?php if (! empty($banks)): ?>
                            <optgroup label="Bank">
                                <?php foreach ($banks as $bank): ?>
                                    <option value="<?= esc($bank['id'], 'attr') ?>" data-type="bank" data-bsb="<?= esc($bank['bsb_code'], 'attr') ?>" <?= (string) $selectedProviderId === (string) $bank['id'] ? 'selected' : '' ?>><?= esc($bank['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php if (! empty($wallets)): ?>
                            <optgroup label="Digital Wallet">
                                <?php foreach ($wallets as $wallet): ?>
                                    <option value="<?= esc($wallet['id'], 'attr') ?>" data-type="digital_wallet" <?= (string) $selectedProviderId === (string) $wallet['id'] ? 'selected' : '' ?>><?= esc($wallet['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="flex flex-col gap-1" id="bsb_field" style="display:none;">
                    <label class="kt-form-label font-normal text-mono" for="bsb_code_display">BSB code</label>
                    <input class="kt-input" type="text" id="bsb_code_display" readonly>
                </div>

                <?php $currentAccountType = old('account_type', $payoutAccount['account_type'] ?? 'savings'); ?>
                <div class="flex flex-col gap-1" id="account_type_field" style="display:none;">
                    <label class="kt-form-label font-normal text-mono" for="account_type">Account type</label>
                    <select class="kt-select" id="account_type" name="account_type">
                        <option value="savings" <?= $currentAccountType === 'savings' ? 'selected' : '' ?>>Savings</option>
                        <option value="checking" <?= $currentAccountType === 'checking' ? 'selected' : '' ?>>Checking</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="account_number" id="account_number_label">Account number</label>
                    <input class="kt-input" type="text" id="account_number" name="account_number" value="<?= esc(old('account_number', $payoutAccount['account_number'] ?? ''), 'attr') ?>" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="account_name">Account name</label>
                    <input class="kt-input" type="text" id="account_name" name="account_name" value="<?= esc(old('account_name', $payoutAccount['account_name'] ?? ''), 'attr') ?>" required>
                </div>

                <button type="submit" class="kt-btn kt-btn-primary justify-center">Save changes</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        var providerSelect     = document.getElementById('payout_provider_id');
        var bsbField            = document.getElementById('bsb_field');
        var bsbDisplay           = document.getElementById('bsb_code_display');
        var accountTypeField    = document.getElementById('account_type_field');
        var accountNumberLabel  = document.getElementById('account_number_label');

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
