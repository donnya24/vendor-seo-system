<!DOCTYPE html>
<html lang="id" x-data>
<head>
  <meta charset="UTF-8" />
  <title>Register | Vendor Partnership & SEO Performance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    html,body{height:100%}body{font-family:'Montserrat',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial}
    
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

    /* Password Requirements */
    .requirements {
      font-size: 0.7rem;
      color: #6b7280;
      margin-top: 0.5rem;
    }
    
    .requirement {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      margin-bottom: 0.125rem;
    }
    
    .requirement.met {
      color: #16a34a;
    }
    
    .requirement.unmet {
      color: #6b7280;
    }
  </style>
</head>
<body class="bg-gray-50">

  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative py-8"
       style="background-image:url('/assets/img/logo/background.png');">
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Container yang bisa di-scroll -->
    <div class="relative z-10 w-full h-full flex items-center justify-center p-4 overflow-auto">
      <!-- Card yang lebih kecil -->
      <div class="w-full max-w-xs bg-white rounded-xl shadow-xl overflow-hidden"
           x-data="{
             vendor_name: '<?= esc(old('vendor_name') ?? ($prefillData['vendor_name'] ?? '')) ?>',
             email: '<?= esc(old('email') ?? ($prefillData['email'] ?? '')) ?>',
             password: '',
             confirm: '',
             showPw: false,
             showCf: false,
             
             // Password strength properties
             passwordStrength: 0,
             strengthText: '',
             barWidth: '0%',
             barColor: '#e5e7eb',
             
             // Password requirements
             hasMinLength: false,
             hasLowerCase: false,
             hasUpperCase: false,
             hasNumber: false,
             hasSpecialChar: false,
             
             checkPasswordStrength() {
               const password = this.password;
               let strength = 0;
               
               // Reset requirements
               this.hasMinLength = false;
               this.hasLowerCase = false;
               this.hasUpperCase = false;
               this.hasNumber = false;
               this.hasSpecialChar = false;
               
               if (password.length === 0) {
                 this.passwordStrength = 0;
                 this.strengthText = '';
                 this.barWidth = '0%';
                 this.barColor = '#e5e7eb';
                 return;
               }
               
               // Check requirements
               this.hasMinLength = password.length >= 8;
               this.hasLowerCase = /[a-z]/.test(password);
               this.hasUpperCase = /[A-Z]/.test(password);
               this.hasNumber = /[0-9]/.test(password);
               this.hasSpecialChar = /[^a-zA-Z0-9]/.test(password);
               
               // Calculate strength
               if (this.hasMinLength) strength += 1;
               if (this.hasLowerCase) strength += 1;
               if (this.hasUpperCase) strength += 1;
               if (this.hasNumber) strength += 1;
               if (this.hasSpecialChar) strength += 1;
               
               // Additional points for length
               if (password.length >= 12) strength += 1;
               
               this.passwordStrength = strength;
               
               // Determine strength level
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
             },
             
             get okPw(){ return this.password.length >= 8 },
             get match(){ return this.password !== '' && this.password === this.confirm },
             get canSubmit(){ 
               return this.vendor_name.trim() && 
                      this.email.trim() && 
                      this.okPw && 
                      this.match 
             }
           }"
           x-effect="checkPasswordStrength()">

        <div class="px-5 pt-6 pb-4 text-center border-b">
          <div class="mx-auto w-10 h-10 rounded-full bg-white flex items-center justify-center shadow">
            <img src="/assets/img/logo/icon.png" alt="Logo" class="w-6 h-6" />
          </div>
          <h1 class="mt-2 text-lg font-bold text-gray-800">Buat Akun Vendor</h1>
          <p class="text-gray-500 text-xs mt-1">Kelola profil, leads & laporan</p>
        </div>

        <form action="<?= site_url('register') ?>" method="post" class="px-5 py-5 space-y-4" novalidate>
          <?= csrf_field() ?>

          <?php if (session()->getFlashdata('error')): ?>
            <div class="text-red-600 text-xs p-2 bg-red-50 rounded-lg"><?= session()->getFlashdata('error') ?></div>
          <?php elseif (session()->getFlashdata('message')): ?>
            <div class="text-green-600 text-xs p-2 bg-green-50 rounded-lg"><?= session()->getFlashdata('message') ?></div>
          <?php endif; ?>

          <!-- Nama Vendor -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Vendor</label>
            <input type="text" name="vendor_name" x-model="vendor_name"
                   class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-colors"
                   placeholder="Nama perusahaan / brand" required enterkeyhint="next" />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
            <input type="email" name="email" x-model="email"
                   class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-colors"
                   placeholder="you@example.com" required autocomplete="username" inputmode="email" autocapitalize="none" enterkeyhint="next" />
          </div>

          <!-- Password -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
            <div class="relative">
              <input :type="showPw ? 'text' : 'password'" name="password" x-model="password"
                     class="w-full pr-16 pl-3 py-2 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-colors"
                     placeholder="Buat password yang kuat" required minlength="8" autocomplete="new-password" 
                     @input="checkPasswordStrength()" />
              <button type="button"
                      class="absolute inset-y-0 right-0 px-3 flex items-center text-xs text-gray-500 hover:text-gray-700 transition-colors"
                      @click="showPw = !showPw" x-text="showPw ? 'Sembunyi' : 'Tampil'"></button>
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
            
            <!-- Password Requirements -->
            <div class="requirements" x-show="password.length > 0">
              <div class="requirement" :class="hasMinLength ? 'met' : 'unmet'">
                <i class="fas" :class="hasMinLength ? 'fa-check-circle' : 'fa-circle'"></i>
                <span>Minimal 8 karakter</span>
              </div>
              <div class="requirement" :class="hasLowerCase ? 'met' : 'unmet'">
                <i class="fas" :class="hasLowerCase ? 'fa-check-circle' : 'fa-circle'"></i>
                <span>Huruf kecil (a-z)</span>
              </div>
              <div class="requirement" :class="hasUpperCase ? 'met' : 'unmet'">
                <i class="fas" :class="hasUpperCase ? 'fa-check-circle' : 'fa-circle'"></i>
                <span>Huruf besar (A-Z)</span>
              </div>
              <div class="requirement" :class="hasNumber ? 'met' : 'unmet'">
                <i class="fas" :class="hasNumber ? 'fa-check-circle' : 'fa-circle'"></i>
                <span>Angka (0-9)</span>
              </div>
              <div class="requirement" :class="hasSpecialChar ? 'met' : 'unmet'">
                <i class="fas" :class="hasSpecialChar ? 'fa-check-circle' : 'fa-circle'"></i>
                <span>Karakter khusus (!@#$%^&*)</span>
              </div>
            </div>
            
            <p class="mt-1 text-xs text-gray-500" x-show="password.length === 0">
              Buat password yang kuat dengan kombinasi huruf, angka, dan karakter khusus
            </p>
          </div>

          <!-- Konfirmasi Password -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Password</label>
            <div class="relative">
              <input :type="showCf ? 'text' : 'password'" name="pass_confirm" x-model="confirm"
                     class="w-full pr-16 pl-3 py-2 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-1 focus:ring-blue-200 outline-none text-sm transition-colors"
                     placeholder="Ulangi password" required autocomplete="new-password" />
              <button type="button"
                      class="absolute inset-y-0 right-0 px-3 flex items-center text-xs text-gray-500 hover:text-gray-700 transition-colors"
                      @click="showCf = !showCf" x-text="showCf ? 'Sembunyi' : 'Tampil'"></button>
            </div>
            <p class="mt-1 text-xs" :class="match ? 'text-green-600' : 'text-red-600'"
               x-text="match ? '✓ Password cocok' : '✗ Password tidak cocok'"></p>
          </div>

          <button type="submit"
                  class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow disabled:opacity-60 disabled:cursor-not-allowed text-sm"
                  :disabled="!canSubmit">
            Daftar
          </button>

          <!-- Divider -->
          <div class="divider">atau</div>

          <!-- Google Sign Up Button - MENGGUNAKAN ENDPOINT REGISTER -->
          <a href="<?= site_url('auth/google/register') ?>" 
             class="w-full py-2.5 rounded-lg google-btn font-semibold shadow flex items-center justify-center gap-2 transition-colors text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            <span>Sign up with Google</span>
          </a>

          <p class="text-center text-xs text-gray-600">
            Sudah punya akun?
            <a href="<?= site_url('login') ?>" class="text-blue-600 font-semibold hover:underline">Masuk</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</body>
</html>