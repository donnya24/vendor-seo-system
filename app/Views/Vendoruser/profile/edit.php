<?php
$session = session();
$vp = $vp ?? [];
$status = $vp['status'] ?? 'pending';
$isVerified = $status === 'verified';
$hasCommissionApproved = !empty($vp['commission_rate']);
$currentCommission = $vp['commission_rate'] ?? null;
$requestedCommission = $vp['requested_commission'] ?? null;

$auth = service('auth');
$user = $auth->user();
$userEmail = $user->email ?? '';

$successMessage = $session->getFlashdata('success');
$errorMessage = $session->getFlashdata('error');
$errors = $session->getFlashdata('errors');

$profileImage = $vp['profile_image'] ?? '';
$profileImagePath = base_url('assets/img/default-avatar.png');
if (!empty($profileImage) && file_exists(FCPATH . 'uploads/vendoruser/profiles/' . $profileImage)) {
    $profileImagePath = base_url('uploads/vendoruser/profiles/' . $profileImage);
}
?>

<!-- MODAL: Ubah Password -->
<div x-show="$store.ui.modal==='passwordEdit'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.away="$store.ui.modal=null">
    <div class="px-6 py-4 border-b flex items-center justify-between">
      <h3 class="text-lg font-semibold">Ubah Password</h3>
      <button class="text-gray-500 hover:text-gray-700" @click="$store.ui.modal=null"><i class="fas fa-times"></i></button>
    </div>
    <form action="<?= site_url('vendoruser/profile/passwordUpdate') ?>" method="post" class="p-6 space-y-4">
      <?= csrf_field() ?>
      <?php if($session->getFlashdata('error_password')): ?>
        <div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm"><?= $session->getFlashdata('error_password') ?></div>
      <?php endif; ?>
      <?php if($session->getFlashdata('success_password')): ?>
        <div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm"><?= $session->getFlashdata('success_password') ?></div>
      <?php endif; ?>

      <?php foreach (['current_password'=>'Password Sekarang','new_password'=>'Password Baru','pass_confirm'=>'Konfirmasi Password'] as $name=>$label): ?>
      <div x-data="{ show: false }">
        <label class="block text-sm font-semibold mb-1"><?= $label ?></label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" name="<?= $name ?>" required class="w-full border rounded-lg px-3 py-2.5 pr-10">
          <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 w-10 text-gray-400"><i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i></button>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="pt-2">
        <button class="px-4 py-2.5 bg-blue-600 text-white rounded-lg">Simpan</button>
        <button type="button" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg ml-2" @click="$store.ui.modal=null">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Edit Profil -->
<div x-show="$store.ui.modal==='profileEdit'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.away="$store.ui.modal=null">
    <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white z-10">
      <h3 class="text-lg font-semibold">Edit Profil Vendor</h3>
      <button class="text-gray-500 hover:text-gray-700" @click="$store.ui.modal=null"><i class="fas fa-times"></i></button>
    </div>

    <!-- Status -->
    <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
      <span class="text-sm font-medium text-gray-600">Status Verifikasi:</span>
      <span class="px-3 py-1 rounded-full text-xs font-semibold 
        <?= $status==='verified'?'bg-green-100 text-green-800':($status==='rejected'?'bg-red-100 text-red-800':($status==='inactive'?'bg-gray-100 text-gray-800':'bg-yellow-100 text-yellow-800')) ?>">
        <?= ucfirst($status) ?>
      </span>
    </div>

    <?php if ($successMessage): ?><div class="px-6 py-4 bg-green-50"><?= esc($successMessage) ?></div><?php endif; ?>
    <?php if ($errorMessage): ?><div class="px-6 py-4 bg-red-50"><?= esc($errorMessage) ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="px-6 py-4 bg-red-50"><?= implode('<br>', $errors) ?></div><?php endif; ?>

    <form action="<?= site_url('vendoruser/profile/update') ?>" method="post" enctype="multipart/form-data" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <?= csrf_field() ?>

      <!-- Foto Profil -->
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold mb-2">Foto Profil</label>
        <div class="flex items-center">
          <img id="profile-image-preview" src="<?= $profileImagePath ?>" class="w-24 h-24 rounded-full object-cover border mr-4">
          <label class="cursor-pointer text-blue-600">
            <i class="fas fa-camera"></i>
            <input type="file" id="profile_image" name="profile_image" class="hidden" onchange="previewImage(this,'profile-image-preview')">
          </label>
          <input type="hidden" id="remove_profile_image" name="remove_profile_image" value="0">
        </div>
      </div>

      <!-- Email readonly -->
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold mb-2">Email</label>
        <input type="email" value="<?= esc($userEmail) ?>" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-600">
      </div>

      <!-- Bisnis -->
      <div>
        <label class="block text-sm font-semibold mb-2">Nama Bisnis</label>
        <input name="business_name" value="<?= esc($vp['business_name'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2">Nama Pemilik</label>
        <input name="owner_name" value="<?= esc($vp['owner_name'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
      </div>

      <!-- Kontak -->
      <div>
        <label class="block text-sm font-semibold mb-2">No. WhatsApp</label>
        <input name="whatsapp_number" value="<?= esc($vp['whatsapp_number'] ?? '') ?>" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2">No. Telepon (opsional)</label>
        <input name="phone" value="<?= esc($vp['phone'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2">
      </div>

      <!-- Komisi -->
      <div class="md:col-span-2">
        <h4 class="font-semibold mb-2">Pengajuan Komisi</h4>
        <?php if ($isVerified && $hasCommissionApproved): ?>
          <input type="text" value="<?= $currentCommission ?>%" readonly class="w-24 border rounded-lg px-3 py-2 bg-green-100 text-green-800">
        <?php elseif ($requestedCommission && !$hasCommissionApproved): ?>
          <input type="text" value="<?= $requestedCommission ?>%" readonly class="w-24 border rounded-lg px-3 py-2 bg-blue-100 text-blue-800">
        <?php else: ?>
          <input type="number" name="requested_commission" min="1" max="100" step="0.1" value="<?= $requestedCommission ?>" class="w-24 border rounded-lg px-3 py-2">
        <?php endif; ?>
      </div>

      <div class="md:col-span-2 pt-4 border-t mt-4 flex justify-between">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
        <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg" @click="$store.ui.modal=null">Tutup</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewImage(input, previewId){
  const file = input.files[0]; const preview=document.getElementById(previewId);
  if(file){
    if(file.size>2*1024*1024){alert('Maksimal 2MB');input.value='';return;}
    const reader=new FileReader(); reader.onload=e=>{preview.src=e.target.result}; reader.readAsDataURL(file);
    document.getElementById('remove_profile_image').value="0";
  }
}
</script>
