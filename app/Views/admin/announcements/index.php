<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements Management</title>
    <!-- Custom Styles -->
    <style>
        /* Custom animations that aren't available in Tailwind */
        .animate-slide-up{ animation:slideUp .5s ease-out forwards; opacity:0; transform:translateY(20px); }
        @keyframes slideUp{ to{ opacity:1; transform:translateY(0); } }
        .animate-fade-in{ animation:fadeIn .5s ease-out forwards; }
        @keyframes fadeIn{ from{opacity:0} to{opacity:1} }
        
        /* Pastikan dropdown header selalu di atas */
        .header-dropdown {
            position: fixed !important;
            z-index: 99999 !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?= $this->include('admin/layouts/header'); ?>
    
    <script>
        document.addEventListener('alpine:init', () => {
            // Jika store 'layout' belum ada (fallback antar halaman), buat dengan default aman
            if (!Alpine.store('layout')) {
                Alpine.store('layout', {
                    sidebarOpen: false,
                    isDesktop: window.matchMedia('(min-width: 768px)').matches
                });
            } else {
                // Pastikan properti minimum tersedia
                if (typeof Alpine.store('layout').isDesktop === 'undefined') {
                    Alpine.store('layout').isDesktop = window.matchMedia('(min-width: 768px)').matches;
                }
                if (typeof Alpine.store('layout').sidebarOpen === 'undefined') {
                    Alpine.store('layout').sidebarOpen = false;
                }
            }
        });

        // Sinkronkan isDesktop saat resize
        (function () {
            const mq = window.matchMedia('(min-width: 768px)');
            function apply() {
                if (window.Alpine && Alpine.store('layout')) {
                    Alpine.store('layout').isDesktop = mq.matches;
                }
            }
            mq.addEventListener ? mq.addEventListener('change', apply) : mq.addListener(apply);
            window.addEventListener('turbo:load', apply);
            window.addEventListener('DOMContentLoaded', apply);
        })();
        
        // Perbaikan untuk dropdown header
        document.addEventListener('DOMContentLoaded', function() {
            // Memastikan dropdown header memiliki z-index tertinggi
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                // Cari dropdown di dalam node yang ditambahkan
                                const dropdowns = node.querySelectorAll ? node.querySelectorAll('[x-show*="notifOpen"], [x-show*="open"]') : [];
                                dropdowns.forEach(dropdown => {
                                    dropdown.classList.add('header-dropdown');
                                });
                            }
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            // Terapkan pada dropdown yang sudah ada
            setInterval(() => {
                document.querySelectorAll('[x-show*="notifOpen"], [x-show*="open"]').forEach(el => {
                    if (!el.classList.contains('header-dropdown')) {
                        el.classList.add('header-dropdown');
                    }
                });
            }, 100);
        });
    </script>

    <!-- Wrapper halaman -->
   <div id="pageWrap" class="flex-1 flex flex-col min-h-screen bg-gray-50" x-data>
    <header class="bg-white shadow-sm z-10 relative">
        <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Announcements Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your announcements and notifications</p>
            </div>
            <button onclick="openAnnouncementModal()" class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium flex items-center transition">
                <i class="fa fa-plus-circle mr-2"></i> New Announcement
            </button>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 p-4 rounded-xl bg-green-100 text-green-700 text-sm flex items-center animate-fade-in">
                <i class="fa fa-check-circle mr-2"></i><?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-slide-up">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Konten</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Audience</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Publish</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Expired</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach(($items ?? []) as $a): ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?= esc($a['title'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600"><?= esc(mb_strimwidth($a['content'] ?? '-', 0, 80, '...')) ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                        $audienceLabels = ['all'=>'Semua Pengguna','vendor'=>'Vendor','seo_team'=>'Tim SEO'];
                                        $audience = $a['audience'] ?? 'all';
                                        $audienceText = $audienceLabels[$audience] ?? ucfirst($audience);
                                    ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= esc($audienceText) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= !empty($a['publish_at']) ? date('d M Y, H:i', strtotime($a['publish_at'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4 text-sm <?= empty($a['expires_at']) ? 'text-gray-400' : 'text-gray-600' ?>">
                                    <?= !empty($a['expires_at']) ? date('d M Y, H:i', strtotime($a['expires_at'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($a['status'] === 'active'): ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fa fa-check-circle mr-1"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <i class="fa fa-times-circle mr-1"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button type="button"
                                    onclick="openEditModal(this)"
                                    data-id="<?= $a['id'] ?>"
                                    data-title="<?= esc($a['title']) ?>"
                                    data-content="<?= esc($a['content']) ?>"
                                    data-audience="<?= esc($a['audience']) ?>"
                                    data-publish="<?= esc($a['publish_at']) ?>"
                                    data-expires="<?= esc($a['expires_at']) ?>"
                                    data-status="<?= esc($a['status']) ?>"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 transition">
                                    <i class="fa fa-edit mr-1"></i> Edit
                                    </button>
                                    <form action="<?= site_url('admin/announcements/'.$a['id'].'/delete'); ?>" method="post" class="inline" onsubmit="return confirm('Hapus pengumuman?')">
                                        <?= csrf_field() ?>
                                        <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                            <i class="fa fa-trash mr-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fa fa-bullhorn text-4xl mb-3"></i>
                                        <p class="text-lg font-medium">Tidak ada data announcements</p>
                                        <p class="text-sm mt-1">Buat announcement baru untuk memulai</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

    <!-- ========================= MODAL: New Announcement ========================= -->
    <div id="announcementModal" class="fixed inset-0 bg-black bg-opacity-55 flex items-center justify-center z-[70] opacity-0 invisible transition-all duration-300" onclick="if(event.target === this) closeAnnouncementModal()">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[calc(100vh-64px)] m-4 shadow-2xl flex flex-col overflow-hidden transform scale-95 transition-transform duration-250">
        <div class="bg-gradient-to-r from-blue-800 to-blue-700 text-white p-4 flex items-center justify-between rounded-t-2xl">
        <h3 class="text-xl font-bold">Tambah Announcement Baru</h3>
        <button class="bg-transparent border-none text-white cursor-pointer text-xl flex items-center justify-center" onclick="closeAnnouncementModal()">
            <i class="fa-solid fa-times"></i>
        </button>
        </div>

        <!-- HANYA BODY YANG SCROLL -->
        <div class="p-4 flex-1 overflow-y-auto scrollbar-gutter-stable-both-edges">
        <form id="announcementForm" action="<?= site_url('admin/announcements/store'); ?>" method="post" class="space-y-4">
            <?= csrf_field() ?>

            <!-- Judul -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                Judul Announcement <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-heading"></i></span>
                <input type="text" name="title"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Masukkan judul announcement" required>
            </div>
            </div>

            <!-- Konten -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                Konten <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-align-left"></i></span>
                <textarea name="content" rows="6"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Masukkan konten announcement" required></textarea>
            </div>
            </div>

            <!-- Audience -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                Target Audience <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-users"></i></span>
                <select name="audience"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition appearance-none"
                        required>
                <option value="all">Semua Pengguna</option>
                <option value="vendor">Vendor</option>
                <option value="seo_team">Tim SEO</option>
                </select>
                <span class="absolute right-3 top-3 text-gray-400 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
            </div>
            </div>

            <!-- Publish At -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                Tanggal Publish <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-calendar"></i></span>
                <input type="datetime-local" name="publish_at"
                    value="<?= date('Y-m-d\TH:i'); ?>"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
            </div>
            </div>

            <!-- Expired At -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
                Tanggal Berakhir (optional)
            </label>
            <div class="relative">
                <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-hourglass-end"></i></span>
                <input type="datetime-local" name="expires_at"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            </div>

            <!-- Hidden Status (selalu aktif) -->
            <input type="hidden" name="status" value="active">
        </form>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-200 flex justify-end gap-3">
        <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white font-medium transition hover:bg-gray-50" onclick="closeAnnouncementModal()">Batal</button>
        <button class="px-4 py-2 border-none rounded-lg text-white bg-green-600 font-medium transition hover:bg-green-700" onclick="document.getElementById('announcementForm').submit()">Simpan Announcement</button>
        </div>
    </div>
    </div>

    <!-- ========================= Custom Styles ========================= -->
    <style>
        #announcementModal::-webkit-scrollbar{ width:6px; }
        #announcementModal::-webkit-scrollbar-track{ background:transparent; }
        #announcementModal::-webkit-scrollbar-thumb{ background:rgba(148,163,184,.85); border-radius:9999px; }
        #announcementModal::-webkit-scrollbar-thumb:hover{ background:rgba(100,116,139,1); }
        #announcementModal{ scrollbar-width:thin; scrollbar-color:rgba(148,163,184,.85) transparent; }
        
        #announcementModal.active { opacity:1; visibility:visible; }
        #announcementModal.active > div { transform:scale(1); }
        
        @media (max-width:640px){
            #announcementModal > div {
                max-width:95vw;
                max-height:calc(100vh - 48px);
                margin:8px; 
                border-radius:12px;
            }
        }
    </style>

    <!-- ========================= MODAL: Edit Announcement ========================= -->
    <div id="editAnnouncementModal" class="fixed inset-0 bg-black bg-opacity-55 flex items-center justify-center z-[70] opacity-0 invisible transition-all duration-300" onclick="if(event.target === this) closeEditModal()">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[calc(100vh-64px)] m-4 shadow-2xl flex flex-col overflow-hidden transform scale-95 transition-transform duration-250">
        <div class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white p-4 flex items-center justify-between rounded-t-2xl">
        <h3 class="text-xl font-bold">Edit Announcement</h3>
        <button class="bg-transparent border-none text-white cursor-pointer text-xl flex items-center justify-center" onclick="closeEditModal()">
            <i class="fa-solid fa-times"></i>
        </button>
        </div>

        <!-- Body -->
        <div class="p-4 flex-1 overflow-y-auto scrollbar-gutter-stable-both-edges">
        <form id="editAnnouncementForm" action="" method="post" class="space-y-4">
            <?= csrf_field() ?>

            <!-- Judul -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Judul Announcement</label>
            <input type="text" name="title" id="edit_title"
                    class="w-full pl-3 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition" required>
            </div>

            <!-- Konten -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Konten</label>
            <textarea name="content" id="edit_content" rows="6"
                        class="w-full pl-3 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition" required></textarea>
            </div>

            <!-- Audience -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Target Audience</label>
            <select name="audience" id="edit_audience"
                    class="w-full pl-3 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition" required>
                <option value="all">Semua Pengguna</option>
                <option value="vendor">Vendor</option>
                <option value="seo_team">Tim SEO</option>
            </select>
            </div>

            <!-- Publish -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Tanggal Publish</label>
            <input type="datetime-local" name="publish_at" id="edit_publish_at"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" required>
            </div>

            <!-- Expired -->
            <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Tanggal Berakhir</label>
            <input type="datetime-local" name="expires_at" id="edit_expires_at"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
            </div>

            <!-- Hidden status (otomatis aktif / nonaktif sesuai tanggal) -->
            <input type="hidden" name="status" id="edit_status" value="active">

        </form>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-200 flex justify-end gap-3">
        <button class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white font-medium transition hover:bg-gray-50" onclick="closeEditModal()">Batal</button>
        <button class="px-4 py-2 border-none rounded-lg text-white bg-yellow-600 font-medium transition hover:bg-yellow-700" onclick="document.getElementById('editAnnouncementForm').submit()">Update</button>
        </div>
    </div>
    </div>

    <script>
    function openEditModal(el) {
    const modal = document.getElementById('editAnnouncementModal');
    const form = document.getElementById('editAnnouncementForm');

    // ambil data dari atribut data-*
    const id = el.dataset.id;
    const title = el.dataset.title || '';
    const content = el.dataset.content || '';
    const audience = el.dataset.audience || 'all';
    const publish = el.dataset.publish || '';
    const expires = el.dataset.expires || '';
    const status = el.dataset.status || 'active';

    // isi form
    form.action = `<?= site_url('admin/announcements'); ?>/${id}/update`;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_content').value = content;
    document.getElementById('edit_audience').value = audience;
    document.getElementById('edit_publish_at').value = publish.replace(' ', 'T');
    document.getElementById('edit_expires_at').value = expires ? expires.replace(' ', 'T') : '';

    // buka modal
    modal.classList.add('active');
    document.documentElement.classList.add('overflow-hidden');
    document.body.classList.add('overflow-hidden');
    }

    function closeEditModal() {
    const modal = document.getElementById('editAnnouncementModal');
    modal.classList.remove('active');
    document.documentElement.classList.remove('overflow-hidden');
    document.body.classList.remove('overflow-hidden');
    }
    </script>

    <style>
    #editAnnouncementModal::-webkit-scrollbar{width:6px;}
    #editAnnouncementModal::-webkit-scrollbar-thumb{background:rgba(148,163,184,.85);border-radius:9999px;}
    #editAnnouncementModal.active{opacity:1;visibility:visible;}
    #editAnnouncementModal.active>div{transform:scale(1);}
    </style>


    <!-- ========================= JS: Open / Close ========================= -->
    <script>
        function openAnnouncementModal(){
            const modal = document.getElementById('announcementModal');
            modal.classList.add('active');
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeAnnouncementModal(){
            const modal = document.getElementById('announcementModal');
            modal.classList.remove('active');
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Tutup dengan ESC
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAnnouncementModal(); });

        // Validasi ringan
        document.querySelectorAll('#announcementForm input, #announcementForm select, #announcementForm textarea').forEach(el=>{
            el.addEventListener('blur',function(){
                if (!this.value && this.hasAttribute('required')) this.classList.add('border-red-500');
                else this.classList.remove('border-red-500');
            });
        });
    </script>
</body>
</html>

<?= $this->include('admin/layouts/footer'); ?>