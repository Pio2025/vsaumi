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
                <label class="kt-form-label font-normal text-mono" for="password">Password</label>
                <input class="kt-input" type="password" id="password" name="password" required minlength="8">
                <span class="text-2sm text-secondary-foreground">At least 8 characters. This is for logging into your merchant dashboard — separate from your API secret.</span>
            </div>

            <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">Create account</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
