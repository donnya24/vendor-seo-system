<?php
 $isModal = $isModal ?? false;
 $vendor = $vendor ?? [];
 $pre = $selectedAreas ?? [];
 $isAll = $isAllIndonesia ?? false;
 
 // Pastikan vendors tersedia
 $vendors = $vendors ?? [];
 
 // Tambahkan variabel vendorIdsWithAreas
 $vendorIdsWithAreas = $vendorIdsWithAreas ?? [];
?>
<?php if (!$isModal): ?>
<div class="flex-1 p-4">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-xl font-semibold mb-6 text-gray-800"><?= esc($title ?? 'Edit Area Layanan Vendor') ?></h2>
<?php endif; ?>

<style>[x-cloak]{display:none!important}</style>

<!-- PERBAIKAN: Simpan data di HTML attributes seperti di edit -->
<div id="modalData" 
     data-vendors='<?= json_encode($vendors) ?>'
     data-areas='<?= json_encode($pre, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_NUMERIC_CHECK) ?>'
     data-all-indonesia='<?= $isAll ? 'true' : 'false' ?>'
     data-search-url='<?= site_url('admin/areas/search') ?>'
     style="display: none;">
</div>

<div class="<?= !$isModal ? 'bg-white rounded-lg shadow-sm border border-gray-200 p-6' : '' ?>">
    <div
        x-data="{
            // PERBAIKAN: Ambil data dari HTML attributes seperti di edit
            getModalData() {
                const el = document.getElementById('modalData');
                return {
                    vendors: JSON.parse(el.dataset.vendors || '[]'),
                    areas: JSON.parse(el.dataset.areas || '[]'),
                    allIndonesia: el.dataset.allIndonesia === 'true',
                    searchUrl: el.dataset.searchUrl || ''
                };
            },
            
            // Initialize data
            allIndonesia: false,
            query: '',
            selected: [],
            suggestions: [],
            open: false,
            hi: -1,
            _t: null,
            searchUrl: '',
            vendors: [],
            selectedVendor: null,

            init() {
                // Load data dari HTML
                const modalData = this.getModalData();
                this.vendors = modalData.vendors;
                this.selected = modalData.areas;
                this.allIndonesia = modalData.allIndonesia;
                this.searchUrl = modalData.searchUrl;
                
                console.log('Alpine initialized with vendors:', this.vendors);
                console.log('Alpine initialized with areas:', this.selected);
                
                // Handle Seluruh Indonesia
                if (this.allIndonesia) {
                    this.selected = [{ 
                        id: 0, 
                        name: 'Seluruh Indonesia', 
                        type: 'region', 
                        path: 'Seluruh Indonesia' 
                    }];
                }
            },

            // Watch untuk allIndonesia
            $watch: {
                allIndonesia(v) {
                    if (v) {
                        this.selected = [{ 
                            id: 0, 
                            name: 'Seluruh Indonesia', 
                            type: 'region', 
                            path: 'Seluruh Indonesia' 
                        }];
                        this.query = '';
                        this.closeDropdown();
                    } else {
                        // Kembalikan data asli
                        const modalData = this.getModalData();
                        this.selected = modalData.areas;
                    }
                },
                
                // PERBAIKAN: Watch untuk selectedVendor
                selectedVendor(value) {
                    console.log('Selected vendor changed to:', value);
                    console.log('Vendor details:', this.selectedVendorDetails);
                }
            },

            // PERBAIKAN: Get selected vendor details dengan pendekatan yang lebih sederhana
            get selectedVendorDetails() {
                if (!this.selectedVendor) return null;
                
                // Konversi ke string untuk perbandingan yang lebih aman
                const vendorId = String(this.selectedVendor);
                console.log('Looking for vendor ID:', vendorId);
                
                // Debug: Tampilkan semua vendor ID yang tersedia
                console.log('Available vendor IDs:', this.vendors.map(v => ({ id: v.id, type: typeof v.id })));
                
                // PERBAIKAN: Gunakan perbandingan string untuk menghindari masalah tipe data
                const vendor = this.vendors.find(v => String(v.id) === vendorId);
                console.log('Found vendor:', vendor);
                return vendor || null;
            },

            // Clear all
            clearAll() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus semua area?',
                    text: 'Ini akan mengosongkan daftar area yang tersimpan di form.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.allIndonesia = false;
                        this.selected = [];
                        this.query = '';
                        this.closeDropdown();
                        Swal.fire({
                            icon: 'success',
                            title: 'Bersih',
                            text: 'Semua area dihapus dari form.',
                            timer: 1400,
                            showConfirmButton: false,
                        });
                    }
                });
            },

            // Search functions
            onInput() { 
                clearTimeout(this._t); 
                this._t = setTimeout(() => this.fetchSuggest(), 200); 
            },
            
            openDropdown() { 
                this.open = true; 
                if (this.query.trim().length >= 2) this.fetchSuggest(); 
            },
            
            closeDropdown() { 
                this.open = false; 
                this.hi = -1; 
            },

            fetchSuggest() {
                const q = this.query.trim();
                if (q.length < 2) { 
                    this.suggestions = []; 
                    return; 
                }
                
                fetch(this.searchUrl + '?q=' + encodeURIComponent(q), {
                    headers: {'X-Requested-With':'XMLHttpRequest'}
                })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(res => {
                    const picked = new Set(this.selected.map(x => parseInt(x.id)));
                    const data = (res && res.status === 'success' ? res.data : []) || [];
                    this.suggestions = data.filter(x => !picked.has(parseInt(x.id)));
                    this.open = true;
                    this.hi = this.suggestions.length ? 0 : -1;
                })
                .catch(() => { 
                    this.suggestions = []; 
                });
            },

            select(s) {
                this.selected.push({ 
                    id: parseInt(s.id), 
                    name: s.name, 
                    type: s.type, 
                    path: s.path 
                });
                this.query = ''; 
                this.closeDropdown(); 
                this.$nextTick(() => {
                    if (this.$refs.input) {
                        this.$refs.input.focus();
                    }
                });
            },

            remove(idx) { 
                this.selected.splice(idx, 1); 
            },
            
            move(d) { 
                if (!this.open || this.suggestions.length === 0) return; 
                this.hi = (this.hi + d + this.suggestions.length) % this.suggestions.length; 
            },
            
            enterSelect() { 
                if (this.open && this.hi >= 0 && this.hi < this.suggestions.length) {
                    this.select(this.suggestions[this.hi]);
                }
            },

            // Submit
            async handleSubmit(e) {
                e.preventDefault();
                
                // Validasi vendor dipilih
                if (!this.selectedVendor) {
                    showMini('warning', 'Pilih vendor terlebih dahulu');
                    return;
                }
                
                if (this.selected.length === 0 && !this.allIndonesia) {
                    showMini('warning', 'Pilih minimal satu area atau centang Seluruh Indonesia');
                    return;
                }

                const formData = new FormData(e.target);
                
                try {
                    const response = await fetch(e.target.action, {
                        method: 'POST',
                        headers: {'X-Requested-With':'XMLHttpRequest'},
                        body: formData
                    });

                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        showMini('success', result.message, result.redirect || '<?= site_url('admin/areas') ?>');
                    } else {
                        showMini('error', result.message);
                    }
                } catch (error) {
                    console.error('Submit error:', error);
                    showMini('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                }
            }
        }"
        x-init="init()"
        class="space-y-6"
        x-cloak
    >
        <form
            action="<?= site_url('admin/areas/attach') ?>"
            method="post"
            class="space-y-6"
            @submit.prevent="handleSubmit($event)"
        >
            <?= csrf_field() ?>
            
            <!-- Pilih Vendor -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Pilih Vendor</label>
                
                <!-- PERBAIKAN: Gunakan pendekatan langsung dengan PHP untuk dropdown -->
                <?php if (empty($vendors)): ?>
                    <!-- Tampilkan pesan informatif bukan error -->
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                        <p class="text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            Semua vendor yang sudah verified sudah memiliki area layanan. Silakan pilih vendor yang ada di halaman index untuk mengedit area.
                        </p>
                    </div>
                <?php else: ?>
                    <select x-model="selectedVendor" name="vendor_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Pilih Vendor --</option>
                        <?php foreach ($vendors as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= esc($v['business_name']) ?> (<?= esc($v['status']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                
                <p class="text-xs text-gray-500">Pilih vendor yang akan ditambahkan area layanan</p>
            </div>

            <!-- Info Vendor (tampil setelah vendor dipilih) -->
            <div x-show="selectedVendor" x-transition class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-3">Informasi Vendor</h3>
                <!-- PERBAIKAN: Tampilkan informasi vendor dengan format yang sama seperti di edit -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <template x-if="selectedVendorDetails">
                        <div class="col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700">Nama Bisnis:</span>
                                    <span class="text-blue-700 font-medium" x-text="selectedVendorDetails.business_name"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700">ID Vendor:</span>
                                    <span class="text-blue-700 font-mono" x-text="selectedVendorDetails.id"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700">Status:</span>
                                    <span class="px-2 py-1 text-xs rounded-full"
                                          :class="selectedVendorDetails.status == 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                          x-text="selectedVendorDetails.status.charAt(0).toUpperCase() + selectedVendorDetails.status.slice(1)"></span>
                                </div>
                                <div x-show="selectedVendorDetails.phone" class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700">Telepon:</span>
                                    <span class="text-blue-700" x-text="selectedVendorDetails.phone"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedVendorDetails">
                        <div class="col-span-2 text-red-500">
                            Data vendor tidak ditemukan
                        </div>
                    </template>
                </div>
            </div>

            <!-- Area yang tersimpan saat ini -->
            <div class="border border-gray-200 rounded-lg bg-white p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm font-medium text-gray-700">Area yang tersimpan saat ini</div>
                    <button
                        type="button"
                        x-show="selected.length > 0 || allIndonesia"
                        @click.prevent="clearAll()"
                        class="text-xs px-3 py-1.5 rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition-colors"
                    >
                        Hapus semua
                    </button>
                </div>

                <!-- Tampilkan Seluruh Indonesia -->
                <template x-if="allIndonesia">
                    <div class="space-y-2">
                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-green-50 border border-green-200 text-green-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">Seluruh Indonesia</span>
                        </span>
                        <p class="text-xs text-gray-500">Vendor melayani seluruh wilayah Indonesia</p>
                    </div>
                </template>

                <!-- Tampilkan area spesifik -->
                <template x-if="!allIndonesia">
                    <div class="space-y-3">
                        <template x-if="selected.length === 0">
                            <div class="text-center py-4 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p>Belum ada area yang dipilih</p>
                            </div>
                        </template>

                        <template x-if="selected.length > 0">
                            <div class="space-y-2">
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(area, index) in selected" :key="area.id">
                                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-800"
                                              :title="area.path">
                                            <span x-text="area.name" class="font-medium"></span>
                                            <span class="text-xs bg-blue-200 text-blue-800 px-1.5 py-0.5 rounded capitalize" x-text="area.type"></span>
                                        </span>
                                    </template>
                                </div>
                                <p class="text-xs text-gray-500" x-text="'Total ' + selected.length + ' area terpilih'"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Checkbox Seluruh Indonesia -->
            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input type="checkbox" 
                       x-model="allIndonesia" 
                       id="all_indonesia"
                       class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                <label for="all_indonesia" class="text-sm font-medium text-gray-700 cursor-pointer">
                    Seluruh Indonesia
                </label>
            </div>

            <!-- PERBAIKAN: Input pencarian area dengan wrapper untuk z-index -->
            <div class="space-y-3" x-show="!allIndonesia">
                <label class="block text-sm font-medium text-gray-700">Tambah/Kurangi Area Layanan</label>
                
                <div class="relative">
                    <!-- PERBAIKAN: Tambahkan wrapper untuk mengontrol z-index -->
                    <div class="relative">
                        <!-- Input container dengan chips -->
                        <div class="border border-gray-300 rounded-lg p-3 bg-white flex flex-wrap gap-2 items-center min-h-[60px] transition-colors hover:border-gray-400 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-200"
                             @click="$refs.input?.focus()">
                            
                            <!-- Chips area yang dipilih -->
                            <template x-for="(area, index) in selected" :key="area.id">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 border border-blue-300 text-blue-800 text-sm transition-colors hover:bg-blue-200"
                                      :title="area.path">
                                    <span x-text="area.name" class="font-medium"></span>
                                    <button type="button" 
                                            class="w-4 h-4 flex items-center justify-center rounded-full bg-blue-300 text-blue-800 hover:bg-blue-400 transition-colors text-xs font-bold"
                                            @click="remove(index)"
                                            :aria-label="'Hapus ' + area.name">
                                        ×
                                    </button>
                                </span>
                            </template>

                            <!-- Input pencarian -->
                            <div class="relative flex-1 min-w-[200px]">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input x-ref="input" 
                                       type="text" 
                                       x-model="query"
                                       autocomplete="off"
                                       placeholder="Ketik nama area..."
                                       @focus="openDropdown()"
                                       @input="onInput()"
                                       @keydown.down.prevent="move(1)"
                                       @keydown.up.prevent="move(-1)"
                                       @keydown.enter.prevent="enterSelect()"
                                       @keydown.esc.prevent="closeDropdown()"
                                       class="block w-full pl-10 pr-3 py-2 border-0 focus:ring-0 text-sm placeholder-gray-400 bg-transparent">
                            </div>
                        </div>

                        <!-- PERBAIKAN: Dropdown suggestions dengan z-index yang lebih tinggi dan @mousedown.prevent -->
                        <div x-show="open" 
                             x-transition
                             @mousedown.prevent
                             class="absolute z-[9999] w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-auto">
                            
                            <template x-if="suggestions.length === 0 && query.trim().length < 2">
                                <div class="px-4 py-3 text-sm text-gray-500">Ketik minimal 2 huruf...</div>
                            </template>
                            
                            <template x-if="suggestions.length === 0 && query.trim().length >= 2">
                                <div class="px-4 py-3 text-sm text-gray-500">Tidak ada hasil untuk "<span x-text="query"></span>"</div>
                            </template>

                            <template x-for="(suggestion, index) in suggestions" :key="suggestion.id">
                                <button type="button"
                                        :class="index === hi ? 'bg-blue-50 border-l-4 border-l-blue-500' : 'hover:bg-gray-50'"
                                        class="w-full text-left px-4 py-3 border-b border-gray-100 last:border-b-0 transition-colors"
                                        @mouseenter="hi = index"
                                        @click="select(suggestion)">
                                    <div class="font-medium text-gray-900" x-text="suggestion.name"></div>
                                    <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                        <span class="capitalize px-2 py-0.5 bg-gray-100 rounded" x-text="suggestion.type"></span>
                                        <span x-text="suggestion.path"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500">Cari dan pilih area layanan untuk vendor</p>
            </div>

            <!-- Hidden inputs -->
            <input type="hidden" name="all_indonesia" x-bind:value="allIndonesia ? 1 : 0">
            <input type="hidden" name="area_ids_json" x-bind:value="JSON.stringify(selected.map(x => x.id))">

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                <?php if ($isModal): ?>
                    <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Tutup
                    </button>
                <?php else: ?>
                    <a href="<?= site_url('admin/areas') ?>" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                <?php endif; ?>
                
                <button type="submit" 
                        x-show="vendors.length > 0"  // Hanya tampilkan tombol simpan jika ada vendor
                        class="px-6 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!$isModal): ?>
    </div>
</div>
<?php endif; ?>