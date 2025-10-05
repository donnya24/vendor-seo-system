<?php
$ap = $ap ?? [];
$profileImage = $ap['profile_image'] ?? '';
$profileOnDisk = $profileImage ? (FCPATH . 'uploads/admin_profiles/' . $profileImage) : '';
$hasProfileImage = ($profileImage && is_file($profileOnDisk));
$profileImagePath = $hasProfileImage 
    ? base_url('uploads/admin_profiles/' . $profileImage)
    : base_url('assets/img/default-avatar.png');
?>

<div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4" id="profileModalBackdrop">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Edit Profil</h3>
            <button type="button" class="text-gray-500 hover:text-gray-700 transition-colors close-modal-btn">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="profileEditForm" action="<?= site_url('admin/profile/update'); ?>" method="post" enctype="multipart/form-data" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <!-- Foto Profil -->
            <div class="flex items-center space-x-4">
                <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 border border-gray-300 relative">
                    <?php if ($hasProfileImage): ?>
                        <!-- Jika ada foto profil, tampilkan gambar -->
                        <img src="<?= $profileImagePath ?>" class="w-full h-full object-cover" alt="Foto Profil" id="profileImagePreview">
                    <?php else: ?>
                        <!-- Jika tidak ada foto profil, tampilkan ikon user -->
                        <div class="w-full h-full flex items-center justify-center bg-gray-300" id="profileImagePreview">
                            <i class="fas fa-user text-gray-500 text-2xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
                    <input type="file" name="profile_image" accept="image/*" class="text-sm text-gray-500 w-full" id="profileImageInput">
                    <div class="mt-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="remove_profile_image" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Hapus foto profil</span>
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?= esc($ap['name'] ?? '') ?>" required
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors"
                       placeholder="Masukkan nama lengkap">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="<?= esc($ap['email'] ?? '') ?>" required
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors"
                       placeholder="Masukkan alamat email">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                <input type="text" name="phone" value="<?= esc($ap['phone'] ?? '') ?>"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors"
                       placeholder="Masukkan nomor telepon">
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors font-medium cancel-modal-btn">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Function untuk menutup modal
function closeProfileModal() {
    const modalBackdrop = document.getElementById('profileModalBackdrop');
    if (modalBackdrop) {
        modalBackdrop.remove();
    }
}

// Initialize modal functionality setelah modal di-load
function initProfileModal() {
    // Preview image saat memilih file
    const profileImageInput = document.getElementById('profileImageInput');
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profileImagePreview');
                    if (preview) {
                        // Ganti konten preview dengan gambar baru
                        // Hapus semua konten sebelumnya (baik itu img atau div dengan ikon)
                        preview.innerHTML = '';
                        
                        // Buat elemen gambar baru
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-full h-full object-cover';
                        img.alt = 'Foto Profil';
                        
                        // Tambahkan gambar ke preview
                        preview.appendChild(img);
                        
                        // Pastikan container preview memiliki class yang tepat untuk gambar
                        preview.classList.remove('flex', 'items-center', 'justify-center', 'bg-gray-300');
                        preview.classList.add('w-full', 'h-full', 'object-cover');
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle checkbox hapus foto profil
    const removeCheckbox = document.querySelector('input[name="remove_profile_image"]');
    if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function(e) {
            const preview = document.getElementById('profileImagePreview');
            if (preview) {
                if (this.checked) {
                    // Tampilkan ikon user ketika hapus foto dipilih
                    preview.innerHTML = '<i class="fas fa-user text-gray-500 text-2xl"></i>';
                    preview.className = 'w-full h-full flex items-center justify-center bg-gray-300';
                } else {
                    // Kembalikan ke gambar sebelumnya jika checkbox dicentang ulang
                    const profileImageInput = document.getElementById('profileImageInput');
                    if (profileImageInput && profileImageInput.files.length > 0) {
                        // Jika ada file yang dipilih, tampilkan preview file tersebut
                        const file = profileImageInput.files[0];
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = '';
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'w-full h-full object-cover';
                            img.alt = 'Foto Profil';
                            preview.appendChild(img);
                            preview.classList.remove('flex', 'items-center', 'justify-center', 'bg-gray-300');
                            preview.classList.add('w-full', 'h-full', 'object-cover');
                        }
                        reader.readAsDataURL(file);
                    } else {
                        // Jika tidak ada file yang dipilih, kembalikan ke gambar default
                        const currentImg = preview.querySelector('img');
                        if (!currentImg || !currentImg.src.includes('data:')) {
                            // Hanya reset jika gambar bukan dari upload baru
                            preview.innerHTML = '<i class="fas fa-user text-gray-500 text-2xl"></i>';
                            preview.className = 'w-full h-full flex items-center justify-center bg-gray-300';
                        }
                    }
                }
            }
        });
    }

    // Handle form submission
    const profileEditForm = document.getElementById('profileEditForm');
    if (profileEditForm) {
        profileEditForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            
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
                        confirmButtonText: 'OK',
                        timer: 2000
                    });
                    
                    // Close modal
                    closeProfileModal();
                    
                    // Refresh page to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                    
                } else {
                    let errorMessage = 'Terjadi kesalahan';
                    if (result.errors) {
                        errorMessage = Object.values(result.errors).join('<br>');
                    } else if (result.message) {
                        errorMessage = result.message;
                    }
                    
                    await Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                    confirmButtonText: 'OK'
                });
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Close modal handlers
    const closeButtons = document.querySelectorAll('.close-modal-btn, .cancel-modal-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', closeProfileModal);
    });

    // Close modal ketika klik di luar modal content
    const modalBackdrop = document.getElementById('profileModalBackdrop');
    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfileModal();
            }
        });
    }

    // Juga handle ESC key
    const handleEsc = function(e) {
        if (e.key === 'Escape') {
            closeProfileModal();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);
}

// Panggil init function ketika modal selesai di-load
document.addEventListener('DOMContentLoaded', function() {
    // Jika modal sudah ada di DOM, initialize
    if (document.getElementById('profileModalBackdrop')) {
        initProfileModal();
    }
});

// Juga panggil init setelah modal di-load via AJAX
initProfileModal();
</script>