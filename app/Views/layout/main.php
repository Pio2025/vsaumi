<?php
$isAdmin    = (bool) session()->get('admin_id');
$isMerchant = (bool) session()->get('merchant_id');
$uriPath    = trim(service('request')->getUri()->getPath(), '/');

if ($isAdmin) {
    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'element-11', 'url' => 'admin', 'match' => 'admin'],
        ['label' => 'Merchant', 'icon' => 'people', 'url' => 'admin/merchants', 'match' => 'admin/merchants'],
        ['label' => 'Application', 'icon' => 'code', 'url' => 'admin/applications', 'match' => 'admin/applications'],
        ['label' => 'Payout', 'icon' => 'wallet', 'children' => [
            ['label' => 'Withdrawal Request', 'url' => 'admin/withdrawals', 'match' => 'admin/withdrawals'],
            ['label' => 'Payouts Listing', 'url' => 'admin/payouts', 'match' => 'admin/payouts'],
        ]],
    ];
    $displayName = session()->get('admin_name') ?? 'Admin';
    $roleLabel   = 'Administrator';
    $logoutUrl   = 'admin/logout';
} elseif ($isMerchant) {
    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'dashboard', 'match' => 'dashboard'],
        ['label' => 'Applications', 'icon' => 'code', 'url' => 'dashboard/applications', 'match' => 'dashboard/applications'],
        ['label' => 'Transactions', 'icon' => 'bill', 'url' => 'dashboard/transactions', 'match' => 'dashboard/transactions'],
        ['label' => 'Payout', 'icon' => 'wallet', 'children' => [
            ['label' => 'My Withdrawal Request', 'url' => 'dashboard/withdrawals', 'match' => 'dashboard/withdrawals'],
            ['label' => 'My Payout', 'url' => 'dashboard/payouts', 'match' => 'dashboard/payouts'],
        ]],
        ['label' => 'Subscriptions', 'icon' => 'crown', 'url' => 'dashboard/subscriptions', 'match' => 'dashboard/subscriptions'],
        ['label' => 'Settings', 'icon' => 'setting-2', 'url' => 'dashboard/settings', 'match' => 'dashboard/settings'],
    ];
    $displayName = session()->get('merchant_name') ?? 'Merchant';
    $roleLabel   = 'Merchant';
    $logoutUrl   = 'logout';
} else {
    $navItems    = [];
    $displayName = '';
    $roleLabel   = '';
    $logoutUrl   = '/';
}

$initials = strtoupper(substr(trim((string) $displayName) ?: 'V', 0, 1));
?>
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
    <link rel="stylesheet" href="<?= base_url('assets/metronic/vendors/apexcharts/apexcharts.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/metronic/vendors/keenicons/styles.bundle.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/metronic/css/styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <style>
        .kt-menu-tree { position: relative; }
        .kt-menu-tree::before { content: ""; position: absolute; inset-inline-start: 19px; top: 0; bottom: 0; width: 1px; background-color: var(--border); }
        .kt-menu-tree .kt-menu-link { padding-inline-start: 28px; position: relative; }
        .kt-menu-tree-dot { position: absolute; inset-inline-start: 16px; top: 50%; width: 6px; height: 6px; border-radius: 9999px; background-color: var(--muted-foreground); transform: translateY(-50%); transition: background-color .15s ease; }
        .kt-menu-tree .kt-menu-item.active .kt-menu-tree-dot,
        .kt-menu-tree .kt-menu-link:hover .kt-menu-tree-dot { background-color: var(--primary); }
        .kt-menu-arrow .ki-minus { display: none; }
        .kt-menu-item.show > .kt-menu-link .kt-menu-arrow .ki-plus { display: none; }
        .kt-menu-item.show > .kt-menu-link .kt-menu-arrow .ki-minus { display: inline-flex; }
    </style>
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background demo1 kt-sidebar-fixed kt-header-fixed">
<script>
    var themeMode = localStorage.getItem('kt-theme') || 'light';
    document.documentElement.classList.add(themeMode);
</script>

