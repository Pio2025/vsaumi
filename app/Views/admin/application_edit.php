<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin/applications/' . $application['id']) ?>" class="kt-btn kt-btn-outline">Back to application</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-[640px]">
    <div class="kt-card">
        <div class="kt-card-content flex flex-col gap-5 p-7.5">
            <div class="flex flex-col gap-0.5">
                <span class="text-xs text-secondary-foreground">Company</span>
                <span class="text-2sm text-mono"><?= $merchant !== null ? esc($merchant['business_name']) : '—' ?></span>
            </div>

            <form method="post" action="<?= site_url('admin/applications/' . $application['id'] . '/update') ?>" class="flex flex-col gap-5">
                <?= csrf_field() ?>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="name">Application name</label>
                    <input class="kt-input" type="text" id="name" name="name" value="<?= esc(old('name', $application['name']), 'attr') ?>" required>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="website_url">Website URL</label>
                    <input class="kt-input" type="url" id="website_url" name="website_url" value="<?= esc(old('website_url', $application['website_url'] ?? ''), 'attr') ?>">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="kt-form-label font-normal text-mono" for="status">Status</label>
                    <?php $currentStatus = old('status', $application['status']); ?>
                    <select class="kt-select" id="status" name="status" data-kt-select="true" required>
                        <?php foreach (['active', 'suspended'] as $status): ?>
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
