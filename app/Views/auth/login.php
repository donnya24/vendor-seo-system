<!DOCTYPE html>
<html lang="id" x-data>
<head>
  <meta charset="UTF-8" />
  <title>Login | Vendor Partnership SEO Performance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    html,body{height:100%}
    body{font-family:'Montserrat',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial}
    
    .swal2-popup {
      font-family: 'Montserrat', sans-serif !important;
      border-radius: 16px !important;
      padding: 1.5rem !important;
      width: auto !important;
      max-width: 420px !important;
    }
    
    .swal2-title {
      font-size: 1.25rem !important;
      font-weight: 700 !important;
      padding: 0.5rem 0 1rem 0 !important;
    }
    
    .swal2-html-container {
      font-size: 0.9rem !important;
      line-height: 1.5 !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    
    .swal2-actions {
      margin-top: 1.25rem !important;
    }
    
    .swal2-confirm {
      border-radius: 10px !important;
      padding: 0.6rem 1.5rem !important;
      font-size: 0.9rem !important;
      font-weight: 600 !important;
    }
    
    .admin-contact {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 0.75rem;
      margin-top: 1rem;
      font-size: 0.8rem;
    }
    
    .admin-contact-title {
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .contact-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.25rem 0;
      color: #6b7280;
    }
    
    .contact-item i {
      width: 16px;
      text-align: center;
      color: #3b82f6;
    }

    /* Google Button Styles */
    .google-btn {
      background: white;
      border: 2px solid #e2e8f0;
      color: #374151;
      transition: all 0.3s ease;
    }
    
    .google-btn:hover {
      border-color: #3b82f6;
      background: #f8fafc;
    }
    
    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      color: #6b7280;
      font-size: 0.875rem;
      margin: 1rem 0;
    }
    
    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #e5e7eb;
    }
    
    .divider:not(:empty)::before {
      margin-right: 0.75rem;
    }
    
    .divider:not(:empty)::after {
      margin-left: 0.75rem;
    }
  </style>
