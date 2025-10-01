<div class="px-4 py-5">
  <form action="<?= site_url('admin/users/store'); ?>" 
        method="post" 
        x-data="seoForm"
        @submit.prevent="submitForm">

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
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1">No. Telepon</label>
        <input name="phone" 
               placeholder="08xx xxxx xxxx" 
               value="<?= old('phone') ?>"
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
               class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
      </div>

      <!-- Password -->
      <div x-data="{ show: false }">
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Password <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" 
                 name="password" 
                 required 
                 minlength="8"
                 placeholder="Minimal 8 karakter"
                 class="w-full pr-10 px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
          <button type="button" 
                  @click="show = !show" 
                  class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
            <i :class="show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
          </button>
        </div>
      </div>

      <!-- Konfirmasi Password -->
      <div x-data="{ show: false }">
        <label class="block text-xs font-semibold text-gray-700 mb-1">
          Konfirmasi Password <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" 
                 name="password_confirm" 
                 required 
                 minlength="8"
                 placeholder="Ulangi password"
                 class="w-full pr-10 px-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
          <button type="button" 
                  @click="show = !show" 
                  class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
            <i :class="show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
          </button>
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
        <span x-show="!loading">Simpan User</span>
        <span x-show="loading" class="flex items-center gap-2">
          <i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...
        </span>
      </button>
    </div>
  </form>
</div>

<script>
function seoForm() {
  return {
    loading: false,
    
    closeModal() {
      // Tutup modal create
      const createModal = document.querySelector('#createUserModal');
      if (createModal && createModal.__x) {
        createModal.__x.close();
      }
    },
    
    async submitForm(e) {
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form);
      
      // Validasi password
      const password = formData.get('password');
      const passwordConfirm = formData.get('password_confirm');
      
      if (password !== passwordConfirm) {
        alert('Konfirmasi password tidak sama!');
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (response.ok) {
          // Redirect ke halaman users dengan tab yang sesuai
          window.location.href = '<?= site_url('admin/users?tab=seo') ?>';
        } else {
          const result = await response.json();
          alert(result.message || 'Gagal menyimpan user');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>