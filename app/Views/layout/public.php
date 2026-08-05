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

<?php
$navGroups = [
    'product' => [
        'label' => 'Product',
        'items' => [
            ['icon' => 'ki-wallet', 'title' => 'Payments', 'desc' => 'Accept cards and mobile wallets', 'href' => site_url('/') . '#payments'],
            ['icon' => 'ki-basket', 'title' => 'Checkout', 'desc' => 'Hosted checkout page', 'href' => site_url('/') . '#how-it-works'],
            ['icon' => 'ki-arrows-circle', 'title' => 'Payouts', 'desc' => 'Settlement to your bank account', 'href' => site_url('/') . '#how-it-works'],
            ['icon' => 'ki-bill', 'title' => 'Billing', 'desc' => 'Plans, invoices and usage', 'href' => site_url('/') . '#pricing'],
        ],
    ],
    'solutions' => [
        'label' => 'Solutions',
        'items' => [
            ['icon' => 'ki-shop', 'title' => 'Online stores', 'desc' => 'Sell products and services online', 'href' => site_url('/') . '#payments'],
            ['icon' => 'ki-users', 'title' => 'Marketplaces', 'desc' => 'Split payments between sellers', 'href' => site_url('/') . '#payments'],
            ['icon' => 'ki-graph-up', 'title' => 'Subscription businesses', 'desc' => 'Recurring billing made simple', 'href' => site_url('/') . '#pricing'],
        ],
    ],
    'developers' => [
        'label' => 'Developers',
        'items' => [
            ['icon' => 'ki-key', 'title' => 'Get API keys', 'desc' => 'Sign up for sandbox access', 'href' => site_url('signup')],
            ['icon' => 'ki-rocket', 'title' => 'Try a demo checkout', 'desc' => 'See the payment flow in action', 'href' => site_url('/') . '#how-it-works'],
        ],
    ],
    'resources' => [
        'label' => 'Resources',
        'items' => [
            ['icon' => 'ki-book-open', 'title' => 'How payments work', 'desc' => 'From checkout to settlement', 'href' => site_url('/') . '#how-it-works'],
            ['icon' => 'ki-setting-2', 'title' => 'Pricing & plans', 'desc' => 'Simple, transparent pricing', 'href' => site_url('/') . '#pricing'],
            ['icon' => 'ki-flag', 'title' => 'Admin portal', 'desc' => 'Platform administration', 'href' => site_url('admin/login')],
        ],
    ],
    'pricing' => [
        'label' => 'Pricing',
        'items' => [
            ['icon' => 'ki-bill', 'title' => 'Starter — $29/mo', 'desc' => 'Up to 500 transactions/mo', 'href' => site_url('/') . '#pricing'],
            ['icon' => 'ki-bill', 'title' => 'Growth — $79/mo', 'desc' => 'Up to 5,000 transactions/mo', 'href' => site_url('/') . '#pricing'],
            ['icon' => 'ki-bill', 'title' => 'Enterprise — $199/mo', 'desc' => 'Unlimited transactions', 'href' => site_url('/') . '#pricing'],
        ],
    ],
];
?>
<header class="border-b border-border bg-background" data-kt-sticky="true" data-kt-sticky-class="shadow-sm" data-kt-sticky-name="public-header">
    <div class="kt-container-fixed flex items-center justify-between py-3.5 gap-4">
        <a href="<?= site_url('/') ?>" class="flex items-center gap-2 shrink-0">
            <img src="<?= base_url('assets/logo/logo_white_small.png') ?>" alt="VSaumi" class="max-h-[30px] w-auto light:hidden">
            <img src="<?= base_url('assets/logo/logo_small.png') ?>" alt="VSaumi" class="max-h-[30px] w-auto dark:hidden">
        </a>
        <nav class="hidden lg:flex items-center gap-1">
            <?php foreach ($navGroups as $group): ?>
                <div class="shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="0, 10px" data-kt-dropdown-placement="bottom-start" data-kt-dropdown-trigger="click">
                    <button type="button" class="kt-btn kt-btn-ghost text-sm font-medium text-secondary-foreground" data-kt-dropdown-toggle="true">
                        <?= esc($group['label']) ?>
                        <i class="ki-filled ki-down text-2xs"></i>
                    </button>
                    <div class="kt-dropdown-menu w-[280px]" data-kt-dropdown-menu="true">
                        <?php foreach ($group['items'] as $item): ?>
                            <a class="kt-dropdown-menu-link" href="<?= $item['href'] ?>">
                                <i class="ki-filled <?= esc($item['icon']) ?>"></i>
                                <span class="flex flex-col">
                                    <span class="text-sm font-medium text-mono"><?= esc($item['title']) ?></span>
                                    <span class="text-xs text-secondary-foreground"><?= esc($item['desc']) ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>
        <nav class="hidden lg:flex items-center gap-2.5">
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
        <button type="button" class="kt-btn kt-btn-icon kt-btn-ghost lg:hidden" data-kt-drawer-toggle="#public_nav_drawer">
            <i class="ki-filled ki-menu"></i>
        </button>
    </div>
</header>

<div class="kt-drawer kt-drawer-end top-0 bottom-0 hidden w-full max-w-[320px]" data-kt-drawer="true" id="public_nav_drawer">
    <div class="kt-drawer-header">
        <h3 class="kt-drawer-title">Menu</h3>
        <button class="kt-drawer-close" data-kt-drawer-dismiss="true" type="button">
            <i class="ki-filled ki-cross"></i>
        </button>
    </div>
    <div class="kt-drawer-content">
        <div class="flex flex-col gap-1 px-2.5 py-2">
            <?php foreach ($navGroups as $group): ?>
                <details class="border-b border-border">
                    <summary class="flex items-center justify-between py-3 text-sm font-medium text-mono cursor-pointer">
                        <?= esc($group['label']) ?>
                        <i class="ki-filled ki-down text-2xs"></i>
                    </summary>
                    <div class="flex flex-col gap-1 pb-3">
                        <?php foreach ($group['items'] as $item): ?>
                            <a class="kt-dropdown-menu-link" href="<?= $item['href'] ?>">
                                <i class="ki-filled <?= esc($item['icon']) ?>"></i>
                                <span class="flex flex-col">
                                    <span class="text-sm font-medium text-mono"><?= esc($item['title']) ?></span>
                                    <span class="text-xs text-secondary-foreground"><?= esc($item['desc']) ?></span>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="kt-drawer-footer flex items-center gap-2.5">
        <?php if (session()->get('admin_id')): ?>
            <a href="<?= site_url('admin') ?>" class="kt-btn kt-btn-outline justify-center w-full">Admin Dashboard</a>
            <a href="<?= site_url('admin/logout') ?>" class="kt-btn kt-btn-ghost justify-center w-full">Log out</a>
        <?php elseif (session()->get('merchant_id')): ?>
            <a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline justify-center w-full">Dashboard</a>
            <a href="<?= site_url('logout') ?>" class="kt-btn kt-btn-ghost justify-center w-full">Log out</a>
        <?php else: ?>
            <a href="<?= site_url('login') ?>" class="kt-btn kt-btn-ghost justify-center w-full">Log in</a>
            <a href="<?= site_url('signup') ?>" class="kt-btn kt-btn-primary justify-center w-full">Sign up</a>
        <?php endif; ?>
    </div>
</div>

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
