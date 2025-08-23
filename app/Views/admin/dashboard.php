<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<!-- Konten -->
<div class="flex-1 flex flex-col min-h-screen md:ml-64 pb-16 md:pb-0">
  <!-- Topbar -->
  <header class="bg-white shadow z-10 sticky top-0">
    <div class="flex items-center justify-between p-4">
      <!-- Tombol sidebar untuk mobile -->
      <button class="md:hidden text-gray-700 p-2 rounded-md hover:bg-gray-100 sidebar-toggle" 
              @click="sidebarOpen = true" 
              aria-label="Toggle sidebar"
              style="z-index: 40;">
        <i class="fas fa-bars"></i>
      </button>

      <!-- Desktop Search -->
      <div class="relative w-full max-w-md mx-4 hidden md:block">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-search text-gray-400"></i>
        </div>
        <input type="text"
               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500
                      focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
               placeholder="Search vendors, projects, leads...">
      </div>

      <!-- Mobile Search -->
      <div class="md:hidden flex-1 mx-2" x-show="searchOpen" x-transition x-cloak>
        <div class="relative">
          <input type="text"
                 x-ref="searchInput"
                 class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500
                        focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                 placeholder="Search...">
          <button @click="searchOpen = false" class="absolute right-2 top-2 text-gray-400">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center space-x-3">
        <!-- Mobile Search Toggle -->
        <button class="md:hidden text-gray-700 p-2 rounded-md hover:bg-gray-100" 
                @click="toggleSearch" 
                aria-label="Toggle search">
          <i class="fas fa-search"></i>
        </button>

        <!-- Desktop Profile -->
        <div class="hidden md:flex items-center space-x-3 profile-dropdown">
          <button @click="profileDropdownOpen = !profileDropdownOpen" class="flex items-center space-x-2">
            <img class="h-8 w-8 rounded-full" src="https://i.pravatar.cc/80" alt="Admin">
            <span class="hidden lg:inline text-sm font-medium">Admin User</span>
            <i class="fas fa-chevron-down text-xs"></i>
          </button>
          
          <!-- Profile Dropdown Desktop -->
          <div x-show="profileDropdownOpen" x-cloak 
               class="absolute right-4 top-14 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
            <button @click="showLogoutModal = true" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50 no-scrollbar">
    <!-- Stats Cards - Grid Responsive -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6">
      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4"><i class="fas fa-users text-lg"></i></div>
          <div>
            <p class="text-sm font-medium text-gray-500">Total Vendors</p>
            <p class="text-2xl font-semibold"><?= esc($stats['totalVendors'] ?? 0); ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4"><i class="fas fa-bolt text-lg"></i></div>
          <div>
            <p class="text-sm font-medium text-gray-500">Today's Leads</p>
            <p class="text-2xl font-semibold"><?= esc($stats['todayLeads'] ?? 0); ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4"><i class="fas fa-handshake text-lg"></i></div>
          <div>
            <p class="text-sm font-medium text-gray-500">Monthly Deals</p>
            <p class="text-2xl font-semibold"><?= esc($stats['monthlyDeals'] ?? 0); ?></p>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4"><i class="fas fa-key text-lg"></i></div>
          <div>
            <p class="text-sm font-medium text-gray-500">Top Keywords</p>
            <p class="text-2xl font-semibold"><?= esc($stats['topKeywords'] ?? 0); ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Leads Section -->
    <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-4 sm:px-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
          <i class="fas fa-list mr-2 text-blue-600"></i> Recent Leads
        </h3>
        <div class="flex items-center space-x-2 self-end sm:self-auto">
          <a href="<?= site_url('admin/leads'); ?>" class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm flex items-center">
            <i class="fas fa-eye mr-1"></i> View All
          </a>
        </div>
      </div>
      <div class="p-4">
        <div class="overflow-x-auto no-scrollbar">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Layanan</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Tanggal</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr>
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">John Doe</div>
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">Pembuatan Website</td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    Baru
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell"><?= date('d M Y') ?></td>
              </tr>
              <tr>
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">Jane Smith</div>
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">Digital Marketing</td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    Proses
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell"><?= date('d M Y', strtotime('-1 day')) ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>