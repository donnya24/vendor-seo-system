<!-- app/Views/vendoruser/leads/create.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>
<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-6 shadow max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Tambah Lead</h2>
    <form method="post" action="<?= site_url('vendor/leads/store'); ?>" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-semibold block mb-1">Nama Pelanggan</label>
        <input name="customer_name" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Nomor Kontak</label>
        <input name="customer_phone" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Layanan</label>
        <select name="service_id" class="w-full border rounded-lg px-3 py-2">
          <?php foreach($services as $s): ?>
            <option value="<?= $s['id']; ?>"><?= esc($s['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Sumber</label>
        <select name="source" class="w-full border rounded-lg px-3 py-2">
          <option value="vendor_manual">Input Manual</option>
          <option value="wa_inbox">WA Inbox</option>
          <option value="wa_outbox">WA Outbox</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Status Awal</label>
        <select name="status" class="w-full border rounded-lg px-3 py-2">
          <option value="new">new</option>
          <option value="in_progress">in_progress</option>
          <option value="closed">closed</option>
          <option value="rejected">rejected</option>
        </select>
      </div>
      <div class="pt-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
      </div>
    </form>
  </div>
</div>
<?= $this->include('vendoruser/layouts/footer'); ?>
