<?= $this->include('admin/layouts/header'); ?>

<div x-data="adminDashboard()" x-init="init()" x-cloak class="flex">
  <?= $this->include('admin/layouts/sidebar'); ?>
  
  <div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
    
    <!-- HEADER -->
    <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
      <div class="flex items-center justify-between p-4">
        <h2 class="text-lg font-semibold flex items-center gap-2">
          <i class="fas fa-bullhorn text-blue-600"></i> Edit Announcement
        </h2>
        <a href="<?= site_url('admin/announcements'); ?>" class="text-sm text-blue-600 hover:underline">
          ← Kembali
        </a>
      </div>
    </header>

    <div class="h-16"></div>

    <!-- MAIN CONTENT -->
    <main class="p-4 bg-gray-50">
      <form action="<?= site_url('admin/announcements/'.$item['id'].'/update'); ?>" method="post"
            class="bg-white rounded-lg shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <?= csrf_field() ?>

        <!-- Title -->
        <div class="md:col-span-2">
          <label class="block text-gray-700 font-medium mb-1">Judul Announcement</label>
          <input name="title" required value="<?= esc($item['title']); ?>"
                 class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Content -->
        <div class="md:col-span-2">
          <label class="block text-gray-700 font-medium mb-1">Konten</label>
          <textarea name="content" rows="6" required
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"><?= esc($item['content']); ?></textarea>
        </div>

        <!-- Publish -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">Tanggal Publish</label>
          <input type="datetime-local" name="publish_at"
                 value="<?= $item['publish_at'] ? date('Y-m-d\TH:i', strtotime($item['publish_at'])) : date('Y-m-d\TH:i'); ?>"
                 class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Expire -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">Tanggal Berakhir</label>
          <input type="datetime-local" name="expires_at"
                 value="<?= $item['expires_at'] ? date('Y-m-d\TH:i', strtotime($item['expires_at'])) : ''; ?>"
                 class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Audience -->
        <div>
          <label class="block text-gray-700 font-medium mb-1">Target Audience</label>
          <select name="audience"
                  class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="all" <?= ($item['audience'] ?? '') === 'all' ? 'selected' : ''; ?>>Semua Pengguna</option>
            <option value="vendor" <?= ($item['audience'] ?? '') === 'vendor' ? 'selected' : ''; ?>>Vendor</option>
            <option value="seo_team" <?= ($item['audience'] ?? '') === 'seo_team' ? 'selected' : ''; ?>>Tim SEO</option>
          </select>
        </div>

        <!-- Buttons -->
        <div class="md:col-span-2 flex gap-3 mt-4">
          <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded font-medium">
            <i class="fa fa-save mr-1"></i> Update
          </button>
          <a href="<?= site_url('admin/announcements'); ?>"
             class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">Batal</a>
        </div>
      </form>
    </main>

    <?= $this->include('admin/layouts/footer'); ?>
  </div>
</div>

<?= $this->include('admin/partials/logout'); ?>

<script>
function adminDashboard(){
  return {
    sidebarOpen: window.innerWidth > 768,
    init(){
      const p = localStorage.getItem('sidebarOpen');
      this.sidebarOpen = p !== null ? (p === 'true') : (window.innerWidth > 768);
      window.addEventListener('resize',()=>{ if(window.innerWidth<=768) this.sidebarOpen=false });
      this.$watch('sidebarOpen',v=>localStorage.setItem('sidebarOpen',v));
    }
  }
}
</script>
