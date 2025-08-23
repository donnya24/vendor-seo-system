<?= $this->include('admin/layouts/header'); ?>
<div x-data="adminDashboard()" x-init="init()" x-cloak class="flex">
  <?= $this->include('admin/layouts/sidebar'); ?>
  <div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
    <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
      <div class="flex items-center justify-between p-4">
        <h2 class="text-lg font-semibold"><i class="fas fa-bullhorn mr-2 text-blue-600"></i> Edit Announcement</h2>
        <a href="<?= site_url('admin/announcements'); ?>" class="text-sm text-blue-600 hover:underline">Back</a>
      </div>
    </header>
    <div class="h-16"></div>

    <main class="p-4 bg-gray-50">
      <form action="<?= site_url('admin/announcements/'.$item['id'].'/update'); ?>" method="post" class="bg-white rounded shadow p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <?= csrf_field() ?>
        <div class="md:col-span-2">
          <label class="block text-gray-700 mb-1">Title</label>
          <input name="title" required class="w-full border rounded px-3 py-2" value="<?= esc($item['title']); ?>">
        </div>
        <div class="md:col-span-2">
          <label class="block text-gray-700 mb-1">Content</label>
          <textarea name="content" rows="5" required class="w-full border rounded px-3 py-2"><?= esc($item['content']); ?></textarea>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Publish Date</label>
          <input type="date" name="publish_date" class="w-full border rounded px-3 py-2" value="<?= esc($item['publish_date']); ?>">
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Expire Date</label>
          <input type="date" name="expire_date" class="w-full border rounded px-3 py-2" value="<?= esc($item['expire_date']); ?>">
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Audience</label>
          <select name="audience" class="w-full border rounded px-3 py-2">
            <?php foreach(['all','admin','seoteam','vendor'] as $aud): ?>
              <option value="<?= $aud ?>" <?= ($item['audience']??'all')===$aud?'selected':''; ?>><?= $aud ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <input id="pin" type="checkbox" name="is_pinned" value="1" class="h-4 w-4" <?= ($item['is_pinned']??0)?'checked':''; ?>>
          <label for="pin" class="text-gray-700">Pin to top</label>
        </div>
        <div class="md:col-span-2 flex gap-2">
          <button class="px-4 py-2 bg-green-600 text-white rounded">Update</button>
          <a href="<?= site_url('admin/announcements'); ?>" class="px-4 py-2 border rounded">Cancel</a>
        </div>
      </form>
    </main>

    <?= $this->include('admin/layouts/footer'); ?>
  </div>
</div>
<?= $this->include('admin/partials/logout'); ?>
<script>function adminDashboard(){return{sidebarOpen:window.innerWidth>768,init(){const p=localStorage.getItem('sidebarOpen');this.sidebarOpen=p!==null?(p==='true'):(window.innerWidth>768);window.addEventListener('resize',()=>{if(window.innerWidth<=768)this.sidebarOpen=false});this.$watch('sidebarOpen',v=>localStorage.setItem('sidebarOpen',v));}}}</script>
