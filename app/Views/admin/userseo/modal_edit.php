<?php
// File: app/Views/admin/userseo/modal_edit.php
?>

<div class="bg-white rounded-lg max-w-2xl mx-auto p-6">
    <h2 class="text-xl font-semibold mb-4">Edit Tim SEO</h2>
    
    <form id="editSeoForm" 
          action="<?= site_url('admin/userseo/update/' . ($user['id'] ?? '')) ?>" 
          method="post">
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
                    <!-- Error container will be added dynamically by JAavaScript -->
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
                    <!-- Error container will be added dynamically by JavaScript -->
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
                        <div class="relative">
                            <input type="password" 
                                   id="edit_password" 
                                   name="password" 
                                   value=""
                                   placeholder="Kosongkan jika tidak ingin mengubah"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" 
                                    onclick="togglePasswordDirect('edit_password', this)"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-10">
                                <i class="fa-regular fa-eye text-sm"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
                        <!-- Error container will be added dynamically by JavaScript -->
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <div class="relative">
                            <input type="password" 
                                   id="edit_password_confirm" 
                                   name="password_confirm" 
                                   value=""
                                   placeholder="Kosongkan jika tidak ingin mengubah"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" 
                                    onclick="togglePasswordDirect('edit_password_confirm', this)"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center justify-center text-gray-400 hover:text-gray-600 focus:outline-none z-10">
                                <i class="fa-regular fa-eye text-sm"></i>
                            </button>
                        </div>
                        <!-- Error container will be added dynamically by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end mt-6 space-x-3">
            <button type="button" 
                    onclick="closeModal('editUserModal')"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Batal
            </button>
            <button type="submit" 
                    id="submitEditBtn"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                Update
            </button>
        </div>
    </form>
</div>

<script>
// Event listener untuk form submission - GUNAKAN FUNGSI GLOBAL
document.getElementById('editSeoForm').addEventListener('submit', function(event) {
    event.preventDefault();
    submitSeoForm(event.target, true); // true = edit
});
</script>