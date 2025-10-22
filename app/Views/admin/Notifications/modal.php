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

  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4"
       @click.stop
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-1">

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b">
      <h3 class="text-lg font-semibold text-gray-900">Semua Notifikasi</h3>
      <button type="button"
              @click.prevent.stop="notifModal=false; $store.ui.modal=null"
              class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="max-h-96 overflow-y-auto divide-y" id="notifications-container">
      <div class="p-4 text-center">
        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
        <p class="mt-2 text-sm text-gray-500">Memuat notifikasi...</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-4 py-3 border-t flex justify-between items-center">
      <button type="button" 
              onclick="markAllAsRead()"
              class="px-3 py-1 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
        Tandai Semua Dibaca
      </button>
      <button type="button" 
              onclick="deleteAllNotifications()"
              class="px-3 py-1 rounded bg-red-600 text-white text-sm hover:bg-red-700">
        Hapus Semua
      </button>
    </div>
  </div>
</div>

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
                        loadNotifications();
                        // Update badge notifikasi saat modal dibuka
                        updateNotifBadge();
                    }
                }
            });
        });
        
        observer.observe(notifModal, { attributes: true });
    }
});

// Fungsi untuk refresh CSRF token
function refreshCSRF() {
    fetch('<?= site_url('admin/notifications/refreshCSRF') ?>', {
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
        }
    })
    .catch(error => {
        console.error('Error refreshing CSRF token:', error);
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
                container.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>';
            } else {
                container.innerHTML = data.notifications.map(n => `
                    <div class="p-4 flex justify-between items-center hover:bg-gray-50" id="notification-${n.id}">
                        <div class="flex-1 pr-4">
                            <p class="text-sm font-semibold text-gray-900">
                                ${n.title || '-'}
                                ${!n.is_read ? '<span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Baru</span>' : ''}
                            </p>
                            <p class="text-xs text-gray-600">${n.message || '-'}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                ${n.date || '-'}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            ${!n.is_read ? `<button type="button" onclick="markAsRead(${n.id})" class="text-xs text-blue-600 hover:underline">Tandai Dibaca</button>` : ''}
                            <button type="button" onclick="deleteNotification(${n.id})" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </div>
                    </div>
                `).join('');
            }
            // Update badge notifikasi setelah memuat
            updateNotifBadge();
        } else {
            container.innerHTML = '<div class="p-4 text-center text-sm text-red-500">Gagal memuat notifikasi.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = '<div class="p-4 text-center text-sm text-red-500">Terjadi kesalahan saat memuat notifikasi.</div>';
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
            
            // Tampilkan pesan sukses
            showToast('Notifikasi ditandai sebagai dibaca', 'success');
            
            // Update badge notifikasi
            updateNotifBadge();
        } else {
            showToast(data.message || 'Gagal menandai notifikasi sebagai dibaca', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
    });
}

// Fungsi untuk menghapus notifikasi
function deleteNotification(id) {
    if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
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
                // Hapus elemen notifikasi dari DOM
                const notification = document.getElementById('notification-' + id);
                if (notification) {
                    notification.remove();
                }
                
                // Periksa apakah masih ada notifikasi
                const container = document.getElementById('notifications-container');
                if (container && container.children.length === 0) {
                    container.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>';
                }
                
                // Tampilkan pesan sukses
                showToast('Notifikasi berhasil dihapus', 'success');
                
                // Update badge notifikasi
                updateNotifBadge();
            } else {
                showToast(data.message || 'Gagal menghapus notifikasi', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
        });
    }
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
            // Hapus semua badge "Baru"
            document.querySelectorAll('.bg-yellow-100').forEach(badge => {
                badge.remove();
            });
            
            // Sembunyikan semua tombol "Tandai Dibaca"
            document.querySelectorAll('button[onclick^="markAsRead"]').forEach(btn => {
                btn.style.display = 'none';
            });
            
            // Tampilkan pesan sukses
            showToast('Semua notifikasi ditandai sebagai dibaca', 'success');
            
            // Update badge notifikasi
            updateNotifBadge();
        } else {
            showToast(data.message || 'Gagal menandai semua notifikasi sebagai dibaca', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
    });
}

// Fungsi untuk menghapus semua notifikasi
function deleteAllNotifications() {
    if (confirm('Apakah Anda yakin ingin menghapus semua notifikasi?')) {
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
                // Hapus semua elemen notifikasi dari DOM
                const container = document.getElementById('notifications-container');
                if (container) {
                    container.innerHTML = '<div class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>';
                }
                
                // Tampilkan pesan sukses
                showToast('Semua notifikasi berhasil dihapus', 'success');
                
                // Update badge notifikasi
                updateNotifBadge();
            } else {
                showToast(data.message || 'Gagal menghapus semua notifikasi', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
        });
    }
}

// Fungsi untuk menampilkan toast notification
function showToast(message, type = 'info') {
    // Buat elemen toast jika belum ada
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(toastContainer);
    }
    
    // Buat elemen toast
    const toast = document.createElement('div');
    
    // Set class berdasarkan type
    let bgColor = 'bg-blue-500';
    if (type === 'success') bgColor = 'bg-green-500';
    else if (type === 'error') bgColor = 'bg-red-500';
    else if (type === 'warning') bgColor = 'bg-yellow-500';
    
    toast.className = `${bgColor} text-white px-4 py-2 rounded shadow-lg transform transition-transform duration-300 translate-x-full`;
    toast.textContent = message;
    
    // Tambahkan ke container
    toastContainer.appendChild(toast);
    
    // Animasi muncul
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 10);
    
    // Hapus setelah 3 detik
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}
</script>