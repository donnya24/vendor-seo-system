<form action="<?= site_url('seo/profile/password/update'); ?>" method="post">
  <?= csrf_field() ?>

  <!-- Password Lama -->
  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Password Lama</label>
    <div class="relative">
      <input type="password" name="current_password" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10" id="oldPassword">
      <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400" onclick="togglePassword('oldPassword', this)">
        <i class="fas fa-eye"></i>
      </button>
    </div>
  </div>

  <!-- Password Baru -->
  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Password Baru</label>
    <div class="relative">
      <input type="password" name="new_password" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10" id="newPassword">
      <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400" onclick="togglePassword('newPassword', this)">
        <i class="fas fa-eye"></i>
      </button>
    </div>
  </div>

  <!-- Konfirmasi -->
  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
    <div class="relative">
      <input type="password" name="confirm_password" class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10" id="confirmPassword">
      <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400" onclick="togglePassword('confirmPassword', this)">
        <i class="fas fa-eye"></i>
      </button>
    </div>
  </div>

  <!-- Tombol -->
  <div class="flex justify-end gap-2">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Ubah Password</button>
  </div>
</form>

<script>
function togglePassword(fieldId, btn) {
  const field = document.getElementById(fieldId);
  const icon = btn.querySelector('i');
  if (field.type === "password") {
    field.type = "text";
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    field.type = "password";
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
}
</script>
