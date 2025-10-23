<?php
// File: app/Views/admin/Notifications/modal.php
?>

<!-- 🔔 Modal Notifikasi -->
<div x-show="notifModal"
     x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
     @click.self="notifModal=false; $store.ui.modal=null"
     style="display:none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4"
       @click.stop
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-1">

    <!-- Header -->
    <div class="flex items-center justify-between px-3 py-2 border-b">
      <h3 class="text-base font-semibold text-gray-900">Notifikasi</h3>
      <button type="button"
              @click.prevent.stop="notifModal=false; $store.ui.modal=null"
              class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times text-sm"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="max-h-80 overflow-y-auto divide-y" id="notifications-container">
      <div class="p-3 text-center">
        <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
        <p class="mt-1 text-xs text-gray-500">Memuat notifikasi...</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-3 py-2 border-t flex justify-between items-center">
      <button type="button" 
              onclick="markAllAsRead()"
              class="px-2 py-1 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">
        Tandai Semua Dibaca
      </button>
      <button type="button" 
              onclick="deleteAllNotifications()"
              class="px-2 py-1 rounded bg-red-600 text-white text-xs hover:bg-red-700">
        Hapus Semua
      </button>
    </div>
  </div>
</div>

<!-- Include SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<script>
// Load notifications when modal is opened
document.addEventListener('DOMContentLoaded', function() {
    const notifModal = document.querySelector('[x-show="notifModal"]');
    if (notifModal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const isVisible = notifModal.style.display !== 'none';
                    if (isVisible) {
                        // Refresh CSRF token saat modal dibuka
                        refreshCSRF().then(() => {
                            loadNotifications();
                            // Update badge notifikasi saat modal dibuka
                            updateNotifBadge();
                        }).catch(error => {
                            console.error('Error refreshing CSRF token:', error);
                            loadNotifications();
                            updateNotifBadge();
                        });
                    }
                }
            });
        });
        
        observer.observe(notifModal, { attributes: true });
    }
});

// Fungsi untuk refresh CSRF token
function refreshCSRF() {
    return fetch('<?= site_url('admin/notifications/refreshCSRF') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update meta tag
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
            return data.token;
        }
        throw new Error('Failed to refresh CSRF token');
    })
    .catch(error => {
        console.error('Error refreshing CSRF token:', error);
        throw error;
    });
}

// Refresh CSRF token setiap 15 menit
setInterval(refreshCSRF, 15 * 60 * 1000);

// Fungsi untuk update badge notifikasi
function updateNotifBadge() {
    fetch('<?= site_url('admin/notifications/getUnreadCount') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('notifBadge');
            if (data.unread > 0) {
                if (badge) {
                    badge.textContent = data.unread > 99 ? '99+' : data.unread;
                    badge.style.display = 'flex';
                } else {
                    // Buat badge baru jika tidak ada
                    const notifButton = document.querySelector('button[aria-label="Toggle sidebar"]').nextElementSibling;
                    if (notifButton) {
                        const newBadge = document.createElement('span');
                        newBadge.id = 'notifBadge';
                        newBadge.className = 'absolute -top-0.5 -right-0.5 bg-red-500 text-white rounded-full min-w-[1.25rem] h-5 flex items-center justify-center text-xs font-medium px-1';
                        newBadge.textContent = data.unread > 99 ? '99+' : data.unread;
                        notifButton.appendChild(newBadge);
                    }
                }
            } else {
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        }
    })
    .catch(error => {
        console.error('Error updating notification badge:', error);
    });
}

// Function to load notifications via AJAX
function loadNotifications() {
    const container = document.getElementById('notifications-container');
    
    fetch('<?= site_url('admin/notifications/modal-data') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.notifications) {
            if (data.notifications.length === 0) {
                container.innerHTML = '<div class="p-3 text-center text-xs text-gray-500">Tidak ada notifikasi.</div>';
            } else {
                container.innerHTML = data.notifications.map(n => `
                    <div class="p-3 flex justify-between items-center hover:bg-gray-50" id="notification-${n.id}">
                        <div class="flex-1 pr-3">
                            <p class="text-xs font-semibold text-gray-900">
                                ${n.title || '-'}
                                ${!n.is_read ? '<span class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Baru</span>' : ''}
                            </p>
                            <p class="text-xs text-gray-600 mt-0.5">${n.message || '-'}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                ${n.date || '-'}
                            </p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            ${!n.is_read ? `<button type="button" onclick="markAsRead(${n.id})" class="text-xs text-blue-600 hover:underline">Dibaca</button>` : ''}
                            <button type="button" onclick="deleteNotification(${n.id})" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </div>
                    </div>
                `).join('');
            }
            // Update badge notifikasi setelah memuat
            updateNotifBadge();
        } else {
            container.innerHTML = '<div class="p-3 text-center text-xs text-red-500">Gagal memuat notifikasi.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = '<div class="p-3 text-center text-xs text-red-500">Terjadi kesalahan saat memuat notifikasi.</div>';
    });
}

