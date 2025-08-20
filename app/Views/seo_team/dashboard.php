<!DOCTYPE html>
<html lang="id" x-data="seoDashboard()" :class="{'overflow-hidden': modalOpen}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SEO Dashboard | Vendor Partnership SEO Performance</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    [x-cloak]{display:none!important}
    .sidebar{transition:all .3s ease}
    .nav-item{position:relative;transition:transform .14s ease, box-shadow .14s ease, background .14s ease}
    .nav-item:hover{transform:translateX(2px)}
    .nav-item.active{background:linear-gradient(90deg,rgba(59,130,246,.25),rgba(37,99,235,.35));box-shadow:inset 0 0 0 1px rgba(255,255,255,.08),0 0 0 2px rgba(59,130,246,.2),0 8px 28px rgba(30,64,175,.35)}
    .nav-item.active::before{content:"";position:absolute;left:-4px;top:10%;bottom:10%;width:6px;border-radius:9999px;background:radial-gradient(10px 60% at 50% 50%,rgba(191,219,254,.95),rgba(59,130,246,.4) 60%,transparent 70%)}
    .badge{font-size:.65rem;padding:.15rem .35rem}
  </style>
</head>
<body class="bg-gray-50 font-sans" x-cloak>
<div class="flex h-screen overflow-hidden">
  <!-- Sidebar -->
  <aside class="sidebar text-white w-64 fixed h-full p-4 flex flex-col bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900"
         :class="{'-ml-64': !sidebarOpen}" x-show="sidebarOpen" @click.away="sidebarOpen=false">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl font-bold flex items-center"><i class="fas fa-chart-line mr-2"></i> Imersa SEO</h1>
      <button @click="sidebarOpen=false" class="md:hidden"><i class="fas fa-times"></i></button>
    </div>

    <div class="border-b border-white/20 my-3"></div>

    <nav class="flex-1">
      <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Main</p>
      <a href="#" @click.prevent="setActive('Dashboard')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='Dashboard'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-gauge-high mr-3"></i> Dashboard
        <span class="badge bg-blue-500 rounded-full ml-auto" x-text="stats.newLeads"></span>
      </a>
      <a href="#" @click.prevent="setActive('VendorApprovals')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='VendorApprovals'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-user-check mr-3"></i> Approval Vendor
        <span class="badge bg-yellow-500 rounded-full ml-auto" x-text="pendingVendors.length"></span>
      </a>
      <a href="#" @click.prevent="setActive('LeadDistribution')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='LeadDistribution'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-share-alt mr-3"></i> Distribusi Leads
      </a>
      <a href="#" @click.prevent="setActive('SEOReports')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='SEOReports'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-chart-line mr-3"></i> Laporan SEO
      </a>
      <a href="#" @click.prevent="setActive('KeywordAnalysis')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='KeywordAnalysis'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-key mr-3"></i> Analisis Keyword
      </a>

      <p class="text-blue-200 uppercase text-xs font-semibold mt-6 mb-2">Kolaborasi</p>
      <a href="#" @click.prevent="setActive('VendorChat')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='VendorChat'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-comments mr-3"></i> Pesan Vendor
        <span class="badge bg-blue-500 rounded-full ml-auto" x-text="stats.unreadMessages"></span>
      </a>

      <p class="text-blue-200 uppercase text-xs font-semibold mt-6 mb-2">Komisi</p>
      <a href="#" @click.prevent="setActive('Commission')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='Commission'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-money-bill-wave mr-3"></i> Terima Komisi
        <span class="badge bg-yellow-500 rounded-full ml-auto" x-text="stats.pendingCommissions"></span>
      </a>
    </nav>

    <div class="pt-4 border-t border-blue-700/60">
      <a href="#" @click.prevent="setActive('Logout')" class="block py-2 px-3 rounded-lg flex items-center nav-item"
         :class="activeMenu==='Logout'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-sign-out-alt mr-3"></i> Logout
      </a>
    </div>
  </aside>

  <!-- Main -->
  <div class="flex-1 flex flex-col overflow-hidden" :class="{'md:ml-64': sidebarOpen}">
    <!-- Topbar -->
    <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen?'md:ml-64':''">
      <div class="flex items-center justify-between p-4">
        <button @click="sidebarOpen=!sidebarOpen" class="hover:opacity-80"><i class="fas fa-bars text-gray-700"></i></button>
        <div class="relative w-full max-w-md mx-4">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" x-model="searchKeyword" @keyup.enter="searchKeywordHandler"
                 class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                 placeholder="Cari keyword, vendor, atau leads...">
        </div>
        <div class="flex items-center space-x-4">
          <button @click="openModal('notifications')" class="relative text-gray-600 hover:text-gray-900">
            <i class="fas fa-bell text-xl"></i>
            <span x-show="stats.unreadNotifications>0"
                  class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
                  x-text="stats.unreadNotifications"></span>
          </button>
          <div class="relative ml-3">
            <button @click="profileDropdownOpen=!profileDropdownOpen" class="flex text-sm rounded-full focus:outline-none">
              <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=256&h=256&fit=facearea&facepad=2" alt="">
            </button>
            <div x-show="profileDropdownOpen" @click.away="profileDropdownOpen=false"
                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black/5">
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
              <a href="#" @click.prevent="setActive('Logout')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="h-16"></div>

    <main class="flex-1 overflow-y-auto p-4 bg-gray-50">

      <!-- DASHBOARD -->
      <template x-if="activeMenu==='Dashboard'">
        <div>
          <!-- Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4"><i class="fas fa-bolt text-lg"></i></div>
                <div><p class="text-sm text-gray-500">Leads Baru</p><p class="text-2xl font-semibold" x-text="stats.newLeads"></p></div>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4"><i class="fas fa-check-circle text-lg"></i></div>
                <div><p class="text-sm text-gray-500">Leads Terdistribusi</p><p class="text-2xl font-semibold" x-text="stats.distributedLeads"></p></div>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4"><i class="fas fa-chart-line text-lg"></i></div>
                <div><p class="text-sm text-gray-500">Keyword Ranking Naik</p><p class="text-2xl font-semibold" x-text="stats.rankingUp"></p></div>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4"><i class="fas fa-money-bill-wave text-lg"></i></div>
                <div><p class="text-sm text-gray-500">Komisi Belum Diterima</p><p class="text-2xl font-semibold" x-text="stats.pendingCommissions"></p></div>
              </div>
            </div>
          </div>

          <!-- Leads + Pesan + Keyword Warning -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Leads Baru -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center justify-between">
                  <span><i class="fas fa-bolt mr-2 text-blue-600"></i> Leads Baru</span>
                  <button @click="openModal('assignLeads')" class="text-sm text-blue-600 hover:text-blue-800">Assign Semua</button>
                </h3>
              </div>
              <div class="divide-y divide-gray-200">
                <template x-for="lead in newLeads" :key="lead.id">
                  <div class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="lead.project"></p>
                        <p class="text-sm text-gray-500 truncate" x-text="'Dari: '+lead.customer"></p>
                      </div>
                      <div class="ml-4 flex-shrink-0 space-x-2">
                        <button @click="assignLead(lead.id)" class="text-green-600 hover:text-green-800" title="Assign"><i class="fas fa-user-plus"></i></button>
                        <button @click="viewLeadDetail(lead.id)" class="text-blue-600 hover:text-blue-800" title="Detail"><i class="fas fa-eye"></i></button>
                      </div>
                    </div>
                    <div class="mt-2 flex justify-between text-sm text-gray-500">
                      <span x-text="'Keyword: '+lead.keyword"></span><span x-text="lead.date"></span>
                    </div>
                  </div>
                </template>
              </div>
              <div class="px-4 py-4 sm:px-6 border-t bg-gray-50">
                <a href="#" @click.prevent="setActive('LeadDistribution')" class="text-sm font-medium text-blue-600 hover:text-blue-500">Lihat semua leads</a>
              </div>
            </div>

            <!-- Pesan Terbaru -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center"><i class="fas fa-comments mr-2 text-purple-600"></i> Pesan Terbaru</h3>
              </div>
              <div class="divide-y divide-gray-200">
                <template x-for="message in recentMessages" :key="message.id">
                  <div class="px-4 py-4 sm:px-6 hover:bg-gray-50 cursor-pointer" @click="openChat(message.vendorId)">
                    <div class="flex items-center">
                      <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center"><i class="fas fa-store text-purple-600"></i></div>
                      <div class="ml-4 w-full">
                        <div class="flex justify-between">
                          <p class="text-sm font-medium text-gray-900" x-text="message.vendor"></p>
                          <span class="text-xs text-gray-500" x-text="message.time"></span>
                        </div>
                        <p class="text-sm text-gray-500 truncate" x-text="message.text"></p>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
              <div class="px-4 py-4 sm:px-6 border-t bg-gray-50">
                <a href="#" @click.prevent="setActive('VendorChat')" class="text-sm font-medium text-blue-600 hover:text-blue-500">Lihat semua percakapan</a>
              </div>
            </div>

            <!-- Keyword Perlu Optimasi -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center"><i class="fas fa-triangle-exclamation mr-2 text-yellow-600"></i> Keyword Perlu Optimasi</h3>
              </div>
              <div class="divide-y divide-gray-200">
                <template x-for="keyword in needOptimization" :key="keyword.id">
                  <div class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                      <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="keyword.keyword"></p>
                        <p class="text-sm text-gray-500" x-text="'Posisi: '+keyword.position"></p>
                      </div>
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="keyword.trend==='up'?'bg-green-100 text-green-800':'bg-red-100 text-red-800'"
                            x-text="keyword.trend==='up'?'Naik':'Turun'"></span>
                    </div>
                    <div class="mt-2 flex justify-between text-sm text-gray-500">
                      <span x-text="'Proyek: '+keyword.project"></span>
                      <button @click="openKeywordDetail(keyword.id)" class="text-blue-600 hover:text-blue-800">Optimasi</button>
                    </div>
                  </div>
                </template>
              </div>
              <div class="px-4 py-4 sm:px-6 border-t bg-gray-50">
                <a href="#" @click.prevent="setActive('KeywordAnalysis')" class="text-sm font-medium text-blue-600 hover:text-blue-500">Analisis lebih lanjut</a>
              </div>
            </div>
          </div>

          <!-- Komisi Menunggu Penerimaan -->
          <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900 flex items-center justify-between">
                <span><i class="fas fa-money-bill-wave mr-2 text-green-600"></i> Komisi Menunggu Penerimaan</span>
                <button @click="openModal('receiveAll')" class="text-sm text-green-600 hover:text-green-800">Terima Semua</button>
              </h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proyek</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Leads</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Komisi</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="commission in pendingCommissions" :key="commission.id">
                    <tr>
                      <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="commission.vendor"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="commission.project"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="commission.leads"></td>
                      <td class="px-6 py-4 text-sm text-gray-500" x-text="'Rp '+commission.amount.toLocaleString('id-ID')"></td>
                      <td class="px-6 py-4 text-right text-sm font-medium">
                        <button @click="receiveCommission(commission.id)" class="text-green-600 hover:text-green-900 mr-3">Terima</button>
                        <button @click="openModal('commissionDetail', commission.id)" class="text-blue-600 hover:text-blue-900">Detail</button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
            <div class="px-4 py-4 sm:px-6 border-t bg-gray-50">
              <a href="#" @click.prevent="setActive('Commission')" class="text-sm font-medium text-blue-600 hover:text-blue-500">Lihat semua komisi</a>
            </div>
          </div>
        </div>
      </template>

      <!-- VENDOR APPROVALS -->
      <template x-if="activeMenu==='VendorApprovals'">
        <div>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center"><i class="fas fa-user-check mr-2 text-blue-600"></i> Approval Vendor</h2>
          </div>
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Area</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="v in pendingVendors" :key="v.id">
                  <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="v.name"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="v.email"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="v.area"></td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                      <button @click="approveVendor(v.id)" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                      <button @click="rejectVendor(v.id)" class="text-red-600 hover:text-red-900">Reject</button>
                    </td>
                  </tr>
                </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>

      <!-- LEADS -->
      <template x-if="activeMenu==='LeadDistribution'">
        <div>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center"><i class="fas fa-share-alt mr-2 text-blue-600"></i> Distribusi Leads</h2>
            <div class="flex space-x-2">
              <button @click="openModal('filterLeads')" class="px-3 py-2 border rounded-md text-sm bg-white hover:bg-gray-50"><i class="fas fa-filter mr-2"></i>Filter</button>
              <button @click="openModal('exportLeads')" class="px-3 py-2 border rounded-md text-sm bg-white hover:bg-gray-50"><i class="fas fa-file-export mr-2"></i>Export</button>
            </div>
          </div>
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="text-lg font-medium text-gray-900">Daftar Leads</h3>
                  <p class="mt-1 text-sm text-gray-500">Total <span x-text="leads.length"></span> leads ditemukan</p>
                </div>
                <div class="relative">
                  <select x-model="leadFilter" @change="filterLeads" class="appearance-none bg-gray-100 border text-gray-700 py-2 px-4 pr-8 rounded-md focus:outline-none focus:bg-white focus:border-gray-500 text-sm">
                    <option value="all">Semua Status</option>
                    <option value="new">Baru</option>
                    <option value="assigned">Terdistribusi</option>
                    <option value="converted">Converted</option>
                    <option value="unresponsive">Tidak Responsif</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Lead</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proyek/Keyword</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="lead in filteredLeads" :key="lead.id">
                  <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#'+lead.id"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="lead.customer"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="lead.project"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="lead.vendor || '-'"></td>
                    <td class="px-6 py-4">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="{'bg-green-100 text-green-800':lead.status==='converted','bg-blue-100 text-blue-800':lead.status==='assigned','bg-yellow-100 text-yellow-800':lead.status==='new','bg-red-100 text-red-800':lead.status==='unresponsive'}"
                            x-text="{converted:'Converted',assigned:'Terdistribusi',new:'Baru',unresponsive:'Tidak Responsif'}[lead.status]"></span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="lead.date"></td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                      <template x-if="lead.status==='new'">
                        <button @click="openModal('assignLead', lead.id)" class="text-green-600 hover:text-green-900 mr-3">Assign</button>
                      </template>
                      <template x-if="lead.status==='unresponsive'">
                        <button @click="openModal('reassignLead', lead.id)" class="text-blue-600 hover:text-blue-900 mr-3">Reassign</button>
                      </template>
                      <button @click="openModal('leadDetail', lead.id)" class="text-blue-600 hover:text-blue-900">Detail</button>
                    </td>
                  </tr>
                </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>

      <!-- SEO REPORTS -->
      <template x-if="activeMenu==='SEOReports'">
        <div>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center"><i class="fas fa-chart-line mr-2 text-blue-600"></i> Laporan SEO</h2>
            <button @click="openModal('addReport')" class="px-3 py-2 rounded-md text-sm text-white bg-blue-600 hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Tambah Laporan</button>
          </div>
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="text-lg font-medium text-gray-900">Riwayat Laporan</h3>
                  <p class="mt-1 text-sm text-gray-500">Total <span x-text="seoReports.length"></span> laporan</p>
                </div>
                <div class="relative">
                  <select x-model="reportFilter" @change="filterReports" class="appearance-none bg-gray-100 border text-gray-700 py-2 px-4 pr-8 rounded-md text-sm focus:outline-none">
                    <option value="all">Semua Proyek</option>
                    <option value="project1">Label Baju Stratlaya</option>
                    <option value="project2">Cetak Yasin Bangkalan</option>
                    <option value="project3">Kursus Bahasa Inggris</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proyek</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keyword</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posisi</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perubahan</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="report in filteredReports" :key="report.id">
                  <tr>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="report.date"></td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="report.project"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="report.keyword"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="report.position"></td>
                    <td class="px-6 py-4">
                      <span class="flex items-center" :class="report.trend==='up'?'text-green-600':'text-red-600'">
                        <i class="fas" :class="report.trend==='up'?'fa-arrow-up':'fa-arrow-down'"></i>
                        <span x-text="report.change" class="ml-1"></span>
                      </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                      <button @click="openModal('editReport', report.id)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                      <button @click="deleteReport(report.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                    </td>
                  </tr>
                </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>

      <!-- KEYWORD ANALYSIS -->
      <template x-if="activeMenu==='KeywordAnalysis'">
        <div>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center"><i class="fas fa-key mr-2 text-blue-600"></i> Analisis Keyword</h2>
            <button @click="openModal('addKeyword')" class="px-3 py-2 rounded-md text-sm text-white bg-blue-600 hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Tambah Keyword</button>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Performa -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 border-b"><h3 class="text-lg font-medium text-gray-900">Performa Keyword</h3></div>
              <div class="px-4 py-5">
                <div class="flex items-center justify-between mb-4"><div class="text-sm text-gray-500">Total Keyword</div><div class="text-2xl font-semibold" x-text="stats.totalKeywords"></div></div>
                <div class="space-y-4">
                  <div>
                    <div class="flex items-center justify-between mb-1"><span class="text-sm text-gray-500">Page 1 (1-10)</span><span class="text-sm font-semibold" x-text="stats.keywordsPage1"></span></div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5"><div class="bg-green-600 h-2.5 rounded-full" :style="'width:'+(stats.keywordsPage1/stats.totalKeywords*100)+'%'"></div></div>
                  </div>
                  <div>
                    <div class="flex items-center justify-between mb-1"><span class="text-sm text-gray-500">Page 2 (11-20)</span><span class="text-sm font-semibold" x-text="stats.keywordsPage2"></span></div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5"><div class="bg-yellow-500 h-2.5 rounded-full" :style="'width:'+(stats.keywordsPage2/stats.totalKeywords*100)+'%'"></div></div>
                  </div>
                  <div>
                    <div class="flex items-center justify-between mb-1"><span class="text-sm text-gray-500">Page 3+ (>20)</span><span class="text-sm font-semibold" x-text="stats.keywordsPage3"></span></div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5"><div class="bg-red-600 h-2.5 rounded-full" :style="'width:'+(stats.keywordsPage3/stats.totalKeywords*100)+'%'"></div></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Trend -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 border-b"><h3 class="text-lg font-medium text-gray-900">Trend Keyword</h3></div>
              <div class="px-4 py-5">
                <div class="flex items-center justify-center h-48 bg-gray-50 rounded-md mb-4"><p class="text-gray-500">Chart placeholder</p></div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center"><div class="text-2xl font-semibold text-green-600" x-text="stats.keywordsUp"></div><div class="text-sm text-gray-500">Keyword Naik</div></div>
                  <div class="text-center"><div class="text-2xl font-semibold text-red-600" x-text="stats.keywordsDown"></div><div class="text-sm text-gray-500">Keyword Turun</div></div>
                </div>
              </div>
            </div>

            <!-- Aksi Cepat -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 border-b"><h3 class="text-lg font-medium text-gray-900">Aksi Cepat</h3></div>
              <div class="px-4 py-5">
                <div class="grid grid-cols-1 gap-4">
                  <button @click="openModal('optimizeKeywords')" class="w-full px-4 py-2 rounded-md text-sm text-white bg-purple-600 hover:bg-purple-700"><i class="fas fa-wand-magic-sparkles mr-2"></i>Optimasi Keyword</button>
                  <button @click="openModal('exportKeywords')" class="w-full px-4 py-2 rounded-md text-sm text-white bg-indigo-600 hover:bg-indigo-700"><i class="fas fa-file-export mr-2"></i>Export Data</button>
                  <button @click="openModal('requestContent')" class="w-full px-4 py-2 rounded-md text-sm text-white bg-green-600 hover:bg-green-700"><i class="fas fa-edit mr-2"></i>Request Konten</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabel Keyword -->
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="text-lg font-medium text-gray-900">Daftar Keyword</h3>
                  <p class="mt-1 text-sm text-gray-500">Total <span x-text="keywords.length"></span> keyword</p>
                </div>
                <div class="relative">
                  <select x-model="keywordFilter" @change="filterKeywords" class="appearance-none bg-gray-100 border text-gray-700 py-2 px-4 pr-8 rounded-md text-sm focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="page1">Page 1</option>
                    <option value="page2">Page 2</option>
                    <option value="page3">Page 3+</option>
                    <option value="up">Trend Naik</option>
                    <option value="down">Trend Turun</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keyword</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proyek</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posisi</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perubahan</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Volume</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="keyword in filteredKeywords" :key="keyword.id">
                  <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="keyword.text"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="keyword.project"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="keyword.position"></td>
                    <td class="px-6 py-4">
                      <span class="flex items-center" :class="keyword.trend==='up'?'text-green-600':'text-red-600'">
                        <i class="fas" :class="keyword.trend==='up'?'fa-arrow-up':'fa-arrow-down'"></i>
                        <span x-text="keyword.change" class="ml-1"></span>
                      </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="keyword.volume"></td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                      <button @click="openModal('keywordDetail', keyword.id)" class="text-blue-600 hover:text-blue-900 mr-3">Detail</button>
                      <button @click="openModal('optimizeKeyword', keyword.id)" class="text-green-600 hover:text-green-900">Optimasi</button>
                    </td>
                  </tr>
                </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>

      <!-- CHAT VENDOR -->
      <template x-if="activeMenu==='VendorChat'">
        <div class="h-full flex flex-col">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center"><i class="fas fa-comments mr-2 text-blue-600"></i> Pesan Vendor</h2>
            <button @click="openModal('newChat')" class="px-3 py-2 rounded-md text-sm text-white bg-blue-600 hover:bg-blue-700"><i class="fas fa-plus mr-2"></i>Chat Baru</button>
          </div>

          <div class="flex-1 flex overflow-hidden bg-white rounded-lg shadow">
            <!-- List -->
            <div class="w-1/3 border-r flex flex-col">
              <div class="p-4 border-b"><input type="text" placeholder="Cari vendor..." class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"></div>
              <div class="flex-1 overflow-y-auto">
                <template x-for="chat in vendorChats" :key="chat.id">
                  <div @click="selectChat(chat.id)" class="p-4 border-b hover:bg-gray-50 cursor-pointer" :class="{'bg-blue-50':activeChat===chat.id}">
                    <div class="flex items-center">
                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center"><i class="fas fa-store text-blue-600"></i></div>
                      <div class="ml-3 w-full">
                        <div class="flex justify-between">
                          <p class="text-sm font-medium text-gray-900" x-text="chat.vendor"></p>
                          <span class="text-xs text-gray-500" x-text="chat.time"></span>
                        </div>
                        <p class="text-sm text-gray-500 truncate" x-text="chat.lastMessage"></p>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>
            <!-- Room -->
            <div class="flex-1 flex flex-col" x-show="activeChat">
              <div class="p-4 border-b flex items-center">
                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center"><i class="fas fa-store text-blue-600"></i></div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900" x-text="getActiveChat().vendor"></p>
                  <p class="text-xs text-gray-500" x-text="getActiveChat().status"></p>
                </div>
              </div>
              <div class="flex-1 p-4 overflow-y-auto bg-gray-50">
                <template x-for="message in getActiveChat().messages" :key="message.id">
                  <div class="mb-4" :class="{'text-right':message.sender==='me'}">
                    <div class="inline-block max-w-xs lg:max-w-md px-4 py-2 rounded-lg"
                         :class="message.sender==='me'?'bg-blue-600 text-white':'bg-white text-gray-800 border'">
                      <p x-text="message.text"></p>
                      <p class="text-xs mt-1" :class="message.sender==='me'?'text-blue-200':'text-gray-500'" x-text="message.time"></p>
                    </div>
                  </div>
                </template>
              </div>
              <div class="p-4 border-t">
                <div class="flex items-center">
                  <input type="text" placeholder="Ketik pesan..." class="flex-1 px-3 py-2 border rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                  <button class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700"><i class="fas fa-paper-plane"></i></button>
                </div>
              </div>
            </div>
            <!-- Placeholder -->
            <div x-show="!activeChat" class="flex-1 flex items-center justify-center bg-gray-50">
              <div class="text-center"><i class="fas fa-comments text-4xl text-gray-300 mb-2"></i><p class="text-gray-500">Pilih percakapan untuk memulai chat</p></div>
            </div>
          </div>
        </div>
      </template>

      <!-- COMMISSIONS -->
      <template x-if="activeMenu==='Commission'">
        <div>
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center"><i class="fas fa-money-bill-wave mr-2 text-blue-600"></i> Terima Komisi</h2>
            <div class="flex space-x-2">
              <button @click="openModal('filterCommissions')" class="px-3 py-2 border rounded-md text-sm bg-white hover:bg-gray-50"><i class="fas fa-filter mr-2"></i>Filter</button>
              <button @click="openModal('exportCommissions')" class="px-3 py-2 border rounded-md text-sm bg-white hover:bg-gray-50"><i class="fas fa-file-export mr-2"></i>Export</button>
            </div>
          </div>
          <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h3 class="text-lg font-medium text-gray-900">Daftar Komisi</h3>
                  <p class="mt-1 text-sm text-gray-500">Total <span x-text="commissions.length"></span> komisi</p>
                </div>
                <div class="relative">
                  <select x-model="commissionFilter" @change="filterCommissions" class="appearance-none bg-gray-100 border text-gray-700 py-2 px-4 pr-8 rounded-md text-sm focus:outline-none">
                    <option value="all">Semua Status</option>
                    <option value="pending">Belum Diterima</option>
                    <option value="received">Diterima</option>
                    <option value="rejected">Ditolak</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Komisi</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Leads</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Komisi</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="commission in filteredCommissions" :key="commission.id">
                  <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900" x-text="'#'+commission.id"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="commission.vendor"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="commission.period"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="commission.leads"></td>
                    <td class="px-6 py-4 text-sm text-gray-500" x-text="'Rp '+commission.amount.toLocaleString('id-ID')"></td>
                    <td class="px-6 py-4">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="{'bg-yellow-100 text-yellow-800':commission.status==='pending','bg-green-100 text-green-800':commission.status==='received','bg-red-100 text-red-800':commission.status==='rejected'}"
                            x-text="{pending:'Belum Diterima',received:'Diterima',rejected:'Ditolak'}[commission.status]"></span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                      <template x-if="commission.status==='pending'">
                        <button @click="receiveCommission(commission.id)" class="text-green-600 hover:text-green-900 mr-3">Terima</button>
                      </template>
                      <button @click="openModal('commissionDetail', commission.id)" class="text-blue-600 hover:text-blue-900">Detail</button>
                    </td>
                  </tr>
                </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </template>

      <!-- LOGOUT -->
      <template x-if="activeMenu==='Logout'">
        <div class="flex items-center justify-center h-full">
          <div class="text-center max-w-md p-8 bg-white rounded-lg shadow-lg">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
              <i class="fas fa-sign-out-alt text-blue-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Keluar dari Sistem</h2>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin keluar dari sistem?</p>
            <div class="flex justify-center space-x-4">
              <button @click="activeMenu='Dashboard'" class="px-4 py-2 border rounded-md text-sm bg-white hover:bg-gray-50">Batal</button>
              <button @click="logout()" class="px-4 py-2 rounded-md text-sm text-white bg-red-600 hover:bg-red-700">Ya, Keluar</button>
            </div>
          </div>
        </div>
      </template>

    </main>
  </div>

  <!-- ===== MODALS (contoh: Assign Lead & Add Report; lainnya ikuti pola) ===== -->
  <div x-show="modalOpen==='assignLead'" class="fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div x-show="modalOpen==='assignLead'" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
      <div x-show="modalOpen==='assignLead'" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100"><i class="fas fa-user-plus text-blue-600"></i></div>
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg font-medium text-gray-900">Assign Lead</h3>
            <div class="mt-2">
              <form class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 text-left">Lead</label>
                  <p class="mt-1 text-sm text-gray-500" x-text="selectedLead.project"></p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 text-left">Pilih Vendor</label>
                  <select class="mt-1 block w-full border rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option>Pilih Vendor</option>
                    <template x-for="vendor in vendors" :key="vendor.id">
                      <option :value="vendor.id" x-text="vendor.name"></option>
                    </template>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 text-left">Catatan (Opsional)</label>
                  <textarea rows="3" class="mt-1 block w-full border rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
          <button type="button" @click="confirmAssign()" class="w-full inline-flex justify-center rounded-md bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Assign Lead</button>
          <button @click="modalOpen=null" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border px-4 py-2 bg-white">Batal</button>
        </div>
      </div>
    </div>
  </div>

  <div x-show="modalOpen==='addReport'" class="fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div x-show="modalOpen==='addReport'" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
      <div x-show="modalOpen==='addReport'" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100"><i class="fas fa-chart-line text-green-600"></i></div>
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg font-medium text-gray-900">Tambah Laporan SEO</h3>
            <div class="mt-2">
              <form class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 text-left">Proyek</label>
                  <select class="mt-1 block w-full border rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option>Pilih Proyek</option>
                    <option>Label Baju Stratlaya</option>
                    <option>Cetak Yasin Bangkalan</option>
                    <option>Kursus Bahasa Inggris</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 text-left">Keyword</label>
                  <input type="text" class="mt-1 block w-full border rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 text-left">Posisi</label>
                    <input type="number" class="mt-1 block w-full border rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 text-left">Perubahan</label>
                    <div class="flex">
                      <select class="mt-1 block w-1/3 border rounded-l-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="up">Naik</option>
                        <option value="down">Turun</option>
                      </select>
                      <input type="number" class="mt-1 block w-2/3 border-l-0 border rounded-r-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 text-left">Catatan</label>
                  <textarea rows="3" class="mt-1 block w-full border rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3">
          <button type="button" class="w-full inline-flex justify-center rounded-md bg-blue-600 text-white px-4 py-2 hover:bg-blue-700">Simpan Laporan</button>
          <button @click="modalOpen=null" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border px-4 py-2 bg-white">Batal</button>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function seoDashboard(){
  return {
    // UI
    sidebarOpen: window.innerWidth>768,
    profileDropdownOpen:false,
    modalOpen:null,
    activeMenu:'Dashboard',
    activeChat:null,
    searchKeyword:'',
    // filters
    leadFilter:'all', reportFilter:'all', keywordFilter:'all', commissionFilter:'all',
    // stats
    stats:{ newLeads:24, distributedLeads:156, rankingUp:38, pendingCommissions:12,
            unreadMessages:5, unreadNotifications:3, totalKeywords:128,
            keywordsPage1:42, keywordsPage2:35, keywordsPage3:51, keywordsUp:28, keywordsDown:15 },
    // data (dummy → ganti via controller CI4)
    pendingVendors:[
      {id:11, name:'CV Sinar Abadi', email:'sinar@vendor.id', area:'Jawa Timur'},
      {id:12, name:'PT Maju Jaya', email:'admin@majujaya.co', area:'DIY'}
    ],
    newLeads:[
      {id:1, project:'Label Baju Sidoarjo', customer:'Ridzy', keyword:'label baju murah', date:'2025-08-15'},
      {id:2, project:'Villa Kaliurang', customer:'Nasma', keyword:'villa di kaliurang', date:'2025-08-15'}
    ],
    recentMessages:[
      {id:1, vendorId:1, vendor:'Labelin Aksara', text:'Baik, akan segera saya follow up leadnya', time:'10:30'},
      {id:2, vendorId:2, vendor:'Yasin Barokah', text:'Ukuran A5 bisa ya?', time:'09:45'}
    ],
    needOptimization:[
      {id:1, keyword:'baju kaos distro', position:'15', project:'Label Baju Stratlaya', trend:'down'},
      {id:2, keyword:'villa murah di jogja', position:'8', project:'Villa Kaliurang', trend:'up'}
    ],
    pendingCommissions:[
      {id:1, vendor:'Labelin Aksara', project:'Label Baju Stratlaya', leads:8, amount:1200000},
      {id:2, vendor:'Yasin Barokah', project:'Cetak Yasin Bangkalan', leads:5, amount:750000}
    ],
    leads:[
      {id:1001, customer:'Ridzy', project:'Label Baju Sidoarjo', vendor:'Labelin Aksara', status:'converted', date:'2025-08-15'},
      {id:1002, customer:'Nasma', project:'Villa Kaliurang', vendor:'Villa Asri', status:'assigned', date:'2025-08-14'},
      {id:1003, customer:'Sisman', project:'Kursus Bahasa Inggris', vendor:'Kursus Pro', status:'new', date:'2025-08-14'}
    ],
    seoReports:[
      {id:1, date:'2025-08-15', project:'Label Baju Stratlaya', keyword:'label baju murah', position:'5', trend:'up', change:'2'},
      {id:2, date:'2025-08-14', project:'Cetak Yasin Bangkalan', keyword:'cetak yasin hardcover', position:'12', trend:'down', change:'3'}
    ],
    keywords:[
      {id:1, text:'label baju murah', project:'Label Baju Stratlaya', position:'5', trend:'up', change:'2', volume:'1200'},
      {id:2, text:'cetak yasin hardcover', project:'Cetak Yasin Bangkalan', position:'12', trend:'down', change:'3', volume:'850'}
    ],
    vendorChats:[
      {id:1, vendor:'Labelin Aksara', lastMessage:'Baik, segera follow up', time:'10:30', status:'Online',
       messages:[{id:1,sender:'vendor',text:'Ada lead baru?',time:'10:00'},{id:2,sender:'me',text:'Ada 1 lead label baju',time:'10:15'}]},
      {id:2, vendor:'Yasin Barokah', lastMessage:'A5 bisa?', time:'09:45', status:'Offline',
       messages:[{id:1,sender:'vendor',text:'Cetak yasin A5 bisa?',time:'09:45'}]}
    ],
    commissions:[
      {id:1, vendor:'Labelin Aksara', period:'2025-08-01 - 2025-08-15', leads:8, amount:1200000, status:'pending'},
      {id:2, vendor:'Yasin Barokah', period:'2025-08-01 - 2025-08-15', leads:5, amount:750000, status:'pending'},
      {id:3, vendor:'Kursus Pro', period:'2025-07-01 - 2025-07-31', leads:12, amount:1800000, status:'received'}
    ],
    vendors:[{id:1,name:'Labelin Aksara'},{id:2,name:'Yasin Barokah'},{id:3,name:'Kursus Pro'}],

    // computed
    get filteredLeads(){ if(this.leadFilter==='all') return this.leads; return this.leads.filter(l=>l.status===this.leadFilter); },
    get filteredReports(){ if(this.reportFilter==='all') return this.seoReports; return this.seoReports.filter(r=>r.project.toLowerCase().includes(this.reportFilter)); },
    get filteredKeywords(){
      if(this.keywordFilter==='all') return this.keywords;
      if(this.keywordFilter==='page1') return this.keywords.filter(k=>k.position<=10);
      if(this.keywordFilter==='page2') return this.keywords.filter(k=>k.position>10&&k.position<=20);
      if(this.keywordFilter==='page3') return this.keywords.filter(k=>k.position>20);
      if(this.keywordFilter==='up') return this.keywords.filter(k=>k.trend==='up');
      if(this.keywordFilter==='down') return this.keywords.filter(k=>k.trend==='down');
      return this.keywords;
    },
    get filteredCommissions(){ if(this.commissionFilter==='all') return this.commissions; return this.commissions.filter(c=>c.status===this.commissionFilter); },

    // methods (hook ke endpoint CI4 nantinya)
    setActive(n){ this.activeMenu=n; },
    openModal(name,id=null){ this.modalOpen=name; if(id && name==='assignLead'){ this.selectedLead=this.leads.find(l=>l.id===id)||{}; } },
    searchKeywordHandler(){ console.log('search:', this.searchKeyword); },
    approveVendor(id){ this.pendingVendors = this.pendingVendors.filter(v=>v.id!==id); /* CALL: /approvals/vendors/approve/:id */ },
    rejectVendor(id){  this.pendingVendors = this.pendingVendors.filter(v=>v.id!==id); /* CALL: /approvals/vendors/reject/:id  */ },
    assignLead(id){ this.openModal('assignLead', id); },
    confirmAssign(){ /* CALL: /leads/assign */ this.modalOpen=null; },
    viewLeadDetail(){ /* open drawer/detail */ },
    receiveCommission(id){
      const i=this.commissions.findIndex(c=>c.id===id);
      if(i!==-1){ this.commissions[i].status='received'; }
      /* CALL: /commissions/receive/:id */
    },
    selectChat(id){ this.activeChat=id; },
    getActiveChat(){ return this.vendorChats.find(c=>c.id===this.activeChat)||{messages:[]}; },
    deleteReport(id){ this.seoReports=this.seoReports.filter(r=>r.id!==id); /* CALL: /reports/delete/:id */ },
    logout(){ window.location.href='/login'; },

    selectedLead:{},

    init(){
      const pref=localStorage.getItem('sidebarOpen');
      this.sidebarOpen = pref!==null ? pref==='true' : window.innerWidth>768;
      window.addEventListener('resize',()=>{ if(window.innerWidth<=768){ this.sidebarOpen=false; } });
      this.$watch('sidebarOpen',v=>localStorage.setItem('sidebarOpen',v));
    }
  }
}
</script>
</body>
</html>