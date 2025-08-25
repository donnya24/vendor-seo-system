<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>

<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-6 shadow max-w-3xl">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Detail Lead</h2>
      <div class="space-x-2">
        <a href="<?= site_url('vendor/leads/'.$lead['id'].'/edit'); ?>" class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Edit</a>
        <a href="<?= site_url('vendor/leads'); ?>" class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Kembali</a>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <div>
        <div class="text-gray-500">Customer</div>
        <div class="font-medium"><?= esc($lead['customer_name']); ?></div>
      </div>
      <div>
        <div class="text-gray-500">Kontak</div>
        <div class="font-medium"><?= esc($lead['customer_phone']); ?></div>
      </div>
      <div>
        <div class="text-gray-500">Status</div>
        <div class="font-medium"><?= esc($lead['status']); ?></div>
      </div>
      <div>
        <div class="text-gray-500">Sumber</div>
        <div class="font-medium"><?= esc($lead['source']); ?></div>
      </div>
      <div class="md:col-span-2">
        <div class="text-gray-500 mb-1">Waktu Kontak</div>
        <div class="font-medium"><?= esc($lead['contact_time'] ?? ''); ?></div>
      </div>
      <div class="md:col-span-2">
        <div class="text-gray-500 mb-1">Ringkasan</div>
        <div class="font-medium whitespace-pre-line"><?= esc($lead['summary'] ?? '-'); ?></div>
      </div>
    </div>
  </div>
</div>

<?= $this->include('vendoruser/layouts/footer'); ?>
