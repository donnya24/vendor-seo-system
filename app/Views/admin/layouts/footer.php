        </div> <!-- Tutup content-area -->
    </div> <!-- Tutup main-content-container -->
</div> <!-- Tutup layout container -->

<!-- Overlay untuk mobile ketika sidebar terbuka -->
<div
    class="sidebar-overlay fixed inset-0 bg-black/40 md:hidden z-30"
    x-show="$store.ui.sidebar"
    x-transition.opacity
    @click="$store.ui.sidebar = false"
    x-cloak
></div>

<!-- Modal Logout dengan Alpine.js -->
<div x-data="{ showLogoutModal: false }">
    <div x-show="showLogoutModal" 
         x-transition.opacity 
         x-cloak 
         class="fixed inset-0 z-50">
        <div class="min-h-screen flex items-center justify-center p-4 no-scrollbar">
            
            <!-- backdrop -->
            <div class="fixed inset-0 bg-black/40" @click="showLogoutModal = false"></div>

            <!-- card -->
            <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center">
                    <i class="fa-solid fa-right-from-bracket text-2xl"></i>
                </div>

                <h3 class="mt-4 text-center text-xl font-semibold text-gray-900">Keluar dari Sistem?</h3>
                <p class="mt-2 text-center text-sm text-gray-500">Anda akan keluar dari sesi saat ini.</p>

                <!-- FORM POST logout -->
                <form action="<?= site_url('logout'); ?>" method="post" 
                      class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <?= csrf_field() ?>
                    <button type="button"
                            class="w-full sm:w-auto px-4 py-3 sm:py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                            @click="showLogoutModal = false">
                        Batal
                    </button>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-3 sm:py-2 rounded-md bg-red-600 text-white hover:bg-red-700">
                        Ya, Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Profile Dropdown Mobile -->
<div x-data="{ profileDropdownOpen: false }">
    <div x-show="profileDropdownOpen" x-cloak class="fixed inset-0 z-40 md:hidden" @click="profileDropdownOpen = false">
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-lg p-4 no-scrollbar" @click.stop>
            <div class="flex items-center space-x-3 mb-4">
                <?php if (!empty($profileImage) && is_file($profileOnDisk)): ?>
                    <img class="h-10 w-10 rounded-full object-cover" src="<?= $profileImagePath ?>" alt="Admin">
                <?php else: ?>
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <p class="font-medium"><?= esc($ap['name'] ?? $user->username ?? 'Admin') ?></p>
                    <p class="text-sm text-gray-500"><?= service('auth')->user()->username ?? 'Admin' ?></p>
                </div>
            </div>
            <div class="space-y-2">
                <a href="javascript:void(0)" onclick="openEditProfileModal()" class="block py-2 px-4 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-user-edit w-4 mr-3 text-gray-500"></i> Profil Saya
                </a>
                <a href="javascript:void(0)" onclick="openPasswordModal()" class="block py-2 px-4 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-lock w-4 mr-3 text-gray-500"></i> Ubah Password
                </a>
                <button @click="showLogoutModal = true; profileDropdownOpen = false" class="w-full text-left py-2 px-4 rounded-lg hover:bg-gray-100 text-red-600">
                    <i class="fas fa-sign-out-alt w-4 mr-3"></i> Logout
                </button>
            </div>
            <button @click="profileDropdownOpen = false" class="w-full mt-4 py-2 text-center text-gray-500">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
// === SweetAlert Mini Configuration ===
const swalConfig = {
    width: '320px',
    padding: '1.25rem',
    customClass: {
        popup: 'rounded-xl shadow-md',
        title: 'text-base font-semibold mb-2',
        htmlContainer: 'text-sm mb-3',
        confirmButton: 'px-4 py-2 text-sm rounded-lg',
        icon: 'text-lg mb-2'
    }
};

// === Function untuk membuka modal edit profile ===
async function openEditProfileModal() {
    try {
        const loadingSwal = Swal.fire({
            title: 'Memuat...',
            text: 'Sedang memuat form edit profil',
            allowOutsideClick: false,
            ...swalConfig,
            didOpen: () => Swal.showLoading()
        });

        const response = await fetch('<?= site_url('admin/profile/edit-modal') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const result = await response.json();
        await loadingSwal.close();
        
        if (result.status === 'success') {
            closeAllModals();
            document.body.insertAdjacentHTML('beforeend', result.data.html);
            initProfileModal();
        } else {
            throw new Error('Gagal memuat modal');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Tidak dapat memuat form edit profil',
            ...swalConfig
        });
    }
}

// === Function untuk membuka modal ubah password ===
async function openPasswordModal() {
    try {
        const loadingSwal = Swal.fire({
            title: 'Memuat...',
            text: 'Sedang memuat form ubah password',
            allowOutsideClick: false,
            ...swalConfig,
            didOpen: () => Swal.showLoading()
        });

        const response = await fetch('<?= site_url('admin/profile/password-modal') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const result = await response.json();
        await loadingSwal.close();
        
        if (result.status === 'success') {
            closeAllModals();
            document.body.insertAdjacentHTML('beforeend', result.data.html);
            initProfileModal();
        } else {
            throw new Error('Gagal memuat modal');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Tidak dapat memuat form ubah password',
            ...swalConfig
        });
    }
}

// === Function untuk menutup semua modal ===
function closeAllModals() {
    const modals = document.querySelectorAll('#profileModalBackdrop');
    modals.forEach(modal => {
        modal.remove();
    });
}

// === Function untuk menutup modal spesifik ===
function closeProfileModal() {
    closeAllModals();
}

