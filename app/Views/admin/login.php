<?= $this->extend('layout/public') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-center grow py-10">
    <div class="kt-card max-w-[370px] w-full">
        <form method="post" action="<?= site_url('admin/login') ?>" class="kt-card-content flex flex-col gap-5 p-10">
            <?= csrf_field() ?>
            <div class="text-center mb-2.5">
                <h3 class="text-lg font-medium text-mono leading-none mb-2.5">Admin Log In</h3>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="email">Email</label>
                <input class="kt-input" type="email" id="email" name="email" value="<?= esc(old('email'), 'attr') ?>" required>
            </div>

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="password">Password</label>
                <input class="kt-input" type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">Log in</button>

            <span class="text-2sm text-secondary-foreground text-center">Demo admin: admin@vsaumi.com / Admin123</span>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
