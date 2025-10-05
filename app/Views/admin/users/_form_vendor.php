<form id="createVendorForm" action="<?= site_url('admin/users/store') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="role" value="vendor">
    
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
        </div>
        
        <div>
            <label for="business_name" class="block text-sm font-medium text-gray-700">Nama Vendor</label>
            <input type="text" id="business_name" name="business_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
        </div>
        
        <div>
            <label for="owner_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input type="text" id="owner_name" name="owner_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
        </div>
        
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
            <input type="text" id="phone" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        </div>
        
        <div>
            <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">No. WhatsApp</label>
            <input type="text" id="whatsapp_number" name="whatsapp_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
        </div>
        
        <div>
            <label for="commission_type" class="block text-sm font-medium text-gray-700">Tipe Komisi</label>
            <select id="commission_type" name="commission_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="percent">Persentase (%)</option>
                <option value="nominal" selected>Nominal (Rp)</option>
            </select>
        </div>
        
        <div>
            <label for="requested_commission_nominal" class="block text-sm font-medium text-gray-700">Komisi yang Diajukan</label>
            <input type="number" id="requested_commission_nominal" name="requested_commission_nominal" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
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
    
    <div class="flex justify-end mt-6">
        <button type="button" onclick="closeModal()" class="mr-2 px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Batal</button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Simpan</button>
    </div>
</form>