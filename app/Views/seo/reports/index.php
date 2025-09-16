<?php // app/Views/seo/reports.php ?>
<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="seoReports()" x-init="init()">

  <head>
      <meta name="csrf-token-name" content="<?= csrf_token() ?>">
      <meta name="csrf-token" content="<?= csrf_hash() ?>">
  </head>

  <!-- Header -->
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold text-gray-800">SEO Reports</h2>
    <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
      + Tambah Report
    </button>
  </div>

  <!-- Table -->
  <div class="bg-white p-4 rounded-xl shadow border border-gray-200 overflow-x-auto">
    <table class="min-w-full text-sm divide-y divide-gray-200">
      <thead class="bg-blue-600 text-white uppercase text-xs">
        <tr>
          <th class="p-3 text-center">No</th>
          <th class="p-3 text-left">Tanggal</th>
          <th class="p-3 text-left">Project / Keyword</th>
          <th class="p-3 text-center">Posisi</th>
          <th class="p-3 text-center">Perubahan</th>
          <th class="p-3 text-center">Trend</th>
          <th class="p-3 text-center">Leads</th>
          <th class="p-3 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        <?php if (!empty($reports)): $no=1; foreach($reports as $r): ?>
        <tr class="hover:bg-gray-50">
          <td class="p-3 text-center"><?= $no++ ?></td>
          <td class="p-3"><?= esc(date('d M Y', strtotime($r['created_at']))) ?></td>
          <td class="p-3">
            <div class="font-medium"><?= esc($r['project']) ?></div>
            <div class="text-xs text-gray-500"><?= esc($r['keyword']) ?></div>
          </td>
          <td class="p-3 text-center"><?= $r['position'] ?: '—' ?></td>
          <td class="p-3 text-center <?= $r['change']>0?'text-green-600':($r['change']<0?'text-red-600':'text-gray-400') ?>">
            <?= $r['change'] ? (($r['change']>0?'+':'').$r['change']) : '—' ?>
          </td>
          <td class="p-3 text-center"><?= esc($r['trend'] ?: '-') ?></td>
          <td class="p-3 text-center"><?= $r['leads'] ?? '—' ?></td>
          <td class="p-3 text-center space-x-2">
            <button @click="edit(<?= $r['id'] ?>)" 
                    class="px-3 py-1 text-xs rounded bg-blue-500 text-white hover:bg-blue-600">
              Edit
            </button>
            <button @click="destroy(<?= $r['id'] ?>)" 
                    class="px-3 py-1 text-xs rounded bg-green-500 text-white hover:bg-green-600">
              Hapus
            </button>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="8" class="p-3 text-center text-gray-500">Tidak ada data</td></tr>
        <?php endif ?>
      </tbody>
    </table>
  </div>

  <!-- Modal -->
  <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-cloak>
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 relative" @click.away="closeModal()">
      <button @click="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      <h3 class="text-xl font-semibold mb-4" x-text="modalTitle">Tambah Report</h3>

      <form @submit.prevent="submitForm" class="space-y-4">
        <input type="hidden" name="id" x-model="form.id">
        <input type="hidden" name="vendor_id" value="<?= esc($vendorId) ?>">

        <div>
          <label class="block text-sm font-medium text-gray-700">Project</label>
          <input type="text" x-model="form.project" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Keyword</label>
          <input type="text" x-model="form.keyword" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Posisi</label>
            <input type="number" x-model="form.position" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Perubahan</label>
            <input type="number" x-model="form.change" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Trend</label>
          <select x-model="form.trend" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="stable">Stable</option>
            <option value="up">Up</option>
            <option value="down">Down</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Leads</label>
          <input type="number" x-model="form.leads" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex justify-end space-x-2 pt-4">
          <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function seoReports() {
    return {
        showModal: false,
        modalTitle: 'Tambah Report',
        form: {
            id: '',
            vendor_id: '<?= esc($vendorId) ?>',
            project: '',
            keyword: '',
            position: '',
            change: '',
            trend: 'stable',
            leads: ''
        },
        openModal() {
            this.modalTitle = 'Tambah Report';
            this.resetForm();
            this.showModal = true;
        },
        resetForm() {
            this.form = {
                id: '',
                vendor_id: '<?= esc($vendorId) ?>',
                project: '',
                keyword: '',
                position: '',
                change: '',
                trend: 'stable',
                leads: ''
            };
        },
        closeModal() {
            this.showModal = false;
        },
        edit(id) {
            fetch(`<?= site_url('seo/reports/edit') ?>/${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                this.modalTitle = 'Edit Report';
                this.form = { ...this.form, ...data };
                this.showModal = true;
            });
        },
        destroy(id) {
            if (!confirm('Yakin ingin menghapus report ini?')) return;

            const csrfName = document.querySelector('meta[name="csrf-token-name"]').content;
            const csrfHash = document.querySelector('meta[name="csrf-token"]').content;

            const formData = new FormData();
            formData.append(csrfName, csrfHash);

            fetch(`<?= site_url('seo/reports/delete') ?>/${id}`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus report.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan: ' + err.message);
            });
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
                ? `<?= site_url('seo/reports/update') ?>/${this.form.id}`
                : `<?= site_url('seo/reports/store') ?>`;

            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(async res => {
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch {
                    throw new Error(`Server response bukan JSON: ${text}`);
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
