<form id="editSeoForm" 
      action="<?= site_url('admin/users/update/' . ($user['id'] ?? '')) ?>" 
      method="post"
      x-data="editSeoForm()"
      @submit.prevent="submitForm($event)">
    <?= csrf_field() ?>
    <input type="hidden" name="role" value="seoteam">
    <input type="hidden" name="id" value="<?= $user['id'] ?? '' ?>">
    
    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?= esc($user['username'] ?? '') ?>"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" 
                       required>
            </div>
            
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= esc($user['email'] ?? '') ?>"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" 
                       required>
            </div>
            
            <!-- Nama Lengkap -->
            <div class="md:col-span-2">
                <label for="fullname" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" 
                       id="fullname" 
                       name="fullname" 
                       value="<?= esc($user['name'] ?? ($profile['name'] ?? '')) ?>"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- No. Telepon -->
            <div class="md:col-span-2">
                <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                <input type="text" 
                       id="phone" 
                       name="phone" 
                       value="<?= esc($user['phone'] ?? ($profile['phone'] ?? '')) ?>"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        
        <!-- Password Section -->
        <div class="border-t pt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Ubah Password (Opsional)</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Password Baru -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           value=""
                           placeholder="Kosongkan jika tidak ingin mengubah"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <input type="password" 
                           id="password_confirm" 
                           name="password_confirm" 
                           value=""
                           placeholder="Kosongkan jika tidak ingin mengubah"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>
    </div>
    
    <div class="flex justify-end mt-6 space-x-3">
        <button type="button" 
                @click="closeModal()" 
                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            Batal
        </button>
        <button type="submit" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
                :disabled="loading">
            <span x-show="!loading">Update</span>
            <span x-show="loading">
                <i class="fas fa-spinner fa-spin"></i> Updating...
            </span>
        </button>
    </div>
</form>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('editSeoForm', () => ({
        loading: false,
        
        async submitForm(event) {
            this.loading = true;
            
            const form = event.target;
            const formData = new FormData(form);
            
            // Validasi password jika diisi
            const password = formData.get('password');
            const passwordConfirm = formData.get('password_confirm');
            
            if (password && password !== passwordConfirm) {
                alert('Konfirmasi password tidak sama!');
                this.loading = false;
                return;
            }
            
            if (password && password.length < 8) {
                alert('Password minimal 8 karakter!');
                this.loading = false;
                return;
            }
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.status === 'success') {
                        this.closeModal();
                        showToast(result.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        alert(result.message || 'Gagal mengupdate data');
                    }
                } else {
                    const errorText = await response.text();
                    console.error('Server error:', errorText);
                    alert('Terjadi kesalahan server');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data');
            } finally {
                this.loading = false;
            }
        },
        
        closeModal() {
            const modal = document.querySelector('#editUserModal');
            if (modal && modal.__x) {
                modal.__x.close();
            } else {
                // Fallback
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
    }));
});
</script>