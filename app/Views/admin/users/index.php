<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 sticky top-0">
    <div class="flex items-center justify-between p-4">
      <h2 class="font-semibold text-gray-700">Users</h2>
      <a href="<?= site_url('admin/users/create'); ?>" class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm">
        <i class="fa fa-plus mr-1"></i> New User
      </a>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-100 text-xs uppercase text-gray-600">
          <tr>
            <th class="px-4 py-2 text-left">ID</th>
            <th class="px-4 py-2 text-left">Username</th>
            <th class="px-4 py-2 text-left">Email</th>
            <th class="px-4 py-2 text-left">Role</th>
            <th class="px-4 py-2 text-left">Vendor Status</th>
            <th class="px-4 py-2 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <?php foreach ($users as $u): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= esc($u['id']) ?></td>
              <td class="px-4 py-2"><?= esc($u['username'] ?? '-') ?></td>
              <td class="px-4 py-2">
                <?php // ambil email dari identities kalau butuh; untuk ringkas diasumsikan ada $u['email'] ?>
                <?= esc($u['email'] ?? '-') ?>
              </td>
              <td class="px-4 py-2"><?= esc(implode(',', $u['groups'])) ?></td>
              <td class="px-4 py-2"><?= esc($u['vendor_status'] ?? '-') ?></td>
              <td class="px-4 py-2 text-right space-x-1">
                <a href="<?= site_url('admin/users/'.$u['id'].'/edit'); ?>"
                   class="px-2 py-1 rounded bg-yellow-500 text-white text-xs">Edit</a>
                <form action="<?= site_url('admin/users/'.$u['id'].'/delete'); ?>" method="post" class="inline"
                      onsubmit="return confirm('Hapus user ini?')">
                  <?= csrf_field() ?>
                  <button class="px-2 py-1 rounded bg-red-600 text-white text-xs">Delete</button>
                </form>
                <?php if (in_array('vendor', $u['groups'])): ?>
                  <form action="<?= site_url('admin/users/'.$u['id'].'/toggle-suspend'); ?>" method="post" class="inline"
                        onsubmit="return confirm('Toggle suspend vendor ini?')">
                    <?= csrf_field() ?>
                    <button class="px-2 py-1 rounded bg-gray-700 text-white text-xs">Suspend/Unsuspend</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($users)): ?>
            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>


