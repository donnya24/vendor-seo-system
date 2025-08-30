<?php
include_once(APPPATH . 'Views/vendoruser/layouts/header.php');
include_once(APPPATH . 'Views/vendoruser/layouts/sidebar.php');
?>

<!-- Main -->
<div class="flex-1 flex flex-col overflow-hidden" :class="{'md:ml-64': $store.ui.sidebar}">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="$store.ui.sidebar ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h1 class="text-lg font-semibold text-gray-700">Kelola Area</h1>
    </div>
  </header>

  <div class="h-16"></div>

  <!-- CONTENT -->
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <?php if(session()->getFlashdata('success')): ?>
      <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
        <?= session()->getFlashdata('success') ?>
      </div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
      <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded">
        <?= session()->getFlashdata('error') ?>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-md font-medium text-gray-900">Daftar Area</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Area</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if(empty($areas)): ?>
              <tr>
                <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data area.</td>
              </tr>
            <?php else: foreach($areas as $a): ?>
              <tr>
                <td class="px-6 py-4 text-sm"><?= esc($a['id']) ?></td>
                <td class="px-6 py-4 text-sm"><?= esc($a['name']) ?></td>
                <td class="px-6 py-4 text-sm">
                  <?php if(in_array($a['id'], $attachedIds)): ?>
                    <form method="post" action="<?= site_url('vendoruser/areas/detach/'.$a['id']) ?>" class="inline">
                      <?= csrf_field() ?>
                      <button type="submit"
                        class="px-3 py-1 rounded bg-red-600 text-white text-xs hover:bg-red-700">
                        Hapus
                      </button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="<?= site_url('vendoruser/areas/attach') ?>" class="inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="area_id" value="<?= $a['id'] ?>">
                      <button type="submit"
                        class="px-3 py-1 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">
                        Tambahkan
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php include_once(APPPATH . 'Views/vendoruser/layouts/footer.php'); ?>
