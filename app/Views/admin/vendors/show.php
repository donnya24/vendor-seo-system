<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>
<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h2 class="text-xl font-bold text-gray-800">
        <i class="fas fa-store mr-2 text-blue-600"></i> Vendor: <?= esc($vendor['business_name'] ?: ('#'.$vendor['id'])); ?>
      </h2>
      <a href="<?= site_url('admin/vendors'); ?>" class="text-sm text-gray-600 hover:text-gray-900">Back</a>
    </div>
  </header>
  <div class="h-16"></div>
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <?= $this->include('admin/partials/flash'); ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white rounded-lg p-6 shadow">
        <h3 class="font-semibold mb-2">Profile</h3>
        <div class="text-sm text-gray-700 space-y-1">
          <div><span class="text-gray-500">ID:</span> <?= esc($vendor['id']); ?></div>
          <div><span class="text-gray-500">Status:</span> <?= esc($vendor['status'] ?: 'pending'); ?></div>
          <div><span class="text-gray-500">Verified:</span> <?= $vendor['is_verified']?'Yes':'No'; ?></div>
          <div><span class="text-gray-500">WhatsApp:</span> <?= esc($vendor['whatsapp_number'] ?? '—'); ?></div>
        </div>
        <div class="mt-4 flex gap-2">
          <?php if(!$vendor['is_verified']): ?>
            <form method="post" action="<?= site_url('admin/vendors/'.$vendor['id'].'/verify'); ?>"><?= csrf_field(); ?>
              <button class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700"><i class="fas fa-check-circle mr-1"></i> Verify</button>
            </form>
          <?php else: ?>
            <form method="post" action="<?= site_url('admin/vendors/'.$vendor['id'].'/unverify'); ?>"><?= csrf_field(); ?>
              <button class="bg-yellow-600 text-white px-3 py-1 rounded-md hover:bg-yellow-700"><i class="fas fa-ban mr-1"></i> Unverify</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <a href="<?= site_url('admin/vendors/'.$vendor['id'].'/products'); ?>" class="bg-white rounded-lg p-6 shadow hover:shadow-md">
        <h3 class="font-semibold"><i class="fas fa-box mr-2 text-blue-600"></i> Products</h3>
        <p class="text-sm text-gray-500 mt-2">Kelola produk vendor.</p>
      </a>
      <a href="<?= site_url('admin/vendors/'.$vendor['id'].'/services'); ?>" class="bg-white rounded-lg p-6 shadow hover:shadow-md">
        <h3 class="font-semibold"><i class="fas fa-toolbox mr-2 text-green-600"></i> Services</h3>
        <p class="text-sm text-gray-500 mt-2">Atur layanan yang dikerjakan vendor.</p>
      </a>
      <a href="<?= site_url('admin/vendors/'.$vendor['id'].'/areas'); ?>" class="bg-white rounded-lg p-6 shadow hover:shadow-md">
        <h3 class="font-semibold"><i class="fas fa-map-marker-alt mr-2 text-indigo-600"></i> Areas</h3>
        <p class="text-sm text-gray-500 mt-2">Atur cakupan area vendor.</p>
      </a>
    </div>
  </main>
</div>
<?= $this->include('layouts/footer'); ?>
