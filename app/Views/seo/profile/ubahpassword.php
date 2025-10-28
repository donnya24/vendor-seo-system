<form action="<?= site_url('seo/profile/password/update'); ?>" method="post" id="passwordForm">
  <?= csrf_field() ?>

  <!-- Password Lama -->
  <div class="mb-4" x-data="{ show: false }">
    <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
    <div class="relative">
      <input :type="show ? 'text' : 'password'" name="current_password" required autocomplete="current-password"
             class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10">
      <button type="button" @click="show = !show"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
        <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
      </button>
    </div>
  </div>

  <!-- Password Baru -->
  <div class="mb-4" x-data="{ show: false }">
    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
    <div class="relative">
      <input :type="show ? 'text' : 'password'" name="new_password" required minlength="8" autocomplete="new-password" aria-describedby="pwHelp"
             class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10">
      <button type="button" @click="show = !show"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
        <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
      </button>
    </div>
    <div id="pwHelp" class="text-xs text-gray-500 mt-1">
      <p class="font-medium mb-1">Password harus mengandung:</p>
      <ul class="space-y-1 ml-4">
        <li>• Minimal 8 karakter</li>
        <li>• Huruf kecil (a-z)</li>
        <li>• Huruf besar (A-Z)</li>
        <li>• Angka (0-9)</li>
        <li>• Karakter khusus (!@#$%^&*)</li>
      </ul>
    </div>
  </div>

  <!-- Konfirmasi Password Baru -->
  <div class="mb-4" x-data="{ show: false }">
    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
    <div class="relative">
      <input :type="show ? 'text' : 'password'" name="confirm_password" required minlength="8" autocomplete="new-password"
             class="mt-1 block w-full border rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10">
      <button type="button" @click="show = !show"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
        <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
      </button>
    </div>
  </div>

  <!-- Notifikasi Error -->
  <?php if (session()->getFlashdata('errors_password')): ?>
    <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
      <i class="fas fa-exclamation-circle mr-1"></i>
      <ul class="list-disc pl-5">
        <?php foreach (session()->getFlashdata('errors_password') as $error): ?>
          <li><?= esc($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error_password')): ?>
    <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
      <i class="fas fa-exclamation-circle mr-1"></i><?= esc(session()->getFlashdata('error_password')) ?>
    </div>
  <?php endif; ?>

  <!-- Tombol -->
  <div class="flex justify-end gap-2">
    <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium" @click="$store.ui.modal=null">
      Batal
    </button>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
      <i class="fas fa-save mr-2"></i>Simpan Password
    </button>
  </div>
</form>

<!-- Hidden input untuk CSRF token yang akan digunakan oleh AJAX -->
<input type="hidden" id="csrf_token_name" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

<script>
document.addEventListener('alpine:init', () => {
  // Handle form submission with AJAX
  document.getElementById('passwordForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    
    try {
      // Get CSRF token from hidden input
      const csrfToken = document.getElementById('csrf_token_name').value;
      const csrfName = document.getElementById('csrf_token_name').name;
      
      const response = await fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken // Send CSRF token in header
        }
      });
      
      // Cek apakah response adalah redirect (biasanya setelah logout)
      if (response.redirected) {
        // Jika response adalah redirect, arahkan ke URL tersebut
        window.location.href = response.url;
        return;
      }
      
      // Check if response is ok
      if (!response.ok) {
        // Try to get error message from response
        let errorMessage = 'Terjadi kesalahan server';
        try {
          const errorData = await response.json();
          errorMessage = errorData.message || errorMessage;
        } catch (e) {
          // If response is not JSON, try to get text
          try {
            const errorText = await response.text();
            if (errorText.includes('<')) {
              // It's HTML, probably an error page
              errorMessage = 'Server mengembalikan halaman error. Silakan refresh halaman dan coba lagi.';
            } else {
              errorMessage = errorText || errorMessage;
            }
          } catch (e2) {
            console.error('Error parsing error response:', e2);
          }
        }
        
        throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
      }
      
      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        // Jika bukan JSON, kemungkinan besar adalah halaman login setelah logout
        // Arahkan ke halaman login
        window.location.href = '/login';
        return;
      }
      
      const result = await response.json();
      
      if (result.status === 'success') {
        // Show success notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50 shadow-lg';
        notification.innerHTML = `
          <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>${result.message}</span>
          </div>
        `;
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
          notification.remove();
        }, 3000);
        
        // Close modal and reset form
        setTimeout(() => {
          if (window.Alpine && window.Alpine.store('ui')) {
            window.Alpine.store('ui').modal = null;
          }
          this.reset();
          
          // Redirect to login if needed
          if (result.redirect) {
            window.location.href = result.redirect;
          }
        }, 1500);
      } else {
        // Show error notification
        let errorMessage = result.message || 'Terjadi kesalahan';
        if (result.errors) {
          errorMessage = Object.values(result.errors).join('<br>');
        }
        
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50 shadow-lg';
        notification.innerHTML = `
          <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>${errorMessage}</span>
          </div>
        `;
        document.body.appendChild(notification);
        
        // Remove notification after 5 seconds
        setTimeout(() => {
          notification.remove();
        }, 5000);
      }
    } catch (error) {
      console.error('Error:', error);
      
      // Cek apakah error karena redirect (misalnya setelah logout)
      if (error.message.includes('Response bukan JSON')) {
        // Arahkan ke halaman login
        window.location.href = '/login';
        return;
      }
      
      // Show network error notification
      const notification = document.createElement('div');
      notification.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50 shadow-lg';
      notification.innerHTML = `
        <div class="flex items-center">
          <i class="fas fa-exclamation-circle mr-2"></i>
          <span>Terjadi kesalahan: ${error.message}</span>
        </div>
      `;
      document.body.appendChild(notification);
      
      // Remove notification after 5 seconds
      setTimeout(() => {
        notification.remove();
      }, 5000);
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan Password';
    }
  });
});
</script>