<div class="flex grow">
    <!-- Sidebar -->
    <div class="kt-sidebar bg-background border-e border-e-border fixed top-0 bottom-0 z-20 hidden lg:flex flex-col items-stretch shrink-0 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar">
        <div class="kt-sidebar-header hidden lg:flex items-center relative justify-between px-3 lg:px-6 shrink-0" id="sidebar_header">
            <a class="flex items-center gap-2" href="<?= site_url('/') ?>">
                <img class="h-[22px] w-auto light:hidden" src="<?= base_url('assets/logo/logo_white_small.png') ?>" alt="VSaumi">
                <img class="h-[22px] w-auto dark:hidden" src="<?= base_url('assets/logo/logo_small.png') ?>" alt="VSaumi">
            </a>
            <button class="kt-btn kt-btn-outline kt-btn-icon size-[30px] absolute start-full top-2/4 -translate-x-2/4 -translate-y-2/4" data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle">
                <i class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 transition-all duration-300"></i>
            </button>
        </div>
        <div class="kt-sidebar-content flex grow shrink-0 py-5 pe-2" id="sidebar_content">
            <div class="kt-scrollable-y-hover grow shrink-0 flex ps-2 lg:ps-5 pe-1 lg:pe-3" data-kt-scrollable="true" data-kt-scrollable-height="auto">
                <div class="kt-menu flex flex-col grow gap-1" data-kt-menu="true" id="sidebar_menu">
                    <?php foreach ($navItems as $item): ?>
                        <?php if (isset($item['children'])): ?>
                            <?php
                                $childActive = false;
                                foreach ($item['children'] as $child) {
                                    if (str_starts_with($uriPath, $child['match'])) {
                                        $childActive = true;
                                        break;
                                    }
                                }
                            ?>
                            <div class="kt-menu-item <?= $childActive ? 'active show' : '' ?>" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                                <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[10px] ps-[10px] pe-[10px] py-[8px] cursor-pointer">
                                    <span class="kt-menu-icon items-start text-muted-foreground kt-menu-item-active:text-primary w-[20px]">
                                        <i class="ki-filled ki-<?= esc($item['icon'], 'attr') ?> text-lg"></i>
                                    </span>
                                    <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold">
                                        <?= esc($item['label']) ?>
                                    </span>
                                    <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 flex items-center justify-center">
                                        <i class="ki-filled ki-plus text-2xs"></i>
                                        <i class="ki-filled ki-minus text-2xs"></i>
                                    </span>
                                </a>
                                <div class="kt-menu-accordion kt-menu-tree gap-1 <?= $childActive ? 'show' : '' ?>">
                                    <?php foreach ($item['children'] as $child): ?>
                                        <?php $active = str_starts_with($uriPath, $child['match']); ?>
                                        <div class="kt-menu-item <?= $active ? 'active' : '' ?>">
                                            <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[10px] pe-[10px] py-[8px]" href="<?= site_url($child['url']) ?>">
                                                <span class="kt-menu-tree-dot"></span>
                                                <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold">
                                                    <?= esc($child['label']) ?>
                                                </span>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php $active = ($item['match'] === 'admin' || $item['match'] === 'dashboard') ? ($uriPath === $item['match']) : str_starts_with($uriPath, $item['match']); ?>
                            <div class="kt-menu-item <?= $active ? 'active' : '' ?>">
                                <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[10px] ps-[10px] pe-[10px] py-[8px]" href="<?= site_url($item['url']) ?>">
                                    <span class="kt-menu-icon items-start text-muted-foreground kt-menu-item-active:text-primary w-[20px]">
                                        <i class="ki-filled ki-<?= esc($item['icon'], 'attr') ?> text-lg"></i>
                                    </span>
                                    <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold">
                                        <?= esc($item['label']) ?>
                                    </span>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Sidebar -->

    <div class="kt-wrapper flex grow flex-col">
        <!-- Header -->
        <header class="kt-header fixed top-0 z-10 start-0 end-0 flex items-stretch shrink-0 bg-background" data-kt-sticky="true" data-kt-sticky-class="border-b border-border" data-kt-sticky-name="header" id="header">
            <div class="kt-container-fixed flex justify-between items-stretch lg:gap-4">
                <div class="flex gap-2.5 lg:hidden items-center -ms-1">
                    <button class="kt-btn kt-btn-icon kt-btn-ghost" data-kt-drawer-toggle="#sidebar">
                        <i class="ki-filled ki-menu"></i>
                    </button>
                    <a class="shrink-0" href="<?= site_url('/') ?>">
                        <img class="max-h-[25px] w-auto light:hidden" src="<?= base_url('assets/logo/logo_white_small.png') ?>" alt="VSaumi">
                        <img class="max-h-[25px] w-auto dark:hidden" src="<?= base_url('assets/logo/logo_small.png') ?>" alt="VSaumi">
                    </a>
                </div>

                <div class="flex items-center gap-2.5">
                    <span class="hidden lg:flex text-sm font-medium text-secondary-foreground"><?= esc($pageTitle ?? '') ?></span>
                </div>

                <div class="flex items-center gap-2.5">
                    <span class="kt-badge kt-badge-outline kt-badge-sm hidden md:inline-flex"><?= esc($roleLabel) ?></span>

                    <div class="shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px" data-kt-dropdown-placement="bottom-end" data-kt-dropdown-trigger="click">
                        <div class="cursor-pointer shrink-0 flex items-center justify-center size-9 rounded-full bg-primary text-primary-foreground text-sm font-semibold" data-kt-dropdown-toggle="true">
                            <?= esc($initials) ?>
                        </div>
                        <div class="kt-dropdown-menu w-[220px]" data-kt-dropdown-menu="true">
                            <div class="flex items-center gap-2.5 px-2.5 py-2">
                                <div class="flex items-center justify-center size-9 shrink-0 rounded-full bg-primary text-primary-foreground text-sm font-semibold"><?= esc($initials) ?></div>
                                <div class="flex flex-col">
                                    <span class="text-sm text-foreground font-semibold leading-none"><?= esc($displayName) ?></span>
                                    <span class="text-xs text-secondary-foreground font-medium mt-1"><?= esc($roleLabel) ?></span>
                                </div>
                            </div>
                            <ul class="kt-dropdown-menu-sub">
                                <li><div class="kt-dropdown-menu-separator"></div></li>
                            </ul>
                            <div class="px-2.5 py-2 flex flex-col gap-3.5">
                                <div class="flex items-center gap-2 justify-between">
                                    <span class="flex items-center gap-2">
                                        <i class="ki-filled ki-moon text-base text-muted-foreground"></i>
                                        <span class="font-medium text-2sm">Dark Mode</span>
                                    </span>
                                    <input class="kt-switch" data-kt-theme-switch-state="dark" data-kt-theme-switch-toggle="true" type="checkbox" value="1">
                                </div>
                                <a class="kt-btn kt-btn-outline justify-center w-full" href="<?= site_url($logoutUrl) ?>">
                                    <i class="ki-filled ki-exit-right"></i> Log out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- End of Header -->

        <main class="grow pt-5" id="content" role="content">
            <div class="kt-container-fixed">
                <?php $flashError = session()->getFlashdata('error'); $flashSuccess = session()->getFlashdata('success'); ?>
                <?php if ($flashError): ?>
                    <div class="kt-alert kt-alert-destructive mb-5" role="alert"><span class="kt-alert-icon"><i class="ki-filled ki-information-2"></i></span><span class="kt-alert-title"><?= esc($flashError) ?></span></div>
                <?php endif; ?>
                <?php if ($flashSuccess): ?>
                    <div class="kt-alert kt-alert-success mb-5" role="alert"><span class="kt-alert-icon"><i class="ki-filled ki-check-circle"></i></span><span class="kt-alert-title"><?= esc($flashSuccess) ?></span></div>
                <?php endif; ?>

                <?php if (isset($pageTitle)): ?>
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                        <h1 class="text-xl font-medium leading-none text-mono"><?= esc($pageTitle) ?></h1>
                        <div class="flex items-center gap-2.5"><?= $this->renderSection('pageActions') ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="kt-container-fixed pb-10">
                <?= $this->renderSection('content') ?>
            </div>
        </main>

        <footer class="kt-footer">
            <div class="kt-container-fixed">
                <div class="flex flex-col md:flex-row justify-center md:justify-between items-center gap-3 py-5">
                    <div class="flex order-2 md:order-1 gap-2 font-normal text-sm text-secondary-foreground">
                        <span>VSaumi — a demo payment gateway for Fiji. Visa · Mastercard · M-PAiSA · MyCash.</span>
                    </div>
                    <nav class="flex order-1 md:order-2 gap-4 font-normal text-sm text-secondary-foreground">
                        <a class="hover:text-primary" href="<?= site_url('admin/login') ?>">Admin</a>
                    </nav>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="<?= base_url('assets/metronic/vendors/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/metronic/js/core.bundle.js') ?>"></script>
