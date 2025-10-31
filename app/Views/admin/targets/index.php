<?= $this->include('admin/layouts/header') ?>
<?= $this->include('admin/layouts/sidebar') ?>

<div class="p-6 space-y-6 index-page" x-data="seoTargets()" x-init="init()">
    <head>
        <meta name="csrf-token-name" content="<?= csrf_token() ?>">
        <meta name="csrf-token" content="<?= csrf_hash() ?>">
    </head>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Management Targets SEO</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola target keyword untuk semua vendor</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= site_url('admin/targets/export-csv') ?>?<?= $_SERVER['QUERY_STRING'] ?? '' ?>" 
            class="inline-flex items-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-file-csv mr-2"></i> Export CSV
            </a>
            <button @click="openModal()" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i> Buat Target
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <form method="get" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
            <!-- Vendor Filter -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter text-blue-600 mr-1"></i> Filter Vendor
                </label>
                <select name="vendor_id" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
                    <option value="">📋 Semua Vendor</option>
                    <?php foreach($vendors as $vendor): ?>
                        <option value="<?= $vendor['id'] ?>" 
                                <?= ($vendorId == $vendor['id']) ? 'selected' : '' ?>>
                            🏢 <?= esc($vendor['business_name']) ?> (ID: <?= $vendor['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Priority Filter -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-flag text-blue-600 mr-1"></i> Filter Priority
                </label>
                <select name="priority"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
                    <option value="">Semua Priority</option>
                    <option value="high" <?= ($priority == 'high') ? 'selected' : '' ?>>🔴 High</option>
                    <option value="medium" <?= ($priority == 'medium') ? 'selected' : '' ?>>🟡 Medium</option>
                    <option value="low" <?= ($priority == 'low') ? 'selected' : '' ?>>🟢 Low</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-blue-600 mr-1"></i> Filter Status
                </label>
                <select name="status"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= ($status == 'pending') ? 'selected' : '' ?>>⏳ Pending</option>
                    <option value="in_progress" <?= ($status == 'in_progress') ? 'selected' : '' ?>>🔄 In Progress</option>
                    <option value="completed" <?= ($status == 'completed') ? 'selected' : '' ?>>✅ Completed</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center shadow-sm">
                    <i class="fas fa-search mr-2"></i> Terapkan Filter
                </button>
                <a href="<?= site_url('admin/targets') ?>" 
                   class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium flex items-center shadow-sm">
                    <i class="fas fa-refresh mr-2"></i> Reset
                </a>
            </div>
        </div>

        <!-- Active Filter Info -->
        <?php if(!empty($vendorId) || !empty($priority) || !empty($status)): ?>
        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <span class="text-sm text-blue-700">
                        Filter aktif: 
                        <?php if(!empty($vendorId)): ?>
                            <span class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
                                Vendor: <?= $vendorId ?>
                            </span>
                        <?php endif; ?>
                        <?php if(!empty($priority)): ?>
                            <span class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
                                Priority: <?= ucfirst($priority) ?>
                            </span>
                        <?php endif; ?>
                        <?php if(!empty($status)): ?>
                            <span class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
                                Status: <?= ucfirst(str_replace('_', ' ', $status)) ?>
                            </span>
                        <?php endif; ?>
                    </span>
                </div>
                <span class="text-xs text-blue-600 bg-white px-2 py-1 rounded">
                    <?= count($targets) ?> target ditemukan
                </span>
            </div>
        </div>
        <?php endif; ?>
    </form>

    <!-- Table Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="text-lg font-medium text-gray-900">Daftar Target</h2>
            <div class="text-sm text-gray-500">
                Total: <span class="font-medium"><?= count($targets) ?></span> target
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600 text-white uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Vendor</th>
                        <th class="px-4 py-3 text-left">Project</th>
                        <th class="px-4 py-3 text-left">Keyword</th>
                        <th class="px-4 py-3 text-center">Current</th>
                        <th class="px-4 py-3 text-center">Target</th>
                        <th class="px-4 py-3 text-center">Deadline</th>
                        <th class="px-4 py-3 text-center">Priority</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Notes</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($targets)): $no=1; foreach($targets as $t): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 text-center"><?= $no++ ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-building text-blue-600 text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900"><?= esc($t['vendor_name'] ?? '-') ?></div>
                                    <div class="text-xs text-gray-500">ID: <?= $t['vendor_id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3"><?= esc($t['project_name']) ?></td>
                        <td class="px-4 py-3"><?= esc($t['keyword']) ?></td>
                        <td class="px-4 py-3 text-center"><?= $t['current_position'] ?: '—' ?></td>
                        <td class="px-4 py-3 text-center"><?= $t['target_position'] ?: '—' ?></td>
                        <td class="px-4 py-3 text-center"><?= $t['deadline'] ? date('d M Y', strtotime($t['deadline'])) : '—' ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php 
                                $prio = strtolower($t['priority']);
                                $prioClass = match($prio) {
                                    'high'   => 'bg-red-100 text-red-700',
                                    'medium' => 'bg-yellow-100 text-yellow-700',
                                    default  => 'bg-green-100 text-green-700'
                                };
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $prioClass ?>">
                                <?= ucfirst($t['priority']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php 
                                $stat = strtolower($t['status']);
                                $statClass = match($stat) {
                                    'completed'   => 'bg-green-100 text-green-700',
                                    'in_progress' => 'bg-blue-100 text-blue-700',
                                    default       => 'bg-gray-100 text-gray-700'
                                };
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $statClass ?>">
                                <?= ucfirst(str_replace('_',' ',$t['status'])) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-[200px]"><?= esc($t['notes'] ?? '-') ?></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-wrap gap-2 justify-center">
                                <button @click="edit(<?= $t['id'] ?>)" 
                                        class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button @click="confirmDelete(<?= $t['id'] ?>)" 
                                        class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                    <i class="fas fa-trash-alt mr-1"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                                <p class="text-lg font-medium text-gray-900">Tidak ada data target</p>
                                <p class="mt-1 text-sm text-gray-500">
                                    <?php if(!empty($vendorId) || !empty($priority) || !empty($status)): ?>
                                        Tidak ada target dengan filter yang dipilih
                                    <?php else: ?>
                                        Belum ada target SEO yang tersedia
                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div x-show="showModal" x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="closeModal()"
        @click.self="closeModal()">
        
        <!-- Modal Content dengan struktur yang benar untuk scroll -->
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col mx-auto"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-blue-600 flex-shrink-0">
                <h3 class="text-xl font-semibold text-white" x-text="modalTitle"></h3>
                <button @click="closeModal()" 
                        class="text-white hover:text-gray-200 transition-colors p-1 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- FORM DIMULAI DI SINI - MEMBUNGKUS SELURUH KONTEN MODAL -->
            <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto flex flex-col">
                <div class="p-6 flex-1">
                    <input type="hidden" name="id" x-model="form.id">

                    <!-- Vendor Selection -->
                    <div>
                        <label for="vendor_id" class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                        <select id="vendor_id" x-model="form.vendor_id" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" 
                                required>
                            <option value="">Pilih Vendor</option>
                            <?php foreach($vendors as $vendor): ?>
                                <option value="<?= $vendor['id'] ?>"><?= esc($vendor['business_name']) ?> (ID: <?= $vendor['id'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Project Name -->
                    <div>
                        <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                        <input type="text" id="project_name" x-model="form.project_name" 
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" 
                            required>
                    </div>

                    <!-- Keyword -->
                    <div>
                        <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">Keyword</label>
                        <input type="text" id="keyword" x-model="form.keyword" 
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" 
                            required>
                    </div>

                    <!-- Current & Target -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="current_position" class="block text-sm font-medium text-gray-700 mb-1">Current Position</label>
                            <input type="number" id="current_position" x-model="form.current_position" min="1" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                        </div>
                        <div>
                            <label for="target_position" class="block text-sm font-medium text-gray-700 mb-1">Target Position</label>
                            <input type="number" id="target_position" x-model="form.target_position" min="1" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" 
                                required>
                        </div>
                    </div>

                    <!-- Deadline -->
                    <div>
                        <label for="deadline" class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                        <input type="date" id="deadline" x-model="form.deadline" 
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                    </div>

                    <!-- Priority & Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select id="priority" x-model="form.priority" 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" x-model="form.status" 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea id="notes" x-model="form.notes" rows="3" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3"></textarea>
                    </div>
                </div>

                <!-- Modal Footer - TOMBOL DI DALAM FORM -->
                <div class="flex flex-col sm:flex-row sm:justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
                    <button type="button" @click="closeModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="showDeleteModal = false"
         @click.self="showDeleteModal = false">
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-blue-600">
                <h3 class="text-lg font-semibold text-white">Konfirmasi Hapus</h3>
                <button @click="showDeleteModal = false" 
                        class="text-white hover:text-gray-200 transition-colors p-1 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Apakah Anda yakin?</h3>
                <p class="text-sm text-gray-500 text-center mb-6">Target yang dihapus tidak dapat dikembalikan. Apakah Anda yakin ingin menghapus target ini?</p>
                
                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row sm:justify-center gap-3">
                    <button @click="showDeleteModal = false" 
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors">
                        Batal
                    </button>
                    <button @click="executeDelete()" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors">
                        Hapus Target
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div x-show="notification.show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-4 right-4 z-[10001] bg-white rounded-lg shadow-lg border-l-4 p-4 max-w-md"
         :class="{
           'border-green-500': notification.type === 'success',
           'border-red-500': notification.type === 'error'
         }">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas text-xl" :class="{
                  'fa-check-circle text-green-500': notification.type === 'success',
                  'fa-exclamation-circle text-red-500': notification.type === 'error'
                }"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
            </div>
            <div class="ml-auto pl-3">
                <button @click="notification.show = false" 
                        class="text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div x-show="loading" x-cloak
         class="fixed inset-0 z-[10002] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;">
        
        <!-- Loading Content -->
        <div class="relative bg-white rounded-lg p-6 flex flex-col items-center shadow-xl">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
            <p class="text-gray-700 font-medium">Memproses...</p>
        </div>
    </div>
</div>

<style>
/* PERBAIKAN: CSS khusus untuk halaman index agar tidak mempengaruhi modal lain */
.index-page .fixed.inset-0 {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
    z-index: 9999 !important;
}

/* Pastikan modal backdrop menutupi seluruh viewport */
.index-page .modal-backdrop-full {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0, 0, 0, 0.5) !important;
    backdrop-filter: blur(4px) !important;
    z-index: 9998 !important;
}

/* Loading overlay yang memenuhi halaman */
.index-page .loading-overlay-full {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0, 0, 0, 0.7) !important;
    backdrop-filter: blur(8px) !important;
    z-index: 10000 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* Pastikan body tidak scroll saat modal terbuka */
body.modal-open {
    overflow: hidden !important;
    height: 100vh !important;
    position: fixed !important;
    width: 100% !important;
}

[x-cloak] { 
    display: none !important; 
}

/* Pastikan modal content di tengah */
.index-page .modal-content-center {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 10000 !important;
}
</style>

<script>
function seoTargets() {
    return {
        showModal: false,
        showDeleteModal: false,
        modalTitle: 'Tambah Target',
        notification: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            timeout: null
        },
        loading: false,
        deleteId: null,
        form: {
            id: '',
            vendor_id: '',
            project_name: '',
            keyword: '',
            current_position: '',
            target_position: '',
            deadline: '',
            priority: 'low',
            status: 'pending',
            notes: ''
        },
        init() {
            // Initialization code if needed
        },
        showNotification(type, title, message) {
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }
            
            this.notification.type = type;
            this.notification.title = title;
            this.notification.message = message;
            this.notification.show = true;
            
            this.notification.timeout = setTimeout(() => {
                this.notification.show = false;
            }, 5000);
        },
        openModal() {
            this.modalTitle = 'Tambah Target';
            this.resetForm();
            this.showModal = true;
            // Lock body scroll
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        },
        resetForm() {
            this.form = {
                id: '',
                vendor_id: '',
                project_name: '',
                keyword: '',
                current_position: '',
                target_position: '',
                deadline: '',
                priority: 'low',
                status: 'pending',
                notes: ''
            };
        },
        closeModal() {
            this.showModal = false;
            // Unlock body scroll
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        },
        edit(id) {
            this.loading = true;
            fetch(`<?= site_url('admin/targets/edit') ?>/${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(async res => {
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Server tidak mengembalikan JSON saat edit: ' + text);
                }
                if (!data.success) throw new Error(data.message || 'Gagal load data');
                this.modalTitle = 'Edit Target';
                this.form = { ...this.form, ...data.data };
                this.showModal = true;
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
                this.loading = false;
            })
            .catch(err => {
                this.loading = false;
                this.showNotification('error', 'Error', err.message);
            });
        },
        confirmDelete(id) {
            this.deleteId = id;
            this.showDeleteModal = true;
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        },
        executeDelete() {
            if (!this.deleteId) return;
            
            this.loading = true;
            this.showDeleteModal = false;
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            
            const csrfName = document.querySelector('meta[name="csrf-token-name"]').content;
            const csrfHash = document.querySelector('meta[name="csrf-token"]').content;

            const formData = new FormData();
            formData.append(csrfName, csrfHash);

            fetch(`<?= site_url('admin/targets/delete') ?>/${this.deleteId}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(async res => {
                const ct = res.headers.get('content-type') || '';
                const text = await res.text();
                if (ct.includes('application/json')) {
                    return JSON.parse(text);
                } else {
                    throw new Error('Server error: ' + text);
                }
            })
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.showNotification('success', 'Berhasil', 'Target berhasil dihapus');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.showNotification('error', 'Gagal', data.message || 'Gagal menghapus data.');
                }
            })
            .catch(err => {
                this.loading = false;
                this.showNotification('error', 'Error', 'Error: ' + err.message);
            });
        },
        submitForm() {
            this.loading = true;
            
            const csrfName = document.querySelector('meta[name="csrf-token-name"]').content;
            const csrfHash = document.querySelector('meta[name="csrf-token"]').content;

            const formData = new FormData();
            formData.append(csrfName, csrfHash);
            for (const key in this.form) {
                formData.append(key, this.form[key] ?? '');
            }

            const url = this.form.id
                ? `<?= site_url('admin/targets/update') ?>/${this.form.id}`
                : `<?= site_url('admin/targets/store') ?>`;

            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(async res => {
                const ct = res.headers.get('content-type') || '';
                const text = await res.text();
                if (ct.includes('application/json')) {
                    return JSON.parse(text);
                } else {
                    throw new Error('Server response bukan JSON: ' + text);
                }
            })
            .then(data => {
                this.loading = false;
                if (data.success) {
                    const action = this.form.id ? 'diperbarui' : 'ditambahkan';
                    this.showNotification('success', 'Berhasil', 'Target berhasil ' + action);
                    setTimeout(() => {
                        this.closeModal();
                        location.reload();
                    }, 1500);
                } else {
                    this.showNotification('error', 'Gagal', data.message || 'Gagal menyimpan data.');
                }
            })
            .catch(err => {
                this.loading = false;
                console.error(err);
                this.showNotification('error', 'Error', 'Terjadi kesalahan: ' + err.message);
            });
        }
    }
}

// Event listener untuk menangani escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const manager = document.querySelector('[x-data]').__x.$data;
        if (manager.showModal) {
            manager.closeModal();
        }
        if (manager.showDeleteModal) {
            manager.showDeleteModal = false;
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        }
    }
});
</script>

<?= $this->include('admin/layouts/footer') ?>