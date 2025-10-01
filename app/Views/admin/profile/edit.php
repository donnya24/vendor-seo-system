<?php
 $session = session();
 $ap = $ap ?? [];
 $errors = $session->getFlashdata('errors') ?? [];
 $success = $session->getFlashdata('success');

// Foto profil
 $profileImage = $ap['profile_image'] ?? '';
 $profileOnDisk = $profileImage ? (FCPATH . 'uploads/admin_profiles/' . $profileImage) : '';
 $profileImagePath = ($profileImage && is_file($profileOnDisk))
  ? base_url('uploads/admin_profiles/' . $profileImage)
  : base_url('assets/img/default-avatar.png');
?>

<div x-show="$store.ui.modal==='profileEdit'" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.away="$store.ui.modal=null">
    <div class="px-6 py-4 border-b flex items-center justify-between">
      <h3 class="text-lg font-semibold">Edit Profil</h3>
      <button class="text-gray-500 hover:text-gray-700 transition-colors" @click="$store.ui.modal=null">
        <i class="fas fa-times text-lg"></i>
      </button>
    </div>

    <form action="<?= site_url('admin/profile/update'); ?>" method="post" enctype="multipart/form-data" class="p-6 space-y-4">
      <?= csrf_field() ?>

      <?php if (!empty($success)): ?>
        <div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200">
          <div class="flex items-start"><i class="fas fa-check-circle mr-2 mt-0.5"></i><span><?= esc($success) ?></span></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
          <?php foreach ($errors as $msg): ?>
            <div class="flex items-start"><i class="fas fa-exclamation-circle mr-2 mt-0.5"></i><span><?= esc($msg) ?></span></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Foto Profil -->
      <div class="flex items-center space-x-4">
        <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 border border-gray-300">
          <img src="<?= $profileImagePath ?>" class="w-full h-full object-cover" alt="Foto Profil">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
          <input type="file" name="profile_image" accept="image/*" class="text-sm text-gray-500">
          <div class="mt-1">
            <label class="inline-flex items-center">
              <input type="checkbox" name="remove_profile_image" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
              <span class="ml-2 text-sm text-gray-600">Hapus foto</span>
            </label>
          </div>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
        <input type="text" name="name" value="<?= esc($ap['name'] ?? '') ?>" required
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="<?= esc($ap['email'] ?? '') ?>" required
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
        <input type="text" name="phone" value="<?= esc($ap['phone'] ?? '') ?>"
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
      </div>

      <div class="pt-2">
        <button type="submit" class="px-4 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium">
          Simpan Perubahan
        </button>
        <button type="button" class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 ml-2 transition-colors"
                @click="$store.ui.modal=null">Batal</button>
      </div>
    </form>
  </div>
</div>