<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="kt-card kt-card-grid">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Merchants (<?= count($merchants) ?>)</h3>
        <?php if (! empty($merchants)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input data-kt-datatable-search="#merchantsTable" placeholder="Search merchants" type="text" value="">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($merchants)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No merchants have signed up yet.</p></div>
    <?php else: ?>
        <div class="kt-card-content">
            <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10" id="merchantsTable">
                <div class="kt-scrollable-x-auto">
                    <table class="kt-table kt-table-border" data-kt-datatable-table="true">
                        <thead>
                            <tr>
                                <th class="min-w-[240px]">
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Business</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Phone</span>
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
                                        <span class="kt-table-col-label">Plan</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th>
                                    <span class="kt-table-col">
                                        <span class="kt-table-col-label">Signed up</span>
                                        <span class="kt-table-col-sort"></span>
                                    </span>
                                </th>
                                <th class="w-[60px]" data-kt-datatable-column-sort="false">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($merchants as $merchant): ?>
                                <tr>
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="leading-none font-medium text-sm text-mono"><?= esc($merchant['business_name']) ?></span>
                                            <span class="text-xs text-secondary-foreground font-normal"><?= esc($merchant['contact_email']) ?></span>
                                        </div>
                                    </td>
                                    <td><span class="text-2sm"><?= $merchant['contact_phone'] ? esc($merchant['contact_phone']) : '—' ?></span></td>
                                    <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($merchant['status']) ?>"><?= esc(ucfirst($merchant['status'])) ?></span></td>
                                    <td><?= esc(ucfirst($latestPlans[$merchant['id']] ?? '—')) ?></td>
                                    <td><?= esc($merchant['created_at']) ?></td>
                                    <td class="w-[60px]">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <div class="kt-menu" data-kt-menu="true">
                                                <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                    <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button">
                                                        <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                    </button>
                                                    <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                                        <div class="kt-menu-item">
                                                            <a class="kt-menu-link" href="<?= site_url('admin/merchants/' . $merchant['id']) ?>">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                                <span class="kt-menu-title">View</span>
                                                            </a>
                                                        </div>
                                                        <div class="kt-menu-separator"></div>
                                                        <?php if ($merchant['status'] === 'pending'): ?>
                                                            <div class="kt-menu-item">
                                                                <form method="post" action="<?= site_url('admin/merchants/' . $merchant['id'] . '/approve') ?>" class="w-full">
                                                                    <?= csrf_field() ?>
                                                                    <button type="submit" class="kt-menu-link w-full text-left">
                                                                        <span class="kt-menu-icon"><i class="ki-filled ki-check"></i></span>
                                                                        <span class="kt-menu-title">Approve</span>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                            <div class="kt-menu-separator"></div>
                                                        <?php endif; ?>
                                                        <div class="kt-menu-item">
                                                            <a class="kt-menu-link" href="<?= site_url('admin/merchants/' . $merchant['id'] . '/edit') ?>">
                                                                <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                                <span class="kt-menu-title">Edit</span>
                                                            </a>
                                                        </div>
                                                        <div class="kt-menu-separator"></div>
                                                        <div class="kt-menu-item">
                                                            <form method="post" action="<?= site_url('admin/merchants/' . $merchant['id'] . '/delete') ?>" class="js-delete-form" data-confirm-name="<?= esc($merchant['business_name'], 'attr') ?>">
                                                                <?= csrf_field() ?>
                                                                <button type="submit" class="kt-menu-link w-full text-left text-destructive">
                                                                    <span class="kt-menu-icon"><i class="ki-filled ki-trash text-destructive"></i></span>
                                                                    <span class="kt-menu-title text-destructive">Delete</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
