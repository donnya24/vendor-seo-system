<form id="createVendorForm" 
      action="<?= site_url('admin/users/store') ?>" 
      method="post"
      x-data="vendorForm()"
      @submit.prevent="submitForm($event)">
    <?= csrf_field() ?>
    <input type="hidden" name="role" value="vendor">
    
    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
                <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
            </div>
            
            <div>
                <label for="password_confirm" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
            </div>
            
            <div>
                <label for="business_name" class="block text-sm font-medium text-gray-700">Nama Vendor</label>
                <input type="text" id="business_name" name="business_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
            
            <div>
                <label for="owner_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" id="owner_name" name="owner_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
            
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                <input type="text" id="phone" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
            
            <div>
                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">No. WhatsApp</label>
                <input type="text" id="whatsapp_number" name="whatsapp_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            </div>
            
            <!-- Tipe Komisi dengan Toggle -->
            <div>
                <label for="commission_type" class="block text-sm font-medium text-gray-700">Tipe Komisi</label>
                <select id="commission_type" name="commission_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" onchange="toggleCommissionFields()">
                    <option value="percent">Persentase (%)</option>
                    <option value="nominal" selected>Nominal (Rp)</option>
                </select>
            </div>
            
            <!-- Field Komisi Persentase -->
            <div id="percent_commission_field" style="display: none;">
                <label for="requested_commission" class="block text-sm font-medium text-gray-700">Komisi Persentase</label>
                <input type="number" id="requested_commission" name="requested_commission" 
                       step="0.01" min="0" max="100" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                       placeholder="Contoh: 10.5">
                <p class="mt-1 text-xs text-gray-500">Masukkan persentase (0-100%)</p>
            </div>
            
            <!-- Field Komisi Nominal -->
            <div id="nominal_commission_field">
                <label for="requested_commission_nominal" class="block text-sm font-medium text-gray-700">Komisi Nominal</label>
                <input type="number" id="requested_commission_nominal" name="requested_commission_nominal" 
                       step="0.01" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                       placeholder="Contoh: 500000">
                <p class="mt-1 text-xs text-gray-500">Masukkan nominal dalam Rupiah</p>
            </div>
            
            <div>
                <label for="vendor_status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="vendor_status" name="vendor_status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    <option value="pending" selected>Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="flex justify-end mt-6 space-x-3">
        <button type="button" 
                @click="closeModal()" 
                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
            Batal
        </button>
        <button type="submit" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" 
                :disabled="loading">
            <span x-show="!loading">Simpan</span>
            <span x-show="loading">
                <i class="fas fa-spinner fa-spin"></i> Menyimpan...
            </span>
        </button>
    </div>
</form>

<script>
// Fungsi untuk toggle field komisi
function toggleCommissionFields() {
    const commissionType = document.getElementById('commission_type').value;
    const percentField = document.getElementById('percent_commission_field');
    const nominalField = document.getElementById('nominal_commission_field');
    
    if (commissionType === 'percent') {
        percentField.style.display = 'block';
        nominalField.style.display = 'none';
        // Clear nominal field when switching to percent
        document.getElementById('requested_commission_nominal').value = '';
    } else {
        percentField.style.display = 'none';
        nominalField.style.display = 'block';
        // Clear percent field when switching to nominal
        document.getElementById('requested_commission').value = '';
    }
}

// Panggil saat halaman load untuk set initial state
document.addEventListener('DOMContentLoaded', function() {
    toggleCommissionFields();
});

document.addEventListener('alpine:init', () => {
    Alpine.data('vendorForm', () => ({
        loading: false,
        
        async submitForm(event) {
            this.loading = true;
            
            const form = event.target;
            const formData = new FormData(form);
            
            // Validasi password
            const password = formData.get('password');
            const passwordConfirm = formData.get('password_confirm');
            
            if (password !== passwordConfirm) {
                alert('Konfirmasi password tidak sama!');
                this.loading = false;
                return;
            }
            
            if (password.length < 8) {
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
                            window.location.href = '<?= site_url('admin/users?tab=vendor') ?>';
                        }, 1500);
                    } else {
                        alert(result.message || 'Gagal menyimpan data');
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
            const modal = document.querySelector('#createUserModal');
            if (modal && modal.__x) {
                modal.__x.close();
            }
        }
    }));
});
</script>