<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-plus mr-2 text-blue-600"></i> Create Service</h2>
    </div>
  </header>
  <div class="h-16"></div>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
      <form method="post" action="<?= site_url('admin/services/store'); ?>" class="space-y-4">
        <?= csrf_field(); ?>
        <div>
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input name="name" required class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3"/>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Vendor (optional)</label>
          <select name="vendor_id" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3">
            <option value="">— none —</option>
            <?php foreach($vendors as $v): ?>
              <option value="<?= esc($v['id']); ?>"><?= esc($v['business_name'] ?: ('Vendor #'.$v['id'])); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex gap-2">
          <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Save</button>
          <a href="<?= site_url('admin/services'); ?>" class="px-4 py-2 rounded-md border">Cancel</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?= $this->include('layouts/footer'); ?>
