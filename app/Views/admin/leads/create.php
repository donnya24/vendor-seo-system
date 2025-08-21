<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-plus mr-2 text-blue-600"></i> Create Lead</h2>
      <a href="<?= site_url('admin/leads'); ?>" class="text-sm text-gray-600 hover:text-gray-900">Back</a>
    </div>
  </header>
  <div class="h-16"></div>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <?= $this->include('admin/partials/flash'); ?>
    <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
      <form method="post" action="<?= site_url('admin/leads/store'); ?>" class="grid grid-cols-1 gap-4">
        <?= csrf_field(); ?>
        <div>
          <label class="block text-sm font-medium text-gray-700">Customer Name</label>
          <input name="customer_name" required class="mt-1 block w-full border rounded-md py-2 px-3"/>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Service</label>
            <select name="service_id" class="mt-1 block w-full border rounded-md py-2 px-3">
              <?php foreach($services as $s): ?>
                <option value="<?= esc($s['id']); ?>"><?= esc($s['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Vendor</label>
            <select name="vendor_id" class="mt-1 block w-full border rounded-md py-2 px-3">
              <?php foreach($vendors as $v): ?>
                <option value="<?= esc($v['id']); ?>"><?= esc($v['business_name'] ?: ('Vendor #'.$v['id'])); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="mt-1 block w-full border rounded-md py-2 px-3">
              <option value="new">New</option>
              <option value="in_progress">In Progress</option>
              <option value="closed">Closed</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Source</label>
            <select name="source" class="mt-1 block w-full border rounded-md py-2 px-3">
              <option value="vendor_manual">Vendor Manual</option>
              <option value="wa_inbox">WA Inbox</option>
              <option value="wa_outbox">WA Outbox</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">WA Chat ID (opsional)</label>
            <input name="wa_chat_id" class="mt-1 block w-full border rounded-md py-2 px-3"/>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">WA Message ID (opsional)</label>
            <input name="wa_message_id" class="mt-1 block w-full border rounded-md py-2 px-3"/>
          </div>
        </div>
        <div class="flex gap-2">
          <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Save</button>
          <a href="<?= site_url('admin/leads'); ?>" class="px-4 py-2 rounded-md border">Cancel</a>
        </div>
      </form>
    </div>
  </main>
</div>

<?= $this->include('layouts/footer'); ?>
