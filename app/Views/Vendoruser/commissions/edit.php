<div class="w-full">
  <h2 class="text-lg font-semibold mb-4">Edit Komisi</h2>
  <form method="post" action="<?= site_url('vendoruser/commissions/'.$item['id'].'/update'); ?>" 
        class="space-y-4" onsubmit="return validateForm(this)" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div>
      <label class="text-sm font-semibold mb-1 block">Periode Mulai *</label>
      <input type="date" name="period_start" 
             value="<?= old('period_start', esc($item['period_start'])) ?>" required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Periode Akhir *</label>
      <input type="date" name="period_end" 
             value="<?= old('period_end', esc($item['period_end'])) ?>" required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Penghasilan (Rp) *</label>
      <input type="number" name="earning" min="0" 
             value="<?= old('earning', esc($item['earning'])) ?>" required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Nominal Total (Rp) *</label>
      <input type="number" name="amount" min="0" 
             value="<?= old('amount', esc($item['amount'])) ?>" required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Bukti Pembayaran</label>
      <?php if(!empty($item['proof']) && file_exists(FCPATH.'uploads/commissions/'.$item['proof'])): ?>
        <div class="mb-2">
          <a href="<?= base_url('uploads/commissions/'.$item['proof']) ?>" target="_blank" class="text-blue-600 hover:underline">
            Lihat Bukti Saat Ini
          </a>
        </div>
      <?php endif; ?>
      <input type="file" name="proof" 
             class="w-full border rounded-lg px-3 py-2"
             accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
      <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Word, Excel, PPT (Opsional)</p>
    </div>

    <div class="pt-2 flex gap-2">
      <button type="submit" 
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        Update
      </button>
      <button type="button" onclick="closeModal()" 
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
        Batal
      </button>
    </div>
  </form>
</div>

<script>
function validateForm(form) {
  let start = form.querySelector('[name="period_start"]').value;
  let end = form.querySelector('[name="period_end"]').value;
  let earning = form.querySelector('[name="earning"]').value;
  let amount = form.querySelector('[name="amount"]').value;

  if (!start || !end || earning < 0 || amount < 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Kolom wajib diisi',
      text: 'Periode, penghasilan, dan nominal harus diisi dengan benar!',
      width: 350,
      customClass: { popup: 'rounded-lg text-sm' }
    });
    return false;
  }
  return true;
}
</script>
