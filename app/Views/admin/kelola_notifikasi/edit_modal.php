<!-- Edit Notification Modal -->
<div class="bg-white rounded-lg">
    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <form action="<?= base_url('admin/notification-management/update/' . $notification['id']) ?>" method="POST" class="space-y-6">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <input type="hidden" name="id" value="<?= $notification['id'] ?>">
            
            <!-- Judul Notifikasi -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-heading mr-2 text-blue-500"></i>Judul Notifikasi *
                </label>
                <input type="text" name="title" id="title" required maxlength="255"
                       value="<?= esc($notification['title']) ?>"
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
                          placeholder="Tulis pesan notifikasi yang jelas dan informatif..."><?= esc($notification['message']) ?></textarea>
                <div class="flex justify-between text-xs text-gray-500 mt-2">
                    <span>Pesan akan dikirim ke user yang dipilih</span>
                    <span id="charCount"><?= strlen($notification['message']) ?>/1000 karakter</span>
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
                    <option value="announcement" <?= $notification['type'] == 'announcement' ? 'selected' : '' ?>>📢 Announcement - Pengumuman penting</option>
                    <option value="system" <?= $notification['type'] == 'system' ? 'selected' : '' ?>>⚙️ System - Notifikasi sistem</option>
                </select>
                <p class="text-xs text-gray-500 mt-2">Pilih kategori notifikasi sesuai dengan kebutuhan</p>
            </div>

            <!-- Status Baca -->
            <div class="flex items-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                <input type="checkbox" name="is_read" id="is_read" value="1"
                       <?= !empty($notification['is_read']) ? 'checked' : '' ?>
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
                    <i class="fas fa-save mr-2"></i>Update Notifikasi
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
    }
});
</script>