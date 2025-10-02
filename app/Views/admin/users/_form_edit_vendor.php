<!-- app/Views/admin/users/_form_edit_vendor.php -->

<form id="editVendorForm" 
      action="<?= site_url('admin/users/update/'.($user['id'] ?? 0)) ?>" 
      method="post"
      x-data="editVendorForm()"
      @submit.prevent="submitForm($event)">
    <?= csrf_field() ?>
    <input type="hidden" name="role" value="vendor">
    <input type="hidden" name="user_id" value="<?= $user['id'] ?? 0 ?>">
    
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
            
            <!-- Nama Vendor -->
            <div>
                <label for="business_name" class="block text-sm font-medium text-gray-700">Nama Vendor</label>
                <input type="text" 
                       id="business_name" 
                       name="business_name" 
                       value="<?= esc($user['business_name'] ?? ($profile['business_name'] ?? '')) ?>"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- Nama Lengkap -->
            <div>
                <label for="owner_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" 
                       id="owner_name" 
                       name="owner_name" 
                       value="<?= esc($user['owner_name'] ?? ($profile['owner_name'] ?? '')) ?>"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- No. Telepon -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                <input type="text" 
                       id="phone" 
                       name="phone" 
                       value="<?= esc($user['phone'] ?? ($profile['phone'] ?? '')) ?>"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- No. WhatsApp -->
            <div>
                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">No. WhatsApp</label>
                <input type="text" 
                       id="whatsapp_number" 
                       name="whatsapp_number" 
                       value="<?= esc($user['whatsapp_number'] ?? ($profile['whatsapp_number'] ?? '')) ?>"
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- Komisi yang Diajukan -->
<!-- Ganti bagian komisi di form edit vendor -->
<div>
    <label for="commission_type" class="block text-sm font-medium text-gray-700">Tipe Komisi</label>
    <select id="commission_type" name="commission_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" onchange="toggleCommissionFields()">
        <option value="percent" <?= ($profile['commission_type'] ?? 'nominal') === 'percent' ? 'selected' : '' ?>>Persentase (%)</option>
        <option value="nominal" <?= ($profile['commission_type'] ?? 'nominal') === 'nominal' ? 'selected' : '' ?>>Nominal (Rp)</option>
    </select>
</div>

<div id="percent_commission_field" style="display: <?= ($profile['commission_type'] ?? 'nominal') === 'percent' ? 'block' : 'none' ?>;">
    <label for="requested_commission" class="block text-sm font-medium text-gray-700">Komisi Persentase</label>
    <input type="number" id="requested_commission" name="requested_commission" 
           value="<?= esc($profile['requested_commission'] ?? '') ?>"
           step="0.01" min="0" max="100" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
           placeholder="Contoh: 10.5">
    <p class="mt-1 text-xs text-gray-500">Masukkan persentase (0-100%)</p>
</div>

<div id="nominal_commission_field" style="display: <?= ($profile['commission_type'] ?? 'nominal') === 'nominal' ? 'block' : 'none' ?>;">
    <label for="requested_commission_nominal" class="block text-sm font-medium text-gray-700">Komisi Nominal</label>
    <input type="number" id="requested_commission_nominal" name="requested_commission_nominal" 
           value="<?= esc($profile['requested_commission_nominal'] ?? '') ?>"
           step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
           placeholder="Contoh: 500000">
    <p class="mt-1 text-xs text-gray-500">Masukkan nominal dalam Rupiah</p>
</div>
            
            <!-- Status -->
            <div>
                <label for="vendor_status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="vendor_status" 
                        name="vendor_status" 
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="pending" <?= (($user['vendor_status'] ?? $profile['status'] ?? 'pending') === 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="verified" <?= (($user['vendor_status'] ?? $profile['status'] ?? 'pending') === 'verified') ? 'selected' : '' ?>>Verified</option>
                    <option value="rejected" <?= (($user['vendor_status'] ?? $profile['status'] ?? 'pending') === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                    <option value="inactive" <?= (($user['vendor_status'] ?? $profile['status'] ?? 'pending') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        
        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password (kosongkan jika tidak ingin mengubah)</label>
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
                   placeholder="Kosongkan jika tidak ingin mengubah password"
                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
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
            <span x-show="!loading">Simpan</span>
            <span x-show="loading">
                <i class="fas fa-spinner fa-spin"></i> Menyimpan...
            </span>
        </button>
    </div>
</form>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('editVendorForm', () => ({
        loading: false,
        
        async submitForm(event) {
            this.loading = true;
            
            const form = event.target;
            const formData = new FormData(form);
            
            // Validasi password
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