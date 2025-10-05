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

// Ambil data admin - PERBAIKAN: Ambil dari admin_profiles
$user           = service('auth')->user();
$adminProfileModel = new \App\Models\AdminProfileModel();
$ap             = $adminProfileModel->where('user_id', $user->id)->first(); // Ambil data profile

// Tentukan nama admin - PERBAIKAN: Prioritaskan dari admin_profiles
$adminName = $ap['name'] ?? 
            ($user->username ?? 
            (session('user_name') ?? 
            'Admin'));

$openNotifModal = !empty($openNotifModal);

// Foto profil - PERBAIKAN: Ambil dari admin_profiles
$profileImage     = $ap['profile_image'] ?? '';
$profileOnDisk    = $profileImage ? (FCPATH . 'uploads/admin_profiles/' . $profileImage) : '';
$profileImagePath = ($profileImage && is_file($profileOnDisk))
    ? base_url('uploads/admin_profiles/' . $profileImage)
    : base_url('assets/img/default-avatar.png');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <title><?= esc($title ?? 'Admin Dashboard') ?> | Imersa Admin</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
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
        
        /* FIXED: Layout structure untuk menghilangkan ruang kosong */
        .main-content-container {
            width: 100%;
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }
        
        .main-content-container.sidebar-open {
            margin-left: 0;
        }

        @media (min-width: 768px) {
            .main-content-container.sidebar-open {
                margin-left: 16rem;
                width: calc(100% - 16rem);
            }
        }

        /* Header styling */
        .admin-header {
            background: linear-gradient(90deg, #1e40af 0%, #1e3a8a 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
            transition: all .3s ease;
            z-index: 20;
            width: 100%;
        }

        @media (min-width: 768px) {
            .admin-header.sidebar-open {
                left: 16rem;
                width: calc(100% - 16rem);
            }
        }
        
        /* Content area styling - FIXED */
        .content-area {
            width: 100%;
            padding-left: 0;
            padding-right: 0;
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            const saved = localStorage.getItem('ui.sidebar');
            const defaultOpen = saved !== null ? (saved === '1') : (window.innerWidth >= 768);

            Alpine.store('ui', {
                sidebar: defaultOpen,
                modal: null,
                loading: false,

                // ===== Scroll lock yang menjaga posisi (NO JUMP) =====
                _locked: false,
                _y: 0,
                lockScroll(){
                    if (this._locked) return;
                    this._y = window.scrollY || document.documentElement.scrollTop || 0;
                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${this._y}px`;
                    document.body.style.left = '0';
                    document.body.style.right = '0';
                    document.body.style.overflow = 'hidden';
                    this._locked = true;
                },
                unlockScroll(){
                    if (!this._locked) return;
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.left = '';
                    document.body.style.right = '';
                    document.body.style.overflow = '';
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
                }
            });

            Alpine.store('layout', {
                sidebarOpen: defaultOpen,
                isDesktop: window.innerWidth >= 768,
                init() {
                    window.addEventListener('resize', () => {
                        this.isDesktop = window.innerWidth >= 768;
                        if (this.isDesktop && !Alpine.store('ui').sidebar) {
                            Alpine.store('ui').openSidebar();
                        }
                    });
                }
            });
        });
    </script>
</head>

<body class="bg-gray-50 font-sans" x-data x-cloak>
<!-- Layout Container -->
<div class="flex min-h-screen overflow-x-hidden">

    <!-- Sidebar -->
    <div
        id="adminSidebar"
        class="sidebar z-40 text-white w-64 fixed inset-y-0 left-0 flex flex-col
               bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900
               transform transition-transform duration-300 ease-in-out shadow-2xl"
        :class="$store.ui.sidebar ? 'translate-x-0' : '-translate-x-full'"
        x-cloak
        role="navigation"
        aria-label="Sidebar utama"
        :aria-hidden="$store.ui.sidebar ? 'false' : 'true'"
        @click.outside="if (!$store.layout.isDesktop) $store.ui.sidebar = false"
        x-data="{
        activeMenu: '<?= url_is('admin/dashboard*') ? 'dashboard' : (url_is('admin/users*') ? 'users' : (url_is('admin/vendors*') ? 'vendors' : (url_is('admin/services*') ? 'services' : (url_is('admin/areas*') ? 'areas' : (url_is('admin/leads*') ? 'leads' : (url_is('admin/announcements*') ? 'announcements' : (url_is('admin/activity-logs*') ? 'activity-logs' : ''))))))) ?>',
        vendorSubmenu: false,
        userSubmenu: false,
        setActiveMenu(menu){ 
            this.activeMenu = menu; 
            sessionStorage.setItem('activeMenu', menu);
            if(menu !== 'users' && menu !== 'vendors') {
                this.userSubmenu = false;
                this.vendorSubmenu = false;
            }
        },
        toggleUserSubmenu() {
            this.userSubmenu = !this.userSubmenu;
            this.vendorSubmenu = false;
            if(this.userSubmenu) {
                this.activeMenu = 'users';
                sessionStorage.setItem('activeMenu', 'users');
            }
        },
        toggleVendorSubmenu() {
            this.vendorSubmenu = !this.vendorSubmenu;
            this.userSubmenu = false;
            if(this.vendorSubmenu) {
                this.activeMenu = 'vendors';
                sessionStorage.setItem('activeMenu', 'vendors');
            }
        },
        init(){ 
            const s=sessionStorage.getItem('activeMenu'); 
            if(s) this.activeMenu=s;
            const path = window.location.pathname;
            if(path.includes('/admin/users') || path.includes('/admin/user')) {
                this.activeMenu = 'users';
                this.userSubmenu = true;
            }
            if(path.includes('/admin/vendors') || path.includes('/admin/services') || path.includes('/admin/areas')) {
                this.activeMenu = 'vendors';
                this.vendorSubmenu = true;
            }
        }
    }"
        @keydown.escape.window="if (!$store.layout.isDesktop) $store.ui.closeSidebar()"
    >
        <!-- Header -->
        <div class="px-5 py-4 border-b border-blue-600/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-semibold text-sm">Admin Panel</h2>
                        <p class="text-blue-200 text-xs">Control Center</p>
                    </div>
                </div>
                <button
                    class="md:hidden text-blue-200 hover:text-white p-2 rounded-lg hover:bg-blue-600/50 transition-all"
                    @click="$store.ui.closeSidebar()"
                    aria-label="Tutup sidebar"
                    type="button"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-3">
            <!-- Dashboard -->
            <a href="<?= site_url('admin/dashboard'); ?>"
               class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                      hover:bg-blue-700/50 text-blue-100 hover:text-white"
               :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'dashboard'}"
               @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('dashboard')">
                <i class="fas fa-tachometer-alt w-5 text-center"></i>
                <span class="text-sm font-medium">Dashboard</span>
            </a>

            <!-- Divider -->
            <div class="my-4 border-t border-blue-600/30"></div>

            <!-- Management Users -->
            <div class="mb-1">
                <button 
                    @click="toggleUserSubmenu()"
                    class="group w-full flex items-center gap-3 py-2.5 px-3 rounded-lg transition-all
                           hover:bg-blue-700/50 text-blue-100 hover:text-white"
                    :class="{'bg-blue-700/50 text-white': activeMenu === 'users'}"
                >
                    <i class="fas fa-user-shield w-5 text-center"></i>
                    <span class="text-sm font-medium flex-1 text-left">Management Users</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                       :class="{'rotate-180': userSubmenu}"></i>
                </button>
                
                <div x-show="userSubmenu" x-collapse class="mt-1 space-y-1">
                    <a href="<?= site_url('admin/users?tab=seo'); ?>"
                       class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                              hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                       :class="{'bg-blue-600/50 text-white': window.location.search.includes('tab=seo') || (window.location.pathname.includes('/admin/users') && !window.location.search.includes('tab=vendor'))}"
                       @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false;">
                        <i class="fas fa-users w-4 text-center text-xs"></i>
                        <span>User Tim SEO</span>
                    </a>
                    
                    <a href="<?= site_url('admin/users?tab=vendor'); ?>"
                       class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                              hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                       :class="{'bg-blue-600/50 text-white': window.location.search.includes('tab=vendor')}"
                       @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false;">
                        <i class="fas fa-store w-4 text-center text-xs"></i>
                        <span>User Vendor</span>
                    </a>
                </div>
            </div>

            <!-- Management Vendor -->
            <div class="mb-1">
                <button 
                    @click="toggleVendorSubmenu()"
                    class="group w-full flex items-center gap-3 py-2.5 px-3 rounded-lg transition-all
                           hover:bg-blue-700/50 text-blue-100 hover:text-white"
                    :class="{'bg-blue-700/50 text-white': activeMenu === 'vendors'}"
                >
                    <i class="fas fa-store-alt w-5 text-center"></i>
                    <span class="text-sm font-medium flex-1 text-left">Management Vendor</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                       :class="{'rotate-180': vendorSubmenu}"></i>
                </button>
                
                <div x-show="vendorSubmenu" x-collapse class="mt-1 space-y-1">
                    <!-- Area Vendor -->
                    <a href="<?= site_url('admin/areas'); ?>"
                       class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                              hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                       :class="{'bg-blue-600/50 text-white': window.location.pathname.includes('/admin/areas')}"
                       @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false;">
                        <i class="fas fa-map-marked-alt w-4 text-center text-xs"></i>
                        <span>Area Vendor</span>
                    </a>
                    
                    <!-- Layanan & Produk Vendor -->
                    <a href="<?= site_url('admin/services'); ?>"
                       class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                              hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                       :class="{'bg-blue-600/50 text-white': window.location.pathname.includes('/admin/services')}"
                       @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false;">
                        <i class="fas fa-box-open w-4 text-center text-xs"></i>
                        <span>Layanan & Produk Vendor</span>
                    </a>
                </div>
            </div>

            <!-- Leads -->
            <a href="<?= site_url('admin/leads'); ?>"
               class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                      hover:bg-blue-700/50 text-blue-100 hover:text-white"
               :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'leads'}"
               @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('leads')">
                <i class="fas fa-list w-5 text-center"></i>
                <span class="text-sm font-medium">Leads</span>
            </a>

            <!-- Commissions -->
            <a href="<?= site_url('admin/commissions'); ?>"
               class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                      hover:bg-blue-700/50 text-blue-100 hover:text-white"
               :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'commissions'}"
               @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('commissions')">
                <i class="fas fa-coins w-5 text-center"></i>
                <span class="text-sm font-medium">Commissions</span>
            </a>

            <!-- Divider -->
            <div class="my-4 border-t border-blue-600/30"></div>

            <!-- Announcements -->
            <a href="<?= site_url('admin/announcements'); ?>"
               class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                      hover:bg-blue-700/50 text-blue-100 hover:text-white"
               :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'announcements'}"
               @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('announcements')">
                <i class="fas fa-bullhorn w-5 text-center"></i>
                <span class="text-sm font-medium">Announcements</span>
            </a>

            <!-- Activity Logs -->
            <a href="<?= site_url('admin/activity-logs'); ?>"
               class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                      hover:bg-blue-700/50 text-blue-100 hover:text-white"
               :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'activity-logs'}"
               @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('activity-logs')">
                <i class="fas fa-history w-5 text-center"></i>
                <span class="text-sm font-medium">Activity Logs</span>
            </a>
        </nav>

        <!-- Footer -->
        <div class="border-t border-blue-600/30 p-3">
            <div x-data="{ showConfirm: false }">
                <button
                    type="button"
                    class="group w-full flex items-center gap-3 py-2.5 px-3 rounded-lg transition-all
                           hover:bg-red-600/20 text-blue-100 hover:text-red-300"
                    @click="showConfirm = true; if (!$store.layout?.isDesktop) $store.ui.sidebar = false"
                >
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span class="text-sm font-medium flex-1 text-left">Logout</span>
                </button>

                <!-- Logout Modal -->
                <template x-teleport="body">
                    <div
                        x-show="showConfirm"
                        x-transition.opacity
                        @keydown.escape.window="showConfirm = false"
                        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div class="absolute inset-0" @click="showConfirm = false"></div>

                        <div class="relative bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm"
                             x-transition.scale.origin.center>
                            <div class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-full mx-auto mb-4">
                                <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 text-center mb-2">Konfirmasi Logout</h2>
                            <p class="text-center text-gray-600 mb-6">Anda akan keluar dari panel admin</p>
                            
                            <div class="flex gap-3">
                                <button
                                    type="button"
                                    class="flex-1 px-4 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition-colors"
                                    @click="showConfirm = false"
                                >Batal</button>

                                <form method="post" action="<?= site_url('logout'); ?>" class="flex-1">
                                    <?= csrf_field() ?>
                                    <button
                                        type="submit"
                                        class="w-full px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors"
                                    >Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <p class="text-[10px] text-blue-200/70 mt-3 text-center">
                © <?= date('Y'); ?> Imersa
            </p>
        </div>
    </div>

    <!-- Overlay mobile (klik untuk tutup) -->
    <div
        x-show="$store.ui.sidebar"
        @click="$store.ui.sidebar = false"
        class="fixed inset-0 z-30 md:hidden bg-black/40"
        x-transition.opacity
        style="display:none;"
        aria-hidden="true">
    </div>

    <!-- Main Content Area - FIXED: Menghilangkan ruang kosong -->
    <div class="main-content-container flex-1 flex flex-col min-h-screen" :class="{'sidebar-open': $store.ui.sidebar}">
        
        <!-- Header -->
        <header class="admin-header fixed top-0 z-20 shadow-sm"
                :class="{'sidebar-open': $store.ui.sidebar}">
            <div class="flex items-center justify-between h-14 px-3 sm:px-4 lg:px-6">
                <div class="flex items-center space-x-3">
                    <!-- Toggle sidebar -->
                    <button type="button"
                            class="p-2 -ml-2 hover:bg-white/12 rounded-lg transition-colors text-white"
                            @click.prevent.stop="$store.ui.toggleSidebar()"
                            aria-label="Toggle sidebar">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <h1 class="block sm:hidden text-lg font-semibold text-white truncate">
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
                                class="relative p-2 text-white/90 hover:text-white hover:bg-white/12 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 focus:ring-offset-1 focus:ring-offset-transparent"
                                :aria-expanded="notifOpen" aria-haspopup="true">
                            <i class="fas fa-bell text-lg sm:text-base"></i>
                            <?php if (($stats['unread'] ?? 0) > 0): ?>
                                <span id="notifBadge"
                                    class="absolute -top-0.5 -right-0.5 bg-red-500 text-white rounded-full min-w-[1.25rem] h-5 flex items-center justify-center text-xs font-medium px-1">
                                    <?= min(99, (int)($stats['unread'] ?? 0)) ?><?= (int)($stats['unread'] ?? 0) > 99 ? '+' : '' ?>
                                </span>
                            <?php endif; ?>
                        </button>

                        <!-- Dropdown ringkas - PERBAIKAN: z-index dan positioning -->
                        <div x-show="notifOpen" x-cloak
                            @click.outside="notifOpen = false"
                            class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-1rem)] bg-white rounded-lg shadow-lg border py-1 z-[60] max-h-80 overflow-y-auto"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95 translate-y-1"
                            x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 transform scale-95 translate-y-1"
                            style="display:none; position: fixed; right: 1rem;">
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
                        <?= view('admin/notifications/modal', ['notifications' => $notifications]) ?>
                    </div>

                    <!-- 👤 Dropdown Profil -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button"
                                @click.prevent.stop="open = !open"
                                class="flex items-center space-x-2 p-1 hover:bg-white/12 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 focus:ring-offset-1 focus:ring-offset-transparent"
                                :aria-expanded="open" aria-haspopup="true">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full overflow-hidden bg-white/20 border border-white/30 shrink-0">
                                <?php if (!empty($profileImage) && is_file($profileOnDisk)): ?>
                                    <img src="<?= $profileImagePath ?>" class="w-full h-full object-cover" alt="Foto Profil">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-user text-white/90 text-sm"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="hidden sm:flex items-center space-x-1">
                                <span class="text-sm font-medium text-white truncate max-w-32 lg:max-w-none">
                                    <?= esc($adminName) ?>
                                </span>
                                <i class="fas fa-chevron-down text-xs text-white/70" :class="{'rotate-180': open}"></i>
                            </div>
                        </button>

                        <!-- PERBAIKAN: z-index dan positioning untuk dropdown profil -->
                        <div x-show="open" x-cloak @click.outside="open = false"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-[60]"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95 translate-y-1"
                            x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 transform scale-95 translate-y-1"
                            style="position: fixed; right: 1rem;">
                            <div class="block sm:hidden px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900 truncate"><?= esc($adminName) ?></p>
                            </div>
                            <!-- Ganti link di header menjadi: -->
                            <a href="javascript:void(0)" onclick="openEditProfileModal()"
                            class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-user-edit w-4 mr-3 text-gray-500"></i> Edit Profil
                            </a>

                            <a href="javascript:void(0)" onclick="openPasswordModal()"
                            class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-lock w-4 mr-3 text-gray-500"></i> Ubah Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Spacer untuk header -->
        <div class="h-14 shrink-0" aria-hidden="true"></div>

        <!-- Content Area - FIXED: Menghilangkan padding/margin yang tidak perlu -->
        <div class="content-area flex-1">
            <!-- Konten utama akan dimasukkan di sini -->
<script>
// === SweetAlert Mini Configuration ===
const swalConfig = {
    width: '320px',
    padding: '1.25rem',
    customClass: {
        popup: 'rounded-xl shadow-md',
        title: 'text-base font-semibold mb-2',
        htmlContainer: 'text-sm mb-3',
        confirmButton: 'px-4 py-2 text-sm rounded-lg',
        icon: 'text-lg mb-2'
    }
};

// === Function untuk membuka modal edit profile ===
async function openEditProfileModal() {
    try {
        const loadingSwal = Swal.fire({
            title: 'Memuat...',
            text: 'Sedang memuat form edit profil',
            allowOutsideClick: false,
            ...swalConfig,
            didOpen: () => Swal.showLoading()
        });

        const response = await fetch('<?= site_url('admin/profile/edit-modal') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const result = await response.json();
        await loadingSwal.close();
        
        if (result.status === 'success') {
            closeAllModals();
            document.body.insertAdjacentHTML('beforeend', result.data.html);
            initProfileModal();
        } else {
            throw new Error('Gagal memuat modal');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Tidak dapat memuat form edit profil',
            ...swalConfig
        });
    }
}

// === Function untuk membuka modal ubah password ===
async function openPasswordModal() {
    try {
        const loadingSwal = Swal.fire({
            title: 'Memuat...',
            text: 'Sedang memuat form ubah password',
            allowOutsideClick: false,
            ...swalConfig,
            didOpen: () => Swal.showLoading()
        });

        const response = await fetch('<?= site_url('admin/profile/password-modal') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        const result = await response.json();
        await loadingSwal.close();
        
        if (result.status === 'success') {
            closeAllModals();
            document.body.insertAdjacentHTML('beforeend', result.data.html);
            initProfileModal();
        } else {
            throw new Error('Gagal memuat modal');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Tidak dapat memuat form ubah password',
            ...swalConfig
        });
    }
}

// === Function untuk menutup semua modal ===
function closeAllModals() {
    const modals = document.querySelectorAll('#profileModalBackdrop');
    modals.forEach(modal => {
        modal.remove();
    });
}

// === Function untuk menutup modal spesifik ===
function closeProfileModal() {
    closeAllModals();
}

// === Inisialisasi Modal ===
function initProfileModal() {
    console.log('Initializing profile modal...');
    const modalBackdrop = document.getElementById('profileModalBackdrop');
    if (!modalBackdrop) {
        console.error('Modal backdrop not found');
        return;
    }

    // Function untuk menutup modal dengan promise
    const closeModal = function() {
        return new Promise((resolve) => {
            console.log('Closing modal...');
            const backdrop = document.getElementById('profileModalBackdrop');
            if (backdrop) {
                backdrop.remove();
            }
            // Beri waktu untuk DOM update
            setTimeout(resolve, 50);
        });
    };

    // Tombol close (X)
    modalBackdrop.querySelectorAll('.close-modal-btn').forEach(btn => {
        // Hapus event listener lama dan tambah yang baru
        btn.onclick = closeModal;
    });

    // Tombol batal
    modalBackdrop.querySelectorAll('.cancel-modal-btn').forEach(btn => {
        btn.onclick = closeModal;
    });

    // Klik backdrop
    modalBackdrop.onclick = function(e) {
        if (e.target === this) {
            closeModal();
        }
    };

    // Tombol ESC
    const handleEsc = function(e) {
        if (e.key === 'Escape') {
            closeModal();
            document.removeEventListener('keydown', handleEsc);
        }
    };
    document.addEventListener('keydown', handleEsc);

    // === Handle form edit profil ===
    const profileEditForm = document.getElementById('profileEditForm');
    if (profileEditForm) {
        profileEditForm.onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const result = await response.json();

                // TUTUP MODAL TERLEBIH DAHULU sebelum show alert
                await closeModal();

                if (result.status === 'success') {
                    // Tunggu sebentar untuk memastikan modal sudah tertutup
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: result.message,
                            confirmButtonText: 'OK',
                            timer: 3000,
                            ...swalConfig
                        }).then(() => {
                            // Refresh page untuk edit profile
                            window.location.reload();
                        });
                    }, 100);
                } else {
                    let errorMessage = result.errors 
                        ? Object.values(result.errors).join('<br>') 
                        : (result.message || 'Terjadi kesalahan');
                    
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: errorMessage,
                            confirmButtonText: 'OK',
                            ...swalConfig
                        });
                    }, 100);
                }
            } catch (err) {
                console.error(err);
                await closeModal();
                
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                        confirmButtonText: 'OK',
                        ...swalConfig
                    });
                }, 100);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        };
    }

    // === Handle form ubah password ===
   // === Handle form ubah password ===
const passwordForm = document.getElementById('passwordForm');
if (passwordForm) {
    passwordForm.onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const result = await response.json();

            // TUTUP MODAL TERLEBIH DAHULU sebelum show alert
            await closeModal();

            if (result.status === 'success') {
                // CEK APAKAH PERLU LOGOUT OTOMATIS
                if (result.logout_redirect) {
                    // Tampilkan pesan sukses dengan countdown
                    let timeLeft = 3;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Berhasil Diubah',
                        html: `Password Anda berhasil diperbarui. <br><strong>Anda akan logout otomatis dalam <span id="countdown">${timeLeft}</span> detik...</strong>`,
                        showConfirmButton: false,
                        timer: timeLeft * 1000,
                        ...swalConfig,
                        didOpen: () => {
                            const timer = document.getElementById('countdown');
                            const timerInterval = setInterval(() => {
                                timeLeft--;
                                if (timer) timer.textContent = timeLeft;
                                
                                if (timeLeft <= 0) {
                                    clearInterval(timerInterval);
                                }
                            }, 1000);
                        }
                    }).then(() => {
                        // Redirect ke logout setelah timer habis
                        window.location.href = result.redirect_url || '<?= site_url('logout') ?>';
                    });
                } else {
                    // Jika tidak perlu logout, tampilkan pesan biasa
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: result.message,
                            confirmButtonText: 'OK',
                            timer: 3000,
                            ...swalConfig
                        });
                    }, 100);
                }
            } else {
                let errorMessage = result.errors 
                    ? Object.values(result.errors).join('<br>') 
                    : (result.message || 'Terjadi kesalahan');
                
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: errorMessage,
                        confirmButtonText: 'OK',
                        ...swalConfig
                    });
                }, 100);
            }
        } catch (err) {
            console.error(err);
            await closeModal();
            
            setTimeout(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                    confirmButtonText: 'OK',
                    ...swalConfig
                });
            }, 100);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    };
}
    // === Preview gambar ===
    const profileImageInput = document.getElementById('profileImageInput');
    if (profileImageInput) {
        profileImageInput.onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profileImagePreview');
                    if (preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };
    }

    console.log('Profile modal initialized successfully');
}

// === Handle Alpine store untuk modal management ===
document.addEventListener('alpine:initialized', () => {
    // Pastikan Alpine store tersedia untuk modal management
    if (window.Alpine && Alpine.store('ui')) {
        const uiStore = Alpine.store('ui');
        
        // Override modal close behavior jika diperlukan
        const originalCloseModal = uiStore.closeModal;
        uiStore.closeModal = function() {
            closeAllModals();
            if (originalCloseModal) {
                originalCloseModal.call(this);
            }
        };
    }
});

// Debug saat DOM ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, modal functions ready');
});
</script>