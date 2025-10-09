<div class="bg-white rounded-lg max-w-4xl mx-auto p-6">
    <h2 class="text-xl font-semibold mb-4">Tambah Vendor Baru</h2>
    
    <form id="createVendorForm" 
          action="<?= site_url('admin/uservendor/store') ?>" 
          method="post"
          autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="role" value="vendor">
        
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Basic Fields -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username *</label>
                    <input type="text" id="username" name="username" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" 
                           required autocomplete="username new">
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" id="email" name="email" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" 
                           required autocomplete="email new">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                    <div class="relative mt-1">
                        <input type="password" id="password" name="password" 
                               class="block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10" 
                               required minlength="8"
                               placeholder="Minimal 8 karakter"
                               autocomplete="new-password">
                        <button type="button" 
                                onclick="togglePasswordDirect('password', this)"
                                class="password-toggle-btn absolute inset-y-0 right-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-10"
                                title="Tampilkan password">
                            <i class="fa-regular fa-eye text-sm"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
                </div>
                
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Konfirmasi Password *</label>
                    <div class="relative mt-1">
                        <input type="password" id="password_confirm" name="password_confirm" 
                               class="block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10" 
                               required
                               placeholder="Ulangi password"
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
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" 
                           required autocomplete="organization">
                </div>
                
                <div>
                    <label for="owner_name" class="block text-sm font-medium text-gray-700">Nama Pemilik *</label>
                    <input type="text" id="owner_name" name="owner_name" 
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" 
                           required autocomplete="name">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon *</label>
                    <input type="text" id="phone" name="phone" 
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 phone-input" 
                        required autocomplete="tel"
                        inputmode="numeric"
                        pattern="[0-9]*">
                </div>

                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">No. WhatsApp *</label>
                    <input type="text" id="whatsapp_number" name="whatsapp_number" 
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
                                   checked>
                            <span class="ml-2 text-sm font-medium">Persen (%)</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="commission_type" value="nominal" class="form-radio text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium">Nominal (Rp)</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <!-- Input Persentase -->
                    <div id="percent-input" class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ajukan komisi (%) <span class="text-red-500">*</span></label>
                        <div class="flex items-center">
                            <input type="number" name="requested_commission" min="1" max="100" step="0.1"
                                   value=""
                                   class="flex-1 border rounded-lg px-3 py-2 mr-2" required>
                            <span class="text-gray-600 font-medium">%</span>
                        </div>
                    </div>

                    <!-- Input Nominal -->
                    <div id="nominal-input" class="col-span-1" style="display:none">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ajukan komisi (Rp) <span class="text-red-500">*</span></label>
                        <div class="flex items-center">
                            <span class="text-gray-600 font-medium mr-2">Rp</span>
                            <input type="text" name="requested_commission_nominal" id="nominal-field"
                                   value=""
                                   class="flex-1 border rounded-lg px-3 py-2" required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Contoh: 200.000, 1.000.000, 10.000.000</p>
                    </div>
                </div>
                
                <div>
                    <label for="vendor_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="vendor_status" name="vendor_status" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
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
                    id="cancelBtn"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                Batal
            </button>
            <button type="submit" 
                    id="submitBtn"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Simpan
            </button>
        </div>
    </form>
</div>