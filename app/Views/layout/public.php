<!doctype html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= isset($pageTitle) ? esc($pageTitle) . ' · VSaumi' : 'VSaumi — Get Paid, the Fijian Way' ?></title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/favicon_io/favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/favicon_io/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/favicon_io/favicon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/favicon_io/apple-touch-icon.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/metronic/vendors/keenicons/styles.bundle.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/metronic/css/styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="antialiased flex h-full flex-col text-base text-foreground bg-background">
<script>
    var themeMode = localStorage.getItem('kt-theme') || 'light';
    document.documentElement.classList.add(themeMode);
</script>

<header class="border-b border-border bg-background" data-kt-sticky="true" data-kt-sticky-class="shadow-sm" data-kt-sticky-name="public-header">
    <div class="kt-container-fixed flex items-center justify-between py-3.5 gap-4">
        <a href="<?= site_url('/') ?>" class="flex items-center gap-2 shrink-0">
            <img src="<?= base_url('assets/logo/logo_white_small.png') ?>" alt="VSaumi" class="max-h-[30px] w-auto light:hidden">
            <img src="<?= base_url('assets/logo/logo_small.png') ?>" alt="VSaumi" class="max-h-[30px] w-auto dark:hidden">
        </a>
        <nav class="hidden lg:flex items-center gap-6">
            <a href="<?= site_url('/') ?>#payments" class="text-sm font-medium text-secondary-foreground hover:text-primary">Payments</a>
            <a href="<?= site_url('/') ?>#how-it-works" class="text-sm font-medium text-secondary-foreground hover:text-primary">How it works</a>
            <a href="<?= site_url('/') ?>#pricing" class="text-sm font-medium text-secondary-foreground hover:text-primary">Pricing</a>
        </nav>
        <nav class="flex items-center gap-2.5">
            <?php if (session()->get('admin_id')): ?>
                <a href="<?= site_url('admin') ?>" class="kt-btn kt-btn-outline">Admin Dashboard</a>
                <a href="<?= site_url('admin/logout') ?>" class="kt-btn kt-btn-ghost">Log out</a>
            <?php elseif (session()->get('merchant_id')): ?>
                <a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline">Dashboard</a>
                <a href="<?= site_url('logout') ?>" class="kt-btn kt-btn-ghost">Log out</a>
            <?php else: ?>
                <a href="<?= site_url('login') ?>" class="kt-btn kt-btn-ghost">Log in</a>
                <a href="<?= site_url('signup') ?>" class="kt-btn kt-btn-primary">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="grow flex flex-col">
    <div class="kt-container-fixed pt-5">
        <?php $flashError = session()->getFlashdata('error'); $flashSuccess = session()->getFlashdata('success'); ?>
        <?php if ($flashError): ?>
            <div class="kt-alert kt-alert-destructive mb-5" role="alert"><span class="kt-alert-icon"><i class="ki-filled ki-information-2"></i></span><span class="kt-alert-title"><?= esc($flashError) ?></span></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="kt-alert kt-alert-success mb-5" role="alert"><span class="kt-alert-icon"><i class="ki-filled ki-check-circle"></i></span><span class="kt-alert-title"><?= esc($flashSuccess) ?></span></div>
        <?php endif; ?>
    </div>

    <?= $this->renderSection('content') ?>
</main>

<footer class="kt-footer border-t border-border">
    <div class="kt-container-fixed">
        <div class="flex flex-col md:flex-row justify-center md:justify-between items-center gap-3 py-5">
            <div class="flex order-2 md:order-1 gap-2 font-normal text-sm text-secondary-foreground">
                <span>VSaumi — a demo payment gateway for Fiji. Visa · Mastercard · M-PAiSA · MyCash.</span>
            </div>
            <nav class="flex order-1 md:order-2 gap-4 font-normal text-sm text-secondary-foreground">
                <a class="hover:text-primary" href="<?= site_url('/') ?>#pricing">Pricing</a>
                <a class="hover:text-primary" href="<?= site_url('/') ?>#how-it-works">How it works</a>
                <a class="hover:text-primary" href="<?= site_url('admin/login') ?>">Admin</a>
            </nav>
        </div>
    </div>
</footer>

<script src="<?= base_url('assets/metronic/js/core.bundle.js') ?>"></script>
<script src="<?= base_url('assets/metronic/vendors/ktui/ktui.min.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
