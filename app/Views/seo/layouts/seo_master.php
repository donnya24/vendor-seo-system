<?php
// ==== Bootstrap data agar modal SELALU punya $sp & $userEmail ==== //
 $auth = service('auth');
 $user = $auth ? $auth->user() : null;

// Ambil $sp jika belum ada (fallback query ringan)
if (!isset($sp) || !is_array($sp)) {
    $sp = [];
    if ($user) {
        $db = db_connect();
        $sp = $db->table('seo_profiles')
            ->where('user_id', (int)$user->id)
            ->get()
            ->getRowArray() ?? [];
    }
}

// Jadikan $profile = $sp jika $profile belum ada (untuk header/topbar)
 $profile = $profile ?? $sp ?? [];

// Email prioritas dari controller ($userEmail). Jika kosong, fallback ke auth_identities.secret
if (!isset($userEmail) || $userEmail === null) {
    $userEmail = '';
    if ($user) {
        $db = isset($db) ? $db : db_connect();
        $row = $db->table('auth_identities')
            ->select('secret')
            ->where('user_id', (int)$user->id)
            ->whereIn('type', ['email', 'email_password'])
            ->orderBy('id', 'desc')
            ->get()
            ->getRowArray();
        $userEmail = $row['secret'] ?? '';
    }
}

// Foto profil di header (pakai ikon jika tidak ada foto)
 $defaultAvatar   = base_url('assets/img/default-avatar.png'); 
 $profileImage    = $profile['profile_image'] ?? null;
 $hasProfileImage = false;
if ($profileImage && is_file(FCPATH . 'uploads/seo_profiles/' . $profileImage)) {
    $hasProfileImage  = true;
    $profileImagePath = base_url('uploads/seo_profiles/' . $profileImage);
} else {
    $profileImagePath = $defaultAvatar;
}

// ===== Notifikasi =====
 $notifItems       = []; // untuk dropdown
 $allNotifications = []; // untuk modal
 $unreadCount      = 0;

