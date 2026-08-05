<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin/merchants') ?>" class="kt-btn kt-btn-outline">Back to merchants</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-[640px]">
    <div class="kt-card">
        <div class="kt-card-content flex flex-col gap-5 p-7.5">
            <form method="post" action="<?= site_url('admin/merchants/' . $merchant['id'] . '/update') ?>" class="flex flex-col gap-5">
                <?= csrf_field() ?>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="business_name">Business name</label>
                    <input class="kt-input" type="text" id="business_name" name="business_name" value="<?= esc(old('business_name', $merchant['business_name']), 'attr') ?>" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="contact_email">Contact email</label>
                    <input class="kt-input" type="email" id="contact_email" name="contact_email" value="<?= esc(old('contact_email', $merchant['contact_email']), 'attr') ?>" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="contact_phone">Contact phone</label>
                    <input class="kt-input" type="tel" id="contact_phone" name="contact_phone" value="<?= esc(old('contact_phone', $merchant['contact_phone'] ?? ''), 'attr') ?>">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="status">Status</label>
                    <?php $currentStatus = old('status', $merchant['status']); ?>
                    <select class="kt-select" id="status" name="status" data-kt-select="true" required>
                        <?php foreach (['pending', 'approved', 'active', 'suspended'] as $status): ?>
                            <option value="<?= esc($status, 'attr') ?>" <?= $currentStatus === $status ? 'selected' : '' ?>><?= esc(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="kt-btn kt-btn-primary justify-center">Save changes</button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
