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
      max-width: 500px !important;
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

    /* Password Strength Styles */
    .password-strength {
      margin-top: 0.5rem;
    }
    
    .strength-bar {
      height: 4px;
      border-radius: 2px;
      transition: all 0.3s ease;
      background: #e5e7eb;
    }
    
    .strength-text {
      font-size: 0.75rem;
      margin-top: 0.25rem;
      font-weight: 500;
    }
    
    .strength-weak {
      color: #dc2626;
    }
    
    .strength-medium {
      color: #ea580c;
    }
    
    .strength-strong {
      color: #16a34a;
    }
    
    .strength-very-strong {
      color: #059669;
    }

    /* Help section styles */
    .help-section {
      background: #f0f9ff;
      border: 1px solid #bae6fd;
      border-radius: 10px;
      padding: 1rem;
    }

    .help-tips {
      background: white;
      border-radius: 8px;
      padding: 0.75rem;
      margin-top: 0.75rem;
      border: 1px solid #e0f2fe;
    }

    /* Success notification styles */
    .success-notification {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1rem;
      box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
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
           get validPw(){ return this.password.length >= 8 },
           passwordStrength: 0,
           strengthText: '',
           strengthColor: '',
           barWidth: '0%',
           barColor: '#e5e7eb',
           
           checkPasswordStrength() {
             const password = this.password;
             let strength = 0;
             
             if (password.length === 0) {
               this.passwordStrength = 0;
               this.strengthText = '';
               this.barWidth = '0%';
               this.barColor = '#e5e7eb';
               return;
             }
             
             // Length check
             if (password.length >= 8) strength += 1;
             if (password.length >= 12) strength += 1;
             
             // Character variety checks
             if (/[a-z]/.test(password)) strength += 1;
             if (/[A-Z]/.test(password)) strength += 1;
             if (/[0-9]/.test(password)) strength += 1;
             if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
             
             // Determine strength level
             this.passwordStrength = strength;
             
             if (strength <= 2) {
               this.strengthText = 'Lemah';
               this.barWidth = '25%';
               this.barColor = '#dc2626';
             } else if (strength <= 4) {
               this.strengthText = 'Cukup';
               this.barWidth = '50%';
               this.barColor = '#ea580c';
             } else if (strength <= 5) {
               this.strengthText = 'Kuat';
               this.barWidth = '75%';
               this.barColor = '#16a34a';
             } else {
               this.strengthText = 'Sangat Kuat';
               this.barWidth = '100%';
               this.barColor = '#059669';
             }
           }
         }"
         x-effect="checkPasswordStrength()">

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

        <!-- Flash Messages untuk SweetAlert -->
        <?php if (session()->getFlashdata('error') || session()->getFlashdata('message') || session()->getFlashdata('success') || session()->getFlashdata('show_contact') || session()->getFlashdata('login_hint')): ?>
        <div class="hidden" id="flash-messages" 
             data-error="<?= esc(session()->getFlashdata('error') ?? '') ?>"
             data-message="<?= esc(session()->getFlashdata('message') ?? '') ?>"
             data-success="<?= esc(session()->getFlashdata('success') ?? '') ?>"
             data-show-contact="<?= session()->getFlashdata('show_contact') ? 'true' : 'false' ?>"
             data-login-hint="<?= esc(session()->getFlashdata('login_hint') ?? '') ?>">
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
                   placeholder="Masukkan password Anda" required minlength="6" autocomplete="current-password" enterkeyhint="done" 
                   @input="checkPasswordStrength()" />
            <button type="button"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-xs text-gray-500 hover:text-gray-700 transition-colors"
                    @click="show = !show" x-text="show ? 'Sembunyi' : 'Tampil'"></button>
          </div>
          
          <!-- Password Strength Indicator -->
          <div class="password-strength" x-show="password.length > 0">
            <div class="strength-bar" :style="`width: ${barWidth}; background: ${barColor};`"></div>
            <div class="strength-text" :class="{
              'strength-weak': passwordStrength <= 2,
              'strength-medium': passwordStrength > 2 && passwordStrength <= 4,
              'strength-strong': passwordStrength > 4 && passwordStrength <= 5,
              'strength-very-strong': passwordStrength > 5
            }" x-text="strengthText"></div>
          </div>
          
          <p class="mt-1 text-xs text-gray-500" x-show="password.length === 0">
            Minimal 8 karakter untuk keamanan optimal
          </p>
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
                :disabled="!password || loading">
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

        <!-- Section Bantuan Login -->
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-start gap-2">
            <div class="flex-shrink-0 mt-0.5">
              <i class="fas fa-info-circle text-blue-500 text-sm"></i>
            </div>
            <div class="flex-1">
              <h4 class="text-xs font-semibold text-blue-800 mb-1">Butuh Bantuan Login?</h4>
              <p class="text-xs text-blue-700 mb-2">
                Gunakan opsi berikut jika mengalami kendala:
              </p>
              <div class="flex flex-wrap gap-2">
                <a href="<?= site_url('forgot-password') ?>" class="text-xs text-blue-600 hover:text-blue-800 font-medium underline">
                  <i class="fas fa-key mr-1"></i>Lupa Password
                </a>
                <button type="button" onclick="showLoginHelp()" class="text-xs text-blue-600 hover:text-blue-800 font-medium underline">
                  <i class="fas fa-question-circle mr-1"></i>Panduan
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Subtle Hint untuk Google User -->
        <?php if (session()->getFlashdata('login_hint') === 'google_hint'): ?>
        <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-center gap-1.5 text-blue-700">
            <i class="fas fa-lightbulb text-blue-500 text-xs"></i>
            <span class="text-xs">
              <strong>Tips:</strong> Coba gunakan <strong>Login dengan Google</strong> jika biasanya login dengan akun Google.
            </span>
          </div>
        </div>
        <?php endif; ?>

        <p class="text-center text-xs text-gray-600">
          Belum punya akun?
          <a href="<?= site_url('register') ?>" class="text-blue-600 font-semibold hover:underline transition-colors">Daftar</a>
        </p>
      </form>
    </div>
  </div>

  <!-- Modal Panduan Login -->
  <div id="loginHelpModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl max-w-md w-full mx-4 p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800">Panduan Login</h3>
        <button onclick="closeLoginHelp()" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="space-y-4">
        <div class="bg-blue-50 p-4 rounded-lg">
          <h4 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
            <i class="fas fa-envelope"></i>Login dengan Email & Password
          </h4>
          <p class="text-sm text-blue-700">
            Gunakan email dan password yang Anda daftarkan. Pastikan:
          </p>
          <ul class="text-sm text-blue-700 mt-2 list-disc list-inside space-y-1">
            <li>Email sudah terdaftar di sistem</li>
            <li>Password minimal 8 karakter</li>
            <li>Gunakan password yang benar</li>
          </ul>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg">
          <h4 class="font-semibold text-green-800 mb-2 flex items-center gap-2">
            <i class="fab fa-google"></i>Login dengan Google
          </h4>
          <p class="text-sm text-green-700">
            Jika Anda mendaftar menggunakan akun Google, gunakan tombol <strong>"Sign in with Google"</strong>.
          </p>
        </div>
        
        <div class="bg-orange-50 p-4 rounded-lg">
          <h4 class="font-semibold text-orange-800 mb-2 flex items-center gap-2">
            <i class="fas fa-key"></i>Lupa Password?
          </h4>
          <p class="text-sm text-orange-700">
            Klik <strong>"Lupa password"</strong> untuk mereset password Anda.
          </p>
        </div>
      </div>
      
      <div class="mt-6 flex justify-end">
        <button onclick="closeLoginHelp()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
          Mengerti
        </button>
      </div>
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
        const loginHint = flashElem.getAttribute('data-login-hint');

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

        // Fungsi untuk membuat HTML help
        const getLoginHelpHTML = () => {
          return `
            <div class="text-left">
              <div class="mb-4">
                <p class="text-gray-700 mb-3">Beberapa hal yang bisa Anda coba:</p>
                
                <div class="bg-blue-50 p-3 rounded-lg mb-3">
                  <h5 class="font-semibold text-blue-800 mb-1 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>Periksa Kembali
                  </h5>
                  <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                    <li>Pastikan email yang dimasukkan benar</li>
                    <li>Password harus minimal 8 karakter</li>
                    <li>Perhatikan huruf kapital/kecil</li>
                  </ul>
                </div>
                
                <div class="bg-green-50 p-3 rounded-lg">
                  <h5 class="font-semibold text-green-800 mb-1 flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i>Alternatif Lain
                  </h5>
                  <ul class="text-sm text-green-700 list-disc list-inside space-y-1">
                    <li>Gunakan <strong>Login dengan Google</strong> jika pernah mendaftar via Google</li>
                    <li>Klik <strong>Lupa Password</strong> untuk reset password</li>
                    <li>Pastikan akun Anda sudah terdaftar</li>
                  </ul>
                </div>
              </div>
            </div>
          `;
        };
        // ✅ PERBAIKAN: Tampilkan success message dengan SweetAlert yang lebih menarik
        if (success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: `
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-green-600 text-2xl"></i>
                        </div>
                        <p class="text-gray-700 mb-2 text-lg font-semibold">${success}</p>
                        <p class="text-sm text-gray-600">Silakan login dengan password baru Anda</p>
                    </div>
                `,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Login Sekarang',
                width: 420,
                background: '#f9fafb',
                backdrop: 'rgba(16, 185, 129, 0.1)'
            }).then((result) => {
                // Focus ke email field setelah user menutup notifikasi
                if (result.isConfirmed) {
                    document.getElementById('email')?.focus();
                }
            });
        }
        
        // Tampilkan error dengan SweetAlert (warna kuning/warning) + bantuan
        else if (error) {
          Swal.fire({
            icon: 'warning',
            title: 'Login Gagal',
            html: `
              <div class="text-left">
                <p class="mb-3 text-gray-700">${error}</p>
                ${getLoginHelpHTML()}
                ${showContact ? getAdminContactHTML() : ''}
              </div>
            `,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Coba Lagi',
            width: 500
          }).then((result) => {
            // Focus ke email field setelah user menutup notifikasi
            if (result.isConfirmed) {
              document.getElementById('email')?.focus();
            }
          });
        }
        
        // Tampilkan info message dengan SweetAlert (warna biru)
        else if (message) {
          Swal.fire({
            icon: 'info',
            title: 'Informasi',
            html: `
              <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                  <i class="fas fa-info text-blue-600 text-xl"></i>
                </div>
                <p class="text-gray-700">${message}</p>
              </div>
            `,
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Mengerti',
            width: 380
          }).then((result) => {
            // Focus ke email field setelah user menutup notifikasi
            if (result.isConfirmed) {
              document.getElementById('email')?.focus();
            }
          });
        }

        // Tampilkan login hint untuk Google user
        else if (loginHint === 'google_hint') {
          Swal.fire({
            icon: 'info',
            title: 'Login dengan Google',
            html: `
              <div class="text-left">
                <div class="flex items-center gap-3 mb-3">
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fab fa-google text-blue-600"></i>
                  </div>
                  <div>
                    <p class="font-semibold text-gray-800">Akun Terdaftar dengan Google</p>
                    <p class="text-sm text-gray-600">Gunakan tombol Google untuk login</p>
                  </div>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg">
                  <p class="text-sm text-blue-700">
                    <strong>Tips:</strong> Akun ini terdaftar menggunakan Google OAuth. 
                    Silakan gunakan tombol <strong>"Sign in with Google"</strong> di bawah untuk login.
                  </p>
                </div>
              </div>
            `,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Mengerti',
            width: 450
          });
        }
      }

      // Auto focus ke email field setelah page load (jika tidak ada notifikasi)
      setTimeout(() => {
        if (!flashElem) {
          document.getElementById('email')?.focus();
        }
      }, 300);
    });

    function showLoginHelp() {
      document.getElementById('loginHelpModal').classList.remove('hidden');
    }

    function closeLoginHelp() {
      document.getElementById('loginHelpModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('loginHelpModal').addEventListener('click', function(e) {
      if (e.target === this) closeLoginHelp();
    });

    // Handle form submission loading state
    const loginForm = document.querySelector('form');
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const password = this.querySelector('#password').value;
        
        if (!password) {
          e.preventDefault();
          Swal.fire({
            icon: 'warning',
            title: 'Password Kosong',
            text: 'Silakan masukkan password Anda',
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Mengerti'
          });
        }
      });
    }
  </script>
</body>
</html>