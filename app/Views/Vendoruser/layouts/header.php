<?php
// ==== Fallback aman agar header tidak error di halaman mana pun ====
$JSON = static function($arr){
  return json_encode(
    $arr ?? [],
    JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT
  );
};

$stats = $stats ?? [
  'leads_new' => 0,
  'leads_inprogress' => 0,
  'keywords_total' => 0,
  'unread' => 0,
  'leads_closing' => 0,
  'leads_today' => 0,
  'leads_closing_today' => 0,
];

$recentLeads   = $recentLeads   ?? [];
$topKeywords   = $topKeywords   ?? [];
$notifications = $notifications ?? [];

$user          = service('auth')->user();
$vp            = $vp ?? [];
$vendorName    = $vendorName ?? ($vp['business_name'] ?? ($user->username ?? session('user_name') ?? 'Vendor'));

// Foto profil
$profileImage   = $profileImage ?? ($vp['profile_image'] ?? '');
$profileOnDisk  = $profileImage ? (FCPATH . 'uploads/vendor_profiles/' . $profileImage) : '';
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
    /* tetap butuh untuk Alpine x-cloak */
    [x-cloak]{display:none!important}

    /* (opsional) animasi/efek sidebar & menu; kalau mau 100% utility-only, bisa dihapus */
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

    /* ============ Scrollbar Global ============ */
  :root{
    --sb-size: 8px;
    --sb-thumb: rgba(100,116,139,.45); /* slate-ish */
  }

  :where(*::-webkit-scrollbar){
    width: var(--sb-size);
    height: var(--sb-size);
    background: transparent !important;
  }
  :where(*::-webkit-scrollbar-track){
    background: transparent !important;
  }
  :where(*::-webkit-scrollbar-thumb){
    background: var(--sb-thumb) !important;
    border-radius: 9999px !important;
    border: 2px solid transparent !important;
    background-clip: padding-box !important;
  }

  /* Firefox: tiap elemen scroll perlu property ini juga */
  :where(html, body, *){
    scrollbar-width: thin;
    scrollbar-color: var(--sb-thumb) transparent; /* thumb | track */
  }

  /* Opsional: khusus overlay gelap (biar track tak terasa "hitam pekat") */
  .bg-black\/50{
    /* Firefox */
    scrollbar-color: var(--sb-thumb) rgba(255,255,255,.06);
  }
  .bg-black\/50::-webkit-scrollbar-track{
    background: rgba(255,255,255,.06) !important; /* sangat tipis */
  }

  /* Touch-friendly improvements for notifications */
  .notif-item {
    transition: background-color 0.2s ease;
  }

  .notif-item:active {
    background-color: #f3f4f6;
  }

  /* Mobile sidebar overlay */
  .sidebar-overlay {
    backdrop-filter: blur(4px);
  }

  </style>

  <!-- Initialize Alpine.js stores -->
<script>
  document.addEventListener('alpine:init', () => {
    const saved = localStorage.getItem('ui.sidebar');
    const defaultOpen = saved !== null ? (saved === '1') : (window.innerWidth >= 768);

    Alpine.store('ui', {
      sidebar: defaultOpen,
      modal: null,
      toggleSidebar() {
        this.sidebar = !this.sidebar;
        localStorage.setItem('ui.sidebar', this.sidebar ? '1' : '0');
      },
      openSidebar() {
        this.sidebar = true;
        localStorage.setItem('ui.sidebar', '1');
      },
      closeSidebar() {
        this.sidebar = false;
        localStorage.setItem('ui.sidebar', '0');
      },
    });

    Alpine.store('app', {
      init() {
        // Kalau lebar jadi desktop dan sidebar sedang tertutup, boleh auto-buka.
        window.addEventListener('resize', () => {
          if (window.innerWidth >= 768 && !Alpine.store('ui').sidebar) {
            Alpine.store('ui').openSidebar();
          }
        });
      }
    });
  });
</script>
</head>

<body class="bg-gray-50 font-sans" 
      x-data 
      x-cloak 
      :class="{'overflow-hidden': $store.ui.modal}">


