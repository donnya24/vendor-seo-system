<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-up': 'fadeUp 0.55s cubic-bezier(0.22, 0.9, 0.24, 1) forwards',
                        'fade-up-soft': 'fadeUpSoft 0.45s ease-out forwards',
                    },
                    keyframes: {
                        fadeUp: {
                            '0%': { opacity: '0', transform: 'translate3d(0, 18px, 0)' },
                            '100%': { opacity: '1', transform: 'translate3d(0, 0, 0)' }
                        },
                        fadeUpSoft: {
                            '0%': { opacity: '0', transform: 'translate3d(0, 12px, 0)' },
                            '100%': { opacity: '1', transform: 'translate3d(0, 0, 0)' }
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom styles for smaller SweetAlert2 -->
    <style>
        .small-swal {
            font-size: 0.85rem !important;
        }
        
        .small-swal .swal2-title {
            font-size: 1.1rem !important;
        }
        
        .small-swal .swal2-content {
            font-size: 0.9rem !important;
        }
        
        .small-swal .swal2-actions {
            margin-top: 0.8rem !important;
        }
        
        .small-swal .swal2-styled {
            padding: 0.4rem 0.8rem !important;
            font-size: 0.85rem !important;
        }
        
        .small-swal-textarea {
            font-size: 0.85rem !important;
            padding: 0.5rem !important;
        }
        
        .small-swal .swal2-input {
            font-size: 0.85rem !important;
        }
        
        .small-swal .swal2-label {
            font-size: 0.9rem !important;
            margin-bottom: 0.3rem !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?= $this->include('admin/layouts/header'); ?>
    <?= $this->include('admin/layouts/sidebar'); ?>

    <!-- ======================== CONTENT WRAPPER ======================== -->
    <div
        id="pageWrap"
        class="flex-1 flex flex-col min-h-screen bg-gray-50 pb-16 md:pb-0 transition-all duration-300 ease-in-out"
    >
        <!-- ======================== MAIN CONTENT ======================== -->
        <main
            id="pageMain"
            class="flex-1 overflow-y-auto p-3 md:p-4 no-scrollbar animate-fade-up"
            style="--dur:.60s; --delay:.02s"
        >
            <!-- 6 STATS CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mb-6">
                <!-- 1. Total vendor (verified only) -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-2.5 rounded-lg border border-blue-200 shadow-xs hover:shadow-sm transition-shadow animate-fade-up" style="--delay:.08s">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-[10px] font-semibold text-blue-800 uppercase tracking-wider mb-0.5">TOTAL VENDOR</p>
                            <p class="text-lg font-bold text-blue-900"><?= esc($stats['totalVendors'] ?? 0); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-md text-white ml-2">
                            <i class="fas fa-store text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-1.5 pt-1.5 border-t border-blue-200/50">
                        <div class="flex items-center text-blue-700 text-[10px] font-medium">
                            <i class="fas fa-check-circle mr-0.5"></i>
                            <span class="font-semibold">Terverifikasi</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Total komisi bulan ini -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-2.5 rounded-lg border border-green-200 shadow-xs hover:shadow-sm transition-shadow animate-fade-up"
                     style="--delay:.14s">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-[10px] font-semibold text-green-800 uppercase tracking-wider mb-0.5">TOTAL KOMISI</p>
                            <p class="text-lg font-bold text-green-900">Rp <?= number_format($stats['totalCommissionPaid'] ?? 0, 0, ',', '.'); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 bg-green-600 rounded-md text-white ml-2">
                            <i class="fas fa-money-bill-wave text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-1.5 pt-1.5 border-t border-green-200/50">
                        <div class="flex items-center text-green-700 text-[10px] font-medium">
                            <i class="fas fa-check-circle mr-0.5"></i>
                            <span class="font-semibold">Status paid (Semua)</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Top keyword -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-2.5 rounded-lg border border-purple-200 shadow-xs hover:shadow-sm transition-shadow animate-fade-up"
                     style="--delay:.20s">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-[10px] font-semibold text-purple-800 uppercase tracking-wider mb-0.5">LEADS PALING DICARI</p>
                            <p class="text-lg font-bold text-purple-900"><?= esc($stats['topKeyword'] ?? '-'); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 bg-purple-600 rounded-md text-white ml-2">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-1.5 pt-1.5 border-t border-purple-200/50">
                        <div class="flex items-center text-purple-700 text-[10px] font-medium">
                            <i class="fas fa-fire mr-0.5"></i>
                            <span class="font-semibold">Paling dicari</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Total leads masuk -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-2.5 rounded-lg border border-indigo-200 shadow-xs hover:shadow-sm transition-shadow animate-fade-up"
                     style="--delay:.26s">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wider mb-0.5">TOTAL LEADS MASUK</p>
                            <p class="text-lg font-bold text-indigo-900"><?= esc($stats['totalLeadsIn'] ?? 0); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 bg-indigo-600 rounded-md text-white ml-2">
                            <i class="fas fa-inbox text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-1.5 pt-1.5 border-t border-indigo-200/50">
                        <div class="flex items-center text-indigo-700 text-[10px] font-medium">
                            <i class="fas fa-users mr-0.5"></i>
                            <span class="font-semibold">Customer chat</span>
                        </div>
                    </div>
                </div>

                <!-- 5. Total leads closing -->
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-2.5 rounded-lg border border-emerald-200 shadow-xs hover:shadow-sm transition-shadow animate-fade-up"
                     style="--delay:.32s">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-[10px] font-semibold text-emerald-800 uppercase tracking-wider mb-0.5">TOTAL LEADS CLOSING</p>
                            <p class="text-lg font-bold text-emerald-900"><?= esc($stats['totalLeadsClosing'] ?? 0); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 bg-emerald-600 rounded-md text-white ml-2">
                            <i class="fas fa-handshake text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-1.5 pt-1.5 border-t border-emerald-200/50">
                        <div class="flex items-center text-emerald-700 text-[10px] font-medium">
                            <i class="fas fa-check-double mr-0.5"></i>
                            <span class="font-semibold">Deal selesai</span>
                        </div>
                    </div>
                </div>

                <!-- 6. Total tim SEO (active only) -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-2.5 rounded-lg border border-orange-200 shadow-xs hover:shadow-sm transition-shadow animate-fade-up"
                     style="--delay:.38s">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-[10px] font-semibold text-orange-800 uppercase tracking-wider mb-0.5">TOTAL TIM SEO</p>
                            <p class="text-lg font-bold text-orange-900"><?= esc($stats['totalSeoTeam'] ?? 0); ?></p>
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 bg-orange-600 rounded-md text-white ml-2">
                            <i class="fas fa-users-gear text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-1.5 pt-1.5 border-t border-orange-200/50">
                        <div class="flex items-center text-orange-700 text-[10px] font-medium">
                            <i class="fas fa-user-check mr-0.5"></i>
                            <span class="font-semibold">Tim aktif</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 2 -->
            <div class="flex flex-col lg:flex-row gap-3 mb-4">
                <!-- ======= Daftar Vendor (pengajuan terbaru) - 70% ======= -->
                <div class="lg:w-[70%] bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 animate-fade-up"
                     style="--delay:.44s">
                    <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-800 flex items-center">
                            <i class="fa-solid fa-building mr-2 text-blue-600 text-xs"></i>
                            Daftar Pengajuan Vendor
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full table-auto divide-y divide-gray-100">
                            <thead class="bg-blue-600">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">NO</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">VENDOR</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">PEMILIK</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">KONTAK</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">KOMISI DIMINTA</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">STATUS</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <?php
                                $__reqs = $commissionRequests ?? [];
                                $__reqs = array_slice($__reqs, 0, 3);
                                $no = 0;
                                foreach ($__reqs as $r):
                                    $no++;
                                    $delay = number_format(0.48 + 0.04*$no, 2, '.', '');
                                    $id       = (int)($r['id'] ?? 0);
                                    $usaha    = $r['usaha'] ?? ($r['business_name'] ?? '-');
                                    $pemilik  = $r['pemilik'] ?? ($r['owner_name'] ?? '-');

                                    // normalisasi kontak dari controller (sudah di-COALESCE), tapi tetap jaga-jaga
                                    $phone    = trim((string)($r['phone'] ?? ''));
                                    $waRaw    = trim((string)($r['wa'] ?? ($r['whatsapp_number'] ?? '')));

                                    // Perbaikan untuk menangani komisi persentase atau nominal
                                    $komisiType = $r['commission_type'] ?? 'percent';
                                    $komisiValue = 0;
                                    
                                    if ($komisiType === 'percent') {
                                        $komisiValue = is_numeric($r['komisi'] ?? null) ? (float)$r['komisi'] : (float)preg_replace('/[^0-9.]/','', (string)($r['komisi'] ?? 0));
                                    } else {
                                        $komisiValue = is_numeric($r['komisi_nominal'] ?? null) ? (float)$r['komisi_nominal'] : (float)preg_replace('/[^0-9.]/','', (string)($r['komisi_nominal'] ?? 0));
                                    }
                                    
                                    $status   = strtolower((string)($r['status'] ?? 'pending'));
                                    $approved = ($status === 'verified');
                                ?>
                                <tr id="req-row-<?= $id ?>" class="hover:bg-gray-50 animate-fade-up-soft" style="--delay: <?= $delay ?>s;">
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm font-bold text-gray-900"><?= $no ?></div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm font-semibold text-gray-900"><?= esc($usaha) ?></div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm text-gray-900"><?= esc($pemilik) ?></div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center text-sm text-gray-900">
                                                <i class="fa-solid fa-phone text-xs text-gray-400 mr-1.5 w-3"></i>
                                                <span><?= $phone !== '' ? esc($phone) : '—' ?></span>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fa-brands fa-whatsapp text-xs text-gray-400 mr-1.5 w-3"></i>
                                                <?php if ($waRaw !== ''): ?>
                                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $waRaw) ?>" target="_blank" rel="noopener" class="text-sm text-green-600 hover:text-green-700"><?= esc($waRaw) ?></a>
                                                <?php else: ?>
                                                    <span class="text-sm text-gray-900">—</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php if ($komisiType === 'percent'): ?>
                                                <?= number_format($komisiValue, 1) ?>%
                                            <?php else: ?>
                                                Rp <?= number_format($komisiValue, 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <?php if ($approved): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Disetujui</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Belum Disetujui</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <?php if ($approved): ?>
                                            <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 text-xs font-semibold px-3 py-1.5 rounded-xl cursor-not-allowed">Sudah Disetujui</span>
                                        <?php else: ?>
                                            <div class="flex flex-wrap gap-2">
                                                <button class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm"
                                                        onclick="approveVendorRequest(event, <?= $id ?>)">Setujui</button>
                                                <button class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm"
                                                        onclick="rejectVendorRequest(event, <?= $id ?>)">Tolak</button>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if (empty($__reqs)): ?>
                                    <tr class="animate-fade-up-soft" style="--delay:.52s">
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                                            Belum ada pengajuan vendor saat ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions - 30% -->
                <div class="lg:w-[30%] bg-white rounded-lg shadow-xs border border-gray-100 overflow-hidden animate-fade-up"
                     style="--delay:.50s">
                    <div class="px-3 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="p-3">
                        <div class="space-y-2">
                            <a href="<?= site_url('admin/users?tab=seo'); ?>"
                               class="flex items-center p-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition text-xs w-full animate-fade-up-soft"
                               style="--delay:.54s">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white mr-2">
                                    <i class="fas fa-users text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <span class="font-semibold block">Add New Tim SEO</span>
                                    <span class="text-[11px] text-blue-600">Tambah anggota tim SEO</span>
                                </div>
                            </a>
                            <a href="<?= site_url('admin/announcements'); ?>" 
                               class="flex items-center p-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 transition text-xs w-full animate-fade-up-soft"
                               style="--delay:.58s">
                                <div class="flex items-center justify-center h-8 w-8 rounded-full bg-green-600 text-white mr-2">
                                    <i class="fas fa-bullhorn text-sm"></i>
                                </div>
                                <div class="text-left">
                                    <span class="font-semibold block">Post Announcement</span>
                                    <span class="text-[11px] text-green-600">Broadcast messages</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LEADS TERBARU -->
            <section class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 animate-fade-up"
                     style="--delay:.62s">
                <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-list mr-2 text-blue-600 text-xs"></i>
                        Leads Terbaru
                    </h3>
                    <a href="<?= site_url('admin/leads'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded-lg text-xs font-semibold inline-flex items-center gap-1 visited:text-white">
                        <i class="fas fa-eye text-[10px]"></i>
                        Lihat Semua
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full table-auto divide-y divide-gray-100">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">NO</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">VENDOR</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">MASUK</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">CLOSING</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">PERIODE</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">UPDATE</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php
                            $rows = $recentLeads ?? [];
                            $no = 0;
                            ?>
                            <?php foreach ($rows as $lead): $no++; $delay = number_format(0.66 + 0.04 * $no, 2, '.', ''); ?>
                                <tr class="hover:bg-gray-50 animate-fade-up-soft" style="--delay: <?= $delay ?>s;">
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm font-bold text-gray-900"><?= $no ?></div>
                                    </td>
                                    <!-- VENDOR / business_name -->
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm font-bold text-gray-900">
                                            <?= esc($lead['business_name'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm text-gray-900"><?= esc($lead['masuk'] ?? 0) ?></div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm text-gray-900"><?= esc($lead['closing'] ?? 0) ?></div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-xs font-medium">
                                            <span class="font-semibold text-gray-700"><?= date('d M Y', strtotime($lead['tanggal_mulai'] ?? 'now')) ?></span>
                                            <span class="text-gray-500 mx-1">s/d</span>
                                            <span class="font-semibold text-gray-700"><?= date('d M Y', strtotime($lead['tanggal_selesai'] ?? 'now')) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <div class="text-sm text-gray-900"><?= date('d M Y H:i', strtotime($lead['updated_at'] ?? 'now')) ?></div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap align-top">
                                        <button onclick="showLeadDetail(<?= $lead['id_leads'] ?>)" 
                                                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm">
                                            <i class="fa-regular fa-eye text-[11px]"></i>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal untuk Detail Leads -->
    <div id="leadDetailModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75" onclick="closeLeadDetailModal()"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Detail Laporan Leads
                        </h3>
                        <button onclick="closeLeadDetailModal()" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="mt-2">
                        <div id="leadDetailLoading" class="flex justify-center py-8 hidden">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        </div>
                        
                        <div id="leadDetailContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <!-- Konten akan dimuat melalui AJAX -->
                        </div>
                        
                        <div id="leadDetailError" class="bg-red-50 border-l-4 border-red-500 p-4 hidden">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700" id="errorMessage"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeLeadDetailModal()" 
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Kembali
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // --- PERBAIKAN: Buat notifikasi "toast" yang lebih kecil dan di pojok kanan atas ---
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end', // Pojok kanan atas
            showConfirmButton: false,
            timer: 1500, // Hilang otomatis setelah 3 detik
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Fungsi untuk menampilkan detail leads
        async function showLeadDetail(id) {
            // Tampilkan modal
            document.getElementById('leadDetailModal').classList.remove('hidden');
            document.getElementById('leadDetailLoading').classList.remove('hidden');
            document.getElementById('leadDetailContent').classList.add('hidden');
            document.getElementById('leadDetailError').classList.add('hidden');
            
            try {
                // Ambil data leads melalui AJAX
                const response = await fetch(`<?= site_url('admin/api/leads') ?>/${id}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    const lead = result.data;
                    
                    // Tampilkan data leads
                    document.getElementById('leadDetailContent').innerHTML = `
                        <div>
                            <div class="text-gray-500">Vendor</div>
                            <div class="font-medium">${lead.vendor}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Periode Laporan</div>
                            <div class="font-medium">${lead.periode}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Leads Masuk</div>
                            <div class="font-medium">${lead.leads_masuk}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Leads Closing</div>
                            <div class="font-medium">${lead.leads_closing}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Dilaporkan Oleh Vendor</div>
                            <div class="font-medium">${lead.reported_by}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Terakhir Diperbarui</div>
                            <div class="font-medium">${lead.updated_at}</div>
                        </div>
                    `;
                    document.getElementById('leadDetailContent').classList.remove('hidden');
                } else {
                    // Tampilkan pesan error
                    document.getElementById('errorMessage').textContent = result.message;
                    document.getElementById('leadDetailError').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                // Tampilkan pesan error
                document.getElementById('errorMessage').textContent = 'Terjadi kesalahan saat memuat data. Silakan coba lagi.';
                document.getElementById('leadDetailError').classList.remove('hidden');
            } finally {
                document.getElementById('leadDetailLoading').classList.add('hidden');
            }
        }
        
        // Fungsi untuk menutup modal
        function closeLeadDetailModal() {
            document.getElementById('leadDetailModal').classList.add('hidden');
        }

        // Waktu real-time (independen)
        (function(){
            const fmtDate = new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:'short',year:'numeric'});
            const fmtTime = new Intl.DateTimeFormat('id-ID',{hour:'2-digit',minute:'2-digit'});
            function render(){
                document.querySelectorAll('.js-date').forEach(el=>{const d=new Date(el.dataset.ts);el.textContent=isNaN(d)?'—':fmtDate.format(d);});
                document.querySelectorAll('.js-time').forEach(el=>{const d=new Date(el.dataset.ts);el.textContent=isNaN(d)?'—':fmtTime.format(d);});
            }
            render();
            setInterval(render, 30000);
        })();

        // --- PERBAIKAN: Fungsi approveVendorRequest dengan SweetAlert2 ---
        async function approveVendorRequest(e, id) {
            e.preventDefault();
            
            // Tampilkan konfirmasi dengan SweetAlert2 (ukuran lebih kecil)
            const result = await Swal.fire({
                title: 'Setujui Vendor?',
                html: `Apakah Anda yakin ingin menyetujui pengajuan vendor ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
                width: '320px', // Perkecil lebar modal
                padding: '0.8rem', // Perkecil padding
                customClass: {
                    popup: 'small-swal' // Tambahkan class kustom
                }
            });

            if (!result.isConfirmed) return;

            // Tampilkan loading
            Swal.fire({
                title: 'Memproses...',
                html: 'Sedang menyetujui vendor.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                width: '280px', // Lebar lebih kecil untuk loading
                padding: '0.6rem'
            });

            try {
                const formData = new FormData();
                formData.append("id", id);
                // Tambahkan CSRF token
                formData.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

                // PERBAIKAN: Ubah URL untuk menggunakan format dash-separated
                const res = await fetch("<?= site_url('admin/dashboard/approve-vendor-request') ?>", {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                const data = await res.json();
                
                if (data.status === "success") {
                    // Tampilkan notifikasi sukses dengan Toast
                    Toast.fire({
                        icon: 'success',
                        title: data.message
                    }).then(() => {
                        // Reload halaman setelah notifikasi muncul
                        location.reload();
                    });
                } else {
                    // Tampilkan notifikasi error
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan yang tidak diketahui.',
                        width: '300px',
                        padding: '0.8rem'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                    width: '300px',
                    padding: '0.8rem'
                });
            }
        }

        // --- PERBAIKAN: Fungsi rejectVendorRequest dengan SweetAlert2 ---
        async function rejectVendorRequest(e, id) {
            e.preventDefault();
            
            // Tampilkan input alasan dengan SweetAlert2 (ukuran lebih kecil)
            const { value: reason } = await Swal.fire({
                title: 'Tolak Vendor',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan',
                inputPlaceholder: 'Masukkan alasan penolakan...',
                inputAttributes: {
                    'aria-label': 'Masukkan alasan penolakan'
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan harus diisi!'
                    }
                },
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Tolak',
                cancelButtonText: 'Batal',
                width: '340px', // Lebar sedikit lebih besar karena ada textarea
                padding: '0.8rem',
                customClass: {
                    popup: 'small-swal',
                    input: 'small-swal-textarea' // Class kustom untuk textarea
                }
            });

            if (!reason) return;

            // Tampilkan loading
            Swal.fire({
                title: 'Memproses...',
                html: 'Sedang menolak vendor.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                width: '280px',
                padding: '0.6rem'
            });

            try {
                const formData = new FormData();
                formData.append("id", id);
                formData.append("reason", reason);
                // Tambahkan CSRF token
                formData.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");

                // PERBAIKAN: Ubah URL untuk menggunakan format dash-separated
                const res = await fetch("<?= site_url('admin/dashboard/reject-vendor-request') ?>", {
                    method: "POST",
                    body: formData,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                const data = await res.json();
                
                if (data.status === "success") {
                    // Tampilkan notifikasi sukses dengan Toast
                    Toast.fire({
                        icon: 'success',
                        title: data.message
                    }).then(() => {
                        // Reload halaman setelah notifikasi muncul
                        location.reload();
                    });
                } else {
                    // Tampilkan notifikasi error
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan yang tidak diketahui.',
                        width: '300px',
                        padding: '0.8rem'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                    width: '300px',
                    padding: '0.8rem'
                });
            }
        }
    </script>

    <?= $this->include('admin/layouts/footer'); ?>
</body>
</html>