<script src="<?= base_url('assets/metronic/vendors/ktui/ktui.min.js') ?>"></script>
<script src="<?= base_url('assets/metronic/vendors/apexcharts/apexcharts.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('submit', 'form.js-delete-form', function (e) {
        e.preventDefault();
        const form = this;
        const itemName = $(form).data('confirm-name') || 'this item';

        Swal.fire({
            title: 'Are you sure?',
            text: `This will permanently delete ${itemName}. This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#be1e2d',
            cancelButtonColor: '#6b5f5e',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(document).on('submit', 'form.js-cancel-form', function (e) {
        e.preventDefault();
        const form = this;
        const itemName = $(form).data('confirm-name') || 'this application';

        Swal.fire({
            title: 'Cancel subscription?',
            text: `This will cancel the active subscription for ${itemName} and put its API access on hold immediately.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel subscription',
            confirmButtonColor: '#be1e2d',
            cancelButtonColor: '#6b5f5e',
            cancelButtonText: 'Back',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(document).on('submit', 'form.js-approve-form', function (e) {
        e.preventDefault();
        const form = this;
        const itemName = $(form).data('confirm-name') || 'this merchant';

        Swal.fire({
            title: 'Approve withdrawal?',
            text: `This will process the payout for ${itemName} and email them that it's on the way.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve & process',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
