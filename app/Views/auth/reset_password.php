<!DOCTYPE html>
<html lang="id" x-data>
<head>
  <meta charset="UTF-8" />
  <title>Reset Password | Vendor Partnership & SEO Performance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    html,body{height:100%} body{font-family:'Montserrat',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial}
    
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
  </style>
</head>
<body class="bg-gray-50">

  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
       style="background-image:url('/assets/img/logo/background.png');">
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Card ringkas & mobile-first -->
    <div class="relative z-10 w-[92%] max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
         x-data="resetPasswordForm()">

      <!-- Header -->
      <div class="px-6 pt-8 pb-5 text-center border-b">
        <div class="mx-auto w-12 h-12 rounded-full bg-white flex items-center justify-center shadow">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="w-7 h-7">
        </div>
        <h1 class="mt-3 text-xl sm:text-2xl font-bold text-gray-800">Reset Password</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">Masukkan password baru Anda</p>
      </div>

      <!-- Form -->
      <form action="<?= site_url('reset-password') ?>" method="post" class="px-6 py-6 sm:py-7 space-y-5" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">

        <!-- Flash Messages untuk SweetAlert -->
        <?php if (session()->getFlashdata('error') || session()->getFlashdata('success')): ?>
        <div class="hidden" id="flash-messages" 
             data-error="<?= esc(session()->getFlashdata('error') ?? '') ?>"
             data-success="<?= esc(session()->getFlashdata('success') ?? '') ?>">
        </div>
        <?php endif; ?>

        <!-- Password Baru -->
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password Baru</label>
          <div class="relative">
            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" x-model="formData.password"
                   class="w-full pr-24 pl-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                   placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password" 
                   @input="checkPasswordStrength()" />
            <button type="button"
                    class="absolute inset-y-0 right-0 px-4 flex items-center text-sm text-gray-500 hover:text-gray-700"
                    @click="showPassword = !showPassword" x-text="showPassword ? 'Sembunyikan' : 'Tampilkan'"></button>
          </div>
          
          <!-- Password Strength Indicator -->
          <div class="password-strength" x-show="formData.password.length > 0">
            <div class="strength-bar" :style="`width: ${barWidth}; background: ${barColor};`"></div>
            <div class="strength-text" :class="{
              'strength-weak': passwordStrength <= 2,
              'strength-medium': passwordStrength > 2 && passwordStrength <= 4,
              'strength-strong': passwordStrength > 4 && passwordStrength <= 5,
              'strength-very-strong': passwordStrength > 5
            }" x-text="strengthText"></div>
          </div>
          
          <p class="mt-1 text-xs" :class="ok ? 'text-green-600' : 'text-gray-500'"
             x-text="ok ? 'Kuat.' : 'Minimal 8 karakter.'"></p>
        </div>

        <!-- Konfirmasi Password -->
        <div>
          <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi Password</label>
          <div class="relative">
            <input :type="showConfirm ? 'text' : 'password'" id="password_confirm" name="password_confirm" x-model="formData.confirm"
                   class="w-full pr-24 pl-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                   placeholder="Ulangi password baru" required autocomplete="new-password" />
            <button type="button"
                    class="absolute inset-y-0 right-0 px-4 flex items-center text-sm text-gray-500 hover:text-gray-700"
                    @click="showConfirm = !showConfirm" x-text="showConfirm ? 'Sembunyikan' : 'Tampilkan'"></button>
          </div>
          <p class="mt-1 text-xs" :class="match ? 'text-green-600' : 'text-gray-500'"
             x-text="match ? 'Cocok.' : 'Ketik ulang sama persis.'"></p>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="!can">
          Reset Password
        </button>

        <!-- Back -->
        <div class="text-center mt-1 text-sm text-gray-600">
          <a href="<?= site_url('login') ?>" class="text-blue-600 font-semibold hover:underline">
            Kembali ke Login
          </a>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Fungsi Alpine.js untuk reset password form
    function resetPasswordForm() {
      return {
        formData: {
          password: '',
          confirm: ''
        },
        showPassword: false,
        showConfirm: false,
        
        // Password strength properties
        passwordStrength: 0,
        strengthText: '',
        barWidth: '0%',
        barColor: '#e5e7eb',
        
        get ok() { 
          return this.formData.password.length >= 8 
        },
        
        get match() { 
          return this.formData.confirm !== '' && this.formData.password === this.formData.confirm 
        },
        
        get can() { 
          return this.ok && this.match 
        },
        
        checkPasswordStrength() {
          const password = this.formData.password;
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
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const flashElem = document.getElementById('flash-messages');
      
      if (flashElem) {
        const error = flashElem.getAttribute('data-error');
        const success = flashElem.getAttribute('data-success');

        if (error) {
          Swal.fire({
            icon: 'error',
            title: 'Reset Password Gagal',
            text: error,
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Mengerti'
          });
        }
        
        if (success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: success,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Login Sekarang'
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = '<?= site_url('login') ?>';
            }
          });
        }
      }
    });
  </script>
</body>
</html>