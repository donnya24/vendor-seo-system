<?php // app/Views/seo/reports.php ?>
<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="seoReports()" x-init="init()">

  <head>
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
  </head>

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <h2 class="text-2xl font-bold text-gray-900">SEO Reports</h2>
  </div>

  <!-- Table Container -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <!-- Table Head -->
        <thead class="bg-blue-600 text-white text-xs uppercase sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-center font-semibold tracking-wider">No</th>
            <th class="px-4 py-3 text-left font-semibold tracking-wider">Tanggal</th>
            <th class="px-4 py-3 text-left font-semibold tracking-wider">Nama Vendor</th>
            <th class="px-4 py-3 text-left font-semibold tracking-wider">Keyword Layanan</th>
            <th class="px-4 py-3 text-center font-semibold tracking-wider">Posisi</th>
            <th class="px-4 py-3 text-center font-semibold tracking-wider">Perubahan</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-gray-100">
          <?php if (!empty($reports)): $no=1; foreach($reports as $r): ?>
            <tr class="hover:bg-gray-50 transition-colors duration-150">
              <td class="px-4 py-3 text-center text-gray-600"><?= $no++ ?></td>
              <td class="px-4 py-3 text-gray-800"><?= esc(date('d M Y', strtotime($r['updated_at'] ?? $r['created_at']))) ?></td>
              <td class="px-4 py-3 text-gray-900 font-medium truncate max-w-[200px]" title="<?= esc($r['vendor_name']) ?>">
                <?= esc($r['vendor_name']) ?>
              </td>
              <td class="px-4 py-3 text-gray-700 truncate max-w-[250px]" title="<?= esc($r['keyword']) ?>">
                <?= esc($r['keyword']) ?>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-gray-100 text-gray-800 font-medium">
                  <?= esc($r['current_position'] ?? '-') ?>
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <?php if ($r['change'] !== null): ?>
                  <?php if ($r['change'] > 0): ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                      +<?= $r['change'] ?> <i class="fas fa-arrow-up ml-1"></i>
                    </span>
                  <?php elseif ($r['change'] < 0): ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                      <?= $r['change'] ?> <i class="fas fa-arrow-down ml-1"></i>
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                      0
                    </span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-gray-400">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr>
              <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                <i class="fas fa-inbox text-gray-300 text-2xl mb-2"></i>
                <p>Belum ada laporan complete.</p>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
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
