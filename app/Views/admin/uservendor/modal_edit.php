<div class="bg-white rounded-lg max-w-4xl mx-auto p-6">
    <h2 class="text-xl font-semibold mb-4">Edit Vendor</h2>
    
    <form id="editVendorForm" 
          action="<?= site_url('admin/uservendor/update/'.$user['id']) ?>" 
          method="post"
          autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Basic Fields -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username *</label>
                    <input type="text" id="username" name="username" 
                           value="<?= esc($user['username']) ?>" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" 
                           required autocomplete="username">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="<?= esc($user['email']) ?>" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" 
                           required autocomplete="email">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative mt-1">
                        <input type="password" id="password" name="password" 
                               class="block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10" 
                               placeholder="Kosongkan jika tidak ingin mengubah"
                               autocomplete="new-password">
                        <button type="button" 
                                onclick="togglePasswordDirect('password', this)"
                                class="password-toggle-btn absolute inset-y-0 right-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-10"
                                title="Tampilkan password">
                            <i class="fa-regular fa-eye text-sm"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.</p>
                </div>
                
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <div class="relative mt-1">
                        <input type="password" id="password_confirm" name="password_confirm" 
                               class="block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10" 
                               placeholder="Kosongkan jika tidak ingin mengubah"
                               autocomplete="new-password">
                        <button type="button" 
                                onclick="togglePasswordDirect('password_confirm', this)"
                                class="password-toggle-btn absolute inset-y-0 right-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-10"
                                title="Tampilkan password">
                            <i class="fa-regular fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Vendor Fields -->
                <div>
                    <label for="business_name" class="block text-sm font-medium text-gray-700">Nama Vendor *</label>
                    <input type="text" id="business_name" name="business_name" 
                           value="<?= esc($profile['business_name'] ?? '') ?>" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" 
                           required autocomplete="organization">
                </div>
                
                <div>
                    <label for="owner_name" class="block text-sm font-medium text-gray-700">Nama Pemilik *</label>
                    <input type="text" id="owner_name" name="owner_name" 
                           value="<?= esc($profile['owner_name'] ?? '') ?>" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" 
                           required autocomplete="name">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon *</label>
                    <input type="text" id="phone" name="phone" 
                        value="<?= esc($profile['phone'] ?? '') ?>" 
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 phone-input" 
                        required autocomplete="tel"
                        inputmode="numeric"
                        pattern="[0-9]*">
                </div>

                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">No. WhatsApp *</label>
                    <input type="text" id="whatsapp_number" name="whatsapp_number" 
                        value="<?= esc($profile['whatsapp_number'] ?? '') ?>" 
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 phone-input" 
                        required autocomplete="tel"
                        inputmode="numeric"
                        pattern="[0-9]*">
                </div>
                
                <!-- Commission Fields -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Komisi <span class="text-red-500">*</span></label>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="commission_type" value="percent" class="form-radio text-blue-600 focus:ring-blue-500" 
                                   <?= (isset($profile['commission_type']) && $profile['commission_type'] === 'percent') ? 'checked' : '' ?>>
                            <span class="ml-2 text-sm font-medium">Persen (%)</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="commission_type" value="nominal" class="form-radio text-blue-600 focus:ring-blue-500"
                                   <?= (isset($profile['commission_type']) && $profile['commission_type'] === 'nominal') ? 'checked' : '' ?>>
                            <span class="ml-2 text-sm font-medium">Nominal (Rp)</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <!-- Input Persentase -->
                    <div id="percent-input" class="col-span-1" style="<?= (isset($profile['commission_type']) && $profile['commission_type'] === 'nominal') ? 'display:none' : '' ?>">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ajukan/ubah komisi (%) <span class="text-red-500">*</span></label>
                        <div class="flex items-center">
                            <input type="number" name="requested_commission" min="1" max="100" step="0.1"
                                   value="<?= esc($profile['requested_commission'] ?? '') ?>"
                                   class="flex-1 border rounded-lg px-3 py-2 mr-2" <?= (isset($profile['commission_type']) && $profile['commission_type'] === 'percent') ? 'required' : '' ?>>
                            <span class="text-gray-600 font-medium">%</span>
                        </div>
                    </div>

                    <!-- Input Nominal -->
                    <div id="nominal-input" class="col-span-1" style="<?= (isset($profile['commission_type']) && $profile['commission_type'] === 'percent') ? 'display:none' : '' ?>">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ajukan/ubah komisi (Rp) <span class="text-red-500">*</span></label>
                        <div class="flex items-center">
                            <span class="text-gray-600 font-medium mr-2">Rp</span>
                            <input type="text" name="requested_commission_nominal" id="nominal-field"
                                   value="<?= isset($profile['commission_type']) && $profile['commission_type'] === 'nominal' ? 
                                           number_format($profile['requested_commission_nominal'] ?? 0, 0, ',', '.') : '' ?>"
                                   class="flex-1 border rounded-lg px-3 py-2" <?= (isset($profile['commission_type']) && $profile['commission_type'] === 'nominal') ? 'required' : '' ?>>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Contoh: 200.000, 1.000.000, 10.000.000</p>
                    </div>
                </div>
                
                <div>
                    <label for="vendor_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="vendor_status" name="vendor_status" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        <option value="pending" <?= (isset($profile['status']) && $profile['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="verified" <?= (isset($profile['status']) && $profile['status'] === 'verified') ? 'selected' : '' ?>>Verified</option>
                        <option value="rejected" <?= (isset($profile['status']) && $profile['status'] === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                        <option value="inactive" <?= (isset($profile['status']) && $profile['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end mt-6 space-x-3">
            <button type="button" 
                    id="cancelEditBtn"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                Batal
            </button>
            <button type="submit" 
                    id="submitEditBtn"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Simpan
            </button>
        </div>
    </form>
</div>