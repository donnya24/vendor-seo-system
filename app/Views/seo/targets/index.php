<?php // app/Views/seo/targets.php ?>
<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="seoTargets()" x-init="init()">
  <head>
      <meta name="csrf-token-name" content="<?= csrf_token() ?>">
      <meta name="csrf-token" content="<?= csrf_hash() ?>">
  </head>

  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">SEO Targets</h1>
      <p class="mt-1 text-sm text-gray-600">Kelola target keyword untuk vendor</p>
    </div>
    <button @click="openModal()" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
      <i class="fas fa-plus mr-2"></i> Buat Target
    </button>
  </div>

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
    <table class="table-auto min-w-[1200px] w-full divide-y divide-gray-200 text-sm">
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
          <td class="px-4 py-3 font-medium text-gray-800"><?= esc($t['vendor_name'] ?? '-') ?></td>
          <td class="px-4 py-3"><?= esc($t['project_name']) ?></td>
          <td class="px-4 py-3 truncate max-w-[200px]" title="<?= esc($t['keyword']) ?>"><?= esc($t['keyword']) ?></td>
          <td class="px-4 py-3 text-center"><?= $t['current_position'] ?: '—' ?></td>
          <td class="px-4 py-3 text-center"><?= $t['target_position'] ?: '—' ?></td>
          <td class="px-4 py-3 text-center"><?= $t['deadline'] ?: '—' ?></td>
          <td class="px-4 py-3 text-center">
            <!-- badge priority -->
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
            <!-- badge status -->
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
          <td class="px-4 py-3 truncate max-w-[200px]" title="<?= esc($t['notes'] ?? '-') ?>"><?= esc($t['notes'] ?? '-') ?></td>
          <td class="px-4 py-3 text-center">
            <div class="flex flex-wrap gap-2 justify-center">
                <button @click="edit(<?= $t['id'] ?>)" 
                        class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                  <i class="fas fa-edit mr-1"></i> Edit
                </button>
                <button @click="destroy(<?= $t['id'] ?>)" 
                        class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                  <i class="fas fa-trash-alt mr-1"></i> Hapus
                </button>
            </div>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr>
          <td colspan="11" class="px-4 py-8 text-center text-gray-500">
            <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i><br>
            Tidak ada data target
          </td>
        </tr>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>

  <!-- Modal -->
  <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.away="closeModal()" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-white z-10 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900" x-text="modalTitle"></h3>
        <button @click="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6">
        <form x-ref="targetForm" @submit.prevent="submitForm" class="space-y-5">
          <input type="hidden" name="id" x-model="form.id">
          <input type="hidden" name="vendor_id" value="<?= esc($vendorId) ?>">

          <!-- Project Name -->
          <div>
            <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
            <input type="text" id="project_name" x-model="form.project_name" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3 transition-colors duration-200" required>
          </div>

          <!-- Keyword -->
          <div>
            <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">Keyword</label>
            <input type="text" id="keyword" x-model="form.keyword" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3 transition-colors duration-200" required>
          </div>

          <!-- Current & Target -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="current_position" class="block text-sm font-medium text-gray-700 mb-1">Current Position</label>
              <input type="number" id="current_position" x-model="form.current_position" min="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3 transition-colors duration-200">
            </div>
            <div>
              <label for="target_position" class="block text-sm font-medium text-gray-700 mb-1">Target Position</label>
              <input type="number" id="target_position" x-model="form.target_position" min="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3 transition-colors duration-200" required>
            </div>
          </div>

          <!-- Deadline -->
          <div>
            <label for="deadline" class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
            <input type="date" id="deadline" x-model="form.deadline" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3 transition-colors duration-200">
          </div>

          <!-- Priority & Status -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <select id="priority" x-model="form.priority" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3 transition-colors duration-200">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select id="status" x-model="form.status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3 transition-colors duration-200">
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
              class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3 transition-colors duration-200"></textarea>
          </div>

          <!-- Buttons -->
          <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t border-gray-200">
            <button type="button" @click="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 font-medium transition-colors duration-200 order-2 sm:order-1">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 font-medium transition-colors duration-200 order-1 sm:order-2">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Notification Toast (for success/error messages) -->
  <div x-show="notification.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
       class="fixed bottom-4 right-4 z-50 bg-white rounded-lg shadow-lg border-l-4 p-4 max-w-md"
       :class="{
         'border-green-500': notification.type === 'success',
         'border-red-500': notification.type === 'error'
       }"
       x-cloak>
    <div class="flex items-start">
      <div class="flex-shrink-0">
        <i class="fas" :class="{
          'fa-check-circle text-green-500': notification.type === 'success',
          'fa-exclamation-circle text-red-500': notification.type === 'error'
        }"></i>
      </div>
      <div class="ml-3">
        <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
        <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
      </div>
      <div class="ml-auto pl-3">
        <button @click="notification.show = false" class="text-gray-400 hover:text-gray-500 focus:outline-none">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>

<script>
function seoTargets() {
    return {
        showModal: false,
        modalTitle: 'Tambah Target',
        notification: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            timeout: null
        },
        form: {
            id: '',
            vendor_id: '<?= esc($vendorId) ?>',
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
            // Clear any existing timeout
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }
            
            // Set notification properties
            this.notification.type = type;
            this.notification.title = title;
            this.notification.message = message;
            this.notification.show = true;
            
            // Auto hide after 5 seconds
            this.notification.timeout = setTimeout(() => {
                this.notification.show = false;
            }, 5000);
        },
        openModal() {
            this.modalTitle = 'Tambah Target';
            this.resetForm();
            this.showModal = true;
            // Add overflow hidden to body when modal is open
            document.body.style.overflow = 'hidden';
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
                priority: 'low',
                status: 'pending',
                notes: ''
            };
        },
        closeModal() {
            this.showModal = false;
            // Restore body overflow when modal is closed
            document.body.style.overflow = '';
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
                document.body.style.overflow = 'hidden';
            })
            .catch(err => {
                this.showNotification('error', 'Error', err.message);
            });
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
                if (data.success) {
                    this.showNotification('success', 'Berhasil', 'Target berhasil dihapus');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.showNotification('error', 'Gagal', data.message || 'Gagal menghapus data.');
                }
            })
            .catch(err => {
                this.showNotification('error', 'Error', 'Error: ' + err.message);
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
                    this.showNotification('success', 'Berhasil', 'Target berhasil disimpan');
                    setTimeout(() => {
                        this.closeModal();
                        location.reload();
                    }, 1500);
                } else {
                    this.showNotification('error', 'Gagal', data.message || 'Gagal menyimpan data.');
                }
            })
            .catch(err => {
                console.error(err);
                this.showNotification('error', 'Error', 'Terjadi kesalahan: ' + err.message);
            });
        }
    }
}
</script>

<?= $this->endSection() ?>