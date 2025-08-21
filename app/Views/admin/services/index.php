<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h2 class="text-xl font-bold text-gray-800 flex items-center"><i class="fas fa-toolbox mr-2 text-blue-600"></i> Services</h2>
      <a href="<?= site_url('admin/services/create'); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
        <i class="fas fa-plus mr-2"></i> New Service
      </a>
    </div>
  </header>
  <div class="h-16"></div>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <div class="bg-white shadow rounded-lg p-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach($items as $it): ?>
              <tr>
                <td class="px-6 py-4 text-sm"><?= esc($it['id']); ?></td>
                <td class="px-6 py-4 text-sm"><?= esc($it['name']); ?></td>
                <td class="px-6 py-4 text-sm"><?= esc($it['vendor_id']); ?></td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <a class="text-blue-600 hover:text-blue-900 mr-3" href="<?= site_url('admin/services/'.$it['id'].'/edit'); ?>"><i class="fas fa-edit"></i></a>
                  <form method="post" action="<?= site_url('admin/services/'.$it['id'].'/delete'); ?>" class="inline">
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