// Prioritaskan menggunakan data dari controller jika tersedia
if (isset($notifications) && is_array($notifications)) {
    // Data sudah disiapkan oleh controller
    $allNotifications = $notifications;
    $notifItems = array_slice($allNotifications, 0, 10);
    
    // Hitung unread
    foreach ($allNotifications as $n) {
        if (empty($n['is_read'])) $unreadCount++;
    }
} 
// Jika tidak ada data dari controller, ambil dari database
else if ($user) {
    $db  = isset($db) ? $db : db_connect();

    if ($db->tableExists('notifications')) {
        $uid   = (int)$user->id;
        $seoId = (int)($sp['id'] ?? 0);

        // Query semua notifikasi user SEO (untuk modal)
        $qbAll = $db->table('notifications n');
        $qbAll->select('
                n.id,
                n.user_id,
                n.seo_id,
                n.type,
                n.title,
                n.message,
                n.is_read AS n_is_read,
                n.created_at,
                nus.is_read AS s_is_read,
                nus.hidden  AS s_hidden
            ')
            ->join('notification_user_state nus', 'nus.notification_id = n.id AND nus.user_id = '.$uid, 'left')
            ->groupStart()
                ->where('n.user_id', $uid) // private
                ->orGroupStart()           // SEO-wide
                    ->where('n.user_id', null)
                    ->where('n.seo_id', $seoId)
                ->groupEnd()
                ->orGroupStart()           // Global announcement
                    ->where('n.user_id', null)
                    ->where('n.seo_id', null)
                    ->where('n.type', 'announcement')
                ->groupEnd()
            ->groupEnd()
            ->groupStart()
                ->where('nus.hidden', 0)
                ->orWhere('nus.hidden IS NULL', null, false)
            ->groupEnd()
            ->orderBy('n.id', 'DESC');

        $allNotifications = $qbAll->get()->getResultArray() ?? [];

        // Normalisasi
        foreach ($allNotifications as &$it) {
            $isPrivate     = (int)($it['user_id'] ?? 0) === $uid;
            $it['is_read'] = $isPrivate ? (int)($it['n_is_read'] ?? 0) : (int)($it['s_is_read'] ?? 0);
            unset($it['n_is_read'], $it['s_is_read'], $it['s_hidden']);
        }
        unset($it);

        // Ambil 10 terakhir untuk dropdown
        $notifItems = array_slice($allNotifications, 0, 10);
    }
    
    // Hitung unread
    foreach ($allNotifications as $n) {
        if (empty($n['is_read'])) $unreadCount++;
    }
}

// Ambil SEMUA notifikasi (hasil query di atas)
 $modalNotifications = $allNotifications;

// Jangan fallback ke $notifItems agar modal tidak cuma 10 notif
 $openNotifModal = !empty($openNotifModal);
?>
<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false, profileOpen: false, notifOpen: false, notifModalOpen: false }">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($title ?? 'SEO Dashboard') ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex h-screen overflow-hidden">
  <!-- OVERLAY MOBILE -->
  <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-black/40 z-30 md:hidden" @click="sidebarOpen=false"></div>

  <!-- SIDEBAR - DIPISAH KE FILE TERPISAH -->
  <?= $this->include('seo/layouts/sidebar') ?>

  <!-- MAIN -->
  <div class="flex-1 flex flex-col md:ml-64">
    <!-- TOPBAR -->
    <header class="bg-white shadow-sm fixed top-0 left-0 right-0 z-20 md:ml-64 border-b border-gray-200">
      <div class="flex items-center justify-between px-4 py-3">
        <button class="md:hidden text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100" @click="sidebarOpen=!sidebarOpen">
          <i class="fas fa-bars text-lg"></i>
        </button>

        <div class="flex items-center gap-3 ml-auto">
          <!-- Notifikasi -->
          <div class="relative">
            <button type="button" @click.stop="notifOpen=!notifOpen" @keydown.escape.window="notifOpen=false"
              class="relative text-gray-600 hover:text-gray-900 p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
              <i class="fas fa-bell text-lg"></i>
              <?php if ($unreadCount > 0): ?>
              <span class="absolute top-1 right-1 inline-flex items-center justify-center
                           text-[10px] leading-none font-semibold text-white bg-red-500 rounded-full
                           w-4 h-4 shadow-sm">
                <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
              </span>
              <?php endif; ?>
            </button>
            <!-- Dropdown -->
            <div x-show="notifOpen" x-cloak @click.outside="notifOpen=false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800">Notifikasi</span>
                <a href="#" @click.prevent.stop="notifOpen=false; notifModalOpen=true"
                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">Lihat semua</a>
              </div>
              <?php if (!empty($notifItems)): ?>
                <ul class="max-h-80 overflow-auto divide-y divide-gray-100">
                  <?php foreach ($notifItems as $it): ?>
                  <li class="px-4 py-3 <?= empty($it['is_read'])?'bg-blue-50':'' ?> hover:bg-gray-50 transition-colors">
                    <div class="flex items-start">
                      <div class="flex-shrink-0 pt-1">
                        <div class="w-2 h-2 rounded-full <?= empty($it['is_read'])?'bg-blue-500':'bg-gray-300' ?>"></div>
                      </div>
                      <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate"><?= esc($it['title'] ?? 'Notifikasi') ?></p>
                        <p class="text-xs text-gray-600 mt-1"><?= esc($it['message'] ?? '') ?></p>
                        <p class="text-[11px] text-gray-400 mt-1"><?= !empty($it['created_at']) ? date('d M Y, H:i', strtotime($it['created_at'])) : '' ?></p>
                      </div>
                    </div>
                  </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="p-6 text-center text-sm text-gray-500">
                  <i class="fas fa-bell-slash text-gray-300 text-2xl mb-2"></i>
                  <p>Belum ada notifikasi.</p>
                </div>
              <?php endif; ?>
              <div class="px-4 py-3 border-t border-gray-100 text-right">
                <button type="button" class="text-xs text-gray-600 hover:text-gray-800 font-medium" @click="notifOpen=false">Tutup</button>
              </div>
            </div>
          </div>

          <!-- Profile -->
          <div class="relative">
            <button type="button" @click.stop="profileOpen=!profileOpen" class="flex items-center gap-3 focus:outline-none">
              <div class="h-10 w-10 rounded-full bg-gray-200 overflow-hidden flex items-center justify-center shadow-sm border border-gray-300">
                <?php if ($hasProfileImage): ?>
                  <img src="<?= esc($profileImagePath) ?>" alt="Profil" class="w-full h-full object-cover">
                <?php else: ?>
                  <i class="fas fa-user text-gray-500"></i>
                <?php endif; ?>
              </div>
              <div class="hidden md:block text-left">
                <div class="text-sm font-medium text-gray-900">
                  <?= esc($profile['name'] ?? session()->get('user_name') ?? 'SEO User') ?>
                </div>
                <div class="text-xs text-gray-500 truncate max-w-[120px]">
                  <?= esc($userEmail) ?>
                </div>
              </div>
              <i class="fas fa-chevron-down text-xs text-gray-500"></i>
            </button>
            <div x-show="profileOpen" x-cloak @click.outside="profileOpen=false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1.5 z-50">
              <a href="#" @click.prevent.stop="$store.ui.modal='seoProfileEdit'; $nextTick(()=>{profileOpen=false})"
                 class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-user-edit mr-2 text-gray-500"></i> Edit Profil
              </a>
              <a href="#" @click.prevent.stop="$store.ui.modal='seoPasswordEdit'; $nextTick(()=>{profileOpen=false})"
                 class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-key mr-2 text-gray-500"></i> Ubah Password
              </a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="h-16"></div>

    <!-- CONTENT -->
    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
      <?= $this->renderSection('content') ?>
    </main>
  </div>
