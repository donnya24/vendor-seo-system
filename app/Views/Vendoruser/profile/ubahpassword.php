<?php
// Force no cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

 $session = session();

// FLASHDATA
 $errorsArr   = $session->getFlashdata('errors') ?? $session->getFlashdata('errors_password') ?? [];
 $successMsg  = $session->getFlashdata('success') ?? $session->getFlashdata('success_password');
 $errorMsg    = $session->getFlashdata('error')   ?? $session->getFlashdata('error_password');

// Data dari controller
 $hasPassword = $content_data['hasPassword'] ?? true;
?>

<style>
  
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

/* Match indicator */
.password-match {
  font-size: 0.75rem;
  margin-top: 0.25rem;
  font-weight: 500;
}

.match-valid {
  color: #16a34a;
}

.match-invalid {
  color: #dc2626;
}

/* Modal lock styles */
.modal-lock {
  pointer-events: auto !important;
}

.password-edit-modal {
  z-index: 99998 !important;
}

.swal2-container {
  z-index: 99999 !important;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  justify-content: flex-end;
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.form-actions button {
  flex: none;
}

.btn-primary {
  background: #2563eb;
  color: white;
  padding: 0.625rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: #2563eb !important;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  padding: 0.625rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
}

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Remove disabled button strikethrough */
.btn-primary:disabled::after,
.btn-secondary:disabled::after {
  display: none !important;
}

/* Reset button styling when not actually disabled */
.btn-primary:not([disabled]) {
  opacity: 1 !important;
  cursor: pointer !important;
  background: #2563eb !important;
}

/* ✅ PERBAIKAN: Perkecil ukuran notifikasi */
.swal2-popup {
  padding: 1rem !important;
  border-radius: 0.5rem !important;
  width: 28rem !important;
  font-size: 0.875rem !important;
}

.swal2-title {
  font-size: 1.125rem !important;
  font-weight: 600 !important;
  margin-bottom: 0.5rem !important;
}

.swal2-html-container {
  padding: 0 !important;
  margin: 0 !important;
  font-size: 0.875rem !important;
}

.swal2-icon {
  width: 3rem !important;
  height: 3rem !important;
  margin: 0 auto 0.75rem !important;
}

.swal2-icon-content {
  font-size: 1.5rem !important;
}

.swal2-timer-progress-bar {
  height: 3px !important;
}

/* CSS spesifik untuk SweetAlert di dalam modal ubah password */
#passwordEditModal .swal2-actions,
#passwordEditModal .swal2-confirm,
#passwordEditModal .swal2-cancel,
#passwordEditModal .swal2-popup .swal2-actions,
#passwordEditModal .swal2-popup .swal2-confirm,
#passwordEditModal .swal2-popup .swal2-cancel {
  display: none !important;
  visibility: hidden !important;
  opacity: 0 !important;
  height: 0 !important;
  width: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  border: none !important;
  position: absolute !important;
  top: -9999px !important;
  left: -9999px !important;
}

/* Pastikan modal ubah password memiliki z-index lebih tinggi */
#passwordEditModal {
  z-index: 99999 !important;
}

#passwordEditContent {
  z-index: 100000 !important;
}

/* Class khusus untuk notifikasi perubahan password */
.password-change-notification {
  padding: 1rem !important;
  border-radius: 0.5rem !important;
  width: 28rem !important;
  font-size: 0.875rem !important;
}

.password-change-notification-container {
  z-index: 100001 !important;
}

/* Sembunyikan tombol untuk notifikasi khusus */
.password-change-notification .swal2-actions {
  display: none !important;
}

.password-change-notification .swal2-confirm,
.password-change-notification .swal2-cancel {
  display: none !important;
  visibility: hidden !important;
  opacity: 0 !important;
}

/* Class untuk SweetAlert tanpa tombol */
.swal2-no-buttons .swal2-actions,
.swal2-no-buttons .swal2-confirm,
.swal2-no-buttons .swal2-cancel {
  display: none !important;
  visibility: hidden !important;
  opacity: 0 !important;
  height: 0 !important;
  width: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  border: none !important;
  position: absolute !important;
  top: -9999px !important;
  left: -9999px !important;
}

.swal2-no-buttons {
  padding-bottom: 1em !important;
}
</style>

