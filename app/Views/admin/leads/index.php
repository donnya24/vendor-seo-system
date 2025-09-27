<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div id="pageWrap" class="flex-1 flex flex-col overflow-hidden">
  <header class="bg-white shadow-md z-20 sticky top-0">
    <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
      <div>
        <h1 class="text-lg font-bold text-gray-800">Leads Management</h1>
        <p class="text-xs text-gray-500 mt-1">Kelola semua leads dari vendor</p>
      </div>
      <!-- ✅ Taruh tombol + modal di sini -->
      <?= $this->include('admin/leads/create'); ?>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="px-4 sm:px-6 py-6">
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold">NO</th>
              <th class="px-4 py-3 text-left text-xs font-semibold">VENDOR</th>
              <th class="px-4 py-3 text-left text-xs font-semibold">MASUK</th>
              <th class="px-4 py-3 text-left text-xs font-semibold">CLOSING</th>
              <th class="px-4 py-3 text-left text-xs font-semibold">TANGGAL</th>
              <th class="px-4 py-3 text-left text-xs font-semibold">UPDATE</th>
              <th class="px-4 py-3 text-left text-xs font-semibold">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php $no=1; foreach($leads as $lead): ?>
              <tr>
                <td class="px-4 py-2"><?= $no++ ?></td>
                <td class="px-4 py-2"><?= esc($lead['vendor_name']) ?></td>
                <td class="px-4 py-2"><?= esc($lead['jumlah_leads_masuk']) ?></td>
                <td class="px-4 py-2"><?= esc($lead['jumlah_leads_closing']) ?></td>
                <td class="px-4 py-2"><?= esc($lead['tanggal']) ?></td>
                <td class="px-4 py-2"><?= esc($lead['updated_at']) ?></td>
                <td class="px-4 py-2 flex gap-2">
                  <!-- Modal Edit -->
                  <?= view('admin/leads/edit', ['lead' => $lead, 'vendors' => $vendors]) ?>

                  <!-- Tombol Hapus -->
                  <form action="<?= site_url('admin/leads/delete/'.$lead['id']); ?>" method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" onclick="return confirm('Yakin hapus?')" class="text-red-600 text-sm hover:underline">
                      Hapus
                    </button>
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

<?= $this->include('admin/layouts/footer'); ?>