// === Inisialisasi Modal ===
function initProfileModal() {
    console.log('Initializing profile modal...');
    const modalBackdrop = document.getElementById('profileModalBackdrop');
    if (!modalBackdrop) {
        console.error('Modal backdrop not found');
        return;
    }

    // Function untuk menutup modal dengan promise
    const closeModal = function() {
        return new Promise((resolve) => {
            console.log('Closing modal...');
            const backdrop = document.getElementById('profileModalBackdrop');
            if (backdrop) {
                backdrop.remove();
            }
            // Beri waktu untuk DOM update
            setTimeout(resolve, 50);
        });
    };

    // Tombol close (X)
    modalBackdrop.querySelectorAll('.close-modal-btn').forEach(btn => {
        // Hapus event listener lama dan tambah yang baru
        btn.onclick = closeModal;
    });

    // Tombol batal
    modalBackdrop.querySelectorAll('.cancel-modal-btn').forEach(btn => {
        btn.onclick = closeModal;
    });

    // Klik backdrop
    modalBackdrop.onclick = function(e) {
        if (e.target === this) {
            closeModal();
        }
    };

    // Tombol ESC
    const handleEsc = function(e) {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);

    // === Handle form edit profil ===
    const profileEditForm = document.getElementById('profileEditForm');
    if (profileEditForm) {
        profileEditForm.onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const result = await response.json();

                // TUTUP MODAL TERLEBIH DAHULU sebelum show alert
                await closeModal();

                if (result.status === 'success') {
                    // Tunggu sebentar untuk memastikan modal sudah tertutup
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: result.message,
                            confirmButtonText: 'OK',
                            timer: 3000,
                            ...swalConfig
                        }).then(() => {
                            // Refresh page untuk edit profile
                            window.location.reload();
                        });
                    }, 100);
                } else {
                    let errorMessage = result.errors 
                        ? Object.values(result.errors).join('<br>') 
                        : (result.message || 'Terjadi kesalahan');
                    
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: errorMessage,
                            confirmButtonText: 'OK',
                            ...swalConfig
                        });
                    }, 100);
                }
            } catch (err) {
                console.error(err);
                await closeModal();
                
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                        confirmButtonText: 'OK',
                        ...swalConfig
                    });
                }, 100);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        };
    }

    // === Handle form ubah password ===
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const result = await response.json();

                // TUTUP MODAL TERLEBIH DAHULU sebelum show alert
                await closeModal();

                if (result.status === 'success') {
                    // CEK APAKAH PERLU LOGOUT OTOMATIS
                    if (result.logout_redirect) {
                        // Tampilkan pesan sukses dengan countdown
                        let timeLeft = 3;
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Berhasil Diubah',
                            html: `Password Anda berhasil diperbarui. <br><strong>Anda akan logout otomatis dalam <span id="countdown">${timeLeft}</span> detik...</strong>`,
                            showConfirmButton: false,
                            timer: timeLeft * 1000,
                            ...swalConfig,
                            didOpen: () => {
                                const timer = document.getElementById('countdown');
                                const timerInterval = setInterval(() => {
                                    timeLeft--;
                                    if (timer) timer.textContent = timeLeft;
                                    
                                    if (timeLeft <= 0) {
                                        clearInterval(timerInterval);
                                    }
                                }, 1000);
                            }
                        }).then(() => {
                            // Redirect ke logout setelah timer habis
                            window.location.href = result.redirect_url || '<?= site_url('logout') ?>';
                        });
                    } else {
                        // Jika tidak perlu logout, tampilkan pesan biasa
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: result.message,
                                confirmButtonText: 'OK',
                                timer: 3000,
                                ...swalConfig
                            });
                        }, 100);
                    }
                } else {
                    let errorMessage = result.errors 
                        ? Object.values(result.errors).join('<br>') 
                        : (result.message || 'Terjadi kesalahan');
                    
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: errorMessage,
                            confirmButtonText: 'OK',
                            ...swalConfig
                        });
                    }, 100);
                }
            } catch (err) {
                console.error(err);
                await closeModal();
                
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                        confirmButtonText: 'OK',
                        ...swalConfig
                    });
                }, 100);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        };
    }

    // === Preview gambar ===
    const profileImageInput = document.getElementById('profileImageInput');
    if (profileImageInput) {
        profileImageInput.onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profileImagePreview');
                    if (preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
    }

    console.log('Profile modal initialized successfully');
}

// === Handle Alpine store untuk modal management ===
document.addEventListener('alpine:initialized', () => {
    // Pastikan Alpine store tersedia untuk modal management
    if (window.Alpine && Alpine.store('ui')) {
        const uiStore = Alpine.store('ui');
        
        // Override modal close behavior jika diperlukan
        const originalCloseModal = uiStore.closeModal;
        uiStore.closeModal = function() {
            closeAllModals();
            if (originalCloseModal) {
                originalCloseModal.call(this);
            }
        };
    }
});

// Debug saat DOM ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, modal functions ready');
});

// === Mobile Profile Dropdown Handler ===
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan event listener untuk tombol profile di mobile jika ada
    const mobileProfileButton = document.querySelector('[x-data*="profileDropdownOpen"]');
    if (!mobileProfileButton) {
        // Jika tidak ada, kita bisa tambahkan secara manual
        const profileButtons = document.querySelectorAll('button, a');
        profileButtons.forEach(btn => {
            if (btn.textContent.includes('Profil') || btn.querySelector('img, .fa-user')) {
                btn.addEventListener('click', function(e) {
                    // Cari atau buka dropdown profile mobile
                    const dropdown = document.querySelector('[x-data*="profileDropdownOpen"]');
                    if (dropdown) {
                        dropdown.__x.$data.profileDropdownOpen = true;
                    }
                });
            }
        });
    }
});
</script>

</body>
</html>