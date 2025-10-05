<form id="editSeoForm" 
<<<<<<< HEAD
      action="<?= site_url('admin/users/update') ?>" 
      method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="role" value="seoteam">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    
    <!-- Debug info untuk memastikan ID benar -->
    <div style="display: none;" id="debugInfo">
        User ID: <?= $user['id'] ?? 'NOT_SET' ?>,
        Username: <?= $user['username'] ?? 'NOT_SET' ?>,
        Name: <?= $user['name'] ?? 'NOT_SET' ?>
    </div>
=======
      action="<?= site_url('admin/users/update/' . ($user['id'] ?? '')) ?>" 
      method="post"
      x-data="editSeoForm()"
      @submit.prevent="submitForm($event)">
    <?= csrf_field() ?>
    <input type="hidden" name="role" value="seoteam">
    <input type="hidden" name="id" value="<?= $user['id'] ?? '' ?>">
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
    
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
<<<<<<< HEAD
                       value="<?= esc($user['name'] ?? '') ?>"
=======
                       value="<?= esc($user['name'] ?? ($profile['name'] ?? '')) ?>"
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- No. Telepon -->
            <div class="md:col-span-2">
                <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                <input type="text" 
                       id="phone" 
                       name="phone" 
<<<<<<< HEAD
                       value="<?= esc($user['phone'] ?? '') ?>"
=======
                       value="<?= esc($user['phone'] ?? ($profile['phone'] ?? '')) ?>"
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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
<<<<<<< HEAD
                onclick="closeEditModal()" 
                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
            Batal
        </button>
        <button type="submit" 
                id="submitBtn"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span id="submitText">Update</span>
            <span id="loadingText" class="hidden">
                <i class="fas fa-spinner fa-spin mr-1"></i> Updating...
=======
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
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
            </span>
        </button>
    </div>
</form>

<script>
<<<<<<< HEAD
// Event listener untuk form submission dengan debugging
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editSeoForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingText = document.getElementById('loadingText');
    const debugInfo = document.getElementById('debugInfo');

    console.log('Form loaded. Debug info:', debugInfo ? debugInfo.textContent : 'No debug info');

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            console.log('Form submitted');
            console.log('Form action:', this.action);
            console.log('User ID from hidden field:', document.querySelector('input[name="id"]').value);
            
            // Validasi form
            if (!validateForm()) {
                return;
            }
            
            // Set loading state
            setLoadingState(true);
            
            try {
                const formData = new FormData(this);
                
                // Log form data untuk debugging
                console.log('FormData entries:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }
                
                const response = await fetch(this.action, {
=======
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
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
<<<<<<< HEAD
                console.log('Response status:', response.status);
                const result = await response.json();
                console.log('Response result:', result);
                
                if (response.ok && result.success) {
                    showToast(result.message || 'Data berhasil diupdate', 'success');
                    closeEditModal();
                    
                    // Redirect setelah delay
                    setTimeout(() => {
                        window.location.href = '<?= site_url('admin/users?tab=seo') ?>';
                    }, 1500);
                    
                } else {
                    showToast(result.message || 'Gagal mengupdate data', 'error');
                    setLoadingState(false);
                }
            
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat menyimpan data: ' + error.message, 'error');
                setLoadingState(false);
            }
        });
    }

    function validateForm() {
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        const userId = document.querySelector('input[name="id"]').value;
        
        console.log('Validating form. User ID:', userId);
        
        if (!userId || userId === '') {
            showToast('ID user tidak valid!', 'error');
            return false;
        }
        
        // Validasi password jika diisi
        if (password || passwordConfirm) {
            if (password !== passwordConfirm) {
                showToast('Konfirmasi password tidak sama!', 'error');
                return false;
            }
            
            if (password.length < 8) {
                showToast('Password minimal 8 karakter!', 'error');
                return false;
            }
        }
        
        // Validasi required fields
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        
        if (!username) {
            showToast('Username harus diisi!', 'error');
            return false;
        }
        
        if (!email) {
            showToast('Email harus diisi!', 'error');
            return false;
        }
        
        // Validasi format email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('Format email tidak valid!', 'error');
            return false;
        }
        
        return true;
    }

    function setLoadingState(isLoading) {
        if (isLoading) {
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            loadingText.classList.remove('hidden');
        } else {
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            loadingText.classList.add('hidden');
        }
    }
});

// Fungsi untuk menutup modal edit
function closeEditModal() {
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.classList.add('modal-hidden');
        modal.classList.remove('modal-active');
    }
    document.body.style.overflow = '';
}

// Fungsi showToast 
function showToast(message, type = 'info') {
    // Hapus toast existing jika ada
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    const types = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500', 
        info: 'bg-blue-500'
    };
    
    toast.className = `toast-notification fixed top-4 right-4 z-[10000] px-6 py-3 rounded-lg text-white shadow-lg ${types[type] || types.info} transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 10);
    
    // Auto remove setelah 4 detik
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 4000);
}

// Event listener untuk menutup modal dengan ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});

// Event listener untuk klik di luar modal
document.addEventListener('click', function(e) {
    const modal = document.getElementById('editUserModal');
    if (modal && e.target === modal) {
        closeEditModal();
    }
=======
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
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
});
</script>