<div x-show="$store.ui.modal==='passwordEdit'" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 modal-lock password-edit-modal"
     id="passwordEditModal"
     x-data="{
       isSubmitting: false,
       init() {
         Alpine.store('ui').lockModal();
         this.$el.addEventListener('x-destroy', () => {
           Alpine.store('ui').unlockModal();
         });
       }
     }">
  <div class="bg-black/50 absolute inset-0" id="passwordEditBackdrop"></div>
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md relative z-10" 
       id="passwordEditContent">
    <div class="px-6 py-4 border-b flex items-center justify-between">
      <h3 class="text-lg font-semibold">
        <?= $hasPassword ? 'Ubah Password' : 'Atur Password Pertama' ?>
      </h3>
      <button class="text-gray-500 hover:text-gray-700 transition-colors" 
              @click="if(!isSubmitting) $store.ui.modal=null; $store.ui.unlockModal();"
              :disabled="isSubmitting">
        <i class="fas fa-times text-lg"></i>
      </button>
    </div>

    <form id="passwordForm" action="<?= site_url('vendoruser/password/update'); ?>" method="post" class="p-6 space-y-4"
          x-data="{
            currentPassword: '',
            newPassword: '',
            confirmPassword: '',
            showCurrent: false,
            showNew: false,
            showConfirm: false,
            passwordStrength: 0,
            strengthText: '',
            barWidth: '0%',
            barColor: '#e5e7eb',
            hasMinLength: false,
            hasLowerCase: false,
            hasUpperCase: false,
            hasNumber: false,
            hasSpecialChar: false,
            
            get passwordsMatch() { 
              return this.newPassword !== '' && this.newPassword === this.confirmPassword;
            },
            get isFormValid() {
              const baseValid = this.newPassword.length >= 8 && this.passwordsMatch;
              return <?= $hasPassword ? 'this.currentPassword !== \'\' && baseValid' : 'baseValid' ?>;
            },
            
            checkPasswordStrength() {
              const password = this.newPassword;
              let strength = 0;
              
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
              
              this.hasMinLength = password.length >= 8;
              this.hasLowerCase = /[a-z]/.test(password);
              this.hasUpperCase = /[A-Z]/.test(password);
              this.hasNumber = /[0-9]/.test(password);
              this.hasSpecialChar = /[^a-zA-Z0-9]/.test(password);
              
              if (this.hasMinLength) strength += 1;
              if (this.hasLowerCase) strength += 1;
              if (this.hasUpperCase) strength += 1;
              if (this.hasNumber) strength += 1;
              if (this.hasSpecialChar) strength += 1;
              
              if (password.length >= 12) strength += 1;
              
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
            },
            
            forceFormUpdate() {
              this.checkPasswordStrength();
              this.$nextTick(() => {
                // Ensure form validation is re-evaluated
              });
            }
          }"
          x-effect="checkPasswordStrength()"
          @submit="isSubmitting = true">
      
      <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrf_token">

      <!-- Flash Messages -->
      <?php if (!empty($errorMsg)): ?>
        <div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
          <div class="flex items-start"><i class="fas fa-exclamation-circle mr-2 mt-0.5"></i><span><?= esc($errorMsg) ?></span></div>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($errorsArr)): ?>
        <div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
          <?php foreach ($errorsArr as $msg): ?>
            <div class="flex items-start"><i class="fas fa-exclamation-circle mr-2 mt-0.5"></i><span><?= esc($msg) ?></span></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($successMsg)): ?>
        <div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200">
          <div class="flex items-start"><i class="fas fa-check-circle mr-2 mt-0.5"></i><span><?= esc($successMsg) ?></span></div>
        </div>
      <?php endif; ?>

      <!-- Informasi untuk User Tanpa Password -->
      <?php if (!$hasPassword): ?>
        <div class="p-3 bg-blue-50 text-blue-700 rounded-lg text-sm border border-blue-200">
          <div class="flex items-start">
            <i class="fas fa-info-circle mr-2 mt-0.5 flex-shrink-0"></i>
            <div>
              <p class="font-semibold">Atur Password Pertama Kali</p>
              <p class="mt-1 text-sm">Silakan atur password untuk akun Anda. Setelah ini, Anda bisa login dengan email dan password.</p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Password Saat Ini -->
      <?php if ($hasPassword): ?>
        <div class="relative">
          <label class="block text-sm font-semibold mb-1 text-gray-700">
            Password Sekarang
          </label>
          <div class="relative">
            <input :type="showCurrent ? 'text' : 'password'" 
                   name="current_password" 
                   x-model="currentPassword"
                   :value="currentPassword"
                   required 
                   placeholder="Masukkan password saat ini"
                   class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors"
                   autocomplete="current-password"
                   :disabled="isSubmitting">
            <button type="button" @click="showCurrent = !showCurrent"
                    class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors"
                    :disabled="isSubmitting">
              <i :class="showCurrent ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
            </button>
          </div>
        </div>
      <?php else: ?>
        <input type="hidden" name="current_password" value="">
      <?php endif; ?>

      <!-- Password Baru -->
      <div class="relative">
        <label class="block text-sm font-semibold mb-1 text-gray-700">
          Password Baru
        </label>
        <div class="relative">
          <input :type="showNew ? 'text' : 'password'" 
                 name="new_password" 
                 x-model="newPassword"
                 :value="newPassword"
                 required 
                 minlength="8" 
                 autocomplete="new-password"
                 placeholder="<?= $hasPassword ? 'Masukkan password baru' : 'Buat password baru untuk akun Anda' ?>"
                 class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors"
                 :disabled="isSubmitting">
          <button type="button" @click="showNew = !showNew"
                  class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors"
                  :disabled="isSubmitting">
            <i :class="showNew ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
          </button>
        </div>
        
        <!-- Password Strength Indicator -->
        <div class="password-strength" x-show="newPassword.length > 0">
          <div class="strength-bar" :style="`width: ${barWidth}; background: ${barColor};`"></div>
          <div class="strength-text" :class="{
            'strength-weak': passwordStrength <= 2,
            'strength-medium': passwordStrength > 2 && passwordStrength <= 4,
            'strength-strong': passwordStrength > 4 && passwordStrength <= 5,
            'strength-very-strong': passwordStrength > 5
          }" x-text="strengthText"></div>
        </div>
        
        <!-- Password Requirements -->
        <div class="requirements" x-show="newPassword.length > 0">
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
        
        <p class="text-xs text-gray-500 mt-1" x-show="newPassword.length === 0">
          Minimal 8 karakter. Disarankan gabungkan huruf, angka, dan simbol untuk keamanan optimal.
        </p>
      </div>

      <!-- Konfirmasi Password -->
      <div class="relative">
        <label class="block text-sm font-semibold mb-1 text-gray-700">
          Konfirmasi Password
        </label>
        <div class="relative">
          <input :type="showConfirm ? 'text' : 'password'" 
                 name="pass_confirm" 
                 x-model="confirmPassword"
                 :value="confirmPassword"
                 required 
                 minlength="8" 
                 autocomplete="new-password"
                 placeholder="Ketik ulang password baru"
                 class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors"
                 :disabled="isSubmitting">
          <button type="button" @click="showConfirm = !showConfirm"
                  class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors"
                  :disabled="isSubmitting">
            <i :class="showConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
          </button>
        </div>
        
        <!-- Password Match Indicator -->
        <div class="password-match" x-show="confirmPassword.length > 0" 
             :class="passwordsMatch ? 'match-valid' : 'match-invalid'"
             x-text="passwordsMatch ? '✓ Password cocok' : '✗ Password tidak cocok'">
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="button" 
                class="btn-secondary"
                @click="if(!isSubmitting) { $store.ui.modal=null; $store.ui.unlockModal(); }"
                :disabled="isSubmitting">
          Batal
        </button>
        <button type="submit" 
                class="btn-primary"
                :disabled="isSubmitting"
                x-ref="submitButton">
          <span x-show="!isSubmitting"><?= $hasPassword ? 'Simpan Password' : 'Atur Password' ?></span>
          <span x-show="isSubmitting" class="flex items-center gap-2">
            <i class="fas fa-spinner fa-spin"></i>
            Memproses...
          </span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('passwordForm');
    if (!form) return;

    const modal = document.getElementById('passwordEditModal');
    const backdrop = document.getElementById('passwordEditBackdrop');
    const content = document.getElementById('passwordEditContent');

    function preventModalClose(e) {
        const hasSweetAlert = document.querySelector('.swal2-container');
        if (hasSweetAlert) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    }

    if (backdrop) {
        backdrop.addEventListener('click', preventModalClose, true);
    }

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                preventModalClose(e);
            }
        }, true);

        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const hasSweetAlert = document.querySelector('.swal2-container');
                if (hasSweetAlert) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
        }, true);
    }

    const closeBtn = content?.querySelector('button[aria-label="Close"]');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            const hasSweetAlert = document.querySelector('.swal2-container');
            if (hasSweetAlert) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }, true);
    }

    function updateCSRFToken(csrfToken) {
        if (!csrfToken) return;
        
        const csrfInputs = document.querySelectorAll('input[name="<?= csrf_token() ?>"]');
        csrfInputs.forEach(input => {
            input.value = csrfToken;
        });
        
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', csrfToken);
        }
        
        if (window.CSRF) {
            window.CSRF.hash = csrfToken;
        }
    }

    function getCSRFToken() {
        const csrfInput = document.getElementById('csrf_token');
        if (csrfInput && csrfInput.value) {
            return csrfInput.value;
        }
        
        const csrfCookie = document.cookie.match(new RegExp('(?:^|;\\s*)csrf_cookie_name=([^;]*)'))?.[1];
        if (csrfCookie) {
            return csrfCookie;
        }
        
        return null;
    }

    async function refreshCSRFToken() {
        try {
            const response = await fetch('<?= site_url('get-csrf-token') ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.token) {
                updateCSRFToken(data.token);
                return data.token;
            }
        } catch (err) {
            return null;
        }
    }

    // Fungsi khusus untuk SweetAlert di modal ubah password
    function showPasswordModalAlert(options) {
      // Pastikan tombol tidak ditampilkan
      options.showConfirmButton = false;
      options.showCancelButton = false;
      
      // Tambahkan class khusus
      if (!options.customClass) {
        options.customClass = {};
      }
      if (!options.customClass.popup) {
        options.customClass.popup = '';
      }
      options.customClass.popup += ' swal2-no-buttons';
      
      // Panggil fungsi asli
      const result = Swal.fire(options);
      
      // Tambahkan CSS spesifik untuk modal ini
      setTimeout(() => {
        const modalElement = document.getElementById('passwordEditModal');
        if (modalElement) {
          const swalContainer = modalElement.querySelector('.swal2-container');
          if (swalContainer) {
            const buttons = swalContainer.querySelectorAll('.swal2-actions, .swal2-confirm, .swal2-cancel');
            buttons.forEach(button => {
              button.style.display = 'none';
              button.style.visibility = 'hidden';
              button.style.opacity = '0';
              button.style.height = '0';
              button.style.width = '0';
              button.style.margin = '0';
              button.style.padding = '0';
              button.style.border = 'none';
              button.style.position = 'absolute';
              button.style.top = '-9999px';
              button.style.left = '-9999px';
            });
          }
        }
      }, 10);
      
      return result;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        const alpineComponent = Alpine.$data(form);
        
        const currentPassword = form.querySelector('input[name="current_password"]')?.value || '';
        const newPassword = form.querySelector('input[name="new_password"]')?.value || '';
        const passConfirm = form.querySelector('input[name="pass_confirm"]')?.value || '';
        
        if (alpineComponent) {
            alpineComponent.isSubmitting = true;
        }
        
        if (btn) {
            btn.disabled = true;
        }

        const fd = new FormData(form);
        fd.set('current_password', currentPassword);
        fd.set('new_password', newPassword);
        fd.set('pass_confirm', passConfirm);

        const csrfName = '<?= csrf_token() ?>';
        let csrfHash = getCSRFToken();
        
        if (csrfHash) {
            fd.set(csrfName, csrfHash);
        }

        const headers = { 
            'X-Requested-With': 'XMLHttpRequest'
        };
        
        if (csrfHash) {
            headers['X-CSRF-Token'] = csrfHash;
        }

        try {
            const res = await fetch(form.action, { 
                method: 'POST', 
                body: fd, 
                headers 
            });
            
            const data = await res.json().catch(() => ({}));

            if (data?.csrf) {
                updateCSRFToken(data.csrf);
            }

            if (res.ok && data?.status === 'success') {
                await showPasswordModalAlert({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `
                        <div class="text-center">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-check text-green-600 text-sm"></i>
                            </div>
                            <p class="text-gray-700 text-sm font-medium mb-1">${data.message}</p>
                            <p class="text-xs text-gray-500">Anda akan di-logout otomatis dalam 3 detik...</p>
                        </div>
                    `,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'password-change-notification swal2-no-buttons',
                        container: 'password-change-notification-container'
                    },
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                setTimeout(() => {
                    window.location.href = '<?= site_url('logout') ?>';
                }, 3000);

            } else {
                let errorMessage = data?.message || 'Gagal memperbarui password.';
                if (data?.errors && typeof data.errors === 'object') {
                    errorMessage = Object.values(data.errors).join('<br>');
                }
                
                await showPasswordModalAlert({
                    icon: 'error',
                    title: 'Gagal',
                    html: `
                        <div class="text-center">
                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-times text-red-600 text-sm"></i>
                            </div>
                            <p class="text-gray-700 text-sm">${errorMessage}</p>
                        </div>
                    `,
                    timer: 2000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'password-change-notification swal2-no-buttons',
                        container: 'password-change-notification-container'
                    }
                });

                if (alpineComponent) {
                    alpineComponent.newPassword = '';
                    alpineComponent.confirmPassword = '';
                } else {
                    form.querySelector('input[name="new_password"]').value = '';
                    form.querySelector('input[name="pass_confirm"]').value = '';
                }
                
                const currentPasswordField = form.querySelector('input[name="current_password"]');
                if (currentPasswordField) {
                    currentPasswordField.focus();
                }
            }
        } catch (err) {
            await showPasswordModalAlert({
                icon: 'error',
                title: 'Gagal',
                html: `
                    <div class="text-center">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-wifi text-red-600 text-sm"></i>
                        </div>
                        <p class="text-gray-700 text-sm">Tidak dapat menghubungi server. Coba lagi.</p>
                    </div>
                `,
                timer: 2000,
                timerProgressBar: true,
                customClass: {
                    popup: 'password-change-notification swal2-no-buttons',
                    container: 'password-change-notification-container'
                }
            });
            
            if (alpineComponent) {
                alpineComponent.newPassword = '';
                alpineComponent.confirmPassword = '';
            } else {
                form.querySelector('input[name="new_password"]').value = '';
                form.querySelector('input[name="pass_confirm"]').value = '';
            }
            
            const currentPasswordField = form.querySelector('input[name="current_password"]');
            if (currentPasswordField) {
                currentPasswordField.focus();
            }
        } finally {
            if (alpineComponent) {
                alpineComponent.isSubmitting = false;
                
                alpineComponent.$nextTick(() => {
                    if (alpineComponent.$refs.submitButton) {
                        alpineComponent.$refs.submitButton.removeAttribute('disabled');
                    }
                    
                    alpineComponent.forceFormUpdate();
                    
                    setTimeout(() => {
                        if (btn) {
                            btn.disabled = false;
                            btn.classList.remove('disabled');
                        }
                    }, 50);
                });
            } else {
                if (btn) {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                }
            }
        }
    });
});

