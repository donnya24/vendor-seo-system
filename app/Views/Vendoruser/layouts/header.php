<?php
// Fallback aman untuk var
$stats = array_merge([
  'leads_new' => 0,
  'leads_inprogress' => 0,
  'keywords_total' => 0,
  'unread' => 0,
  'leads_closing' => 0,
  'leads_today' => 0,
  'leads_closing_today' => 0,
], $stats ?? []);

$recentLeads    = $recentLeads   ?? [];
$topKeywords    = $topKeywords   ?? [];
$notifications  = $notifications ?? [];

$user           = service('auth')->user();
$vp             = $vp ?? [];
$vendorName     = $vendorName ?? ($vp['business_name'] ?? ($user->username ?? session('user_name') ?? 'Vendor'));
$openNotifModal = !empty($openNotifModal);

// Foto profil
$profileImage     = $profileImage ?? ($vp['profile_image'] ?? '');
$profileOnDisk    = $profileImage ? (FCPATH . 'uploads/vendor_profiles/' . $profileImage) : '';
$profileImagePath = ($profileImage && is_file($profileOnDisk))
  ? base_url('uploads/vendor_profiles/' . $profileImage)
  : base_url('assets/img/default-avatar.png');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-header" content="<?= csrf_header() ?>">
  <title><?= esc($title ?? 'Vendor Dashboard') ?> | Vendor Partnership SEO Performance</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- SweetAlert2 (global) -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

  <style>
    [x-cloak]{display:none!important}
    .sidebar{transition:all .25s ease}
    .nav-item{position:relative;transition:transform .14s ease,box-shadow .14s ease, background .14s ease}
    .nav-item:hover{transform:translateX(2px)}
    .nav-item.active{
      background:linear-gradient(90deg, rgba(59,130,246,.25), rgba(37,99,235,.35));
      box-shadow:inset 0 0 0 1px rgba(255,255,255,.08), 0 0 0 2px rgba(59,130,246,.2), 0 8px 28px rgba(30,64,175,.35)
    }
    .nav-item.active::before{
      content:"";position:absolute;left:-4px;top:10%;bottom:10%;width:6px;border-radius:9999px;
      background:radial-gradient(10px 60% at 50% 50%, rgba(191,219,254,.95), rgba(59,130,246,.4) 60%, transparent 70%);
      filter:blur(.2px)
    }
    .badge{font-size:.65rem;padding:.15rem .35rem}
    :root{ --sb-size: 8px; --sb-thumb: rgba(100,116,139,.45); }
    :where(*::-webkit-scrollbar){ width: var(--sb-size); height: var(--sb-size); background: transparent!important; }
    :where(*::-webkit-scrollbar-track){ background: transparent!important; }
    :where(*::-webkit-scrollbar-thumb){
      background: var(--sb-thumb)!important; border-radius: 9999px!important;
      border: 2px solid transparent!important; background-clip: padding-box!important;
    }
    :where(html, body, *){ scrollbar-width: thin; scrollbar-color: var(--sb-thumb) transparent; }

    /* ===== TOAST NOTIFICATION STYLES ===== */
    .custom-toast {
      position: fixed;
      top: 5rem;
      right: 1rem;
      z-index: 10000; /* Lebih tinggi dari SweetAlert (z-index: 1060) */
      min-width: 300px;
      max-width: 400px;
      border-radius: 0.75rem;
      padding: 1rem 1.25rem;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
      display: flex;
      align-items: center;
      gap: 0.75rem;
      transform: translateX(400px);
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      pointer-events: none;
      border-left: 4px solid;
    }

    .custom-toast.show {
      transform: translateX(0);
      opacity: 1;
      pointer-events: all;
    }

    .custom-toast.hide {
      transform: translateX(400px);
      opacity: 0;
    }

    .custom-toast.success {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
      border-left-color: #34d399;
    }

    .custom-toast.error {
      background: linear-gradient(135deg, #ef4444, #dc2626);
      color: white;
      border-left-color: #f87171;
    }

    .custom-toast.warning {
      background: linear-gradient(135deg, #f59e0b, #d97706);
      color: white;
      border-left-color: #fbbf24;
    }

    .custom-toast.info {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
      color: white;
      border-left-color: #60a5fa;
    }

    .custom-toast-icon {
      font-size: 1.25rem;
      width: 24px;
      text-align: center;
      flex-shrink: 0;
    }

    .custom-toast-content {
      flex: 1;
      min-width: 0;
    }

    .custom-toast-title {
      font-weight: 600;
      font-size: 0.875rem;
      margin-bottom: 0.25rem;
      line-height: 1.2;
    }

    .custom-toast-message {
      font-size: 0.8rem;
      opacity: 0.9;
      line-height: 1.3;
      word-wrap: break-word;
    }

    .custom-toast-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
      transition: all 0.2s ease;
      font-size: 0.75rem;
    }

    .custom-toast-close:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: scale(1.1);
    }

    .custom-toast-progress {
      position: absolute;
      bottom: 0;
      left: 0;
      height: 3px;
      background: rgba(255, 255, 255, 0.7);
      border-radius: 0 0 0 0.75rem;
      width: 100%;
      transform-origin: left;
      animation: progress linear;
    }

    @keyframes progress {
      from { transform: scaleX(1); }
      to { transform: scaleX(0); }
    }

    /* Responsive toast */
    @media (max-width: 640px) {
      .custom-toast {
        top: 1rem;
        right: 0.5rem;
        left: 0.5rem;
        min-width: auto;
        max-width: none;
        transform: translateY(-100px);
      }
      
      .custom-toast.show {
        transform: translateY(0);
      }
      
      .custom-toast.hide {
        transform: translateY(-100px);
      }
    }

    /* Additional utility classes */
    .line-clamp-1 {
      overflow: hidden;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 1;
    }

    .line-clamp-2 {
      overflow: hidden;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
    }

    .line-clamp-3 {
      overflow: hidden;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 3;
    }
  </style>

  <script>
  document.addEventListener('alpine:init', () => {
    const saved = localStorage.getItem('ui.sidebar');
    const defaultOpen = saved !== null ? (saved === '1') : (window.innerWidth >= 768);

    Alpine.store('ui', {
      sidebar: defaultOpen,
      _currentModal: null,
      loading: false,

      // ✅ MODAL LOCK SYSTEM
      modalLock: false,
      
      lockModal() {
        this.modalLock = true;
      },
      
      unlockModal() {
        this.modalLock = false;
      },
      
      // Modified modal setter with lock protection
      set modal(value) {
        if (this.modalLock && value === null) {
          console.log('Modal locked, preventing close');
          return;
        }
        this._currentModal = value;
      },
      
      get modal() {
        return this._currentModal;
      },

      _locked: false,
      _y: 0,
      lockScroll(){
        if (this._locked) return;
        this._y = window.scrollY || document.documentElement.scrollTop || 0;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${this._y}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        this._locked = true;
      },
      unlockScroll(){
        if (!this._locked) return;
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        window.scrollTo(0, this._y);
        this._locked = false;
      },

      toggleSidebar(){ 
        this.sidebar = !this.sidebar; 
        localStorage.setItem('ui.sidebar', this.sidebar ? '1' : '0'); 
      },
      openSidebar(){ 
        this.sidebar = true; 
        localStorage.setItem('ui.sidebar', '1'); 
      },
      closeSidebar(){ 
        this.sidebar = false; 
        localStorage.setItem('ui.sidebar', '0'); 
      },
    });

    Alpine.store('app', {
      stats: <?= json_encode($stats ?? []) ?>,
      recentLeads: <?= json_encode($recentLeads ?? []) ?>,
      topKeywords: <?= json_encode($topKeywords ?? []) ?>,
      init(){
        window.addEventListener('resize', () => {
          if (window.innerWidth >= 768 && !Alpine.store('ui').sidebar) {
            Alpine.store('ui').openSidebar();
          }
        });
      }
    });

    // ===== CUSTOM TOAST SYSTEM =====
    Alpine.store('toast', {
      queue: [],
      current: null,
      isShowing: false,

      // Main method to show toast
      show(type, title, message = '', duration = 5000) {
        const toast = { type, title, message, duration, id: Date.now() + Math.random() };
        
        this.queue.push(toast);
        
        if (!this.isShowing) {
          this.processQueue();
        }
        
        return toast.id;
      },

      // Process queue
      processQueue() {
        if (this.queue.length === 0 || this.isShowing) {
          return;
        }

        this.isShowing = true;
        this.current = this.queue.shift();

        // Create toast element
        this.createToastElement(this.current);

        // Auto remove after duration
        setTimeout(() => {
          this.hide();
        }, this.current.duration);
      },

      // Create toast DOM element
      createToastElement(toast) {
        // Remove existing toast
        const existing = document.getElementById('custom-toast-container');
        if (existing) existing.remove();

        const toastEl = document.createElement('div');
        toastEl.id = 'custom-toast-container';
        toastEl.className = `custom-toast ${toast.type} show`;
        toastEl.innerHTML = `
          <div class="custom-toast-icon">
            <i class="fas ${this.getIcon(toast.type)}"></i>
          </div>
          <div class="custom-toast-content">
            <div class="custom-toast-title">${toast.title}</div>
            ${toast.message ? `<div class="custom-toast-message">${toast.message}</div>` : ''}
          </div>
          <button class="custom-toast-close" onclick="Alpine.store('toast').hide()">
            <i class="fas fa-times"></i>
          </button>
          <div class="custom-toast-progress" style="animation-duration: ${toast.duration}ms"></div>
        `;

        document.body.appendChild(toastEl);
      },

      // Hide current toast
      hide() {
        const toastEl = document.getElementById('custom-toast-container');
        if (toastEl) {
          toastEl.classList.remove('show');
          toastEl.classList.add('hide');
          
          setTimeout(() => {
            toastEl.remove();
            this.isShowing = false;
            this.current = null;
            this.processQueue(); // Process next in queue
          }, 400);
        } else {
          this.isShowing = false;
          this.current = null;
          this.processQueue();
        }
      },

      // Helper methods
      getIcon(type) {
        const icons = {
          success: 'fa-check-circle',
          error: 'fa-exclamation-circle',
          warning: 'fa-exclamation-triangle',
          info: 'fa-info-circle'
        };
        return icons[type] || 'fa-info-circle';
      },

      // Convenience methods
      success(title, message = '', duration = 5000) {
        return this.show('success', title, message, duration);
      },

      error(title, message = '', duration = 5000) {
        return this.show('error', title, message, duration);
      },

      warning(title, message = '', duration = 5000) {
        return this.show('warning', title, message, duration);
      },

      info(title, message = '', duration = 5000) {
        return this.show('info', title, message, duration);
      },

      // Clear all toasts
      clear() {
        this.queue = [];
        this.hide();
      }
    });
  });

  // Global toast function untuk digunakan di mana saja
  function showToast(type, title, message = '', duration = 5000) {
    const toastStore = Alpine.store('toast');
    if (toastStore) {
      return toastStore.show(type, title, message, duration);
    }
    return null;
  }

  // Convenience global functions
  function toastSuccess(title, message = '', duration = 5000) {
    return showToast('success', title, message, duration);
  }

  function toastError(title, message = '', duration = 5000) {
    return showToast('error', title, message, duration);
  }

  function toastWarning(title, message = '', duration = 5000) {
    return showToast('warning', title, message, duration);
  }

  function toastInfo(title, message = '', duration = 5000) {
    return showToast('info', title, message, duration);
  }
  </script>
