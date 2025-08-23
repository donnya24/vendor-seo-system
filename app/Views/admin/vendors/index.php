<?= $this->include('admin/layouts/header'); ?>
<div x-data="adminDashboard()" x-init="init()" x-cloak class="flex">
  <?= $this->include('admin/layouts/sidebar'); ?>

  <div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
    <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
      <div class="flex items-center justify-between p-4">
        <h2 class="text-lg font-semibold"><i class="fas fa-users mr-2 text-blue-600"></i> Vendors</h2>
        <button @click="sidebarOpen = !sidebarOpen"><i class="fas fa-bars text-gray-700"></i></button>
      </div>
    </header>
    <div class="h-16"></div>

    <main class="p-4 bg-gray-50">
      <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 p-3 rounded bg-green-50 text-green-700"><?= session('success'); ?></div>
      <?php endif; ?>

      <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">WA</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach($vendors as $v): ?>
              <tr>
                <td class="px-6 py-4">
                  <a class="text-blue-600 hover:underline" href="<?= site_url('admin/vendors/'.$v['id']); ?>">
                    <?= esc($v['business_name'] ?? $v['name'] ?? ('Vendor #'.$v['id'])); ?>
                  </a>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <?= esc($v['wa_number'] ?? '-'); ?>
                  <?php if (!empty($v['imersa_wa_number'])): ?>
                    <div class="text-xs text-gray-400">Imersa WA: <?= esc($v['imersa_wa_number']); ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                    <?= ($v['is_verified']??0) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                    <?= ($v['status'] ?? (($v['is_verified']??0)?'verified':'pending')); ?>
                  </span>
                </td>
                <td class="px-6 py-4"><?= esc($v['commission_rate'] ?? 0) ?>%</td>
                <td class="px-6 py-4 text-right">
                  <?php if (($v['is_verified']??0) == 0): ?>
                    <form action="<?= site_url('admin/vendors/'.$v['id'].'/verify'); ?>" method="post" class="inline">
                      <?= csrf_field() ?>
                      <button class="text-green-600 hover:text-green-800"><i class="fas fa-check mr-1"></i>Verify</button>
                    </form>
                  <?php else: ?>
                    <form action="<?= site_url('admin/vendors/'.$v['id'].'/unverify'); ?>" method="post" class="inline">
                      <?= csrf_field() ?>
                      <button class="text-red-600 hover:text-red-800"><i class="fas fa-times mr-1"></i>Reject</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>

    <?= $this->include('admin/layouts/footer'); ?>
  </div>
</div>
<?= $this->include('admin/partials/logout'); ?>
<script>
  function adminDashboard(){return{sidebarOpen:window.innerWidth>768,init(){const p=localStorage.getItem('sidebarOpen');this.sidebarOpen=p!==null?(p==='true'):(window.innerWidth>768);window.addEventListener('resize',()=>{if(window.innerWidth<=768)this.sidebarOpen=false});this.$watch('sidebarOpen',v=>localStorage.setItem('sidebarOpen',v));}}}
</script>
