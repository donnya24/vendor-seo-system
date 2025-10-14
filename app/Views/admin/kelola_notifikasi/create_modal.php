<div class="bg-white rounded-lg">
    <!-- Form Content -->
    <div class="p-6">
        <form action="<?= base_url('admin/kelola-notifikasi/store') ?>" method="POST" class="space-y-6">
            <?= csrf_field() ?>
            
            <!-- Penerima -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-2 text-blue-500"></i>Penerima *
                </label>
                <select name="user_id" id="user_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">Pilih User Penerima</option>
                    
                    <!-- Group: SEO Profiles -->
                    <?php if (!empty($seoProfiles)): ?>
                    <optgroup label="👨‍💼 Tim SEO">
                        <?php foreach ($seoProfiles as $seo): 
                            $user = $userModel->find($seo['user_id']);
                        ?>
                            <option value="<?= $seo['user_id'] ?>">
                                [SEO-<?= $seo['id'] ?>] <?= esc($seo['name']) ?> 
                                - <?= esc($user->username ?? 'User') ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                    
                    <!-- Group: Vendor Profiles -->
                    <?php if (!empty($vendorProfiles)): ?>
                    <optgroup label="🏢 Vendor">
                        <?php foreach ($vendorProfiles as $vendor): 
                            $user = $userModel->find($vendor['user_id']);
                        ?>
                            <option value="<?= $vendor['user_id'] ?>">
                                [VENDOR-<?= $vendor['id'] ?>] <?= esc($vendor['business_name']) ?> 
                                (<?= esc($vendor['owner_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                </select>
                <p class="text-xs text-gray-500 mt-2">Pilih penerima notifikasi dari daftar yang tersedia</p>
            </div>

            <!-- Judul Notifikasi -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-heading mr-2 text-blue-500"></i>Judul Notifikasi *
                </label>
                <input type="text" name="title" id="title" required maxlength="255"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                       placeholder="Contoh: Pembaruan Komisi, Pengumuman Sistem, dll.">
            </div>

            <!-- Pesan Notifikasi -->
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-blue-500"></i>Pesan Notifikasi *
                </label>
                <textarea name="message" id="message" required maxlength="1000" rows="5"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors resize-none"
                          placeholder="Tulis pesan notifikasi yang jelas dan informatif..."></textarea>
                <div class="flex justify-between text-xs text-gray-500 mt-2">
                    <span>Pesan akan dikirim ke user yang dipilih</span>
                    <span id="charCount">0/1000 karakter</span>
                </div>
            </div>

            <!-- Tipe Notifikasi -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag mr-2 text-blue-500"></i>Tipe Notifikasi *
                </label>
                <select name="type" id="type" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <option value="">Pilih Tipe Notifikasi</option>
                    <option value="commission">💰 Commission - Notifikasi terkait komisi</option>
                    <option value="announcement">📢 Announcement - Pengumuman penting</option>
                    <option value="system">⚙️ System - Notifikasi sistem</option>
                </select>
                <p class="text-xs text-gray-500 mt-2">Pilih kategori notifikasi sesuai dengan kebutuhan</p>
            </div>

            <!-- Status Baca -->
            <div class="flex items-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                <input type="checkbox" name="is_read" id="is_read" value="1"
                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors">
                <label for="is_read" class="ml-3 block text-sm font-medium text-gray-700">
                    📍 Tandai sebagai sudah dibaca
                </label>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeModal()" 
                        class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="submit" 
                        class="px-6 py-3 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 shadow-sm">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Notifikasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Character counter untuk textarea
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    
    if (messageTextarea && charCount) {
        messageTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length}/1000 karakter`;
            
            if (length > 1000) {
                charCount.classList.add('text-red-600', 'font-semibold');
                messageTextarea.classList.add('border-red-300');
            } else {
                charCount.classList.remove('text-red-600', 'font-semibold');
                messageTextarea.classList.remove('border-red-300');
            }
        });
        
        // Trigger initial count
        messageTextarea.dispatchEvent(new Event('input'));
    }
});
</script>