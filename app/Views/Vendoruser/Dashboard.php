<?php 
include_once(APPPATH . 'Views/vendoruser/layouts/header.php');
include_once(APPPATH . 'Views/vendoruser/layouts/sidebar.php');

$user = service('auth')->user(); 
$vendorName = $user->username ?? session('user_name') ?? 'Vendor';

// Ambil data profil vendor
$vendorProfileModel = new \App\Models\VendorProfilesModel();
$vendorProfile = $vendorProfileModel->where('user_id', service('auth')->user()->id)->first();
$profileImage = $vendorProfile['profile_image'] ?? '';

// Tentukan path gambar profil
if (!empty($profileImage) && file_exists(FCPATH . 'uploads/vendoruser/profiles/' . $profileImage)) {
    $profileImagePath = base_url('uploads/vendoruser/profiles/' . $profileImage);
} else {
    $profileImagePath = base_url('assets/img/default-avatar.png');
}

// helper kecil utk JSON aman dimasukkan ke JS
$toJson = static fn($x)=> json_encode($x, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
?>

<!-- Main -->
<div class="flex-1 flex flex-col overflow-hidden" :class="{'md:ml-64': $store.ui.sidebar}">
  <!-- Topbar -->
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="$store.ui.sidebar ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <!-- Sidebar toggle -->
      <button class="hover:opacity-80" @click="$store.ui.sidebar=!$store.ui.sidebar" aria-label="Toggle sidebar">
        <i class="fas fa-bars text-gray-700"></i>
      </button>

      <div class="flex items-center gap-4">
        <!-- 🔔 Notifikasi -->
        <div class="relative" x-data="{ notifOpen:false, notifModal:false }">
          <!-- Tombol Bell -->
          <button @click="notifOpen = !notifOpen; if(notifOpen){ markNotifAsRead(); }" 
                  class="relative text-gray-600 hover:text-gray-900 p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :aria-expanded="notifOpen" aria-haspopup="true">
            <i class="fas fa-bell text-xl"></i>
            <?php if (($stats['unread'] ?? 0) > 0): ?>
              <span id="notifBadge"
                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                <?= $stats['unread'] ?>
              </span>
            <?php endif; ?>
          </button>

          <!-- Dropdown Notifikasi -->
          <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
              class="absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-md shadow-lg py-2 z-50 max-h-96 overflow-y-auto"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 transform scale-95"
              x-transition:enter-end="opacity-100 transform scale-100"
              x-transition:leave="transition ease-in duration-75"
              x-transition:leave-start="opacity-100 transform scale-100"
              x-transition:leave-end="opacity-0 transform scale-95">

            <?php if (empty($notifications)): ?>
              <div class="px-4 py-3 text-sm text-gray-500 text-center">Tidak ada notifikasi.</div>
            <?php else: ?>
              <?php foreach ($notifications as $n): ?>
                <div class="px-4 py-3 border-b hover:bg-gray-50 cursor-pointer" @click="notifModal=true; notifOpen=false">
                  <p class="text-sm font-semibold text-gray-900 flex items-center justify-between">
                    <span><?= esc($n['title']) ?></span>
                    <?php if (! $n['is_read']): ?>
                      <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Baru</span>
                    <?php endif; ?>
                  </p>
                  <p class="text-xs text-gray-600 line-clamp-2"><?= esc($n['message']) ?></p>
                  <p class="text-xs text-gray-400 mt-1"><?= esc($n['date']) ?></p>
                </div>
              <?php endforeach; ?>

              <div class="px-4 py-2 border-t">
                <button @click="notifModal=true; notifOpen=false" 
                        class="block mx-auto text-sm text-blue-600 hover:text-blue-800 font-medium">
                  Lihat Semua Notifikasi
                </button>
              </div>
            <?php endif; ?>
          </div>

          <!-- Modal Semua Notifikasi -->
          <div x-show="notifModal" x-cloak
              class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-2 sm:p-4"
              x-transition:enter="transition ease-out duration-300"
              x-transition:enter-start="opacity-0"
              x-transition:enter-end="opacity-100"
              x-transition:leave="transition ease-in duration-200"
              x-transition:leave-start="opacity-100"
              x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[85vh] sm:max-h-[75vh] flex flex-col" 
                 @click.away="notifModal=false">
              <div class="flex items-center justify-between px-4 py-3 border-b sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold text-gray-900">Semua Notifikasi</h3>
                <button @click="notifModal=false" class="text-gray-500 hover:text-gray-700">
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <div class="flex-1 overflow-y-auto divide-y">
                <?php if (empty($notifications)): ?>
                  <div class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>
                <?php else: ?>
                  <?php foreach ($notifications as $n): ?>
                    <div class="p-4 flex justify-between items-start hover:bg-gray-50">
                      <div class="pr-2 flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 break-words">
                          <?= esc($n['title']) ?>
                          <?php if (! $n['is_read']): ?>
                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Baru</span>
                          <?php endif; ?>
                        </p>
                        <p class="text-xs text-gray-600 break-words mt-1"><?= esc($n['message']) ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= esc($n['date']) ?></p>
                      </div>
                      <div class="flex flex-col gap-1 ml-2 shrink-0">
                        <?php if (! $n['is_read']): ?>
                          <form method="post" action="<?= site_url('vendoruser/notifications/mark/'.$n['id']) ?>">
                            <?= csrf_field() ?>
                            <button class="text-xs text-blue-600 hover:underline whitespace-nowrap" type="submit">Tandai</button>
                          </form>
                        <?php endif; ?>
                        <form method="post" action="<?= site_url('vendoruser/notifications/delete/'.$n['id']) ?>" 
                              onsubmit="return confirm('Hapus notifikasi ini?');">
                          <?= csrf_field() ?>
                          <button class="text-xs text-red-600 hover:underline whitespace-nowrap" type="submit">Hapus</button>
                        </form>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>

              <div class="px-4 py-3 border-t flex flex-col sm:flex-row justify-between gap-2 sticky bottom-0 bg-white">
                <form method="post" action="<?= site_url('vendoruser/notifications/mark-all') ?>" class="flex-1">
                  <?= csrf_field() ?>
                  <button type="submit" class="w-full px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
                    Tandai Semua Dibaca
                  </button>
                </form>
                <form method="post" action="<?= site_url('vendoruser/notifications/delete-all') ?>" 
                      onsubmit="return confirm('Hapus semua notifikasi?');" class="flex-1">
                  <?= csrf_field() ?>
                  <button type="submit" class="w-full px-3 py-2 rounded bg-red-600 text-white text-sm hover:bg-red-700">
                    Hapus Semua
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- 👤 Dropdown Profil -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" 
                  class="flex items-center space-x-2 focus:outline-none" 
                  :aria-expanded="open" aria-haspopup="true">
            <!-- Tampilkan gambar profil -->
            <img src="<?= $profileImagePath ?>" 
                 class="w-8 h-8 rounded-full object-cover border border-gray-300 profile-image"
                 alt="Profile Image"
                 onerror="this.onerror=null; this.src='<?= base_url('assets/img/default-avatar.png') ?>';">
            
            <span class="hidden md:block text-sm font-medium text-gray-700"><?= esc($vendorName) ?></span>
            <i class="fas fa-chevron-down text-xs"></i>
          </button>
          
          <div x-show="open" @click.away="open = false" x-cloak
               class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="opacity-0 transform scale-95"
               x-transition:enter-end="opacity-100 transform scale-100"
               x-transition:leave="transition ease-in duration-75"
               x-transition:leave-start="opacity-100 transform scale-100"
               x-transition:leave-end="opacity-0 transform scale-95">
              <a href="<?= site_url('vendoruser/profile') ?>"
                 @click.prevent="$store.ui.modal='profileEdit'; open=false"
                 class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profil</a>

              <a href="<?= site_url('vendoruser/password') ?>"
                 @click.prevent="$store.ui.modal='passwordEdit'; open=false"
                 class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ubah Password</a>
          </div>
        </div>
      </div>
    </div>
  </header>
  
  <!-- Spacer untuk fixed header -->
  <div class="h-16"></div>

  <!-- CONTENT -->
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <!-- ✅ Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 mt-6">
      <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg hover:scale-[1.02] transition-transform duration-200">
        <div class="flex items-center">
          <div class="p-3 rounded-full mr-4 bg-blue-100 text-blue-600">
            <i class="fas fa-bullseye text-lg"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Leads Baru</p>
            <p class="text-2xl font-semibold"><?= $stats['leads_new'] ?? 0 ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg hover:scale-[1.02] transition-transform duration-200">
        <div class="flex items-center">
          <div class="p-3 rounded-full mr-4 bg-green-100 text-green-600">
            <i class="fas fa-check-circle text-lg"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Leads Diproses</p>
            <p class="text-2xl font-semibold"><?= $stats['leads_inprogress'] ?? 0 ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg hover:scale-[1.02] transition-transform duration-200">
        <div class="flex items-center">
          <div class="p-3 rounded-full mr-4 bg-yellow-100 text-yellow-600">
            <i class="fas fa-key text-lg"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Total Keyword</p>
            <p class="text-2xl font-semibold"><?= $stats['keywords_total'] ?? 0 ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- TOP KEYWORDS + QUICK ACTIONS -->
    <div class="flex flex-col lg:flex-row gap-6 mb-6 mt-4">
      <!-- Top Keywords -->
      <div class="bg-white rounded-lg shadow hover:shadow-lg hover:scale-[1.01] overflow-hidden w-full max-w-md transition-transform duration-200">
        <div class="px-4 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900"><i class="fas fa-chart-line mr-2 text-blue-600"></i>Top Keywords</h3>
        </div>
        <div class="divide-y divide-gray-200 max-h-80 overflow-y-auto">
          <template x-if="$store.app.topKeywords.length === 0">
            <div class="p-4 text-center text-sm text-gray-500">
              Belum ada keyword.
            </div>
          </template>
          <template x-for="k in $store.app.topKeywords" :key="k.id">
            <div class="px-4 py-3 hover:bg-gray-50">
              <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-90 truncate" x-text="k.text || 'Unknown'"></p>
                  <p class="text-xs text-gray-500 truncate" x-text="k.project || 'Unknown project'"></p>
                </div>
                <div class="ml-2 flex-shrink-0 flex flex-col items-end">
                  <span class="inline-flex items-center justify-center h-7 w-7 rounded-full text-xs font-semibold"
                        :class="{
                          'bg-green-100 text-green-700': (k.position || 999) <= 5,
                          'bg-yellow-100 text-yellow-700': (k.position || 999) > 5 && (k.position || 999) <= 10,
                          'bg-gray-100 text-gray-700': (k.position || 999) > 10
                        }"
                        x-text="k.position || '-'"></span>
                  <div class="text-xs mt-1" :class="(k.change || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                    <template x-if="k.change !== null && k.change !== undefined">
                      <span>
                        <i class="fas" :class="(k.change || 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                        <span x-text="Math.abs(k.change || 0)"></span>
                      </span>
                    </template>
                    <template x-if="k.change === null || k.change === undefined">
                      <span>-</span>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow hover:shadow-lg hover:scale-[1.01] overflow-hidden w-full max-w-md transition-transform duration-200">
        <div class="px-4 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
        </div>
        <div class="p-4 space-y-3 flex flex-col items-start">
          <a href="<?= site_url('vendoruser/products/create') ?>"
             class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
            <i class="fas fa-box mr-2"></i> Tambah Produk
          </a>
          <a href="<?= site_url('vendoruser/services/create') ?>"
             class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
            <i class="fas fa-toolbox mr-2"></i> Tambah Layanan
          </a>
          <a href="<?= site_url('vendoruser/leads/create') ?>"
             class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
            <i class="fas fa-bullseye mr-2"></i> Input Lead
          </a>
        </div>
      </div>
    </div>

    <!-- ✅ Leads Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900"><i class="fas fa-bullseye mr-2 text-blue-600"></i>Leads Terbaru</h3>
        <a href="<?= site_url('vendoruser/leads') ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
          <i class="fas fa-eye mr-1"></i> Lihat Semua
        </a>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Telepon</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($recentLeads)): ?>
              <tr>
                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada lead.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentLeads as $lead): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4"><?= esc($lead['id']) ?></td>
                <td class="px-6 py-4"><?= esc($lead['project']) ?></td>
                <td class="px-6 py-4"><?= esc($lead['customer']) ?></td>
                <td class="px-6 py-4"><?= esc($lead['phone']) ?></td>
                <td class="px-6 py-4"><?= esc($lead['source']) ?></td>
                <td class="px-6 py-4"><?= esc($lead['date']) ?></td>
                <td class="px-6 py-4">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                    <?= $lead['status']=='in_progress'?'bg-blue-100 text-blue-800':($lead['status']=='closed'?'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800') ?>">
                    <?= $lead['status']=='in_progress'?'Diproses':($lead['status']=='closed'?'Closed':'Baru') ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <a href="<?= site_url('vendoruser/leads/'.$lead['id']) ?>" class="text-blue-600 hover:text-blue-800">Kelola</a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Include modal views -->
<?= view('vendoruser/profile/edit', ['vp' => $vp ?? []]) ?>
<?= view('vendoruser/profile/ubahpassword') ?>

<script>
function markNotifAsRead(){
  fetch("<?= site_url('vendoruser/notifications/mark-all') ?>", {
    method: "GET",
    headers: {"X-Requested-With": "XMLHttpRequest"}
  }).then(res => {
    if(res.ok){
      let badge=document.getElementById("notifBadge");
      if(badge){ badge.remove(); }
    }
  });
}
document.addEventListener('alpine:init', () => {
  Alpine.store('ui', { sidebar: window.innerWidth > 768, modal: null });
  Alpine.store('app', {
    topKeywords: <?= $toJson($topKeywords ?? []) ?>
  });
});
</script>

<style>
[x-cloak] { display: none !important; }
@media (max-width: 767px) { .max-w-\[90vw\] { max-width: 90vw; } }
</style>

<?php include_once(APPPATH . 'Views/vendoruser/layouts/footer.php'); ?>