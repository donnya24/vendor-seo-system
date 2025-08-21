<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>

<!-- Main Content -->
<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <button @click="sidebarOpen = !sidebarOpen" class="hamburger-btn"><i class="fas fa-bars text-gray-700"></i></button>
      <div class="relative w-full max-w-md mx-4">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-search text-gray-400"></i></div>
        <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500
          focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search vendors, projects, leads...">
      </div>
      <div class="flex items-center">
        <div class="relative ml-3">
          <button class="flex text-sm rounded-full focus:outline-none">
            <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Admin"/>
          </button>
        </div>
      </div>
    </div>
  </header>
  <div class="h-16"></div>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white p-4 rounded-lg shadow"><div class="flex items-center">
        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4"><i class="fas fa-users text-lg"></i></div>
        <div><p class="text-sm font-medium text-gray-500">Total Vendors</p><p class="text-2xl font-semibold"><?= esc($stats['totalVendors']); ?></p></div>
      </div></div>
      <div class="bg-white p-4 rounded-lg shadow"><div class="flex items-center">
        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4"><i class="fas fa-bolt text-lg"></i></div>
        <div><p class="text-sm font-medium text-gray-500">Today's Leads</p><p class="text-2xl font-semibold"><?= esc($stats['todayLeads']); ?></p></div>
      </div></div>
      <div class="bg-white p-4 rounded-lg shadow"><div class="flex items-center">
        <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4"><i class="fas fa-handshake text-lg"></i></div>
        <div><p class="text-sm font-medium text-gray-500">Monthly Deals</p><p class="text-2xl font-semibold"><?= esc($stats['monthlyDeals']); ?></p></div>
      </div></div>
      <div class="bg-white p-4 rounded-lg shadow"><div class="flex items-center">
        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4"><i class="fas fa-key text-lg"></i></div>
        <div><p class="text-sm font-medium text-gray-500">Top Keywords</p><p class="text-2xl font-semibold"><?= esc($stats['topKeywords']); ?></p></div>
      </div></div>
    </div>

    <!-- Placeholder list (bisa kamu sambungkan sesuai kebutuhan) -->
    <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
          <i class="fas fa-list mr-2 text-blue-600"></i> Recent Leads
        </h3>
        <div class="flex items-center space-x-2">
          <a href="<?= site_url('admin/leads/create'); ?>" class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm flex items-center">
            <i class="fas fa-plus mr-1"></i> New Lead
          </a>
        </div>
      </div>
      <div class="p-4 text-gray-500 text-sm">Hubungkan ke tabel <code>leads</code> di halaman Leads.</div>
    </div>
  </main>
</div>

<?= $this->include('admin/partials/logout'); ?>
<?= $this->include('layouts/footer'); ?>
