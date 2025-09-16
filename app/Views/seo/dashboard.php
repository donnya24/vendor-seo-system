<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<!-- FILTER -->
<form method="get" class="flex flex-wrap items-center gap-3 bg-white p-4 rounded-xl shadow border border-blue-50">
  <input type="hidden" name="vendor_id" value="<?= esc($vendorId) ?>">
  <input type="date" name="start" value="<?= esc($start) ?>" class="border border-blue-100 rounded px-3 py-2 focus:ring-2 focus:ring-blue-200">
  <span class="text-gray-400">—</span>
  <input type="date" name="end" value="<?= esc($end) ?>" class="border border-blue-100 rounded px-3 py-2 focus:ring-2 focus:ring-blue-200">
  <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
    <i class="fas fa-filter mr-2"></i> Filter
  </button>
</form>

<!-- STATS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
  <!-- Leads -->
  <div class="bg-white rounded-xl shadow p-4 border border-blue-50 hover:shadow-md transition">
    <div class="text-sm text-gray-500">Leads (Periode)</div>
    <div class="text-3xl font-bold text-blue-700"><?= (int)($leadStats['total'] ?? 0) ?></div>
    <div class="text-xs mt-2 text-gray-600">
      Closed: <span class="text-green-600"><?= (int)($leadStats['closed'] ?? 0) ?></span> ·
      On going: <span class="text-yellow-600"><?= (int)($leadStats['in_progress'] ?? 0) ?></span> ·
      New: <span class="text-blue-600"><?= (int)($leadStats['new_cnt'] ?? 0) ?></span>
    </div>
  </div>

  <!-- Target Aktif -->
  <div class="bg-white rounded-xl shadow p-4 border border-blue-50 hover:shadow-md transition">
    <div class="text-sm text-gray-500">Target Aktif</div>
    <div class="text-3xl font-bold text-blue-700"><?= count($targets) ?></div>
    <div class="text-xs mt-2 text-gray-600">
      Prioritas tinggi: <span class="text-red-600">
        <?= count(array_filter($targets, fn($t)=>($t['priority'] ?? '')==='high')) ?>
      </span>
    </div>
  </div>

  <!-- Komisi -->
  <div class="bg-white rounded-xl shadow p-4 border border-blue-50 hover:shadow-md transition">
    <div class="text-sm text-gray-500">Komisi (Periode)</div>
    <div class="text-3xl font-bold text-blue-700">
      <?= ($commission && $commission['total_amount']>0)
          ? 'Rp'.number_format($commission['total_amount'],0,',','.')
          : '—' ?>
    </div>
    <div class="text-xs mt-2 text-gray-600">
      <?= $commission && $commission['total_amount']>0
          ? strtoupper($commission['status'] ?? 'draft')
          : 'Belum dihitung' ?>
    </div>
  </div>
</div>

<!-- TOP KEYWORDS -->
<div class="bg-white rounded-xl shadow p-4 border border-blue-50 mt-6">
  <div class="flex items-center justify-between mb-3">
    <h3 class="text-lg font-semibold text-gray-800">
      <i class="fas fa-chart-line text-blue-600 mr-2"></i> Target / Keyword
    </h3>
    <a href="<?= site_url('seo/targets?vendor_id='.$vendorId) ?>" 
       class="text-sm text-blue-600 hover:underline">
       Kelola targets
    </a>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm border-collapse">
      <thead class="bg-blue-50 text-blue-800">
        <tr>
          <th class="p-3 text-left">Project / Keyword</th>
          <th class="p-3 text-center">Posisi</th>
          <th class="p-3 text-center">Target</th>
          <th class="p-3 text-center">Deadline</th>
          <th class="p-3 text-center">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (empty($targets)): ?>
        <tr>
          <td colspan="5" class="p-4 text-center text-gray-500">Belum ada target keyword.</td>
        </tr>
        <?php else: foreach(array_slice($targets, 0, 8) as $t): ?>
        <tr class="hover:bg-blue-50 transition">
          <td class="p-3">
            <div class="font-medium text-gray-800"><?= esc($t['project_name'] ?? '-') ?></div>
            <div class="text-xs text-gray-500"><?= esc($t['keyword'] ?? '-') ?></div>
          </td>
          <td class="p-3 text-center">
            <div class="font-semibold"><?= $t['last_position'] ?? $t['current_position'] ?? '—' ?></div>
            <div class="text-xs <?= ($t['last_trend']??'')==='up'?'text-green-600':(($t['last_trend']??'')==='down'?'text-red-600':'text-gray-400') ?>">
              <?= $t['last_trend'] ?? 'stable' ?>
              <?= $t['last_change'] ? '(' . ($t['last_change']>0?'+':'') . $t['last_change'] . ')' : '' ?>
            </div>
          </td>
          <td class="p-3 text-center"><?= $t['target_position'] ?: '—' ?></td>
          <td class="p-3 text-center"><?= $t['deadline'] ?: '—' ?></td>
          <td class="p-3 text-center">
            <span class="px-3 py-1 rounded-full text-xs 
              <?= $t['status']==='in_progress'?'bg-blue-100 text-blue-700':($t['status']==='completed'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-700') ?>">
              <?= esc($t['status'] ?? '-') ?>
            </span>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
