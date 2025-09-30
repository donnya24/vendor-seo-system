<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-6 shadow max-w-3xl">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Detail Laporan Leads</h2>
      <div class="space-x-2">
        <a href="<?= site_url('vendoruser/leads/'.$lead['id'].'/edit'); ?>" 
           class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Edit</a>
        <a href="<?= site_url('vendoruser/leads'); ?>" 
           class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Kembali</a>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <div>
        <div class="text-gray-500">Periode Tanggal</div>
        <div class="font-medium"><?= esc($lead['tanggal_mulai']); ?> – <?= esc($lead['tanggal_selesai']); ?></div>
      </div>
      <div>
        <div class="text-gray-500">Leads Masuk</div>
        <div class="font-medium"><?= esc($lead['jumlah_leads_masuk']); ?></div>
      </div>
      <div>
        <div class="text-gray-500">Leads Closing</div>
        <div class="font-medium"><?= esc($lead['jumlah_leads_closing']); ?></div>
      </div>
      <div>
        <div class="text-gray-500">Reported By Vendor</div>
        <div class="font-medium"><?= esc($lead['reported_by_vendor']); ?></div>
      </div>
      <div>
        <div class="text-gray-500">Terakhir Update</div>
        <div class="font-medium"><?= esc($lead['updated_at']); ?></div>
      </div>
    </div>
  </div>
</div>