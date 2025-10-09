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

// Ambil data admin
$user           = service('auth')->user();
$adminProfileModel = new \App\Models\AdminProfileModel();
$ap             = $adminProfileModel->where('user_id', $user->id)->first();

// Tentukan nama admin
$adminName = $ap['name'] ?? 
            ($user->username ?? 
            (session('user_name') ?? 
            'Admin'));

$openNotifModal = !empty($openNotifModal);

// Foto profil
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
    <meta name="csrf-name" content="<?= csrf_token() ?>">
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