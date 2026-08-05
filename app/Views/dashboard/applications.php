<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('dashboard/applications/new') ?>" class="kt-btn kt-btn-primary">+ New Application</a>
<a href="<?= site_url('dashboard') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if ($newApplicationCredentials): ?>
    <div class="kt-card border-primary mb-5 lg:mb-7.5">
        <div class="kt-card-content p-6">
            <h3 class="text-base font-medium text-mono mb-1.5">Credentials for "<?= esc($newApplicationCredentials['name']) ?>"</h3>
            <p class="text-2sm text-secondary-foreground mb-3">Save your API secret now — it will not be shown again.</p>
            <div class="credential-box">
                <div class="row"><span class="text-2sm text-secondary-foreground">API Key</span><span class="mono"><?= esc($newApplicationCredentials['api_key']) ?></span></div>
                <div class="row"><span class="text-2sm text-secondary-foreground">API Secret</span><span class="mono"><?= esc($newApplicationCredentials['api_secret']) ?></span></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Applications (<?= count($applications) ?>)</h3>
        <?php if (! empty($applications)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input data-kt-datatable-search="#applicationsTable" placeholder="Search applications" type="text" value="">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($applications)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No applications yet. Create one to get an API key.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10" id="applicationsTable">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true">
                        <thead>
                            <tr>
                                <th class="min-w-[200px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Application</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="min-w-[200px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">API Key</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Status</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Subscription</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="w-[140px]" data-kt-datatable-column-sort="false">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="leading-none font-medium text-sm text-mono"><?= esc($app['name']) ?></span>
                                            <span class="text-xs text-secondary-foreground font-normal"><?= $app['website_url'] ? esc($app['website_url']) : '—' ?></span>
                                        </div>
                                    </td>
                                    <td><span class="mono text-2sm"><?= esc($app['api_key']) ?></span></td>
                                    <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($app['status']) ?>"><?= esc(ucfirst($app['status'])) ?></span></td>
                                    <td>
                                        <?php if ($app['has_active_subscription']): ?>
                                            <span class="text-2sm text-mono"><?= esc(ucfirst($app['latest_subscription']['plan'])) ?></span>
                                            <span class="text-xs text-secondary-foreground block">Expires <?= esc($app['latest_subscription']['expires_at']) ?></span>
                                        <?php else: ?>
                                            <span class="text-2sm text-secondary-foreground">No active subscription</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="w-[140px]">
                                        <?php if ($app['has_active_subscription']): ?>
                                            <span class="text-2sm text-secondary-foreground">—</span>
                                        <?php else: ?>
                                            <div class="flex items-center justify-end gap-1.5">
                                                <div class="kt-menu" data-kt-menu="true">
                                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-outline" type="button">
                                                            Subscribe
                                                        </button>
                                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[220px]" data-kt-menu-dismiss="true">
                                                            <div class="kt-menu-item">
                                                                <form method="post" action="<?= site_url('dashboard/applications/' . $app['id'] . '/subscribe') ?>" class="w-full flex flex-col gap-2 p-2.5">
                                                                    <?= csrf_field() ?>
                                                                    <select name="plan" class="kt-select kt-select-sm w-full">
                                                                        <?php foreach ($plans as $key => $plan): ?>
                                                                            <option value="<?= esc($key) ?>"><?= esc($plan['label']) ?> ($<?= esc($plan['price']) ?>/mo)</option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-mono w-full">Simulate Payment</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="kt-card-footer justify-center md:justify-between flex-col md:flex-row gap-5 text-secondary-foreground text-sm font-medium">
                    <div class="flex items-center gap-2 order-2 md:order-1">
                        Show
                        <select class="kt-select w-16" data-kt-datatable-size="true" data-kt-select="" name="perpage"></select>
                        per page
                    </div>
                    <div class="flex items-center gap-4 order-1 md:order-2">
                        <span data-kt-datatable-info="true"></span>
                        <div class="kt-datatable-pagination" data-kt-datatable-pagination="true"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
