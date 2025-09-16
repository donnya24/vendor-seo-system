<?php // app/Views/seo/targets.php ?>
<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="seoTargets()" x-init="init()">
  <head>
      <meta name="csrf-token-name" content="<?= csrf_token() ?>">
      <meta name="csrf-token" content="<?= csrf_hash() ?>">
  </head>

  <!-- Header -->
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold text-gray-800">SEO Targets</h2>
    <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
      Buat Target
    </button>
  </div>

  <!-- Table -->
  <div class="bg-white p-4 rounded-xl shadow border border-gray-200 overflow-x-auto">
    <table class="min-w-full text-sm divide-y divide-gray-200">
      <thead class="bg-blue-600 text-white uppercase text-xs">
        <tr>
          <th class="p-3 text-center">No</th>
          <th class="p-3 text-left">Project</th>
          <th class="p-3 text-left">Keyword</th>
          <th class="p-3 text-center">Current</th>
          <th class="p-3 text-center">Target</th>
          <th class="p-3 text-center">Deadline</th>
          <th class="p-3 text-center">Priority</th>
          <th class="p-3 text-center">Status</th>
          <th class="p-3 text-left">Notes</th>
          <th class="p-3 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($targets)): $no=1; foreach($targets as $t): ?>
        <tr class="hover:bg-gray-50">
          <td class="p-3 text-center"><?= $no++ ?></td>
          <td class="p-3"><?= esc($t['project_name']) ?></td>
          <td class="p-3"><?= esc($t['keyword']) ?></td>
          <td class="p-3 text-center"><?= $t['current_position'] ?: '—' ?></td>
          <td class="p-3 text-center"><?= $t['target_position'] ?: '—' ?></td>
          <td class="p-3 text-center"><?= $t['deadline'] ?: '—' ?></td>
          <td class="p-3 text-center"><?= esc($t['priority']) ?></td>
          <td class="p-3 text-center"><?= esc($t['status']) ?></td>
          <td class="p-3"><?= esc($t['notes'] ?? '-') ?></td>
          <td class="p-3 text-center space-x-2">
            <button @click="edit(<?= $t['id'] ?>)" 
                    class="px-3 py-1 text-xs rounded bg-blue-500 text-white hover:bg-blue-600">
              Edit
            </button>
            <button @click="destroy(<?= $t['id'] ?>)" 
                    class="px-3 py-1 text-xs rounded bg-green-500 text-white hover:bg-green-600">
              Hapus
            </button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
          <td colspan="10" class="p-3 text-center text-gray-500">Tidak ada data</td>
        </tr>
        <?php endif ?>
      </tbody>
    </table>
  </div>

  <!-- Modal -->
  <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-cloak>
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative" @click.away="closeModal()">
      <button @click="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      <h3 class="text-xl font-semibold mb-4" x-text="modalTitle"></h3>

      <form x-ref="targetForm" @submit.prevent="submitForm" class="space-y-4">
        <input type="hidden" name="id" x-model="form.id">
        <input type="hidden" name="vendor_id" value="<?= esc($vendorId) ?>">

        <!-- Project Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Project Name</label>
          <input type="text" x-model="form.project_name" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Keyword -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Keyword</label>
          <input type="text" x-model="form.keyword" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Current & Target -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Current Pos</label>
            <input type="number" x-model="form.current_position" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Target Pos</label>
            <input type="number" x-model="form.target_position" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>

        <!-- Deadline -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Deadline</label>
          <input type="date" x-model="form.deadline" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Priority & Status -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Priority</label>
            <select x-model="form.priority" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
              <option>Low</option>
              <option>Medium</option>
              <option>High</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select x-model="form.status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Notes</label>
          <textarea x-model="form.notes" rows="3"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>

        <!-- Tombol -->
        <div class="flex justify-end space-x-2 pt-4">
          <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
        </div>
      </form>
    </div>
  </div>

<script>
function seoTargets() {
    return {
        showModal: false,
        modalTitle: 'Tambah Target',
        form: {
            id: '',
            vendor_id: '<?= esc($vendorId) ?>',
            project_name: '',
            keyword: '',
            current_position: '',
            target_position: '',
            deadline: '',
            priority: 'Low',
            status: 'pending',
            notes: ''
        },
        // init dipanggil oleh x-init="init()" di root. jangan hapus x-init, biar aman.
        init() {
            // no-op for now; could be used to load defaults
        },
        openModal() {
            this.modalTitle = 'Tambah Target';
            this.resetForm();
            this.showModal = true;
        },
        resetForm() {
            this.form = {
                id: '',
                vendor_id: '<?= esc($vendorId) ?>',
                project_name: '',
                keyword: '',
                current_position: '',
                target_position: '',
                deadline: '',
                priority: 'Low',
                status: 'pending',
                notes: ''
            };
        },
        closeModal() {
            this.showModal = false;
        },
        edit(id) {
            fetch(`<?= site_url('seo/targets/edit') ?>/${id}`, {
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
                // server returns data.data
                this.form = { ...this.form, ...data.data };
                this.showModal = true;
            })
            .catch(err => alert(err.message));
        },
        destroy(id) {
            if (!confirm('Yakin ingin menghapus target ini?')) return;

            const csrfName = document.querySelector('meta[name="csrf-token-name"]').content;
            const csrfHash = document.querySelector('meta[name="csrf-token"]').content;

            const formData = new FormData();
            formData.append(csrfName, csrfHash);

            fetch(`<?= site_url('seo/targets/delete') ?>/${id}`, {
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
                if (data.success) location.reload();
                else alert(data.message || 'Gagal menghapus data.');
            })
            .catch(err => alert('Error: ' + err.message));
        },
        submitForm() {
            const csrfName = document.querySelector('meta[name="csrf-token-name"]').content;
            const csrfHash = document.querySelector('meta[name="csrf-token"]').content;

            const formData = new FormData();
            formData.append(csrfName, csrfHash);
            for (const key in this.form) {
                formData.append(key, this.form[key] ?? '');
            }

            const url = this.form.id
                ? `<?= site_url('seo/targets/update') ?>/${this.form.id}`
                : `<?= site_url('seo/targets/store') ?>`;

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
                    // show server HTML or error text to help debugging
                    throw new Error('Server response bukan JSON: ' + text);
                }
            })
            .then(data => {
                if (data.success) {
                    this.closeModal();
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menyimpan data.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan: ' + err.message);
            });
        }
    }
}
</script>

<?= $this->endSection() ?>
