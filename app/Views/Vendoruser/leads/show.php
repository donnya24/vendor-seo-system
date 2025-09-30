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
    
    <!-- Tambahkan informasi conversion rate -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
      <h3 class="font-medium text-blue-800 mb-2">Conversion Rate</h3>
      <?php 
        $totalLeads = (int)($lead['jumlah_leads_masuk'] ?? 0);
        $totalClosing = (int)($lead['jumlah_leads_closing'] ?? 0);
        $conversionRate = ($totalLeads > 0) ? round(($totalClosing / $totalLeads) * 100, 1) : 0;
      ?>
      <div class="flex items-center">
        <div class="text-2xl font-bold text-blue-700"><?= $conversionRate ?>%</div>
        <div class="ml-4 flex-1">
          <div class="text-xs text-blue-600 mb-1"><?= $totalClosing ?> closing dari <?= $totalLeads ?> leads</div>
          <div class="w-full bg-blue-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full" style="width: <?= min($conversionRate, 100) ?>%"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>