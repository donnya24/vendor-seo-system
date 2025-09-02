<!-- app/Views/vendoruser/commissions/create.php -->
<div class="w-full">
  <h2 class="text-lg font-semibold mb-4">Tambah Komisi</h2>
  <form method="post" action="<?= site_url('vendoruser/commissions/store'); ?>" 
        class="space-y-4" onsubmit="return validateForm(this)" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div>
      <label class="text-sm font-semibold mb-1 block">Periode Mulai *</label>
      <input type="date" name="period_start" 
             value="<?= old('period_start') ?>" required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Periode Akhir *</label>
      <input type="date" name="period_end" 
             value="<?= old('period_end') ?>" required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Nominal (Rp) *</label>
      <input type="number" name="amount" min="0" 
             value="<?= old('amount') ?>" required
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Bukti Pembayaran</label>
      <input type="file" name="proof" 
             class="w-full border rounded-lg px-3 py-2"
             accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
      <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG</p>
    </div>

    <div class="pt-2 flex gap-2">
      <button type="submit" 
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        Simpan
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
  let amount = form.querySelector('[name="amount"]').value;

  if (!start || !end || amount < 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Kolom wajib diisi',
      text: 'Periode dan nominal harus diisi dengan benar!',
      width: 350,
      customClass: { popup: 'rounded-lg text-sm' }
    });
    return false;
  }
  return true;
}
</script>