<div class="flex min-h-screen overflow-x-hidden" x-init="$store.app.init()">

  <?php // ===== Sidebar terpisah ===== ?>
  <?php include_once(APPPATH . 'Views/vendoruser/layouts/sidebar.php'); ?>

  <!-- Kolom kanan -->
  <div class="flex-1 flex flex-col min-h-0 w-0" :class="{'md:ml-64': $store.ui.sidebar}">

    <!-- ===== TOPBAR (HEADER FIXED) ===== -->
    <header class="fixed top-0 left-0 right-0 z-20 bg-white shadow-sm border-b border-gray-200"
            :class="$store.ui.sidebar ? 'md:ml-64' : ''">
      <!-- Responsive container with proper spacing -->
      <div class="flex items-center justify-between h-14 px-3 sm:px-4 lg:px-6">
        <!-- Left side: Toggle + Title (on mobile) -->
        <div class="flex items-center space-x-3">
          <!-- Toggle Sidebar -->
        <!-- WAS: @click="$store.ui.toggleSidebar()" -->
        <button
          class="p-2 -ml-2 hover:bg-gray-100 rounded-lg transition-colors"
          @click="Alpine.store('ui').sidebar = !Alpine.store('ui').sidebar"
          aria-label="Toggle sidebar">
          <i class="fas fa-bars text-gray-700 text-lg"></i>
        </button>
          
          <!-- Mobile title - hidden on larger screens -->
          <h1 class="block sm:hidden text-lg font-semibold text-gray-900 truncate">
            <?= esc($title ?? 'Dashboard') ?>
          </h1>
        </div>

        <!-- Right side: Notifications + Profile -->
        <div class="flex items-center space-x-2 sm:space-x-3">
          <!-- 🔔 Notifikasi -->
          <div class="relative" x-data="{ notifOpen:false, notifModal:false }">
            <button
              @click="notifOpen = !notifOpen; if (notifOpen) { markNotifAsRead?.(); }"
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

            <!-- Dropdown list (tetap gaya baru) -->
            <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
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
                <?php for ($i=0; $i<$displayCount; $i++): $n=$notifications[$i]; ?>
                  <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-b-0 notif-item"
                      @click="notifModal=true; notifOpen=false">
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
                  <button @click="notifModal=true; notifOpen=false"
                          class="w-full text-sm text-blue-600 hover:text-blue-800 font-medium py-1 rounded transition-colors">
                    Lihat Semua (<?= count($notifications) ?>)
                  </button>
                </div>
              <?php endif; ?>
            </div>

            <!-- Modal Semua Notifikasi (gaya baru + perilaku lama kembali) -->
            <div x-show="notifModal" x-cloak
                class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-4"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click.self="notifModal=false"
                style="display:none;">
              <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[85vh] flex flex-col"
                  @click.away="notifModal=false">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                  <h3 class="text-lg font-semibold text-gray-900">Semua Notifikasi</h3>
                  <button @click="notifModal=false"
                          class="p-1 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors">
                    <i class="fas fa-times text-lg"></i>
                  </button>
                </div>

                <div class="flex-1 overflow-y-auto">
                  <?php if (empty($notifications)): ?>
                    <div class="p-8 text-center text-gray-500">
                      <i class="fas fa-bell-slash text-3xl mb-3 text-gray-300"></i>
                      <p class="text-sm">Tidak ada notifikasi</p>
                    </div>
                  <?php else: ?>
                    <div class="divide-y divide-gray-100">
                      <?php foreach ($notifications as $n): ?>
                        <div class="p-4 hover:bg-gray-50 notif-item transition-colors">
                          <div class="flex justify-between items-start space-x-3">
                            <div class="flex-1 min-w-0">
                              <div class="flex items-start justify-between mb-1">
                                <p class="text-sm font-semibold text-gray-900 break-words flex-1"><?= esc($n['title'] ?? '-') ?></p>
                                <?php if (isset($n['is_read']) && !$n['is_read']): ?>
                                  <span class="ml-2 w-2 h-2 bg-blue-500 rounded-full shrink-0 mt-1"></span>
                                <?php endif; ?>
                              </div>
                              <p class="text-xs text-gray-600 break-words mb-2"><?= esc($n['message'] ?? '-') ?></p>
                              <p class="text-xs text-gray-400"><?= esc($n['date'] ?? '-') ?></p>
                            </div>
                          </div>

                          <!-- Aksi: pakai class utk SweetAlert & route lama -->
                          <div class="flex flex-wrap gap-2 mt-3 pt-2 border-t border-gray-100">
                            <?php if (isset($n['is_read']) && !$n['is_read']): ?>
                              <form method="post" action="<?= site_url('vendoruser/notifications/'.$n['id'].'/read') ?>">
                                <?= csrf_field() ?>
                                <button type="submit"
                                        class="text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-2 py-1 rounded transition-colors">
                                  Tandai Dibaca
                                </button>
                              </form>
                            <?php endif; ?>

                            <form method="post"
                                  action="<?= site_url('vendoruser/notifications/'.$n['id'].'/delete') ?>"
                                  class="js-notif-delete">
                              <?= csrf_field() ?>
                              <button type="submit"
                                      class="text-xs text-red-600 hover:text-red-800 hover:bg-red-50 px-2 py-1 rounded transition-colors">
                                Hapus
                              </button>
                            </form>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <?php if (!empty($notifications)): ?>
                <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                  <div class="flex flex-col sm:flex-row gap-2">
                    <form method="post" action="<?= site_url('vendoruser/notifications/mark-all') ?>" class="flex-1">
                      <?= csrf_field() ?>
                      <button type="submit" class="w-full px-3 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors">
                        Tandai Semua Dibaca
                      </button>
                    </form>
                    <form method="post" action="<?= site_url('vendoruser/notifications/delete-all') ?>"
                          class="flex-1 js-notif-delete-all">
                      <?= csrf_field() ?>
                      <button type="submit" class="w-full px-3 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition-colors">
                        Hapus Semua
                      </button>
                    </form>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- 👤 Dropdown Profil -->
          <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center space-x-2 p-1 hover:bg-gray-100 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    :aria-expanded="open" aria-haspopup="true">
              
              <!-- Profile image/avatar -->
              <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full overflow-hidden bg-gray-200 border border-gray-300 shrink-0">
                <?php if (!empty($profileImage) && is_file($profileOnDisk)): ?>
                  <img src="<?= $profileImagePath ?>" class="w-full h-full object-cover" alt="Foto Profil">
                <?php else: ?>
                  <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-user text-gray-500 text-sm"></i>
                  </div>
                <?php endif; ?>
              </div>
              
              <!-- Name and chevron - hidden on mobile -->
              <div class="hidden sm:flex items-center space-x-1">
                <span class="text-sm font-medium text-gray-700 truncate max-w-32 lg:max-w-none">
                  <?= esc($vendorName) ?>
                </span>
                <i class="fas fa-chevron-down text-xs text-gray-500" :class="{'rotate-180': open}"></i>
              </div>
            </button>

            <!-- Dropdown menu -->
            <div x-show="open" @click.away="open = false" x-cloak
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95 translate-y-1"
                 x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 transform scale-95 translate-y-1">
              
              <!-- Mobile: Show name -->
              <div class="block sm:hidden px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900 truncate"><?= esc($vendorName) ?></p>
              </div>
              
              <a href="<?= site_url('vendoruser/profile') ?>"
                 @click.prevent="$store.ui.modal='profileEdit'; open=false"
                 class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="fas fa-user-edit w-4 mr-3 text-gray-500"></i>
                Edit Profil
              </a>
              
              <a href="<?= site_url('vendoruser/password') ?>"
                 @click.prevent="$store.ui.modal='passwordEdit'; open=false"
                 class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                <i class="fas fa-lock w-4 mr-3 text-gray-500"></i>
                Ubah Password
              </a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Spacer for fixed header -->
    <div class="h-14 shrink-0" aria-hidden="true"></div>

    <?php
      // Modal profil & ubah password tersedia di semua halaman
      echo view('vendoruser/profile/edit', ['vp' => $vp]);
      echo view('vendoruser/profile/ubahpassword');
    ?>