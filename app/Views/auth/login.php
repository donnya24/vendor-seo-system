<!DOCTYPE html>
<html lang="id" x-data>
<head>
  <meta charset="UTF-8" />
  <title>Login | Vendor Partnership SEO Performance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- ✅ TAMBAHKAN SweetAlert2 & Font Awesome -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    html,body{height:100%}
    body{font-family:'Montserrat',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial}
    
    /* Custom styles untuk SweetAlert yang lebih kecil dan elegan */
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
    
    /* Style untuk kontak admin */
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
  </style>
</head>
<body class="bg-gray-50">

  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
       style="background-image:url('/assets/img/logo/background.png');">
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Card ringkas -->
    <div class="relative z-10 w-[92%] max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
         x-data="{
           email: '<?= esc(old('email') ?? '') ?>',
           password: '',
           show: false,
           loading: false,
           get validPw(){ return this.password.length >= 8 }
         }">

      <div class="px-6 pt-8 pb-5 text-center border-b">
        <div class="mx-auto w-12 h-12 rounded-full bg-white flex items-center justify-center shadow">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="w-7 h-7" />
        </div>
        <h1 class="mt-3 text-xl sm:text-2xl font-bold text-gray-800">Masuk ke Sistem</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">Vendor Partnership & SEO Performance</p>
      </div>

      <!-- FORM LOGIN -->
      <form action="<?= site_url('login') ?>" method="post" 
            class="px-6 py-6 sm:py-7 space-y-5" 
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
          <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
          <input id="email" name="email" type="email" x-model="email"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px] transition-colors"
                 placeholder="you@example.com" required autocomplete="username" inputmode="email" autocapitalize="none" enterkeyhint="next" />
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Kata Sandi</label>
          <div class="relative">
            <input :type="show ? 'text' : 'password'" id="password" name="password" x-model="password"
                   class="w-full pr-20 pl-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px] transition-colors"
                   placeholder="Minimal 8 karakter" required minlength="6" autocomplete="current-password" enterkeyhint="done" />
            <button type="button"
                    class="absolute inset-y-0 right-0 px-4 flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors"
                    @click="show = !show" x-text="show ? 'Sembunyikan' : 'Tampilkan'"></button>
          </div>
          <p class="mt-1 text-xs transition-colors" :class="validPw ? 'text-green-600' : 'text-gray-500'"
             x-text="validPw ? 'Siap.' : 'Minimal 8 karakter.'"></p>
        </div>

        <!-- Ingat saya & Lupa password -->
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-sm text-gray-700 select-none cursor-pointer">
            <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200 transition-colors" />
            <span>Ingat saya</span>
          </label>
          <a href="<?= site_url('forgot-password') ?>" class="text-blue-600 font-semibold hover:underline text-sm transition-colors">Lupa password?</a>
        </div>

        <!-- Tombol Login -->
        <button type="submit"
                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center"
                :disabled="!validPw || loading">
          <span x-show="!loading" class="flex items-center gap-2">
            <i class="fas fa-sign-in-alt"></i>
            Masuk
          </span>
          <span x-show="loading" class="flex items-center gap-2">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Memproses...
          </span>
        </button>

        <p class="text-center text-sm text-gray-600">
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