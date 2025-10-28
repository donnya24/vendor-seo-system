<div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4" id="profileModalBackdrop">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Ubah Password</h3>
            <button type="button" class="text-gray-500 hover:text-gray-700 transition-colors close-modal-btn">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="passwordForm" action="<?= site_url('admin/profile/password-update'); ?>" method="post" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <div x-data="{ show: false }" class="relative">
                <label class="block text-sm font-semibold mb-1 text-gray-700">Password Sekarang</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="current_password" required autocomplete="current-password"
                           class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
                        <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                    </button>
                </div>
            </div>

            <div x-data="{ show: false }" class="relative">
                <label class="block text-sm font-semibold mb-1 text-gray-700">Password Baru</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="new_password" required minlength="8" autocomplete="new-password" aria-describedby="pwHelp"
                           class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
                        <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                    </button>
                </div>
                <div id="pwHelp" class="text-xs text-gray-500 mt-1">
                    <p class="font-medium mb-1">Password harus mengandung:</p>
                    <ul class="space-y-1 ml-4">
                        <li>• Minimal 8 karakter</li>
                        <li>• Huruf kecil (a-z)</li>
                        <li>• Huruf besar (A-Z)</li>
                        <li>• Angka (0-9)</li>
                        <li>• Karakter khusus (!@#$%^&*)</li>
                    </ul>
                </div>
            </div>

            <div x-data="{ show: false }" class="relative">
                <label class="block text-sm font-semibold mb-1 text-gray-700">Konfirmasi Password</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="pass_confirm" required minlength="8" autocomplete="new-password"
                           class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
                        <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors font-medium cancel-modal-btn">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-save mr-2"></i>Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Handle form submission
document.getElementById('passwordForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Menyimpan...';
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: result.message,
                confirmButtonText: 'OK'
            });
            // Close modal and reset form
            document.querySelector('.close-modal')?.click();
            this.reset();
        } else {
            let errorMessage = result.message || 'Terjadi kesalahan';
            if (result.errors) {
                errorMessage = Object.values(result.errors).join('<br>');
            }
            await Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: errorMessage
            });
        }
    } catch (error) {
        await Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Terjadi kesalahan jaringan'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Simpan Password';
    }
});

// Close modal handlers
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', function() {
        // Close modal logic here
        if (window.Alpine && window.Alpine.store('ui')) {
            window.Alpine.store('ui').modal = null;
        }
    });
});
</script>