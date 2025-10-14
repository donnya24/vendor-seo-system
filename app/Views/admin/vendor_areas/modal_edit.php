<?php
$isModal = $isModal ?? false;
$vendor = $vendor ?? [];
$pre = $selectedAreas ?? [];
$isAll = $isAllIndonesia ?? false;

// Data untuk Alpine.js
$areasData = [];
foreach ($pre as $area) {
    $areasData[] = [
        'id' => (int)($area['id'] ?? 0),
        'name' => $area['name'] ?? '',
        'type' => $area['type'] ?? '',
        'path' => $area['path'] ?? ($area['name'] ?? '')
    ];
}
?>

<style>[x-cloak]{display:none!important}</style>

<!-- Simpan data di HTML attributes -->
<div id="modalData" 
     data-areas='<?= json_encode($areasData) ?>'
     data-all-indonesia='<?= $isAll ? 'true' : 'false' ?>'
     data-search-url='<?= site_url('admin/areas/search') ?>'
     style="display: none;">
</div>

<div class="bg-white p-6">
    <div
        x-data="{
            // Ambil data dari HTML attributes
            getModalData() {
                const el = document.getElementById('modalData');
                return {
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

            init() {
                // Load data dari HTML
                const modalData = this.getModalData();
                this.allIndonesia = modalData.allIndonesia;
                this.selected = modalData.areas;
                this.searchUrl = modalData.searchUrl;
                
                console.log('Alpine initialized with:', this.selected);
                
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
                }
            },

            // Fungsi-fungsi lainnya (SAMA)
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
                    this.open = true; 
                    this.hi = -1; 
                    return; 
                }
                
                fetch(this.searchUrl + '?q=' + encodeURIComponent(q), {
                    headers: {'X-Requested-With':'XMLHttpRequest'}
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        const picked = new Set(this.selected.map(x => parseInt(x.id)));
                        this.suggestions = res.data.filter(x => !picked.has(parseInt(x.id)));
                    } else {
                        this.suggestions = [];
                    }
                    this.open = true;
                    this.hi = this.suggestions.length ? 0 : -1;
                })
                .catch(error => {
                    console.error('Search error:', error);
                    this.suggestions = [];
                    this.open = true;
                    this.hi = -1;
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
                this.$nextTick(() => this.$refs.input?.focus());
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

            clearAll() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus semua area?',
                    text: 'Ini akan mengosongkan daftar area yang tersimpan di form.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    width: 300,
                }).then((r) => {
                    if (r.isConfirmed) {
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
                            width: 280,
                        });
                    }
                });
            },

            async handleSubmit(e) {
                e.preventDefault();
                
                if (this.selected.length === 0 && !this.allIndonesia) {
                    showMini('warning', 'Pilih minimal satu area atau centang Seluruh Indonesia');
                    return;
                }

                const formData = new FormData(e.target);
                
                try {
                    const response = await fetch(e.target.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        showMini('success', result.message, result.redirect || '<?= site_url('admin/areas') ?>');
                        setTimeout(() => closeModal(), 2000);
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
            
            <input type="hidden" name="vendor_id" value="<?= $vendor['id'] ?>">

            <!-- Informasi Vendor -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-3">Informasi Vendor</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-700">Nama Bisnis:</span>
                        <span class="text-blue-700 font-medium"><?= esc($vendor['business_name']) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-700">ID Vendor:</span>
                        <span class="text-blue-700 font-mono"><?= $vendor['id'] ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-700">Status:</span>
                        <span class="px-2 py-1 text-xs rounded-full <?= $vendor['status'] == 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= ucfirst($vendor['status']) ?>
                        </span>
                    </div>
                    <?php if (!empty($vendor['phone'])): ?>
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-700">Telepon:</span>
                        <span class="text-blue-700"><?= esc($vendor['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Area yang tersimpan -->
            <div class="border border-gray-200 rounded-lg bg-white p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm font-medium text-gray-700">Area yang tersimpan saat ini</div>
                    <button
                        type="button"
                        x-show="allIndonesia || selected.length"
                        @click.prevent="clearAll()"
                        class="text-xs px-3 py-1.5 rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition-colors"
                    >Hapus semua</button>
                </div>

                <template x-if="allIndonesia">
                    <div class="space-y-2">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-50 border border-green-200 text-green-800 text-sm">
                            ✅ Seluruh Indonesia
                        </span>
                        <p class="text-xs text-gray-500">Vendor ini melayani seluruh wilayah Indonesia</p>
                    </div>
                </template>

                <template x-if="!allIndonesia && selected.length === 0">
                    <div class="text-center py-4">
                        <span class="text-sm text-gray-500">Belum ada area yang dipilih</span>
                    </div>
                </template>

                <template x-if="!allIndonesia && selected.length > 0">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="(it, index) in selected" :key="it.id">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-800 text-sm"
                                      :title="it.path || it.name">
                                    <span x-text="it.name" class="font-medium"></span>
                                    <button type="button" 
                                            class="leading-none hover:text-red-600 text-xs transition-colors" 
                                            @click="remove(index)"
                                            :aria-label="'Hapus ' + it.name">
                                        ×
                                    </button>
                                </span>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500" x-text="`Total ${selected.length} area terpilih`"></p>
                    </div>
                </template>
            </div>

            <!-- Checkbox Seluruh Indonesia -->
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <input type="checkbox" 
                       x-model="allIndonesia" 
                       class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-700">Seluruh Indonesia</span>
            </div>

            <!-- Input untuk menambah/mengurangi area -->
            <div class="space-y-2" x-show="!allIndonesia">
                <label class="block text-sm font-medium text-gray-700">Tambah/Kurangi Area Layanan</label>
                <div class="relative">
                    <div class="border border-gray-300 rounded-lg p-3 min-h-[60px] bg-white flex flex-wrap gap-2 items-center transition-colors hover:border-gray-400 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-200"
                         @click="$refs.input.focus()">

                        <!-- CHIPS: Area yang sudah dipilih -->
                        <template x-for="(item, idx) in selected" :key="item.id">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 text-blue-800 text-sm transition-colors hover:bg-blue-100"
                                  :title="item.path || item.name">
                                <span x-text="item.name" class="text-xs font-medium"></span>
                                <button type="button" 
                                        class="leading-none hover:text-red-600 text-xs transition-colors" 
                                        @click="remove(idx)"
                                        :aria-label="'Hapus ' + item.name">
                                    ×
                                </button>
                            </span>
                        </template>

                        <!-- Input pencarian -->
                        <div class="relative flex-1 min-w-[200px]">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input x-ref="input" type="text" x-model="query"
                                   autocomplete="off"
                                   placeholder="Cari area..."
                                   @focus="openDropdown()"
                                   @input="onInput()"
                                   @keydown.down.prevent="move(1)"
                                   @keydown.up.prevent="move(-1)"
                                   @keydown.enter.prevent="enterSelect()"
                                   @keydown.esc.prevent="closeDropdown()"
                                   class="block w-full ps-10 pe-3 py-2 border-0 focus:ring-0 text-sm placeholder-gray-400 bg-transparent" />
                        </div>
                    </div>

                    <!-- Dropdown suggestions -->
                    <div x-show="open" @mousedown.prevent
                         class="absolute z-50 mt-1 w-full max-h-64 overflow-auto bg-white border border-gray-200 rounded-lg shadow-lg">
                        <template x-if="(suggestions.length === 0) && (query.trim().length < 2)">
                            <div class="px-4 py-3 text-sm text-gray-500">Ketik minimal 2 huruf...</div>
                        </template>
                        <template x-if="(suggestions.length === 0) && (query.trim().length >= 2)">
                            <div class="px-4 py-3 text-sm text-gray-500">Tidak ada hasil untuk "<span x-text="query"></span>"</div>
                        </template>

                        <template x-for="(s, i) in suggestions" :key="s.id">
                            <button type="button"
                                    :class="i === hi ? 'bg-blue-50 border-l-2 border-l-blue-500' : 'hover:bg-gray-50'"
                                    class="w-full text-left px-4 py-3 text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                                    @mouseenter="hi = i"
                                    @click="select(s)">
                                <div class="font-medium text-gray-900" x-text="s.name"></div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span class="capitalize" x-text="s.type"></span>
                                    <span class="mx-1">·</span>
                                    <span x-text="s.path"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Cari dan pilih area layanan untuk vendor</p>
            </div>

            <input type="hidden" name="all_indonesia" x-bind:value="allIndonesia ? 1 : 0">
            <input type="hidden" name="area_ids_json" x-bind:value="JSON.stringify(selected.map(x => x.id))">

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">Tutup</button>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!$isModal): ?>
    </div>
</div>
<?php endif; ?>