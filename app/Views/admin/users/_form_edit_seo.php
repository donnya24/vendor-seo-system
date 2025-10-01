<?php
/* ================= Data dari controller ================= */
$uid      = $user['id'] ?? '';
$username = $user['username'] ?? '';
$fullname = $user['name'] ?? ($user['fullname'] ?? '');
$phone    = $user['phone'] ?? '';
$email    = $user['email'] ?? '';
?>
<div class="px-4 py-5">
  <form action="<?= site_url('admin/users/' . $uid . '/update'); ?>" 
        method="post" 
        x-data="editSeoForm"
        @submit.prevent="submitForm">

    <?= csrf_field() ?>
    <input type="hidden" name="role" value="seoteam">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Fullname -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Nama Lengkap
        </label>
        <input name="fullname" 
               placeholder="Masukkan nama lengkap" 
               value="<?= esc($fullname) ?>"
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
               value="<?= esc($username) ?>"
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">No. Telepon</label>
        <input name="phone" 
               placeholder="08xx xxxx xxxx" 
               value="<?= esc($phone) ?>"
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
      </div>

      <!-- Email -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Email
        </label>
        <input type="email" 
               name="email" 
               placeholder="email@contoh.com" 
               value="<?= esc($email) ?>"
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
      </div>

      <!-- Password Reset Toggle -->
      <div class="md:col-span-2">
        <div class="flex items-center gap-2 mb-3">
          <input id="toggleResetSEO" type="checkbox" class="h-4 w-4 text-blue-600 rounded border-gray-300" x-model="showResetPassword">
          <label for="toggleResetSEO" class="text-sm font-semibold text-gray-700">Ubah password</label>
        </div>

        <div x-show="showResetPassword" x-transition class="space-y-3">
          <div x-data="{ show: false }">
            <label class="block text-xs font-semibold text-gray-700 mb-1">
              Password Baru <span class="text-gray-400">(opsional)</span>
            </label>
            <div class="relative">
              <input :type="show ? 'text' : 'password'" 
                     name="password" 
                     placeholder="Minimal 8 karakter"
                     class="w-full pr-10 px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
              <button type="button" 
                      @click="show = !show" 
                      class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                <i :class="show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 mt-5">
      <button type="button" 
              @click="closeModal()" 
              class="px-4 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium">
        Batal
      </button>
      <button type="submit" 
              :disabled="loading"
              class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed">
        <span x-show="!loading">Simpan Perubahan</span>
        <span x-show="loading" class="flex items-center gap-2">
          <i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...
        </span>
      </button>
    </div>
  </form>
</div>