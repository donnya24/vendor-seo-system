<?= $this->include('layouts/header'); ?>
<?= $this->include('layouts/sidebar'); ?>
<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <h2 class="text-xl font-bold text-gray-800"><i class="fas fa-toolbox mr-2 text-green-600"></i> Vendor Services — <?= esc($vendor['business_name'] ?? ('Vendor #'.$vendor['id'])); ?></h2>
      <form method="post" action="<?= site_url('admin/vendors/'.$vendor['id'].'/services/attach'); ?>" class="flex gap-2">
        <?= csrf_field(); ?>
        <select name="service_id" class="border rounded-md px-3 py-2">
          <?php foreach($services as $s): ?>
            <?php if(!in_array($s['id'], $attachedIds)): ?>
              <option value="<?= esc($s['id']); ?>"><?= esc($s['name']); ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
        <button class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"><i class="fas fa-plus mr-2"></i> Attach</button>
      </form>
    </div>
  </header>
  <div class="h-16"></div>
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <div class="bg-white shadow rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php foreach($services as $s): ?>
          <div class="border rounded-lg p-4 flex items-center justify-between
            <?= in_array($s['id'],$attachedIds)?'bg-green-50 border-green-200':'bg-white'; ?>">
            <div>
              <div class="font-semibold"><?= esc($s['name']); ?></div>
              <div class="text-sm text-gray-500">ID: <?= esc($s['id']); ?></div>
            </div>
            <?php if(in_array($s['id'],$attachedIds)): ?>
              <form method="post" action="<?= site_url('admin/vendors/'.$vendor['id'].'/services/'.$s['id'].'/detach'); ?>">
                <?= csrf_field(); ?>
                <button class="text-red-600 hover:text-red-800"><i class="fas fa-times"></i> Detach</button>
              </form>
            <?php else: ?>
              <span class="text-gray-400 text-xs">not attached</span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>
<?= $this->include('layouts/footer'); ?>
