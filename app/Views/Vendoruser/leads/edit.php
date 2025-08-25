<!-- app/Views/vendoruser/leads/edit.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>
<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-6 shadow max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Edit Lead</h2>
    <form method="post" action="<?= site_url('vendor/leads/'.$lead['id'].'/update'); ?>" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-semibold block mb-1">Nama Pelanggan</label>
        <input name="customer_name" value="<?= esc($lead['customer_name']); ?>" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Nomor Kontak</label>
        <input name="customer_phone" value="<?= esc($lead['customer_phone']); ?>" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Layanan</label>
        <select name="service_id" class="w-full border rounded-lg px-3 py-2">
          <?php foreach($services as $s): ?>
            <option value="<?= $s['id']; ?>" <?= $s['id']==$lead['service_id']?'selected':''; ?>><?= esc($s['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Sumber</label>
        <select name="source" class="w-full border rounded-lg px-3 py-2">
          <?php foreach(['vendor_manual','wa_inbox','wa_outbox'] as $src): ?>
            <option value="<?= $src; ?>" <?= $lead['source']===$src?'selected':''; ?>><?= $src; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-sm font-semibold block mb-1">Status</label>
        <select name="status" class="w-full border rounded-lg px-3 py-2">
          <?php foreach(['new','in_progress','closed','rejected'] as $st): ?>
            <option value="<?= $st; ?>" <?= $lead['status']===$st?'selected':''; ?>><?= $st; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="pt-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Update</button>
      </div>
    </form>
  </div>
</div>
<?= $this->include('vendoruser/layouts/footer'); ?>
