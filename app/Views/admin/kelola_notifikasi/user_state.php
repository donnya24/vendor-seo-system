<?= $this->include('admin/layouts/header') ?>
<?= $this->include('admin/layouts/sidebar') ?>

<div class="content-area">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">User Notification State</h1>
                <p class="text-gray-600 mt-1">Kelola status notifikasi pengguna</p>
            </div>
            <div class="flex space-x-3 mt-4 sm:mt-0">
                <a href="<?= site_url('admin/kelola-notifikasi') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Notifikasi
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <?= session()->getFlashdata('success') ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <?= session()->getFlashdata('error') ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <label for="filter" class="block text-sm font-medium text-gray-700 mb-2">Filter Status Notifikasi</label>
                    <select id="filter" name="filter" class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Semua Notifikasi</option>
                        <option value="read" <?= $filter == 'read' ? 'selected' : '' ?>>Notifikasi Terbaca</option>
                        <option value="unread" <?= $filter == 'unread' ? 'selected' : '' ?>>Notifikasi Belum Dibaca</option>
                        <option value="hidden" <?= $filter == 'hidden' ? 'selected' : '' ?>>Notifikasi Terhapus</option>
                        <optgroup label="User SEO">
                            <?php foreach ($seoProfiles as $seo): ?>
                                <option value="seo_<?= $seo['id'] ?>" <?= $filter == 'seo_' . $seo['id'] ? 'selected' : '' ?>>SEO: <?= esc($seo['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="User Vendor">
                            <?php foreach ($vendorProfiles as $vendor): ?>
                                <option value="vendor_<?= $vendor['id'] ?>" <?= $filter == 'vendor_' . $vendor['id'] ? 'selected' : '' ?>>Vendor: <?= esc($vendor['business_name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="button" id="applyFilter" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter mr-2"></i> Terapkan
                    </button>
                    <form id="deleteAllForm" action="<?= site_url('admin/kelola-notifikasi/user-state/delete-all') ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="filter" id="deleteFilter" value="<?= $filter ?>">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors" 
                                onclick="return confirm('Apakah Anda yakin ingin menghapus SEMUA data user state? Tindakan ini tidak dapat dibatalkan!')">
                            <i class="fas fa-trash-alt mr-2"></i> Hapus Semua
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg mr-4">
                        <i class="fas fa-database text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-purple-600 font-medium">Total User States</p>
                        <p class="text-2xl font-bold text-purple-900"><?= number_format($stats['user_state_count'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg mr-4">
                        <i class="fas fa-bell text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Total Notifikasi</p>
                        <p class="text-2xl font-bold text-blue-900"><?= number_format($stats['total_notifications'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 rounded-lg mr-4">
                        <i class="fas fa-eye-slash text-orange-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-orange-600 font-medium">Belum Dibaca</p>
                        <p class="text-2xl font-bold text-orange-900"><?= number_format($stats['unread_count'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notifikasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibaca</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dihapus</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($userStates)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-users-slash text-3xl mb-2 text-gray-300"></i>
                                    <p class="text-lg">Tidak ada data user state</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($userStates as $state): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php
                                            if (!empty($state['admin_name'])) {
                                                echo esc($state['admin_name']) . ' <span class="text-purple-600 text-xs">(Admin)</span>';
                                            } elseif (!empty($state['seo_name'])) {
                                                echo esc($state['seo_name']) . ' <span class="text-blue-600 text-xs">(SEO)</span>';
                                            } elseif (!empty($state['vendor_name'])) {
                                                echo esc($state['vendor_name']) . ' <span class="text-green-600 text-xs">(Vendor)</span>';
                                            } else {
                                                echo esc($state['username'] ?? 'Unknown User');
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= esc($state['type'] ?? 'general') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= esc($state['title']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500 truncate max-w-xs">
                                            <?= esc($state['message']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col space-y-1">
                                            <?php if ($state['is_read']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 w-fit">
                                                    <i class="fas fa-check mr-1"></i>
                                                    Dibaca
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 w-fit">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Belum Dibaca
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($state['hidden']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 w-fit">
                                                    <i class="fas fa-eye-slash mr-1"></i>
                                                    Terhapus
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $state['read_at'] ? date('d M Y H:i', strtotime($state['read_at'])) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $state['hidden_at'] ? date('d M Y H:i', strtotime($state['hidden_at'])) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d M Y H:i', strtotime($state['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('filter');
    const applyFilterBtn = document.getElementById('applyFilter');
    const deleteFilterInput = document.getElementById('deleteFilter');
    
    applyFilterBtn.addEventListener('click', function() {
        const filterValue = filterSelect.value;
        const url = new URL(window.location.href);
        url.searchParams.set('filter', filterValue);
        window.location.href = url.toString();
    });
    
    filterSelect.addEventListener('change', function() {
        deleteFilterInput.value = this.value;
    });
});
</script>

<?= $this->include('admin/layouts/footer') ?>