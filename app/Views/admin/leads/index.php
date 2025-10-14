<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div id="pageWrap" class="flex-1 flex flex-col overflow-hidden relative z-10">
  <header class="bg-white shadow-md z-20 sticky top-0">
    <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
      <div>
        <h1 class="text-lg font-bold text-gray-800">Leads Management</h1>
        <p class="text-xs text-gray-500 mt-1">Kelola semua leads dari vendor</p>
      </div>
      <!-- Tombol Tambah -->
      <?= $this->include('admin/leads/create'); ?>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto bg-gray-50 pt-4">
    <div class="px-4 sm:px-6 py-6">
      
      <!-- HEADER DENGAN FILTER DAN DELETE ALL -->
      <div class="mb-5 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <!-- FILTER VENDOR -->
        <form method="get" class="flex items-center gap-3">
          <div class="flex items-center gap-2">
            <label for="vendor_id" class="font-medium text-gray-700">Filter Vendor:</label>
            <select id="vendor_id" name="vendor_id" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
              <option value="">-- Semua Vendor --</option>
              <?php foreach($vendors as $v): ?>
                <option value="<?= esc($v['id']) ?>" <?= ($vendor_id == $v['id']) ? 'selected' : '' ?>>
                  <?= esc($v['business_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg shadow">
            Filter
          </button>
        </form>
        <!-- EXPORT CSV BUTTON -->
        <a href="<?= site_url('admin/leads/export' . (!empty($vendor_id) ? '?vendor_id=' . $vendor_id : '')) ?>" 
          class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
          <i class="fas fa-file-csv"></i> Export CSV
        </a>
        <!-- DELETE ALL BUTTON -->
        <?php if (!empty($leads)): ?>
          <form method="post" action="<?= site_url('admin/leads/delete-all' . (!empty($vendor_id) ? '?vendor_id=' . $vendor_id : '')) ?>" 
                id="deleteAllForm">
            <?= csrf_field() ?>
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
              <i class="fas fa-trash-alt"></i>
              Hapus Semua Leads
            </button>
          </form>
        <?php endif; ?>
      </div>

      <!-- INFO STATS -->
      <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex flex-wrap gap-4 text-sm text-blue-800">
          <div class="flex items-center gap-1">
            <i class="fas fa-building"></i>
            <span>Total Vendor: <?= count($vendors) ?></span>
          </div>
          <div class="flex items-center gap-1">
            <i class="fas fa-list-alt"></i>
            <span>Total Leads: <?= count($leads) ?></span>
          </div>
          <?php if (!empty($vendor_id)): ?>
            <div class="flex items-center gap-1">
              <i class="fas fa-filter"></i>
              <span>Filter aktif: Vendor tertentu</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- TABLE -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                  <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold">NO</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">VENDOR</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">MASUK</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">CLOSING</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">PERIODE TANGGAL</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">UPDATE</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">AKSI</th>
                  </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                  <?php if (!empty($leads)): ?>
                      <?php $no = 1; foreach($leads as $lead): ?>
                          <tr>
                              <td class="px-4 py-2"><?= $no++ ?></td>
                              <td class="px-4 py-2"><?= esc($lead['vendor_name']) ?></td>
                              <td class="px-4 py-2"><?= esc($lead['jumlah_leads_masuk']) ?></td>
                              <td class="px-4 py-2"><?= esc($lead['jumlah_leads_closing']) ?></td>
                              <td class="px-4 py-2">
                                  <?php 
                                      // Format periode tanggal
                                      if (!empty($lead['tanggal_mulai']) && !empty($lead['tanggal_selesai'])) {
                                          echo date('Y-m-d', strtotime($lead['tanggal_mulai'])) . ' s/d ' . date('Y-m-d', strtotime($lead['tanggal_selesai']));
                                      } elseif (!empty($lead['tanggal_mulai'])) {
                                          echo date('Y-m-d', strtotime($lead['tanggal_mulai'])) . ' s/d sekarang';
                                      } elseif (!empty($lead['tanggal_selesai'])) {
                                          echo 'sampai ' . date('Y-m-d', strtotime($lead['tanggal_selesai']));
                                      } else {
                                          echo '<span class="text-gray-400">-</span>';
                                      }
                                  ?>
                              </td>
                              <td class="px-4 py-2"><?= esc($lead['updated_at']) ?></td>
                              <td class="px-4 py-2 flex gap-2">
                                  <!-- Modal Edit -->
                                  <?= view('admin/leads/edit', ['lead' => $lead, 'vendors' => $vendors]) ?>

                                  <!-- Tombol Hapus -->
                                  <button type="button" onclick="confirmDelete(<?= $lead['id'] ?>)" class="text-red-600 text-sm hover:underline">
                                      Hapus
                                  </button>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  <?php else: ?>
                      <tr>
                          <td colspan="7" class="p-4 text-center text-gray-500">
                              <div class="py-8">
                                  <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                                  <p class="text-lg">Belum ada data leads.</p>
                                  <?php if (!empty($vendor_id)): ?>
                                      <p class="text-sm text-gray-500 mt-1">Vendor ini belum memiliki data leads.</p>
                                  <?php endif; ?>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk menampilkan notifikasi SweetAlert mini
    function showNotification(type, title, message) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: type,
            title: title,
            text: message
        });
    }

    // Fungsi untuk konfirmasi hapus
    window.confirmDelete = function(id) {
        Swal.fire({
            title: 'Hapus Lead?',
            text: "Anda tidak akan dapat mengembalikan data ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`<?= site_url('admin/leads/delete') ?>/${id}`, {
                    method: 'POST',
                    body: new FormData()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', 'Berhasil', data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('error', 'Gagal', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Error', 'Terjadi kesalahan pada server');
                    console.error('Error:', error);
                });
            }
        });
    };

    // Handle create form
    const createForm = document.getElementById('createLeadForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validasi tanggal
            const tanggalMulai = this.querySelector('input[name="tanggal_mulai"]').value;
            const tanggalSelesai = this.querySelector('input[name="tanggal_selesai"]').value;
            
            if (!tanggalMulai) {
                showNotification('error', 'Validasi Gagal', 'Tanggal mulai wajib diisi');
                return;
            }
            
            if (tanggalSelesai && new Date(tanggalSelesai) < new Date(tanggalMulai)) {
                showNotification('error', 'Validasi Gagal', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
                return;
            }
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Berhasil', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification('error', 'Gagal', data.message);
                }
            })
            .catch(error => {
                showNotification('error', 'Error', 'Terjadi kesalahan pada server');
                console.error('Error:', error);
            });
        });
    }

    // Handle update form
    const updateForm = document.getElementById('editLeadForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Berhasil', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification('error', 'Gagal', data.message);
                }
            })
            .catch(error => {
                showNotification('error', 'Error', 'Terjadi kesalahan pada server');
                console.error('Error:', error);
            });
        });
    }

    // Handle delete all form
    const deleteAllForm = document.querySelector('#deleteAllForm');
    if (deleteAllForm) {
        deleteAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const vendorFilter = '<?= !empty($vendor_id) ? " untuk vendor ini" : " semua vendor" ?>';
            
            Swal.fire({
                title: 'Hapus Semua Leads?',
                html: `Anda akan menghapus <strong>semua data leads${vendorFilter}</strong>.<br><br>Tindakan ini <strong>tidak dapat dibatalkan</strong>!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                width: 420,
                padding: '1rem',
                customClass: {
                    popup: 'small-swal-popup',
                    title: 'small-swal-title',
                    htmlContainer: 'small-swal-content',
                    confirmButton: 'small-swal-confirm',
                    cancelButton: 'small-swal-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    }
});
</script>

<style>
/* Style untuk SweetAlert yang lebih kecil */
.small-swal-popup {
    font-size: 0.875rem !important;
    border-radius: 12px !important;
}

.small-swal-title {
    font-size: 1.1rem !important;
    margin-bottom: 0.5rem !important;
    padding: 0 !important;
}

.small-swal-content {
    font-size: 0.8rem !important;
    line-height: 1.4 !important;
    padding: 0 !important;
}

.small-swal-confirm,
.small-swal-cancel {
    font-size: 0.8rem !important;
    padding: 0.5rem 1.2rem !important;
    margin: 0 0.3rem !important;
}
</style>

<?= $this->include('admin/layouts/footer'); ?>