</head>

<body class="bg-gray-50 font-sans" x-data x-cloak>
<div class="flex min-h-screen overflow-x-hidden" x-init="$store.app.init()">

  <?php include_once(APPPATH . 'Views/vendoruser/layouts/sidebar.php'); ?>

  <div class="flex-1 flex flex-col min-h-0 w-0" :class="{'md:ml-64': $store.ui.sidebar}">
    <header class="fixed top-0 left-0 right-0 z-20 bg-white shadow-sm border-b border-gray-200"
            :class="$store.ui.sidebar ? 'md:ml-64' : ''">
      <div class="flex items-center justify-between h-14 px-3 sm:px-4 lg:px-6">
        <div class="flex items-center space-x-3">
          <!-- Toggle sidebar -->
          <button type="button"
                  class="p-2 -ml-2 hover:bg-gray-100 rounded-lg transition-colors"
                  @click.prevent.stop="Alpine.store('ui').sidebar = !Alpine.store('ui').sidebar"
                  aria-label="Toggle sidebar">
            <i class="fas fa-bars text-gray-700 text-lg"></i>
          </button>
          <h1 class="block sm:hidden text-lg font-semibold text-gray-900 truncate">
            <?= esc($title ?? 'Dashboard') ?>
          </h1>
        </div>

        <div class="flex items-center space-x-2 sm:space-x-3">
          <!-- 🔔 Notifikasi -->
          <div class="relative"
               x-data="{ notifOpen:false, notifModal:false }"
               x-init="notifModal = <?= $openNotifModal ? 'true' : 'false' ?>;"
               x-effect="
                 if (notifModal) { $store.ui.modal='notif'; $store.ui.lockScroll(); }
                 else { if ($store.ui.modal==='notif') $store.ui.modal=null; $store.ui.unlockScroll(); }
               "
               @keydown.escape.window="notifModal = false">
            <!-- Toggle dropdown -->
            <button type="button"
                    @click.prevent.stop="notifOpen = !notifOpen"
                    class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    :aria-expanded="notifOpen" aria-haspopup="true">
              <i class="fas fa-bell text-lg sm:text-base"></i>
              <?php if (($stats['unread'] ?? 0) > 0): ?>
                <span id="notifBadge"
                      class="absolute -top-0.5 -right-0.5 bg-red-500 text-white rounded-full min-w-[1.25rem] h-5 flex items-center justify-center text-xs font-medium px-1">
                  <?= min(99, (int)($stats['unread'] ?? 0)) ?><?= (int)($stats['unread'] ?? 0) > 99 ? '+' : '' ?>
                </span>
              <?php endif; ?>
            </button>

            <!-- Dropdown ringkas -->
            <div x-show="notifOpen" x-cloak
                 @click.outside="notifOpen = false"
                 class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-1rem)] bg-white rounded-lg shadow-lg border py-1 z-50 max-h-80 overflow-y-auto"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95 translate-y-1"
                 x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 transform scale-95 translate-y-1"
                 style="display:none;">
              <div class="px-4 py-2 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
              </div>

              <?php if (empty($notifications)): ?>
                <div class="px-4 py-8 text-sm text-gray-500 text-center">
                  <i class="fas fa-bell-slash text-2xl mb-2 text-gray-300"></i>
                  <p>Tidak ada notifikasi</p>
                </div>
              <?php else: ?>
                <?php $displayCount = min(5, count($notifications)); ?>
                <?php for ($i = 0; $i < $displayCount; $i++): $n = $notifications[$i]; ?>
                  <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-b-0 notif-item"
                       @click.prevent.stop="notifModal = true; notifOpen = false">
                    <div class="flex justify-between items-start space-x-2">
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 line-clamp-1"><?= esc($n['title'] ?? '-') ?></p>
                        <p class="text-xs text-gray-600 line-clamp-2 mt-1"><?= esc($n['message'] ?? '-') ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= esc($n['date'] ?? '-') ?></p>
                      </div>
                      <?php if (isset($n['is_read']) && !$n['is_read']): ?>
                        <div class="w-2 h-2 bg-blue-500 rounded-full shrink-0 mt-1"></div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endfor; ?>

                <div class="px-4 py-2 border-t border-gray-100 bg-gray-50">
                  <!-- Buka popup (BUKAN pindah halaman) -->
                  <button type="button"
                          @click.prevent.stop="notifModal = true; notifOpen = false"
                          class="w-full text-sm text-blue-600 hover:text-blue-800 font-medium py-1 rounded transition-colors">
                    Lihat Semua (<?= count($notifications) ?>)
                  </button>
                </div>
              <?php endif; ?>
            </div>

            <!-- Modal semua notifikasi -->
            <?= view('vendoruser/notifications/modal', ['notifications' => $notifications]) ?>
          </div>

          <!-- 👤 Dropdown Profil -->
          <div class="relative" x-data="{ open: false }">
            <button type="button"
                    @click.prevent.stop="open = !open"
                    class="flex items-center space-x-2 p-1 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    :aria-expanded="open" aria-haspopup="true">
              <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full overflow-hidden bg-gray-200 border border-gray-300 shrink-0">
                <?php if (!empty($profileImage) && is_file($profileOnDisk)): ?>
                  <img src="<?= $profileImagePath ?>" class="w-full h-full object-cover" alt="Foto Profil">
                <?php else: ?>
                  <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-user text-gray-500 text-sm"></i>
                  </div>
                <?php endif; ?>
              </div>
              <div class="hidden sm:flex items-center space-x-1">
                <span class="text-sm font-medium text-gray-700 truncate max-w-32 lg:max-w-none">
                  <?= esc($vendorName) ?>
                </span>
                <i class="fas fa-chevron-down text-xs text-gray-500" :class="{'rotate-180': open}"></i>
              </div>
            </button>

            <div x-show="open" x-cloak @click.outside="open = false"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95 translate-y-1"
                 x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 transform scale-95 translate-y-1">
              <div class="block sm:hidden px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900 truncate"><?= esc($vendorName) ?></p>
              </div>

              <button type="button"
                      @click.prevent.stop="$store.ui.modal='profileEdit'; open=false"
                      class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="fas fa-user-edit w-4 mr-3 text-gray-500"></i> Edit Profil
              </button>

              <button type="button"
                      @click.prevent.stop="$store.ui.modal='passwordEdit'; open=false"
                      class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="fas fa-lock w-4 mr-3 text-gray-500"></i> Ubah Password
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Spacer -->
    <div class="h-14 shrink-0" aria-hidden="true"></div>