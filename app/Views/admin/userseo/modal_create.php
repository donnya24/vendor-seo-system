<?php
// File: app/Views/admin/userseo/modal_create.php
?>

<div class="px-4 py-5">
  <form action="<?= site_url('admin/userseo/store'); ?>" 
        method="post" 
        id="createSeoForm"
        autocomplete="off">

    <?= csrf_field() ?>
    <input type="hidden" name="role" value="seoteam">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Fullname -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Nama Lengkap <span class="text-red-500">*</span>
        </label>
        <input name="fullname" 
               required 
               placeholder="Masukkan nama lengkap" 
               value="<?= old('fullname') ?>"
               autocomplete="name"
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
      </div>

      <!-- Username -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Username <span class="text-red-500">*</span>
        </label>
        <input name="username" 
               required 
               placeholder="Masukkan username" 
               value="<?= old('username') ?>"
               autocomplete="username new"
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
        <!-- Error container will be added dynamically by JavaScript -->
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">No. Telepon</label>
        <input name="phone" 
               placeholder="08xx xxxx xxxx" 
               value="<?= old('phone') ?>"
               autocomplete="tel"
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
      </div>

      <!-- Email -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Email <span class="text-red-500">*</span>
        </label>
        <input type="email" 
               name="email" 
               required 
               placeholder="email@contoh.com" 
               value="<?= old('email') ?>"
               autocomplete="email new"
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
        <!-- Error container will be added dynamically by JavaScript -->
      </div>

      <!-- Password -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Password <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input type="password" 
                 name="password" 
                 id="create_password"
                 required 
                 minlength="8"
                 placeholder="Minimal 8 karakter"
                 autocomplete="new-password"
                 class="w-full pl-3 pr-10 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
          <button type="button" 
                  onclick="togglePasswordDirect('create_password', this)"
                  class="password-toggle-btn absolute inset-y-0 right-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-10">
            <i class="fa-regular fa-eye text-sm"></i>
          </button>
        </div>
        <!-- Error container will be added dynamically by JavaScript -->
      </div>

      <!-- Konfirmasi Password -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Konfirmasi Password <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input type="password" 
                 name="password_confirm" 
                 id="create_password_confirm"
                 required 
                 minlength="8"
                 placeholder="Ulangi password"
                 autocomplete="new-password"
                 class="w-full pl-3 pr-10 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
          <button type="button" 
                  onclick="togglePasswordDirect('create_password_confirm', this)"
                  class="password-toggle-btn absolute inset-y-0 right-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-10">
            <i class="fa-regular fa-eye text-sm"></i>
          </button>
        </div>
        <!-- Error container will be added dynamically by JavaScript -->
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 mt-5">
      <button type="button" 
              onclick="closeModal('createUserModal')"
              class="px-4 py-2.5 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium">
        Batal
      </button>
      <button type="submit" 
              id="submitCreateBtn"
              class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed">
        <span id="submitText">Simpan User</span>
        <span id="loadingText" class="hidden">
          <i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...
        </span>
      </button>
    </div>
  </form>
</div>

<script>
// Fungsi langsung untuk toggle password
function togglePasswordDirect(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash text-sm';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye text-sm';
        }
    }
}
</script>