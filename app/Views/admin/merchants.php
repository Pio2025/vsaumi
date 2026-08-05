<?= $this->extend('layout/main') ?>
<?= $this->section('pageActions') ?>
<a href="<?= site_url('admin') ?>" class="kt-btn kt-btn-outline">Back to dashboard</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>


<!-- Container -->
     <div class="kt-container-fixed">
      <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
       <div class="flex flex-col justify-center gap-2">
        <h1 class="text-xl font-medium leading-none text-mono">
         Team Members(14)
        </h1>
        <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
         Overview of all team members and roles.
        </div>
       </div>
       <div class="flex items-center gap-2.5">
        <a class="kt-btn kt-btn-outline" href="#">
         Import Members
        </a>
        <a class="kt-btn kt-btn-sm kt-btn-primary" href="#">
         Add Member
        </a>
       </div>
      </div>
     </div>
     <!-- End of Container -->
     <!-- Container -->
     <div class="kt-container-fixed">
      <div class="grid gap-5 lg:gap-7.5">
       <div class="kt-card kt-card-grid min-w-full">
        <div class="kt-card-header py-5 flex-wrap gap-2">
         <h3 class="kt-card-title">
          Team Members
         </h3>
         <div class="flex items-center gap-6">
          <label class="kt-input">
           <i class="ki-filled ki-magnifier">
           </i>
           <input data-kt-datatable-search="#team_members_table" placeholder="Search users" type="text" value=""/>
          </label>
          <label class="kt-label whitespace-nowrap">
           Active Users
           <input class="kt-switch kt-switch-sm" name="check" type="checkbox" value="1"/>
          </label>
         </div>
        </div>
        <div class="kt-card-content">
         <div class="grid" data-kt-datatable="true" data-kt-datatable-page-size="10" id="team_members_table">
          <div class="kt-scrollable-x-auto">
           <table class="kt-table kt-table-border" data-kt-datatable-table="true" id="members_table">
            <thead>
             <tr>
              <th class="w-[60px] text-center">
               <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-check="true" type="checkbox">
               </input>
              </th>
              <th class="min-w-[300px]">
               <span class="kt-table-col">
                <span class="kt-table-col-label">
                 Member
                </span>
                <span class="kt-table-col-sort">
                </span>
               </span>
              </th>
              <th class="text-secondary-foreground font-normal min-w-[220px]">
               Roles
              </th>
              <th class="min-w-[165px]">
               <span class="kt-table-col">
                <span class="kt-table-col-label">
                 Location
                </span>
                <span class="kt-table-col-sort">
                </span>
               </span>
              </th>
              <th class="min-w-[165px]">
               <span class="kt-table-col">
                <span class="kt-table-col-label">
                 Status
                </span>
                <span class="kt-table-col-sort">
                </span>
               </span>
              </th>
              <th class="min-w-[165px]">
               <span class="kt-table-col">
                <span class="kt-table-col-label">
                 Recent activity
                </span>
                <span class="kt-table-col-sort">
                </span>
               </span>
              </th>
              <th class="w-[60px]">
              </th>
             </tr>
            </thead>
            <tbody>
             <tr>
              <td class="text-center">
               <input class="kt-checkbox kt-checkbox-sm" data-kt-datatable-row-check="true" type="checkbox" value="1"/>
              </td>
              <td>
               <div class="flex items-center gap-2.5">
                <div class="">
                 <img class="h-9 rounded-full" src="assets/media/avatars/300-3.png"/>
                </div>
                <div class="flex flex-col gap-0.5">
                 <a class="leading-none font-medium text-sm text-mono hover:text-primary" href="#">
                  Tyler Hero
                 </a>
                 <span class="text-xs text-secondary-foreground font-normal">
                  26 tasks
                 </span>
                </div>
               </div>
              </td>
              <td>
               <div class="flex flex-wrap gap-2.5 mb-2">
                <span class="kt-badge kt-badge-outline">
                 Admin
                </span>
                <span class="kt-badge kt-badge-outline">
                 Support
                </span>
                <span class="kt-badge kt-badge-outline">
                 Editor
                </span>
               </div>
              </td>
              <td>
               <div class="flex items-center gap-1.5">
                <img alt="flag" class="h-4 rounded-full" src="assets/media/flags/estonia.svg">
                 <span class="leading-none text-foreground font-normal">
                  Estonia
                 </span>
                </img>
               </div>
              </td>
              <td>
               <span class="kt-badge kt-badge-outline kt-badge-success">
                Active
               </span>
              </td>
              <td class="text-foreground font-normal">
               Current session
              </td>
              <td class="w-[60px]">
               <div class="kt-menu" data-kt-menu="true">
                <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                 <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                  <i class="ki-filled ki-dots-vertical text-lg">
                  </i>
                 </button>
                 <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                  <div class="kt-menu-item">
                   <a class="kt-menu-link" href="#">
                    <span class="kt-menu-icon">
                     <i class="ki-filled ki-search-list">
                     </i>
                    </span>
                    <span class="kt-menu-title">
                     View
                    </span>
                   </a>
                  </div>
                  <div class="kt-menu-item">
                   <a class="kt-menu-link" href="#">
                    <span class="kt-menu-icon">
                     <i class="ki-filled ki-file-up">
                     </i>
                    </span>
                    <span class="kt-menu-title">
                     Export
                    </span>
                   </a>
                  </div>
                  <div class="kt-menu-separator">
                  </div>
                  <div class="kt-menu-item">
                   <a class="kt-menu-link" href="#">
                    <span class="kt-menu-icon">
                     <i class="ki-filled ki-pencil">
                     </i>
                    </span>
                    <span class="kt-menu-title">
                     Edit
                    </span>
                   </a>
                  </div>
                  <div class="kt-menu-item">
                   <a class="kt-menu-link" href="#">
                    <span class="kt-menu-icon">
                     <i class="ki-filled ki-copy">
                     </i>
                    </span>
                    <span class="kt-menu-title">
                     Make a copy
                    </span>
                   </a>
                  </div>
                  <div class="kt-menu-separator">
                  </div>
                  <div class="kt-menu-item">
                   <a class="kt-menu-link" href="#">
                    <span class="kt-menu-icon">
                     <i class="ki-filled ki-trash">
                     </i>
                    </span>
                    <span class="kt-menu-title">
                     Remove
                    </span>
                   </a>
                  </div>
                 </div>
                </div>
               </div>
              </td>
             </tr>
			</tbody>
		  </table>
	    </div>
	  </div>
	 </div>
    </div>
  </div>
  </div>
		

