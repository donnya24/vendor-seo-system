<?= $this->extend('Seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<?php
$session       = session();
$profile       = $profile ?? [];
$userEmail     = $userEmail ?? '';
$status        = $profile['status'] ?? 'pending';
$successMsg    = $session->getFlashdata('success');
$errorMsg      = $session->getFlashdata('error');

$profileImage     = $profile['profile_image'] ?? '';
$profileImagePath = $profileImage && is_file(FCPATH . 'uploads/seo_profiles/' . $profileImage)
  ? base_url('uploads/seo_profiles/' . $profileImage)
  : base_url('assets/img/default-avatar.png');
?>

<div class="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">
  <div class="flex items-center gap-4 mb-6">
    <img src="<?= esc($profileImagePath) ?>" class="w-16 h-16 rounded-full object-cover border" alt="Foto Profil">
    <div>
      <h3 class="text-lg font-bold"><?= esc($profile['name'] ?? 'SEO User') ?></h3>
      <p class="text-sm">
        <span class="px-2 py-0.5 rounded-full text-xs <?= ($status === 'active') ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
          <?= esc(ucfirst($status)) ?>
        </span>
      </p>
      <p class="text-sm text-gray-600 mt-1"><?= esc($userEmail) ?></p>
    </div>
  </div>

  <?php if ($successMsg): ?>
    <div class="mb-4 p-3 bg-green-50 text-green-700 rounded text-sm">
      <i class="fas fa-check-circle mr-1"></i><?= esc($successMsg) ?>
    </div>
  <?php endif; ?>
  <?php if ($errorMsg): ?>
    <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
      <i class="fas fa-exclamation-circle mr-1"></i><?= esc($errorMsg) ?>
    </div>
  <?php endif; ?>

  <div class="flex gap-2">
    <button @click="$store.ui.modal='seoProfileEdit'" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
      <i class="fas fa-user-edit mr-1"></i>Edit Profil
    </button>
    <button @click="$store.ui.modal='seoPasswordEdit'" class="px-4 py-2 bg-gray-600 text-white rounded text-sm hover:bg-gray-700">
      <i class="fas fa-key mr-1"></i>Ubah Password
    </button>
  </div>
</div>

<?= $this->endSection() ?>