document.addEventListener('alpine:initialized', () => {
  window.addEventListener('beforeunload', () => {
    if (Alpine.store('ui').modalLock) {
      Alpine.store('ui').unlockModal();
    }
  });
});

setInterval(async () => {
    const modal = document.getElementById('passwordEditModal');
    if (modal && !modal.hasAttribute('x-cloak')) {
        try {
            const response = await fetch('<?= site_url('get-csrf-token') ?>', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.token) {
                const csrfInput = document.getElementById('csrf_token');
                if (csrfInput) {
                    csrfInput.value = data.token;
                }
                
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.token);
                }
            }
        } catch (err) {
            // Silently fail
        }
    }
}, 300000);

// Override modal close behavior saat SweetAlert aktif
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const passwordModal = document.getElementById('passwordEditModal');
        const hasSweetAlert = document.querySelector('.swal2-container');
        
        if (passwordModal && hasSweetAlert && 
            window.getComputedStyle(passwordModal).display !== 'none') {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }
}, true);

document.addEventListener('click', function(e) {
    const passwordModal = document.getElementById('passwordEditModal');
    const hasSweetAlert = document.querySelector('.swal2-container');
    
    if (passwordModal && hasSweetAlert && 
        window.getComputedStyle(passwordModal).display !== 'none') {
        const backdrop = document.getElementById('passwordEditBackdrop');
        const closeBtn = passwordModal.querySelector('button[aria-label="Close"]');
        
        if ((e.target === backdrop || e.target === closeBtn) && hasSweetAlert) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }
}, true);
</script>