<div class="kt-card">
    <div class="kt-card-header py-5 flex-wrap gap-2">
        <h3 class="kt-card-title">Merchants (<?= count($merchants) ?>)</h3>
        <?php if (! empty($merchants)): ?>
            <label class="kt-input">
                <i class="ki-filled ki-magnifier"></i>
                <input class="js-datatable-search" data-table="#merchantsTable" placeholder="Search merchants" type="text">
            </label>
        <?php endif; ?>
    </div>
    <?php if (empty($merchants)): ?>
        <div class="p-5"><p class="text-secondary-foreground mb-0">No merchants have signed up yet.</p></div>
    <?php else: ?>
        <div class="kt-card-table">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table kt-table-border" id="merchantsTable">
                    <thead>
                        <tr>
                            <th class="min-w-[240px]">Business</th>
                            <th>Status</th>
                            <th>Plan</th>
                            <th>Signed up</th>
                            <th class="w-[60px]"></th>
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
                                <td><span class="kt-badge kt-badge-sm kt-badge-outline <?= status_badge_class($merchant['status']) ?>"><?= esc(ucfirst($merchant['status'])) ?></span></td>
                                <td><?= esc(ucfirst($latestPlans[$merchant['id']] ?? '—')) ?></td>
                                <td><?= esc($merchant['created_at']) ?></td>
                                <td class="w-[60px]">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <?php if ($merchant['status'] === 'pending'): ?>
                                            <form method="post" action="<?= site_url('admin/merchants/' . $merchant['id'] . '/approve') ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">Approve</button>
                                            </form>
                                        <?php endif; ?>
                                        <div class="kt-menu" data-kt-menu="true">
                                            <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                                <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" type="button">
                                                    <i class="ki-filled ki-dots-vertical text-lg"></i>
                                                </button>
                                                <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
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
        </div>
    <?php endif; ?>
</div>

<?= $this->section('scripts') ?>
<script>
    $(function () {
        $('#merchantsTable').DataTable({
            order: [[3, 'desc']],
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
