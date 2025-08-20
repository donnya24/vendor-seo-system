<!DOCTYPE html>
<html lang="id" x-data :class="{'overflow-hidden': $store.ui.modal}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vendor Dashboard | Vendor Partnership SEO Performance</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- Icon (optional) -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

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
  </style>
</head>

<body class="bg-gray-50 font-sans" x-cloak>
  <div class="flex h-screen overflow-hidden" x-init="$store.app.init()">

    <!-- Sidebar -->
    <aside class="sidebar text-white w-64 fixed h-full p-4 flex flex-col bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900"
           :class="{'-ml-64': !$store.ui.sidebar}">
      <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold flex items-center">
          <i class="fas fa-store mr-2"></i> Vendor Area
        </h1>
        <button class="md:hidden" @click="$store.ui.sidebar=false" aria-label="Tutup sidebar">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="border-b border-white/20 my-3"></div>

      <nav class="flex-1 text-sm">
        <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Main Menu</p>

        <template x-for="m in $store.app.menus" :key="m.key">
          <a href="#"
             @click.prevent="$store.app.active=m.key"
             class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
             :class="$store.app.active===m.key ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i :class="m.icon + ' mr-3'"></i>
            <span x-text="m.label"></span>
            <span class="badge bg-blue-500 rounded-full ml-auto"
                  x-show="m.badge && $store.app.stats[m.badge] > 0"
                  x-text="$store.app.stats[m.badge]"></span>
          </a>
        </template>

        <div class="mt-6">
          <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Akun</p>
          <a href="#" @click.prevent="$store.app.active='Profile'"
             class="block py-2 px-3 rounded-lg flex items-center nav-item"
             :class="$store.app.active==='Profile' ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i class="fas fa-user-cog mr-3"></i> Profil Saya
          </a>
        </div>
      </nav>

        <!-- Tombol Logout di kiri bawah -->
        <div class="mt-auto">
            <!-- Tombol untuk membuka modal konfirmasi logout -->
            <button class="block py-2 px-3 rounded-lg flex items-center nav-item text-white-500 hover:bg-white-700/70 focus:ring-2 focus:ring-white-300/40"
                    @click.prevent="$store.ui.modal='logout'">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </button>
        </div>

        <!-- Modal Konfirmasi Logout -->
        <!-- Modal Konfirmasi Logout (desain sesuai gambar) -->
        <div x-show="$store.ui.modal === 'logout'" x-transition.opacity class="fixed inset-0 z-50" role="dialog" aria-modal="true">
          <div class="min-h-screen flex items-center justify-center p-4">
            <!-- backdrop -->
            <div class="fixed inset-0 bg-black/40" @click="$store.ui.modal=null"></div>

            <!-- card -->
            <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
              <!-- icon bulat -->
              <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center">
                <i class="fa-solid fa-right-from-bracket text-2xl"></i>
              </div>

              <!-- title & subtitle -->
              <h3 class="mt-4 text-center text-xl font-semibold text-gray-900">Keluar dari Sistem?</h3>
              <p class="mt-2 text-center text-sm text-gray-500">Anda akan keluar dari sesi saat ini.</p>

              <!-- actions -->
              <div class="mt-6 flex items-center justify-center gap-3">
                <button
                  class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200"
                  @click="$store.ui.modal=null">
                  Batal
                </button>
                <button
                  class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                  @click="document.getElementById('logoutForm').submit()">
                  Ya, Keluar
                </button>
              </div>
            </div>
          </div>
        </div>


    <!-- Form Logout (Disembunyikan, tidak langsung di-submit) -->
    <form id="logoutForm" action="<?= base_url('logout') ?>" method="post" style="display: none;">
        <?= csrf_field() ?> <!-- CSRF Token -->
    </form>


    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden" :class="{'md:ml-64': $store.ui.sidebar}">
      <!-- Topbar -->
      <header class="bg-white shadow z-20 fixed top-0 left-0 right-0"
              :class="$store.ui.sidebar ? 'md:ml-64' : ''">
        <div class="flex items-center justify-between p-4">
          <button class="hover:opacity-80" @click="$store.ui.sidebar=!$store.ui.sidebar" aria-label="Toggle sidebar">
            <i class="fas fa-bars text-gray-700"></i>
          </button>

          <div class="relative w-full max-w-md mx-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" x-model.trim="$store.app.search"
                   @keyup.enter="$store.app.searchNow()"
                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                   placeholder="Cari produk, leads...">
          </div>

          <div class="flex items-center gap-4">
            <button class="relative text-gray-600 hover:text-gray-900"
                    @click="$store.ui.modal='notifications'">
              <i class="fas fa-bell text-xl"></i>
              <span x-show="$store.app.stats.unreadNotifications>0"
                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
                    x-text="$store.app.stats.unreadNotifications"></span>
            </button>

            <div class="relative">
              <button @click="$store.ui.profile=!$store.ui.profile" class="flex text-sm rounded-full focus:outline-none">
                <img class="h-8 w-8 rounded-full" :src="$store.app.profile.photo" alt="">
              </button>
              <div x-show="$store.ui.profile" @click.outside="$store.ui.profile=false"
                   class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black/5">
                <a href="#" @click.prevent="$store.app.active='Profile';$store.ui.profile=false"
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
                </a>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="h-16"></div>

      <!-- Pages -->
      <main class="flex-1 overflow-y-auto p-4 bg-gray-50">

        <!-- DASHBOARD -->
        <section x-show="$store.app.active==='Dashboard'" x-transition.opacity>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <template x-for="c in $store.app.statCards" :key="c.key">
              <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                  <div class="p-3 rounded-full mr-4" :class="c.bg + ' ' + c.text">
                    <i :class="c.icon + ' text-lg'"></i>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500" x-text="c.label"></p>
                    <p class="text-2xl font-semibold" x-text="c.format($store.app.stats[c.key])"></p>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Leads Terbaru -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900"><i class="fas fa-bullseye mr-2 text-blue-600"></i>Leads Terbaru</h3>
                <a href="#" @click.prevent="$store.app.active='Leads'" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
              </div>
              <div class="divide-y divide-gray-200">
                <template x-for="lead in $store.app.recentLeads" :key="lead.id">
                  <div class="px-4 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                      <div>
                        <p class="text-sm font-medium text-gray-900" x-text="lead.project"></p>
                        <p class="text-sm text-gray-500" x-text="'Dari: ' + lead.customer"></p>
                      </div>
                      <span class="px-2 text-xs font-semibold rounded-full"
                            :class="$store.app.badge(lead.status)"
                            x-text="$store.app.statusText(lead.status)"></span>
                    </div>
                    <div class="mt-2 flex justify-between text-sm text-gray-500">
                      <span x-text="'Tanggal: ' + lead.date"></span>
                      <button @click="$store.app.openLead(lead.id)" class="text-blue-600 hover:text-blue-800">Detail</button>
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <!-- Produk Terpopuler -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900"><i class="fas fa-star mr-2 text-yellow-600"></i>Produk Terpopuler</h3>
              </div>
              <div class="divide-y divide-gray-200">
                <template x-for="p in $store.app.popularProducts" :key="p.id">
                  <div class="px-4 py-4 hover:bg-gray-50">
                    <div class="flex items-center">
                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-box text-blue-600"></i>
                      </div>
                      <div class="ml-4 flex-1">
                        <div class="flex justify-between">
                          <p class="text-sm font-medium text-gray-900" x-text="p.name"></p>
                          <span class="text-sm font-semibold" x-text="'Rp ' + $store.app.toIDR(p.price)"></span>
                        </div>
                        <div class="flex justify-between mt-1 text-xs text-gray-500">
                          <span x-text="p.category"></span>
                          <span x-text="p.leads + ' leads'"></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
              <div class="px-4 py-4 border-t bg-gray-50">
                <a href="#" @click.prevent="$store.app.active='Products'" class="text-sm font-medium text-blue-600 hover:text-blue-500">Kelola Produk</a>
              </div>
            </div>

            <!-- Aksi Cepat -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Aksi Cepat</h3>
              </div>
              <div class="p-6 grid grid-cols-1 gap-3">
                <button @click="$store.ui.modal='addProduct'"
                        class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                  <i class="fas fa-plus mr-2"></i> Tambah Produk
                </button>
                <button @click="$store.ui.modal='contactImersa'"
                        class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                  <i class="fas fa-headset mr-2"></i> Hubungi Imersa
                </button>
                <button @click="$store.ui.modal='requestOptimization'"
                        class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                  <i class="fas fa-magic mr-2"></i> Request Optimasi
                </button>
              </div>
            </div>
          </div>

          <!-- Performa Keyword (ringkas) -->
          <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900"><i class="fas fa-chart-line mr-2 text-blue-600"></i>Performa Keyword</h3>
              <a href="#" @click.prevent="$store.app.active='SEOReports'" class="text-sm text-blue-600 hover:text-blue-800">Lihat Detail</a>
            </div>
            <div class="p-6 grid md:grid-cols-3 gap-6">
              <div class="md:col-span-2 h-56 bg-gray-50 rounded-md flex items-center justify-center text-gray-500">
                Grafik posisi keyword (placeholder)
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">TOP KEYWORDS</h4>
                <div class="space-y-3">
                  <template x-for="k in $store.app.topKeywords" :key="k.id">
                    <div class="flex items-start">
                      <span class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-medium"
                            :class="k.position<=5?'bg-green-100 text-green-800':(k.position<=10?'bg-yellow-100 text-yellow-800':'bg-red-100 text-red-800')"
                            x-text="k.position"></span>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900" x-text="k.text"></p>
                        <div class="flex items-center text-xs text-gray-500">
                          <span x-text="k.project"></span>
                          <span class="mx-1">•</span>
                          <span :class="k.trend==='up'?'text-green-600':'text-red-600'">
                            <i class="fas" :class="k.trend==='up'?'fa-arrow-up':'fa-arrow-down'"></i>
                            <span x-text="k.change"></span>
                          </span>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- LEADS -->
        <section x-show="$store.app.active==='Leads'" x-transition.opacity>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-bullseye mr-2 text-blue-600"></i>Leads Saya</h2>
            <div class="flex gap-2">
              <select x-model="$store.app.filters.lead" @change="$store.app.page=1"
                      class="bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 rounded-md text-sm">
                <option value="all">Semua Status</option>
                <option value="new">Baru</option>
                <option value="processed">Diproses</option>
                <option value="converted">Converted</option>
              </select>
              <button @click="$store.app.exportLeads()" class="px-3 py-2 border rounded-md text-sm bg-white hover:bg-gray-50">
                <i class="fas fa-file-export mr-2"></i>Export
              </button>
            </div>
          </div>

          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Proyek/Keyword</th>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="lead in $store.app.pagedLeads" :key="lead.id">
                    <tr>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#'+lead.id"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="lead.customer"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="lead.project"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="lead.date"></td>
                      <td class="px-6 py-4">
                        <span class="px-2 text-xs font-semibold rounded-full"
                              :class="$store.app.badge(lead.status)"
                              x-text="$store.app.statusText(lead.status)"></span>
                      </td>
                      <td class="px-6 py-4 text-right text-sm">
                        <button x-show="lead.status==='new'"
                                @click="$store.app.setLeadStatus(lead.id,'processed')"
                                class="text-blue-600 hover:text-blue-900 mr-3">Proses</button>
                        <button x-show="lead.status!=='converted'"
                                @click="$store.app.setLeadStatus(lead.id,'converted')"
                                class="text-green-600 hover:text-green-900 mr-3">Convert</button>
                        <button @click="$store.app.openLead(lead.id)" class="text-gray-600 hover:text-gray-900">Detail</button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
              <p class="text-sm text-gray-500">
                Menampilkan <span x-text="$store.app.pagedLeads.length"></span> dari
                <span x-text="$store.app.filteredLeads.length"></span> leads
              </p>
              <div class="flex gap-1">
                <button class="px-2 py-2 border rounded-l-md bg-white text-sm"
                        :disabled="$store.app.page===1"
                        @click="$store.app.page--"><i class="fas fa-chevron-left"></i></button>
                <button class="px-3 py-2 border bg-white text-sm" x-text="$store.app.page"></button>
                <button class="px-2 py-2 border rounded-r-md bg-white text-sm"
                        :disabled="$store.app.page===$store.app.totalPages"
                        @click="$store.app.page++"><i class="fas fa-chevron-right"></i></button>
              </div>
            </div>
          </div>
        </section>

        <!-- PRODUCTS -->
        <section x-show="$store.app.active==='Products'" x-transition.opacity>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-boxes mr-2 text-blue-600"></i>Produk/Layanan</h2>
            <button @click="$store.ui.modal='addProduct'" class="px-3 py-2 rounded-md text-sm text-white bg-blue-600 hover:bg-blue-700">
              <i class="fas fa-plus mr-2"></i>Tambah Produk
            </button>
          </div>

          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-4 border-b flex items-center justify-between">
              <p class="text-sm text-gray-500">Total <span x-text="$store.app.products.length"></span> produk</p>
              <select x-model="$store.app.filters.product" class="bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 rounded-md text-sm">
                <option value="all">Semua Kategori</option>
                <option value="product">Produk Fisik</option>
                <option value="service">Layanan</option>
              </select>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Kategori</th>
                    <th class="px-6 py-3">Harga</th>
                    <th class="px-6 py-3">Leads</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="p in $store.app.filteredProducts" :key="p.id">
                    <tr>
                      <td class="px-6 py-4">
                        <div class="flex items-center">
                          <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas" :class="p.type==='product'?'fa-box text-blue-600':'fa-handshake text-purple-600'"></i>
                          </div>
                          <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900" x-text="p.name"></p>
                            <p class="text-sm text-gray-500 truncate max-w-xs" x-text="p.description"></p>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="p.category"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="'Rp ' + $store.app.toIDR(p.price)"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="p.leads"></td>
                      <td class="px-6 py-4 text-right text-sm">
                        <button @click="$store.app.openEditProduct(p)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                        <button @click="$store.app.deleteProduct(p.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- SEO REPORTS -->
        <section x-show="$store.app.active==='SEOReports'" x-transition.opacity>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-chart-line mr-2 text-blue-600"></i>Ranking Keyword</h2>
            <select x-model="$store.app.filters.keyword" class="bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 rounded-md text-sm">
              <option value="all">Semua Posisi</option>
              <option value="page1">Page 1 (1-10)</option>
              <option value="page2">Page 2 (11-20)</option>
              <option value="page3">Page 3+ (>20)</option>
            </select>
          </div>

          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-3">Keyword</th>
                    <th class="px-6 py-3">Proyek</th>
                    <th class="px-6 py-3">Posisi</th>
                    <th class="px-6 py-3">Perubahan</th>
                    <th class="px-6 py-3">Volume</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="k in $store.app.filteredKeywords" :key="k.id">
                    <tr>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="k.text"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="k.project"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="k.position"></td>
                      <td class="px-6 py-4">
                        <span class="flex items-center" :class="k.trend==='up'?'text-green-600':'text-red-600'">
                          <i class="fas" :class="k.trend==='up'?'fa-arrow-up':'fa-arrow-down'"></i>
                          <span class="ml-1" x-text="k.change"></span>
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="k.volume"></td>
                      <td class="px-6 py-4 text-right text-sm">
                        <button @click="$store.app.openKeyword(k.id)" class="text-blue-600 hover:text-blue-900">Detail</button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- COMMISSIONS -->
        <section x-show="$store.app.active==='Commissions'" x-transition.opacity>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-money-bill-wave mr-2 text-blue-600"></i>Komisi Saya</h2>
            <select x-model="$store.app.filters.commission" class="bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 rounded-md text-sm">
              <option value="all">Semua Status</option>
              <option value="unpaid">Belum Dibayar</option>
              <option value="paid">Sudah Dibayar</option>
            </select>
          </div>

          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Periode</th>
                    <th class="px-6 py-3">Leads</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="c in $store.app.filteredCommissions" :key="c.id">
                    <tr>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#'+c.id"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="c.period"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="c.leads"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="'Rp ' + $store.app.toIDR(c.amount)"></td>
                      <td class="px-6 py-4">
                        <span class="px-2 text-xs font-semibold rounded-full"
                              :class="c.status==='unpaid'?'bg-yellow-100 text-yellow-800':'bg-green-100 text-green-800'"
                              x-text="c.status==='unpaid'?'Belum Dibayar':'Sudah Dibayar'"></span>
                      </td>
                      <td class="px-6 py-4 text-right text-sm">
                        <button @click="$store.app.openCommission(c.id)" class="text-blue-600 hover:text-blue-900">Detail</button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- MESSAGES -->
        <section x-show="$store.app.active==='Messages'" x-transition.opacity>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-comments mr-2 text-blue-600"></i>Pesan</h2>
            <button @click="$store.ui.modal='newMessage'"
                    class="px-3 py-2 rounded-md text-sm text-white bg-blue-600 hover:bg-blue-700">
              <i class="fas fa-plus mr-2"></i> Pesan Baru
            </button>
          </div>

          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-4 border-b flex items-center justify-between">
              <p class="text-sm text-gray-500">Total <span x-text="$store.app.messages.length"></span> pesan</p>
              <select x-model="$store.app.filters.message"
                      class="bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 rounded-md text-sm">
                <option value="all">Semua Pesan</option>
                <option value="unread">Belum Dibaca</option>
                <option value="read">Sudah Dibaca</option>
              </select>
            </div>
            <div class="divide-y divide-gray-200">
              <template x-for="m in $store.app.filteredMessages" :key="m.id">
                <div class="px-4 py-4 hover:bg-gray-50">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-envelope text-blue-600"></i>
                      </div>
                      <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900" x-text="m.subject"></p>
                        <p class="text-sm text-gray-500 truncate max-w-xs" x-text="m.preview"></p>
                      </div>
                    </div>
                    <span class="px-2 text-xs font-semibold rounded-full"
                          :class="m.read?'bg-gray-100 text-gray-800':'bg-blue-100 text-blue-800'"
                          x-text="m.read?'Dibaca':'Baru'"></span>
                  </div>
                  <div class="mt-2 flex justify-between text-sm text-gray-500">
                    <span x-text="'Dari: ' + m.sender"></span>
                    <span x-text="m.date"></span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </section>

        <!-- OPTIMIZATION -->
        <section x-show="$store.app.active==='Optimization'" x-transition.opacity>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-magic mr-2 text-blue-600"></i>Request Optimasi</h2>
            <button @click="$store.ui.modal='requestOptimization'"
                    class="px-3 py-2 rounded-md text-sm text-white bg-purple-600 hover:bg-purple-700">
              <i class="fas fa-plus mr-2"></i> Request Baru
            </button>
          </div>

          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-4 border-b flex items-center justify-between">
              <p class="text-sm text-gray-500">Total <span x-text="$store.app.optimizations.length"></span> request</p>
              <select x-model="$store.app.filters.optimization"
                      class="bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 rounded-md text-sm">
                <option value="all">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">Dalam Proses</option>
                <option value="completed">Selesai</option>
              </select>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Proyek</th>
                    <th class="px-6 py-3">Keyword</th>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="r in $store.app.filteredOptimizations" :key="r.id">
                    <tr>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#'+r.id"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="r.project"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="r.keyword"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="r.date"></td>
                      <td class="px-6 py-4">
                        <span class="px-2 text-xs font-semibold rounded-full"
                              :class="r.status==='pending'?'bg-yellow-100 text-yellow-800':(r.status==='in_progress'?'bg-blue-100 text-blue-800':'bg-green-100 text-green-800')"
                              x-text="r.status==='pending'?'Pending':(r.status==='in_progress'?'Dalam Proses':'Selesai')"></span>
                      </td>
                      <td class="px-6 py-4 text-right text-sm">
                        <button @click="$store.app.openOptimization(r.id)" class="text-blue-600 hover:text-blue-900">Detail</button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- PROFILE -->
        <section x-show="$store.app.active==='Profile'" x-transition.opacity>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-user-cog mr-2 text-blue-600"></i>Profil Saya</h2>
            <button @click="$store.ui.modal='editProfile'" class="px-3 py-2 rounded-md text-sm text-white bg-blue-600 hover:bg-blue-700">
              <i class="fas fa-edit mr-2"></i> Edit Profil
            </button>
          </div>

          <div class="bg-white shadow rounded-lg overflow-hidden p-6">
            <div class="grid md:grid-cols-3 gap-8">
              <div class="flex flex-col items-center">
                <img class="h-32 w-32 rounded-full mb-4" :src="$store.app.profile.photo" alt="">
                <h3 class="text-lg font-medium text-gray-900" x-text="$store.app.profile.name"></h3>
                <p class="text-sm text-gray-500" x-text="$store.app.profile.businessType"></p>
              </div>
              <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><p class="text-xs text-gray-500">Nama Bisnis</p><p class="font-medium" x-text="$store.app.profile.name"></p></div>
                <div><p class="text-xs text-gray-500">Jenis Bisnis</p><p class="font-medium" x-text="$store.app.profile.businessType"></p></div>
                <div><p class="text-xs text-gray-500">Email</p><p class="font-medium" x-text="$store.app.profile.email"></p></div>
                <div><p class="text-xs text-gray-500">WhatsApp</p><p class="font-medium" x-text="$store.app.profile.whatsapp"></p></div>
                <div class="md:col-span-2"><p class="text-xs text-gray-500">Alamat</p><p class="font-medium" x-text="$store.app.profile.address"></p></div>
              </div>
            </div>

            <div class="mt-8">
              <h3 class="text-lg font-medium text-gray-900 mb-2">Dokumen & Katalog</h3>
              <p class="text-sm text-gray-500 mb-4">Upload katalog, price list, atau contoh produk</p>

              <div class="rounded-lg p-6 text-center border-2 border-dashed border-gray-300 hover:border-blue-500 hover:bg-blue-50/30 cursor-pointer"
                   @click="$store.ui.modal='uploadDocument'">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                <p class="text-sm text-gray-600">Klik untuk upload dokumen</p>
                <p class="text-xs text-gray-500 mt-1">PDF, DOC, XLS, JPG, PNG (max. 10MB)</p>
              </div>

              <div class="mt-6 space-y-3">
                <template x-for="doc in $store.app.documents" :key="doc.id">
                  <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                      <i class="fas mr-2"
                         :class="$store.app.docIcon(doc.type)"></i>
                      <div>
                        <p class="text-sm font-medium text-gray-900" x-text="doc.name"></p>
                        <p class="text-xs text-gray-500" x-text="doc.size"></p>
                      </div>
                    </div>
                    <div class="flex gap-2">
                      <a :href="doc.url" target="_blank" class="text-blue-600 hover:text-blue-800" aria-label="Download"><i class="fas fa-download"></i></a>
                      <button @click="$store.app.removeDocument(doc.id)" class="text-red-600 hover:text-red-800" aria-label="Hapus"><i class="fas fa-trash"></i></button>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>

    <!-- MODALS (ringkas; form submit bisa di-wire ke API) -->
    <div x-show="$store.ui.modal==='notifications'" class="fixed inset-0 z-50" role="dialog" aria-modal="true">
      <div class="min-h-screen flex items-end sm:items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" @click="$store.ui.modal=null"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-medium mb-4"><i class="fas fa-bell mr-2 text-blue-600"></i>Notifikasi</h3>
          <div class="space-y-3 max-h-80 overflow-y-auto">
            <template x-for="n in $store.app.notifications" :key="n.id">
              <div class="flex items-start gap-3">
                <i class="fas mt-1"
                   :class="n.type==='lead'?'fa-bullseye text-blue-500':(n.type==='commission'?'fa-money-bill-wave text-green-500':'fa-bullhorn text-yellow-500')"></i>
                <div>
                  <p class="font-medium" x-text="n.title"></p>
                  <p class="text-sm text-gray-600" x-text="n.message"></p>
                  <p class="text-xs text-gray-400" x-text="n.time"></p>
                </div>
              </div>
            </template>
          </div>
          <div class="mt-4 text-right"><button class="px-3 py-2 border rounded-md" @click="$store.ui.modal=null">Tutup</button></div>
        </div>
      </div>
    </div>

    <!-- Contoh modal lain -->
    <div x-show="$store.ui.modal==='addProduct'" class="fixed inset-0 z-50" role="dialog" aria-modal="true">
      <div class="min-h-screen flex items-end sm:items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" @click="$store.ui.modal=null"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
          <h3 class="text-lg font-medium mb-4"><i class="fas fa-box mr-2 text-blue-600"></i>Tambah Produk/Layanan</h3>
          <div class="grid gap-3">
            <select x-model="$store.app.form.type" class="border rounded-md px-3 py-2">
              <option value="product">Produk Fisik</option>
              <option value="service">Layanan</option>
            </select>
            <input x-model="$store.app.form.name" class="border rounded-md px-3 py-2" placeholder="Nama"/>
            <input x-model="$store.app.form.category" class="border rounded-md px-3 py-2" placeholder="Kategori"/>
            <input type="number" x-model.number="$store.app.form.price" class="border rounded-md px-3 py-2" placeholder="Harga (Rp)"/>
            <textarea x-model="$store.app.form.description" class="border rounded-md px-3 py-2" rows="3" placeholder="Deskripsi"></textarea>
          </div>
          <div class="mt-5 flex gap-3 justify-end">
            <button class="px-3 py-2 border rounded-md" @click="$store.ui.modal=null">Batal</button>
            <button class="px-3 py-2 rounded-md bg-blue-600 text-white"
                    @click="$store.app.saveProduct()">Simpan</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Alpine Stores -->
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.store('ui', {
        sidebar: window.innerWidth > 768,
        profile: false,
        modal: null
      });

      Alpine.store('app', {
        // NAV
        active: 'Dashboard',
        menus: [
          {key:'Dashboard',label:'Dashboard',icon:'fas fa-tachometer-alt',badge:'newLeads'},
          {key:'Leads',label:'Leads Saya',icon:'fas fa-bullseye',badge:'unprocessedLeads'},
          {key:'Products',label:'Produk/Layanan',icon:'fas fa-boxes'},
          {key:'SEOReports',label:'Ranking Keyword',icon:'fas fa-chart-line'},
          {key:'Commissions',label:'Komisi Saya',icon:'fas fa-money-bill-wave',badge:'unpaidCommissions'},
          {key:'Messages',label:'Pesan',icon:'fas fa-comments',badge:'unreadMessages'},
          {key:'Optimization',label:'Request Optimasi',icon:'fas fa-magic'}
        ],

        // STATE
        stats: {newLeads:0,unprocessedLeads:0,processedLeads:0,currentMonthCommission:0,keywordsPage1:0,unpaidCommissions:0,unreadMessages:0,unreadNotifications:0},
        profile: {name:'',businessType:'',email:'',whatsapp:'',address:'',photo:'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=256&h=256&q=80&auto=format'},
        notifications: [],
        recentLeads: [], leads: [], page:1, perPage:8,
        products: [], popularProducts: [],
        keywords: [], topKeywords: [],
        commissions: [], messages: [], optimizations: [],
        documents: [],
        search: '',
        form: {type:'product',name:'',category:'',price:null,description:''},

        // FILTERS
        filters: {lead:'all',product:'all',keyword:'all',commission:'all',message:'all',optimization:'all'},

        // CARDS
        statCards: [
          {key:'newLeads', label:'Leads Baru', icon:'fas fa-bullseye', bg:'bg-blue-100', text:'text-blue-600', format:(v)=>v},
          {key:'processedLeads', label:'Leads Diproses', icon:'fas fa-check-circle', bg:'bg-green-100', text:'text-green-600', format:(v)=>v},
          {key:'currentMonthCommission', label:'Komisi Bulan Ini', icon:'fas fa-money-bill-wave', bg:'bg-purple-100', text:'text-purple-600', format:(v)=>'Rp '+(v||0).toLocaleString('id-ID')},
          {key:'keywordsPage1', label:'Keyword Page 1', icon:'fas fa-chart-line', bg:'bg-yellow-100', text:'text-yellow-600', format:(v)=>v}
        ],

        // COMPUTED-LIKE
        get filteredLeads(){
          const f = this.filters.lead==='all'? this.leads : this.leads.filter(l=>l.status===this.filters.lead);
          return f;
        },
        get totalPages(){ return Math.max(1, Math.ceil(this.filteredLeads.length/this.perPage)); },
        get pagedLeads(){
          const s=(this.page-1)*this.perPage;
          return this.filteredLeads.slice(s, s+this.perPage);
        },
        get filteredProducts(){
          if(this.filters.product==='all') return this.products;
          return this.products.filter(p=>p.type===this.filters.product);
        },
        get filteredKeywords(){
          if(this.filters.keyword==='all') return this.keywords;
          if(this.filters.keyword==='page1') return this.keywords.filter(k=>k.position<=10);
          if(this.filters.keyword==='page2') return this.keywords.filter(k=>k.position>10 && k.position<=20);
          return this.keywords.filter(k=>k.position>20);
        },
        get filteredCommissions(){
          if(this.filters.commission==='all') return this.commissions;
          return this.commissions.filter(c=>c.status===this.filters.commission);
        },
        get filteredMessages(){
          if(this.filters.message==='all') return this.messages;
          return this.messages.filter(m=>this.filters.message==='unread'?!m.read:m.read);
        },
        get filteredOptimizations(){
          if(this.filters.optimization==='all') return this.optimizations;
          return this.optimizations.filter(o=>o.status===this.filters.optimization);
        },

        // HELPERS
        badge(st){ return st==='converted'?'bg-green-100 text-green-800':(st==='processed'?'bg-blue-100 text-blue-800':'bg-yellow-100 text-yellow-800'); },
        statusText(st){ return st==='converted'?'Converted':(st==='processed'?'Diproses':'Baru'); },
        toIDR(n){ return (n||0).toLocaleString('id-ID'); },
        docIcon(t){ return t==='pdf'?'fa-file-pdf text-red-500':t==='doc'?'fa-file-word text-blue-500':t==='xls'?'fa-file-excel text-green-500':'fa-file-image text-yellow-500'; },

        // ACTIONS
        searchNow(){ /* opsional: filter di tab aktif */ },
        setLeadStatus(id,status){
          const i=this.leads.findIndex(l=>l.id===id);
          if(i>-1){ this.leads[i].status=status; }
          // TODO: panggil API nyata di sini
        },
        openLead(id){},
        openKeyword(id){},
        openCommission(id){},
        openOptimization(id){},
        openEditProduct(p){ this.form={...p}; Alpine.store('ui').modal='addProduct'; },
        deleteProduct(id){ this.products=this.products.filter(p=>p.id!==id); /* TODO API */ },
        removeDocument(id){ this.documents=this.documents.filter(d=>d.id!==id); /* TODO API */ },
        saveProduct(){
          if(!this.form.name) return alert('Nama harus diisi');
          if(this.form.id){
            const i=this.products.findIndex(p=>p.id===this.form.id);
            if(i>-1) this.products[i]={...this.form};
          }else{
            this.products.push({...this.form, id: Date.now(), leads:0});
          }
          this.popularProducts=this.products.slice(0,5);
          this.form={type:'product',name:'',category:'',price:null,description:''};
          Alpine.store('ui').modal=null;
        },
        exportLeads(){
          const rows = [['ID','Customer','Project','Tanggal','Status']].concat(
            this.filteredLeads.map(l=>[l.id,l.customer,l.project,l.date,l.status])
          );
          const csv = rows.map(r=>r.map(v=>`"${(v??'').toString().replace(/"/g,'""')}"`).join(',')).join('\n');
          const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url; a.download = 'leads.csv'; a.click(); URL.revokeObjectURL(url);
        },

        // INIT: load mock (gampang diganti fetch API beneran)
        async init(){
          const api = this.mockFetch; // ganti ke window.fetch utk real
          const [stats,profile,notes,leads,prods,kws,comms,msgs,opts,docs,recents,tops] = await Promise.all([
            api('stats'), api('profile'), api('notifications'), api('leads'), api('products'),
            api('keywords'), api('commissions'), api('messages'), api('optimizations'),
            api('documents'), api('leads_recent'), api('keywords_top')
          ]);
          this.stats = stats;
          this.profile = profile;
          this.notifications = notes;
          this.leads = leads; this.recentLeads = recents;
          this.products = prods; this.popularProducts = prods.slice(0,5);
          this.keywords = kws; this.topKeywords = tops;
          this.commissions = comms; this.messages = msgs; this.optimizations = opts;
          this.documents = docs;
        },

        // Mock API (samain struktur punya kamu)
        async mockFetch(endpoint){
          const db = {
            stats: {newLeads:5,unprocessedLeads:3,processedLeads:24,currentMonthCommission:1250000,keywordsPage1:8,unpaidCommissions:2,unreadMessages:3,unreadNotifications:1},
            profile: {name:'Labelin Aksara',businessType:'Konveksi & Label',email:'cs@labelin.co.id',whatsapp:'0812-3456-7890',address:'Sidoarjo, Jawa Timur',photo:'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=256&h=256&q=80&auto=format'},
            notifications: [
              {id:1,type:'lead',title:'Lead baru',message:'1 lead masuk untuk Label Baju',time:'10:30'},
              {id:2,type:'commission',title:'Komisi siap',message:'Periode 01–15 Agustus',time:'Kemarin'}
            ],
            leads: [
              {id:1001,customer:'Ridzy',project:'Label Baju Sidoarjo',date:'2025-08-15',status:'converted'},
              {id:1002,customer:'Nasma',project:'Villa Kaliurang',date:'2025-08-14',status:'processed'},
              {id:1003,customer:'Sisman',project:'Kursus Bahasa Inggris',date:'2025-08-14',status:'new'},
              {id:1004,customer:'TanBah',project:'Cetak Yasin',date:'2025-08-13',status:'new'}
            ],
            leads_recent: [
              {id:1009,project:'Label Baju Sidoarjo',customer:'Ridzy',date:'2025-08-15',status:'new'},
              {id:1008,project:'Villa Kaliurang',customer:'Nasma',date:'2025-08-15',status:'processed'}
            ],
            products: [
              {id:1,type:'product',name:'Label Woven Premium',category:'Label Pakaian',price:3500,leads:18,description:'Label woven premium, MOQ 100.'},
              {id:2,type:'service',name:'Jasa Desain Label',category:'Desain',price:150000,leads:9,description:'Desain label 1–2 hari.'}
            ],
            keywords: [
              {id:1,text:'label baju murah',project:'Label Baju Stratlaya',position:5,trend:'up',change:2,volume:1200},
              {id:2,text:'cetak yasin hardcover',project:'Cetak Yasin Bangkalan',position:12,trend:'down',change:3,volume:850},
              {id:3,text:'kursus bahasa inggris online',project:'Kursus Bahasa Inggris',position:8,trend:'up',change:1,volume:3200}
            ],
            keywords_top: [
              {id:1,text:'label baju murah',project:'Label Baju Stratlaya',position:5,trend:'up',change:2},
              {id:3,text:'kursus bahasa inggris online',project:'Kursus Bahasa Inggris',position:8,trend:'up',change:1}
            ],
            commissions: [
              {id:1,period:'2025-08-01 - 2025-08-15',leads:8,amount:1200000,status:'unpaid'},
              {id:2,period:'2025-07-01 - 2025-07-31',leads:12,amount:1800000,status:'paid'}
            ],
            messages: [
              {id:1,subject:'Follow up lead',preview:'Baik, akan segera saya...',sender:'Imersa',date:'2025-08-15',read:false},
              {id:2,subject:'Invoice',preview:'Mohon cek komisi...',sender:'Finance',date:'2025-08-14',read:true}
            ],
            optimizations: [
              {id:11,project:'Label Baju Stratlaya',keyword:'baju kaos distro',date:'2025-08-14',status:'pending'},
              {id:12,project:'Cetak Yasin Bangkalan',keyword:'cetak yasin premium',date:'2025-08-13',status:'in_progress'}
            ],
            documents: [
              {id:1,name:'Katalog-Label.pdf',type:'pdf',size:'1.2 MB',url:'#'},
              {id:2,name:'PriceList-2025.xlsx',type:'xls',size:'220 KB',url:'#'}
            ]
          };
          await new Promise(r=>setTimeout(r,120)); // feel async
          return db[endpoint];
        }
      });
    });
  </script>
</body>
</html>
