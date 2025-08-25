<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h2 class="text-xl font-bold text-gray-800">
        <i class="fas fa-plus mr-2 text-blue-600"></i> New Product — <?= esc($vendor['business_name'] ?? ('Vendor #'.$vendor['id'])); ?>
      </h2>
      <a href="<?= site_url('admin/vendors/'.$vendor['id'].'/products'); ?>" class="text-sm text-gray-600 hover:text-gray-900">Back</a>
    </div>
  </header>
  <div class="h-16"></div>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <?= $this->include('admin/partials/flash'); ?>
    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
      <form method="post" action="<?= site_url('admin/vendors/'.$vendor['id'].'/products/store'); ?>" class="space-y-4">
        <?= csrf_field(); ?>
        <div>
          <label class="block text-sm font-medium text-gray-700">Product Name</label>
          <input name="product_name" required class="mt-1 block w-full border rounded-md py-2 px-3"/>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea name="description" rows="3" class="mt-1 block w-full border rounded-md py-2 px-3"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Price</label>
          <input type="number" step="0.01" name="price" class="mt-1 block w-full border rounded-md py-2 px-3"/>
        </div>
        <div class="flex gap-2">
          <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Save</button>
          <a href="<?= site_url('admin/vendors/'.$vendor['id'].'/products'); ?>" class="px-4 py-2 rounded-md border">Cancel</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?= $this->include('layouts/footer'); ?>
