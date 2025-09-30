<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-8">
    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-tachometer-alt text-blue-600"></i> Dashboard SEO
            </h1>
            <p class="text-gray-600 mt-1">Monitor performa SEO dan target keyword</p>
        </div>
        <div>
            <a href="<?= site_url('seo/reports?vendor_id='.$vendorId) ?>" 
               class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                <i class="fas fa-file-alt mr-2"></i> Laporan SEO
            </a>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Leads -->
        <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl shadow border border-gray-200 overflow-hidden relative">
            <div class="p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Leads (Periode)</p>
                        <p class="text-3xl font-bold text-gray-900"><?= (int)($leadStats['total'] ?? 0) ?></p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-xl">
                        <i class="fas fa-user-friends text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Target Aktif -->
        <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl shadow border border-gray-200 overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Target Aktif</p>
                        <p class="text-3xl font-bold text-gray-900"><?= count($targets) ?></p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-xl">
                        <i class="fas fa-bullseye text-purple-600 text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center">
                    <p class="text-sm text-gray-500">Prioritas tinggi:</p>
                    <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                        <?= count(array_filter($targets, fn($t)=>($t['priority'] ?? '')==='high')) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Komisi -->
        <div class="bg-gradient-to-br from-green-50 to-white rounded-xl shadow border border-gray-200 overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Komisi (Periode)</p>
                        <p class="text-3xl font-bold text-gray-900">
                            <?= ($commission && $commission['total_amount']>0)
                                ? 'Rp'.number_format($commission['total_amount'],0,',','.')
                                : '—' ?>
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-xl">
                        <i class="fas fa-money-bill-wave text-green-600 text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center">
                    <p class="text-sm text-gray-500"></p>
                    <div class="ml-2">
                        <?= $commission && $commission['total_amount']>0
                            ? '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">'.strtoupper($commission['status'] ?? 'draft').'</span>'
                            : '<span class="text-gray-500 text-sm">Belum dihitung</span>' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP KEYWORDS -->
    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-chart-line text-blue-600"></i> Target / Keyword
            </h2>
            <a href="<?= site_url('seo/targets?vendor_id='.$vendorId) ?>" 
               class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline flex items-center">
                Kelola target <i class="fas fa-arrow-right ml-1 text-xs"></i>
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-blue-600 text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center">No</th>
                        <th class="px-4 py-3 text-left">Vendor</th>
                        <th class="px-4 py-3 text-left">Project / Keyword</th>
                        <th class="px-4 py-3 text-center">Posisi</th>
                        <th class="px-4 py-3 text-center">Target</th>
                        <th class="px-4 py-3 text-center">Deadline</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($targets)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                Belum ada target keyword.
                            </td>
                        </tr>
                    <?php else: $no=1; foreach(array_slice($targets, 0, 8) as $t): ?>
                        <tr class="hover:bg-blue-50 transition-colors">
                            <td class="px-4 py-3 text-center text-gray-500"><?= $no++ ?></td>
                            <td class="px-4 py-3 font-medium text-gray-900"><?= esc($t['vendor_name'] ?? '-') ?></td>
                            <td class="px-4 py-3">
                                <div class="text-gray-900 font-medium"><?= esc($t['project_name'] ?? '-') ?></div>
                                <div class="text-xs text-gray-500"><?= esc($t['keyword'] ?? '-') ?></div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="font-semibold text-gray-900"><?= $t['current_position'] ?? '—' ?></div>
                                <div class="text-xs">
                                    <?php if (!is_null($t['last_change'])): ?>
                                        <?php if ($t['last_change'] > 0): ?>
                                            <span class="text-green-600"><i class="fas fa-arrow-up"></i> +<?= $t['last_change'] ?></span>
                                        <?php elseif ($t['last_change'] < 0): ?>
                                            <span class="text-red-600"><i class="fas fa-arrow-down"></i> <?= $t['last_change'] ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400">0</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center"><?= $t['target_position'] ?: '—' ?></td>
                            <td class="px-4 py-3 text-center"><?= $t['deadline'] ?: '—' ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?= $t['status']==='in_progress'?'bg-blue-100 text-blue-800':($t['status']==='completed'?'bg-green-100 text-green-800':'bg-gray-100 text-gray-800') ?>">
                                    <?= esc($t['status'] ?? '-') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($targets) > 8): ?>
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 text-sm">
            <a href="<?= site_url('seo/targets?vendor_id='.$vendorId) ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                Lihat semua target (<?= count($targets) ?> total)
                <i class="fas fa-arrow-right ml-1 text-xs"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
