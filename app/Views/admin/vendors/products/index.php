<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>
<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h2 class="text-xl font-bold text-gray-800">
        <i class="fas fa-box mr-2 text-blue-600"></i> Products — <?= esc($vendor['business_name'] ?? ('Vendor #'.$vendor['id'])); ?>
      </h2>
      <a href="<?= site_url('admin/vendors/'.$vendor['id'].'/products/create'); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i> New Product
      </a>
    </div>
  </header>
  <div class="h-16"></div>
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <div class="bg-white shadow rounded-lg p-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50"><tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr></thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach($products as $p): ?>
              <tr>
                <td class="px-6 py-4 text-sm"><?= esc($p['product_name']); ?></td>
                <td class="px-6 py-4 text-sm"><?= $p['price'] !== null ? number_format($p['price'],2) : '—'; ?></td>
                <td class="px-6 py-4 text-right text-sm">
                  <a class="text-green-600 hover:text-green-900 mr-3"
                     href="<?= site_url('admin/vendors/'.$vendor['id'].'/products/'.$p['id'].'/edit'); ?>"><i class="fas fa-edit"></i></a>
                  <form method="post" action="<?= site_url('admin/vendors/'.$vendor['id'].'/products/'.$p['id'].'/delete'); ?>" class="inline">
                    <?= csrf_field(); ?>
                    <button class="text-red-600 hover:text-red-900" onclick="return confirm('Delete?')"><i class="fas fa-trash-alt"></i></button>
                  </form>
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
