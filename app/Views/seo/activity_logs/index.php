<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<?php
// Hitung pagination manual
$currentPage = isset($page) ? max(1, (int)$page) : max(1, (int)($_GET['page'] ?? 1));
$perPageGuess = isset($perPage) ? (int)$perPage : (is_array($logs ?? null) ? max(1, count($logs)) : 10);
$offset = ($currentPage - 1) * $perPageGuess;
$startNo = $offset + 1;
?>

<h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Log Aktivitas SEO</h2>

<!-- Info Summary -->
<div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-blue-700 font-medium">
                Total Aktivitas: <strong><?= count($logs) ?></strong> records
            </p>
            <p class="text-xs text-blue-600 mt-1">
                Menampilkan semua aktivitas user SEO
            </p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                SEO Activities
            </span>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white text-xs uppercase tracking-wide">
                <tr>
                    <th class="p-4 text-center w-16 font-semibold">No</th>
                    <th class="p-4 text-center font-semibold">Waktu</th>
                    <th class="p-4 text-center font-semibold">Nama SEO</th>
                    <th class="p-4 text-center font-semibold">Aksi</th>
                    <th class="p-4 text-center font-semibold">Module</th>
                    <th class="p-4 text-center font-semibold">Deskripsi</th>
                    <th class="p-4 text-center font-semibold">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(!empty($logs)): ?> 
                    <?php foreach($logs as $i => $log): ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="p-4 text-center font-medium text-gray-600">
                                <?= $startNo + $i ?>
                            </td>
                            <td class="p-4 text-center text-gray-700">
                                <div class="flex flex-col items-center">
                                    <span class="font-medium"><?= date('d M Y', strtotime($log['created_at'])) ?></span>
                                    <span class="text-xs text-gray-500"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <?= esc($log['seo_name'] ?? 'Unknown SEO') ?>
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-medium border 
                                    <?= $log['action'] == 'create' ? 'bg-green-100 text-green-800 border-green-200' : '' ?>
                                    <?= $log['action'] == 'update' ? 'bg-blue-100 text-blue-800 border-blue-200' : '' ?>
                                    <?= $log['action'] == 'delete' ? 'bg-red-100 text-red-800 border-red-200' : '' ?>
                                    <?= $log['action'] == 'read' ? 'bg-gray-100 text-gray-800 border-gray-200' : '' ?>
                                    <?= $log['action'] == 'login' ? 'bg-indigo-100 text-indigo-800 border-indigo-200' : '' ?>
                                    <?= $log['action'] == 'logout' ? 'bg-purple-100 text-purple-800 border-purple-200' : '' ?>
                                    <?= $log['action'] == 'approve' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : '' ?>
                                    <?= $log['action'] == 'reject' ? 'bg-rose-100 text-rose-800 border-rose-200' : '' ?>
                                    <?= !in_array($log['action'], ['create','update','delete','read','login','logout','approve','reject']) ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : '' ?>">
                                    <?= esc(ucfirst($log['action'])) ?>
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 bg-gradient-to-r from-purple-100 to-purple-50 text-purple-800 rounded-full text-xs font-medium border border-purple-200">
                                    <?= esc($log['module']) ?>
                                </span>
                            </td>
                            <td class="p-4 text-center max-w-xs">
                                <div class="flex flex-col items-center">
                                    <?php 
                                        $desc = $log['description'] ?? '';
                                        $plain = strip_tags($desc);
                                        $short = mb_strimwidth($plain, 0, 60, "…");
                                    ?>
                                    <span class="text-gray-700 text-sm"><?= esc($short) ?></span>
                                    <?php if(mb_strlen($plain) > 60): ?>
                                        <button 
                                            type="button"
                                            class="mt-1 text-blue-600 hover:text-blue-800 text-xs font-medium transition-colors viewDescBtn flex items-center"
                                            data-desc="<?= esc($plain) ?>">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            lihat detail
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded border border-gray-200 text-gray-700">
                                    <?= esc($log['ip_address']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="p-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium mb-2">Belum ada aktivitas SEO</p>
                                <p class="text-sm">Aktivitas yang dilakukan oleh user SEO akan muncul di sini</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer Info -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between text-sm text-gray-600">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Menampilkan <?= count($logs) ?> aktivitas terbaru
            </div>
            <div>
                Terakhir diperbarui: <?= date('d M Y H:i:s') ?>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Detail Deskripsi Modal
    document.querySelectorAll('.viewDescBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            Swal.fire({
                title: '<div class="flex items-center text-lg"><svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>Detail Deskripsi Aktivitas</div>',
                html: `<div class="text-left"><p class="text-gray-700 leading-relaxed">${btn.dataset.desc}</p></div>`,
                width: 700,
                padding: '2rem',
                showCloseButton: true,
                confirmButtonText: 'Tutup',
                customClass: {
                    popup: 'rounded-xl shadow-2xl',
                    closeButton: 'hover:bg-gray-100 rounded-full p-2 transition-colors'
                }
            });
        });
    });

    // Hover effects for table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.05)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>

<style>
.swal2-popup {
    border-radius: 1rem !important;
}

tbody tr {
    transition: all 0.2s ease-in-out;
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<?= $this->endSection() ?>