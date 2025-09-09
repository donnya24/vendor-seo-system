<?php
$isModal = $isModal ?? false;
$pre     = $selectedAreas ?? []; // [{id,name,type,path}, ...]
$isAll   = isset($isAllIndonesia) && $isAllIndonesia;
?>
<?php if (!$isModal): ?>
  <div class="flex-1 md:ml-64 p-4">
    <h2 class="text-xl font-semibold mb-4"><?= esc($title ?? 'Edit Area Layanan') ?></h2>
<?php endif; ?>

<style>[x-cloak]{display:none!important}</style>

<script>
  // SET langsung dari server (tanpa ??), supaya selalu fresh
  window.__AREAS_PRE        = <?= json_encode($pre, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_NUMERIC_CHECK) ?>;
  window.__AREAS_SEARCH_URL = '<?= site_url('vendoruser/areas/search') ?>';
</script>

<div
  x-data="{
    allIndonesia: <?= $isAll ? 'true' : 'false' ?>,
    query: '',
    selected: [],
    suggestions: [],
    open: false,
    hi: -1,
    _t: null,

    clearAll(){
      if (!window.Swal) {
        this.allIndonesia = false;
        this.selected = [];
        this.query = '';
        this.closeDropdown();
        return;
      }
      const swalMini = {
        popup: 'rounded-md text-sm p-3',
        title: 'text-sm font-semibold',
        htmlContainer: 'text-sm',
        confirmButton: 'px-3 py-1.5 rounded-lg text-sm',
        cancelButton: 'px-3 py-1.5 rounded-lg text-sm',
      };
      Swal.fire({
        icon: 'warning',
        title: 'Hapus semua area?',
        text: 'Ini akan mengosongkan daftar area yang tersimpan di form.',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        width: 300,
        customClass: swalMini,
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
            customClass: swalMini,
          });
        }
      });
    },

    init(){
      // Prefill: tampilkan area yang sudah tersimpan (sama seperti di index)
      if (this.allIndonesia) {
        this.selected = [{ id: 0, name: 'Seluruh Indonesia', type: 'region', path: 'Seluruh Indonesia' }];
      } else {
        this.selected = (window.__AREAS_PRE || []).map(s => ({
          id: parseInt(s.id), name: s.name, type: s.type, path: s.path || s.name
        }));
      }
      this.$watch('allIndonesia', (v) => {
        if (v){
          this.query = ''; this.closeDropdown();
          this.selected = [{ id: 0, name: 'Seluruh Indonesia', type: 'region', path: 'Seluruh Indonesia' }];
        } else {
          this.selected = (window.__AREAS_PRE || []).map(s => ({
            id: parseInt(s.id), name: s.name, type: s.type, path: s.path || s.name
          }));
        }
      });
    },

    onInput(){ clearTimeout(this._t); this._t = setTimeout(() => this.fetchSuggest(), 200); },
    openDropdown(){ this.open = true; if (this.query.trim().length >= 2) this.fetchSuggest(); },
    closeDropdown(){ this.open = false; this.hi = -1; },

    fetchSuggest(){
      const q = this.query.trim();
      if (q.length < 2) { this.suggestions = []; this.open = true; this.hi = -1; return; }
      fetch(window.__AREAS_SEARCH_URL + '?q=' + encodeURIComponent(q), {
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
      .catch(() => { this.suggestions = []; this.open = true; this.hi = -1; });
    },

    select(s){
      this.selected.push({ id: parseInt(s.id), name: s.name, type: s.type, path: s.path });
      this.query = ''; this.closeDropdown(); this.$nextTick(()=>this.$refs.input && this.$refs.input.focus());
    },

    remove(idx){ this.selected.splice(idx, 1); },
    move(d){ if (!this.open || this.suggestions.length === 0) return; this.hi = (this.hi + d + this.suggestions.length) % this.suggestions.length; },
    enterSelect(){ if (this.open && this.hi >= 0 && this.hi < this.suggestions.length) this.select(this.suggestions[this.hi]); },

    // --- submit AJAX jika dipanggil sebagai modal ---
    async handleSubmit(e){
      const fd = new FormData(e.target);
      try{
        const r  = await fetch(e.target.action, {
          method: 'POST',
          headers: {'X-Requested-With':'XMLHttpRequest'},
          body: fd
        });
        const ct = r.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
          const j = await r.json();
          // showMini() didefinisikan global (footer/index induk)
          if (typeof showMini === 'function') {
            showMini(j.status || 'success', j.message || 'Tersimpan', j.redirect || '<?= site_url('vendoruser/areas') ?>');
          }
          if (!j.redirect && typeof closeAreasPopup === 'function') closeAreasPopup();
        } else {
          // Fallback: jika bukan JSON, lanjut submit normal
          e.target.submit();
        }
      }catch(_){
        if (typeof showMini === 'function') showMini('error', 'Koneksi gagal. Coba lagi.');
      }
    }
  }"
  x-init="init()"
  class="space-y-4 <?= $isModal ? '' : 'max-w-lg' ?>"
  x-cloak
