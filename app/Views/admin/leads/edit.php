<!-- Modal Edit Lead -->
<div x-data="{ open: false }" x-cloak>
  <!-- Tombol buka modal -->
  <button @click="open = true"
          class="text-blue-600 hover:underline text-sm">
    Edit
  </button>

  <!-- Overlay -->
  <div x-show="open"
       class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4"
       @click.self="open = false">
    <!-- Modal Box -->
    <div x-show="open" x-transition
         class="bg-white rounded-xl shadow-xl w-full max-w-lg z-50">
      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Edit Lead</h2>
        <button @click="open = false" class="text-gray-400 hover:text-gray-600">
          <i class="fa fa-times text-lg"></i>
        </button>
      </div>

      <!-- Form -->
      <form action="<?= site_url('admin/leads/update/'.$lead['id']) ?>" method="post" id="editLeadForm" class="p-5 space-y-4">
        <?= csrf_field() ?>

        <!-- Vendor -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
          <select name="vendor_id" required
                  class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
            <?php foreach ($vendors as $v): ?>
              <option value="<?= esc($v['id']) ?>"
                <?= $v['id'] == $lead['vendor_id'] ? 'selected' : '' ?>>
                <?= esc($v['business_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Jumlah Leads Masuk -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Leads Masuk *</label>
          <input type="number" name="jumlah_leads_masuk" required min="0"
                 value="<?= esc($lead['jumlah_leads_masuk']) ?>"
                 class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Jumlah Leads Closing -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Leads Closing *</label>
          <input type="number" name="jumlah_leads_closing" required min="0"
                 value="<?= esc($lead['jumlah_leads_closing']) ?>"
                 class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Periode Tanggal -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Tanggal Mulai -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai *</label>
            <input type="date" name="tanggal_mulai" required
                  value="<?= !empty($lead['tanggal_mulai']) ? date('Y-m-d', strtotime($lead['tanggal_mulai'])) : '' ?>"
                  class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- Tanggal Selesai -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai"
                  value="<?= !empty($lead['tanggal_selesai']) ? date('Y-m-d', strtotime($lead['tanggal_selesai'])) : '' ?>"
                  class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
          <button type="button" @click="open = false"
                  class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
            Batal
          </button>
          <button type="submit"
                  class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
            Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>