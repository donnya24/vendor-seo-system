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
    <header class="bg-white shadow fixed top-0 left-0 right-0 z-20 md:ml-64">
      <div class="flex items-center justify-between p-4">
        <button class="md:hidden text-gray-600" @click="sidebarOpen=!sidebarOpen">
          <i class="fas fa-bars"></i>
        </button>

        <div class="flex items-center gap-4 ml-auto">
          <!-- Notifikasi -->
          <div class="relative">
            <button type="button" @click.stop="notifOpen=!notifOpen" @keydown.escape.window="notifOpen=false"
              class="relative text-gray-600 hover:text-gray-900 p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <i class="fas fa-bell text-lg"></i>
              <?php if ($unreadCount > 0): ?>
              <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center
                           text-[10px] leading-none font-semibold text-white bg-red-600 rounded-full
                           w-4 h-4 shadow">
                <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
              </span>
              <?php endif; ?>
            </button>
            <!-- Dropdown -->
            <div x-show="notifOpen" x-cloak @click.outside="notifOpen=false"
                 class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg border z-50 overflow-hidden">
              <div class="px-4 py-2 border-b flex items-center justify-between">
                <span class="text-sm font-semibold">Notifikasi</span>
                <a href="#" @click.prevent.stop="notifOpen=false; notifModalOpen=true"
                   class="text-xs text-blue-600 hover:underline">Lihat semua</a>
              </div>
              <?php if (!empty($notifItems)): ?>
                <ul class="max-h-80 overflow-auto divide-y">
                  <?php foreach ($notifItems as $it): ?>
                  <li class="px-4 py-3 <?= empty($it['is_read'])?'bg-blue-50':'' ?>">
                    <p class="text-sm font-medium truncate"><?= esc($it['title'] ?? 'Notifikasi') ?></p>
                    <p class="text-xs text-gray-600 truncate"><?= esc($it['message'] ?? '') ?></p>
                    <p class="text-[11px] text-gray-400 mt-1"><?= !empty($it['created_at']) ? date('Y-m-d H:i', strtotime($it['created_at'])) : '' ?></p>
                  </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="p-4 text-sm text-gray-600">Belum ada notifikasi.</div>
              <?php endif; ?>
              <div class="px-4 py-2 border-t text-right">
                <button type="button" class="text-xs text-gray-600 hover:text-gray-800" @click="notifOpen=false">Tutup</button>
              </div>
            </div>
          </div>

          <!-- Profile -->
          <div class="relative">
            <button type="button" @click.stop="profileOpen=!profileOpen" class="flex items-center gap-2 focus:outline-none">
              <div class="h-8 w-8 rounded-full bg-gray-200 overflow-hidden flex items-center justify-center">
                <?php if ($hasProfileImage): ?>
                  <img src="<?= esc($profileImagePath) ?>" alt="Profil" class="w-full h-full object-cover">
                <?php else: ?>
                  <i class="fas fa-user text-gray-500 text-sm"></i>
                <?php endif; ?>
              </div>
              <span class="hidden md:block text-sm font-medium text-gray-700">
                <?= esc($profile['name'] ?? session()->get('user_name') ?? 'SEO User') ?>
              </span>
              <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div x-show="profileOpen" x-cloak @click.outside="profileOpen=false"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
              <a href="#" @click.prevent.stop="$store.ui.modal='seoProfileEdit'; $nextTick(()=>{profileOpen=false})"
                 class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>
              <a href="#" @click.prevent.stop="$store.ui.modal='seoPasswordEdit'; $nextTick(()=>{profileOpen=false})"
                 class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ubah Password</a>
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
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
     @click.self="notifModalOpen=false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
     
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg"
       @click.stop
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-1">

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b">
      <h3 class="text-lg font-semibold text-gray-900">Semua Notifikasi</h3>
      <button type="button"
              @click.prevent.stop="notifModalOpen=false"
              class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="max-h-96 overflow-y-auto divide-y">
      <?php if (empty($modalNotifications)): ?>
        <div class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>
      <?php else: ?>
        <?php foreach ($modalNotifications as $n): ?>
          <div class="p-4 flex justify-between items-start hover:bg-gray-50">
            <div class="min-w-0 pr-3">
              <p class="text-sm font-semibold text-gray-900 truncate">
                <?= esc($n['title'] ?? 'Notifikasi') ?>
                <?php if (empty($n['is_read'])): ?>
                  <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Baru</span>
                <?php endif; ?>
              </p>
              <p class="text-xs text-gray-600 mt-0.5 break-words"><?= esc($n['message'] ?? '') ?></p>
              <p class="text-[11px] text-gray-400 mt-1"><?= esc($n['date'] ?? ($n['created_at'] ?? '')) ?></p>
            </div>
            <div class="flex flex-col gap-1 ml-2 shrink-0">
              <?php if (empty($n['is_read'])): ?>
                <form method="post" action="<?= site_url('seo/notif/read/'.(int)($n['id'] ?? 0)) ?>">
                  <?= csrf_field() ?>
                  <button type="submit" class="text-xs text-blue-600 hover:underline">Tandai Dibaca</button>
                </form>
              <?php endif; ?>
              <form method="post" action="<?= site_url('seo/notif/delete/'.(int)($n['id'] ?? 0)) ?>"
                    onsubmit="return confirm('Sembunyikan notifikasi ini dari tampilan Anda?');">
                <?= csrf_field() ?>
                <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="px-4 py-3 border-t flex justify-between items-center">
      <form method="post" action="<?= site_url('seo/notif/mark-all') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
          Tandai Semua Dibaca
        </button>
      </form>
      <form method="post" action="<?= site_url('seo/notif/delete-all') ?>"
            onsubmit="return confirm('Sembunyikan semua notifikasi dari tampilan Anda?');">
        <?= csrf_field() ?>
        <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white text-sm hover:bg-red-700">
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