</div>

<!-- MODAL PROFIL & PASSWORD -->
<?= $this->include('Seo/profile/modal'); ?>

<!-- MODAL NOTIFIKASI -->
<div x-show="notifModalOpen" x-cloak
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
     @click.self="notifModalOpen=false"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
     
  <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl"
       @click.stop
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 scale-95 translate-y-4"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-4">

    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900 flex items-center">
        <i class="fas fa-bell text-blue-600 mr-2"></i>
        Semua Notifikasi
      </h3>
      <button type="button"
              @click.prevent.stop="notifModalOpen=false"
              class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100 transition-colors">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="max-h-[60vh] overflow-y-auto divide-y divide-gray-100">
      <?php if (empty($modalNotifications)): ?>
        <div class="p-8 text-center text-sm text-gray-500">
          <i class="fas fa-bell-slash text-gray-300 text-4xl mb-3"></i>
          <p class="text-lg font-medium text-gray-900">Tidak ada notifikasi</p>
          <p class="mt-1">Anda tidak memiliki notifikasi saat ini</p>
        </div>
      <?php else: ?>
        <?php foreach ($modalNotifications as $n): ?>
          <div class="p-4 hover:bg-gray-50 transition-colors">
            <div class="flex justify-between">
              <div class="flex-1 min-w-0">
                <div class="flex items-center">
                  <div class="flex-shrink-0 mr-3 mt-1">
                    <div class="w-2 h-2 rounded-full <?= empty($n['is_read'])?'bg-blue-500':'bg-gray-300' ?>"></div>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">
                      <?= esc($n['title'] ?? 'Notifikasi') ?>
                      <?php if (empty($n['is_read'])): ?>
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">Baru</span>
                      <?php endif; ?>
                    </p>
                    <p class="text-sm text-gray-600 mt-1"><?= esc($n['message'] ?? '') ?></p>
                    <p class="text-xs text-gray-400 mt-1">
                      <i class="far fa-clock mr-1"></i>
                      <?= !empty($n['created_at']) ? date('d M Y, H:i', strtotime($n['created_at'])) : '' ?>
                    </p>
                  </div>
                </div>
              </div>
              <div class="flex flex-col gap-2 ml-4 shrink-0">
                <?php if (empty($n['is_read'])): ?>
                  <form method="post" action="<?= site_url('seo/notif/read/'.(int)($n['id'] ?? 0)) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center">
                      <i class="fas fa-check-circle mr-1"></i> Tandai Dibaca
                    </button>
                  </form>
                <?php endif; ?>
                <form method="post" action="<?= site_url('seo/notif/delete/'.(int)($n['id'] ?? 0)) ?>"
                      onsubmit="return confirm('Sembunyikan notifikasi ini dari tampilan Anda?');">
                  <?= csrf_field() ?>
                  <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium flex items-center">
                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="px-5 py-4 border-t border-gray-200 flex justify-between items-center bg-gray-50 rounded-b-xl">
      <form method="post" action="<?= site_url('seo/notif/mark-all') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors flex items-center">
          <i class="fas fa-check-double mr-2"></i>
          Tandai Semua Dibaca
        </button>
      </form>
      <form method="post" action="<?= site_url('seo/notif/delete-all') ?>"
            onsubmit="return confirm('Sembunyikan semua notifikasi dari tampilan Anda?');">
        <?= csrf_field() ?>
        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition-colors flex items-center">
          <i class="fas fa-trash-alt mr-2"></i>
          Hapus Semua
        </button>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.store('ui', {
    modal: null,
    notifModal: <?= $openNotifModal ? 'true' : 'false' ?>
  });
  
  // Auto-open modal notif jika $openNotifModal = true
  <?php if ($openNotifModal): ?>
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      Alpine.data('notificationModal', () => ({
        notifModalOpen: true
      }));
    }, 100);
  });
  <?php endif; ?>
});
</script>

</body>
</html>