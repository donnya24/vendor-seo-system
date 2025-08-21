<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h2 class="text-xl font-bold text-gray-800 flex items-center"><i class="fas fa-users mr-2 text-blue-600"></i> Vendors</h2>
    </div>
  </header>
  <div class="h-16"></div>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <div class="bg-white shadow rounded-lg p-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Business</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Verified</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach($vendors as $v): ?>
              <tr>
                <td class="px-6 py-4 text-sm"><?= esc($v['id']); ?></td>
                <td class="px-6 py-4 text-sm">
                  <a class="text-blue-700 hover:underline" href="<?= site_url('admin/vendors/'.$v['id']); ?>">
                    <?= esc($v['business_name'] ?: '—'); ?>
                  </a>
                </td>
                <td class="px-6 py-4 text-sm"><?= esc($v['status'] ?: 'pending'); ?></td>
                <td class="px-6 py-4 text-sm">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    <?= $v['is_verified']?'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800'; ?>">
                    <?= $v['is_verified']?'Verified':'Pending'; ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <?php if(!$v['is_verified']): ?>
                    <form method="post" action="<?= site_url('admin/vendors/'.$v['id'].'/verify'); ?>" class="inline"><?= csrf_field(); ?>
                      <button class="text-blue-600 hover:text-blue-900"><i class="fas fa-check-circle"></i> Verify</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="<?= site_url('admin/vendors/'.$v['id'].'/unverify'); ?>" class="inline"><?= csrf_field(); ?>
                      <button class="text-yellow-600 hover:text-yellow-900"><i class="fas fa-ban"></i> Unverify</button>
                    </form>
                  <?php endif; ?>
                  <a class="ml-3 text-purple-600 hover:text-purple-800" href="<?= site_url('admin/vendors/'.$v['id'].'/products'); ?>"><i class="fas fa-box"></i> Products</a>
                  <a class="ml-3 text-green-600 hover:text-green-800" href="<?= site_url('admin/vendors/'.$v['id'].'/services'); ?>"><i class="fas fa-toolbox"></i> Services</a>
                  <a class="ml-3 text-indigo-600 hover:text-indigo-800" href="<?= site_url('admin/vendors/'.$v['id'].'/areas'); ?>"><i class="fas fa-map-marker-alt"></i> Areas</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?= $this->include('layouts/footer'); ?>