// Fungsi untuk menandai notifikasi sebagai dibaca
function markAsRead(id) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('<?= site_url('admin/notifications/markRead/') ?>' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': token
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update CSRF token dari respons
            if (data.csrf_token) {
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
            }
            
            // Hapus badge "Baru" jika ada
            const notification = document.getElementById('notification-' + id);
            const badge = notification.querySelector('.bg-yellow-100');
            if (badge) {
                badge.remove();
            }
            
            // Sembunyikan tombol "Tandai Dibaca"
            const markReadBtn = notification.querySelector('button[onclick^="markAsRead"]');
            if (markReadBtn) {
                markReadBtn.style.display = 'none';
            }
            
            // Tampilkan pesan sukses dengan SweetAlert
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: 'success',
                title: 'Dibaca'
            });
            
            // Update badge notifikasi
            updateNotifBadge();
        } else {
            // Tampilkan pesan error dengan SweetAlert
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: 'error',
                title: 'Gagal'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Tampilkan pesan error dengan SweetAlert
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        Toast.fire({
            icon: 'error',
            title: 'Error'
        });
    });
}

// Fungsi untuk menghapus notifikasi
function deleteNotification(id) {
    // Tampilkan konfirmasi dengan SweetAlert
    Swal.fire({
        title: 'Hapus Notifikasi',
        text: "Apakah Anda yakin ingin menghapus notifikasi ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'small-swal'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('<?= site_url('admin/notifications/delete/') ?>' + id, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update CSRF token dari respons
                    if (data.csrf_token) {
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
                    }
                    
                    // Hapus elemen notifikasi dari DOM dengan animasi
                    const notification = document.getElementById('notification-' + id);
                    if (notification) {
                        notification.style.transition = 'all 0.3s ease';
                        notification.style.opacity = '0';
                        notification.style.transform = 'translateX(20px)';
                        
                        setTimeout(() => {
                            notification.remove();
                            
                            // Periksa apakah masih ada notifikasi
                            const container = document.getElementById('notifications-container');
                            if (container && container.children.length === 0) {
                                container.innerHTML = '<div class="p-3 text-center text-xs text-gray-500">Tidak ada notifikasi.</div>';
                            }
                        }, 300);
                    }
                    
                    // Tampilkan pesan sukses dengan SweetAlert
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: 'success',
                        title: 'Dihapus'
                    });
                    
                    // Update badge notifikasi
                    updateNotifBadge();
                    
                    // Muat ulang data notifikasi setelah toast selesai
                    setTimeout(() => {
                        loadNotifications();
                    }, 1600);
                } else {
                    // Tampilkan pesan error dengan SweetAlert
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Gagal'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Tampilkan pesan error dengan SweetAlert
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                
                Toast.fire({
                    icon: 'error',
                    title: 'Error'
                });
            });
        }
    });
}

// Fungsi untuk menandai semua notifikasi sebagai dibaca
function markAllAsRead() {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('<?= site_url('admin/notifications/markAllRead') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': token
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update CSRF token dari respons
            if (data.csrf_token) {
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
            }
            
            // Hapus semua badge "Baru"
            document.querySelectorAll('.bg-yellow-100').forEach(badge => {
                badge.remove();
            });
            
            // Sembunyikan semua tombol "Tandai Dibaca"
            document.querySelectorAll('button[onclick^="markAsRead"]').forEach(btn => {
                btn.style.display = 'none';
            });
            
            // Tampilkan pesan sukses dengan SweetAlert
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: 'success',
                title: 'Semua dibaca'
            });
            
            // Update badge notifikasi
            updateNotifBadge();
        } else {
            // Tampilkan pesan error dengan SweetAlert
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: 'error',
                title: 'Gagal'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Tampilkan pesan error dengan SweetAlert
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        Toast.fire({
            icon: 'error',
            title: 'Error'
        });
    });
}

