<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard/applications') ?>" class="kt-btn kt-btn-outline">Back to applications</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex items-center justify-center grow py-5">
    <div class="kt-card max-w-[440px] w-full">
        <form method="post" action="<?= site_url('dashboard/applications/new') ?>" class="kt-card-content flex flex-col gap-5 p-10">
            <?= csrf_field() ?>
            <div class="text-center mb-2.5">
                <h3 class="text-lg font-medium text-mono leading-none mb-2.5">New application</h3>
                <p class="text-sm text-secondary-foreground mb-0">Register another app or website that will call the payment API under <strong class="text-foreground"><?= esc($merchant['business_name']) ?></strong>. It gets its own API key and secret.</p>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="name">App or website name</label>
                <input class="kt-input" type="text" id="name" name="name" value="<?= esc(old('name'), 'attr') ?>" placeholder="My Kava POS" required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="website_url">Website URL (optional)</label>
                <input class="kt-input" type="url" id="website_url" name="website_url" value="<?= esc(old('website_url'), 'attr') ?>" placeholder="https://mykava.fj">
            </div>

            <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">Create application</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
