<?= $this->include('admin/layouts/header'); ?>
<div x-data="adminDashboard()" x-init="init()" x-cloak class="flex">
  <?= $this->include('admin/layouts/sidebar'); ?>

  <div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
    <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
      <div class="flex items-center justify-between p-4">
        <h2 class="text-lg font-semibold"><i class="fas fa-toolbox mr-2 text-blue-600"></i> Services (Read-Only)</h2>
        <button @click="sidebarOpen = !sidebarOpen"><i class="fas fa-bars text-gray-700"></i></button>
      </div>
    </header>
    <div class="h-16"></div>

    <main class="p-4 bg-gray-50">
      <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach($items as $it): ?>
              <tr>
                <td class="px-6 py-4"><?= esc($it['id']); ?></td>
                <td class="px-6 py-4 font-semibold"><?= esc($it['name']); ?></td>
                <td class="px-6 py-4 text-sm text-gray-600"><?= esc($it['description'] ?? '-'); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>

    <?= $this->include('admin/layouts/footer'); ?>
  </div>
</div>
<?= $this->include('admin/partials/logout'); ?>
<script>
  function adminDashboard(){return{sidebarOpen:window.innerWidth>768,init(){const p=localStorage.getItem('sidebarOpen');this.sidebarOpen=p!==null?(p==='true'):(window.innerWidth>768);window.addEventListener('resize',()=>{if(window.innerWidth<=768)this.sidebarOpen=false});this.$watch('sidebarOpen',v=>localStorage.setItem('sidebarOpen',v));}}}
</script>