// Fungsi untuk menghapus semua notifikasi
function deleteAllNotifications() {
    // Tampilkan konfirmasi dengan SweetAlert
    Swal.fire({
        title: 'Hapus Semua Notifikasi',
        text: "Apakah Anda yakin ingin menghapus semua notifikasi?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus semua!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'small-swal'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('<?= site_url('admin/notifications/deleteAll') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update CSRF token dari respons
                    if (data.csrf_token) {
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf_token);
                    }
                    
                    // Hapus semua elemen notifikasi dari DOM dengan animasi
                    const container = document.getElementById('notifications-container');
                    const notifications = container.querySelectorAll('[id^="notification-"]');
                    
                    if (notifications.length > 0) {
                        notifications.forEach((notification, index) => {
                            setTimeout(() => {
                                notification.style.transition = 'all 0.3s ease';
                                notification.style.opacity = '0';
                                notification.style.transform = 'translateX(20px)';
                                
                                setTimeout(() => {
                                    notification.remove();
                                    
                                    // Jika ini adalah notifikasi terakhir
                                    if (index === notifications.length - 1) {
                                        container.innerHTML = '<div class="p-3 text-center text-xs text-gray-500">Tidak ada notifikasi.</div>';
                                    }
                                }, 300);
                            }, index * 50); // Staggered animation
                        });
                    } else {
                        container.innerHTML = '<div class="p-3 text-center text-xs text-gray-500">Tidak ada notifikasi.</div>';
                    }
                    
                    // Tampilkan pesan sukses dengan SweetAlert
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: 'success',
                        title: 'Semua dihapus'
                    });
                    
                    // Update badge notifikasi
                    updateNotifBadge();
                    
                    // Muat ulang data notifikasi setelah toast selesai
                    setTimeout(() => {
                        loadNotifications();
                    }, 1600);
                } else {
                    // Tampilkan pesan error dengan SweetAlert
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Gagal'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Tampilkan pesan error dengan SweetAlert
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                
                Toast.fire({
                    icon: 'error',
                    title: 'Error'
                });
            });
        }
    });
}

// Include SweetAlert2 JS
document.addEventListener('DOMContentLoaded', function() {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    script.async = true;
    document.head.appendChild(script);
    
    // Tambahkan CSS untuk SweetAlert yang lebih kecil
    const style = document.createElement('style');
    style.textContent = `
        .small-swal {
            width: 300px !important;
            padding: 0.5rem !important;
        }
        .small-swal .swal2-title {
            font-size: 1rem !important;
        }
        .small-swal .swal2-content {
            font-size: 0.875rem !important;
        }
        .small-swal .swal2-actions {
            margin-top: 0.5rem !important;
        }
        .small-swal .swal2-styled {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.875rem !important;
        }
        
        /* Style untuk toast notifikasi yang lebih kecil */
        .swal2-toast {
            width: 280px !important;
            padding: 0.5rem !important;
        }
        .swal2-toast .swal2-title {
            font-size: 0.875rem !important;
            margin: 0 !important;
        }
        .swal2-toast .swal2-icon {
            width: 1.25rem !important;
            height: 1.25rem !important;
            margin: 0 !important;
        }
        .swal2-toast .swal2-icon.swal2-success .swal2-success-ring {
            width: 1.25rem !important;
            height: 1.25rem !important;
        }
        .swal2-toast .swal2-icon.swal2-success [class^='swal2-success-line'] {
            height: 0.125rem !important;
        }
        .swal2-toast .swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
            height: 0.125rem !important;
        }
        .swal2-toast .swal2-icon.swal2-info [class^='swal2-info-line'] {
            height: 0.125rem !important;
        }
        .swal2-toast .swal2-icon.swal2-warning [class^='swal2-warning-line'] {
            height: 0.125rem !important;
        }
        .swal2-toast .swal2-icon.swal2-question [class^='swal2-question-line'] {
            height: 0.125rem !important;
        }
        .swal2-toast .swal2-progress-steps {
            height: 0.25rem !important;
            margin-top: 0.25rem !important;
        }
    `;
    document.head.appendChild(style);
});
</script>