>
  <form
    action="<?= site_url('vendoruser/areas/attach') ?>"
    method="post"
    class="space-y-4"
    <?= $isModal ? '@submit.prevent="handleSubmit($event)"' : '' ?>
  >
    <?= csrf_field() ?>

    <!-- Ringkasan area tersimpan (seperti index) -->
    <div class="border border-gray-200 rounded-lg bg-white p-3">
      <div class="flex items-center justify-between mb-2">
        <div class="text-sm font-medium">Area yang tersimpan saat ini</div>
        <button
          type="button"
          x-show="allIndonesia || selected.length"
          @click.prevent="clearAll()"
          class="text-xs px-2 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50"
        >Hapus semua</button>
      </div>

      <template x-if="allIndonesia">
        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full
                     bg-green-50 border border-green-200 text-green-800 text-sm">
          Seluruh Indonesia
        </span>
      </template>

      <template x-if="!allIndonesia && selected.length === 0">
        <span class="text-sm text-gray-500">Belum ada area.</span>
      </template>

      <template x-if="!allIndonesia && selected.length">
        <div class="flex flex-wrap gap-2">
          <template x-for="it in selected" :key="'summary-'+it.id">
            <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full
                         bg-blue-50 border border-blue-200 text-blue-800 text-sm"
                  :title="it.path || it.name">
              <span x-text="it.name"></span>
            </span>
          </template>
        </div>
      </template>
    </div>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" x-model="allIndonesia" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
      <span class="text-sm font-medium">Seluruh Indonesia</span>
    </label>

    <!-- Input pilih area (bisa tambah/hapus) -->
    <div class="relative">
      <div class="border border-gray-300 rounded-lg p-2 min-h-[56px] bg-white
                  flex flex-wrap gap-2 items-center"
          :class="allIndonesia ? 'opacity-60 pointer-events-none' : ''"
          @click="$refs.input && $refs.input.focus()">

        <!-- chips area -->
        <template x-for="(item, idx) in selected" :key="item.id">
          <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full
                      bg-blue-50 border border-blue-200 text-blue-800 text-sm"
                :title="item.path || item.name">
            <span x-text="item.name"></span>
            <button type="button" class="leading-none hover:text-red-600" @click="remove(idx)">&times;</button>
          </span>
        </template>

        <!-- input + ikon search -->
        <div class="relative flex-1 min-w-[220px]">
          <div class="absolute inset-y-0 start-0 flex items-center ps-2 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg>
          </div>
          <input x-ref="input" type="text" x-model="query"
                autocomplete="off"
                placeholder="Cari area…"
                @focus="openDropdown()"
                @input="onInput()"
                @keydown.down.prevent="move(1)"
                @keydown.up.prevent="move(-1)"
                @keydown.enter.prevent="enterSelect()"
                @keydown.esc.prevent="closeDropdown()"
                class="block w-full ps-8 border-0 focus:ring-0 text-sm placeholder-gray-400" />
        </div>
      </div>

      <!-- dropdown -->
      <div x-show="open && !allIndonesia" @mousedown.prevent
          class="absolute z-50 mt-1 w-full max-h-64 overflow-auto bg-white border border-gray-200 rounded-lg shadow">
        <template x-if="(suggestions.length === 0) && (query.trim().length < 2)">
          <div class="px-3 py-2 text-sm text-gray-500">Ketik minimal 2 huruf…</div>
        </template>
        <template x-if="(suggestions.length === 0) && (query.trim().length >= 2)">
          <div class="px-3 py-2 text-sm text-gray-500">Tidak ada hasil</div>
        </template>

        <template x-for="(s, i) in suggestions" :key="s.id">
          <button type="button"
                  :class="i === hi ? 'bg-blue-50' : ''"
                  class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50"
                  @mouseenter="hi = i"
                  @click="select(s)">
            <div class="font-medium" x-text="s.name"></div>
            <div class="text-xs text-gray-500">
              <span class="capitalize" x-text="s.type"></span>
              <span> · </span>
              <span x-text="s.path"></span>
            </div>
          </button>
        </template>
      </div>
    </div>

    <input type="hidden" name="all_indonesia" x-bind:value="allIndonesia ? 1 : 0">
    <input type="hidden" name="area_ids_json" x-bind:value="JSON.stringify(selected.map(x => x.id))">

    <div class="flex justify-end gap-2 pt-2">
      <?php if ($isModal): ?>
        <button type="button" onclick="closeAreasPopup()" class="px-3 py-2 rounded-lg border">Tutup</button>
      <?php else: ?>
        <a href="<?= site_url('vendoruser/areas') ?>" class="px-3 py-2 rounded-lg border">Batal</a>
      <?php endif; ?>
      <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
        Simpan Perubahan
      </button>
    </div>
  </form>
</div>

<?php if (!$isModal): ?>
  </div>
<?php endif; ?>