</head>
<body class="bg-gray-50">

  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
       style="background-image:url('/assets/img/logo/background.png');">
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Card yang lebih kecil -->
    <div class="relative z-10 w-[90%] max-w-xs bg-white rounded-xl shadow-xl overflow-hidden"
         x-data="{
           email: '<?= esc(old('email') ?? '') ?>',
           password: '',
           show: false,
           loading: false,
           get validPw(){ return this.password.length >= 8 }
         }">

      <div class="px-5 pt-6 pb-4 text-center border-b">
        <div class="mx-auto w-10 h-10 rounded-full bg-white flex items-center justify-center shadow">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="w-6 h-6" />
        </div>
        <h1 class="mt-2 text-lg font-bold text-gray-800">Masuk ke Sistem</h1>
        <p class="text-gray-500 text-xs mt-1">Vendor Partnership & SEO Performance</p>
      </div>

      <!-- FORM LOGIN -->
      <form action="<?= site_url('login') ?>" method="post" 
            class="px-5 py-5 space-y-4" 
            novalidate
            @submit="loading = true">

        <?= csrf_field() ?>

        <!-- Hapus flashdata biasa karena kita pakai SweetAlert -->
        <?php if (session()->getFlashdata('error') || session()->getFlashdata('message') || session()->getFlashdata('success') || session()->getFlashdata('show_contact')): ?>
        <div class="hidden" id="flash-messages" 
             data-error="<?= esc(session()->getFlashdata('error') ?? '') ?>"
             data-message="<?= esc(session()->getFlashdata('message') ?? '') ?>"
             data-success="<?= esc(session()->getFlashdata('success') ?? '') ?>"
             data-show-contact="<?= session()->getFlashdata('show_contact') ? 'true' : 'false' ?>">
        </div>
        <?php endif; ?>

        <!-- Email -->
        <div>
          <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
          <input id="email" name="email" type="email" x-model="email"
                 class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-colors"
                 placeholder="you@example.com" required autocomplete="username" inputmode="email" autocapitalize="none" enterkeyhint="next" />
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi</label>
          <div class="relative">
            <input :type="show ? 'text' : 'password'" id="password" name="password" x-model="password"
                   class="w-full pr-16 pl-3 py-2 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-colors"
                   placeholder="Minimal 8 karakter" required minlength="6" autocomplete="current-password" enterkeyhint="done" />
            <button type="button"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-xs text-gray-500 hover:text-gray-700 transition-colors"
                    @click="show = !show" x-text="show ? 'Sembunyi' : 'Tampil'"></button>
          </div>
          <p class="mt-1 text-xs transition-colors" :class="validPw ? 'text-green-600' : 'text-gray-500'"
             x-text="validPw ? 'Siap.' : 'Minimal 8 karakter.'"></p>
        </div>

        <!-- Ingat saya & Lupa password -->
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-1.5 text-xs text-gray-700 select-none cursor-pointer">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-200 transition-colors" />
            <span>Ingat saya</span>
          </label>
          <a href="<?= site_url('forgot-password') ?>" class="text-blue-600 font-semibold hover:underline text-xs transition-colors">Lupa password?</a>
        </div>

        <!-- Tombol Login -->
        <button type="submit"
                class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center text-sm"
                :disabled="!validPw || loading">
          <span x-show="!loading" class="flex items-center gap-1.5">
            <i class="fas fa-sign-in-alt text-xs"></i>
            Masuk
          </span>
          <span x-show="loading" class="flex items-center gap-1.5">
            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Memproses...
          </span>
        </button>

        <!-- Divider -->
        <div class="divider">atau</div>

        <!-- Google Sign In Button -->
        <a href="<?= site_url('auth/google/login') ?>" 
          class="w-full py-2.5 rounded-lg google-btn font-semibold shadow flex items-center justify-center gap-2 transition-colors text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          <span>Sign in with Google</span>
        </a>

        <p class="text-center text-xs text-gray-600">
          Belum punya akun?
          <a href="<?= site_url('register') ?>" class="text-blue-600 font-semibold hover:underline transition-colors">Daftar</a>
        </p>
      </form>
    </div>
  </div>

  <!-- ✅ SCRIPT UNTUK SWEETALERT NOTIFICATION YANG LEBIH BAIK -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const flashElem = document.getElementById('flash-messages');
      
      if (flashElem) {
        const error = flashElem.getAttribute('data-error');
        const message = flashElem.getAttribute('data-message');
        const success = flashElem.getAttribute('data-success');
        const showContact = flashElem.getAttribute('data-show-contact') === 'true';

        // Fungsi untuk membuat HTML kontak admin
        const getAdminContactHTML = () => {
          return `
            <div class="admin-contact">
              <div class="admin-contact-title">
                <i class="fas fa-headset"></i>
                Hubungi Administrator
              </div>
              <div class="contact-item">
                <i class="fas fa-phone"></i>
                <span>Telepon: <strong>+62 857-5589-6233</strong></span>
              </div>
              <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>Email: <strong>mail@imersa.co.id</strong></span>
              </div>
              <div class="contact-item">
                <i class="fas fa-clock"></i>
                <span>Senin - Jumat, 08:00 - 17:00 WIB</span>
              </div>
            </div>
          `;
        };

        // Tampilkan error dengan SweetAlert (warna merah) + kontak admin
        if (error) {
          Swal.fire({
            icon: 'error',
            title: 'Login Gagal',
            html: `
              <div class="text-left">
                <div class="mb-3">${error}</div>
                ${showContact ? getAdminContactHTML() : ''}
              </div>
            `,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Mengerti',
            width: showContact ? 420 : 380
          });
        }
        
        // Tampilkan success message dengan SweetAlert (warna hijau)
        if (success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: success,
            confirmButtonColor: '#16a34a',
            confirmButtonText: 'Mengerti',
            width: 380
          });
        }
        
        // Tampilkan info message dengan SweetAlert (warna biru)
        if (message) {
          Swal.fire({
            icon: 'info',
            title: 'Informasi',
            text: message,
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Mengerti',
            width: 380
          });
        }
      }
    });
  </script>
</body>
</html>