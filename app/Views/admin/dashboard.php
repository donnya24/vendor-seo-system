<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<!-- CONTENT WRAPPER -->
<div
  id="pageWrap"
  x-data
  class="flex-1 flex flex-col min-h-screen bg-gray-50 pb-16 md:pb-0
         transition-[margin] duration-300 ease-in-out"
  :class="$store.ui.sidebarOpen ? 'md:ml-64' : 'md:ml-0'"
>
  <!-- MAIN (fade-in saat turbo:load) -->
  <main
    id="pageMain"
    class="flex-1 overflow-y-auto p-4 no-scrollbar transition-opacity duration-300 opacity-0"
  >
    <!-- STATS CARDS -->
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

    <!-- RECENT LEADS -->
    <section class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-4 sm:px-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
          <i class="fas fa-list mr-2 text-blue-600"></i> Recent Leads
        </h3>
        <div class="flex items-center space-x-2 self-end sm:self-auto">
          <a href="<?= site_url('admin/leads'); ?>" class="bg-blue-600 hover:bg-blue-700 transition text-white px-3 py-2 rounded-md text-sm flex items-center">
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
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Baru</span>
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
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Proses</span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell"><?= date('d M Y', strtotime('-1 day')) ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
