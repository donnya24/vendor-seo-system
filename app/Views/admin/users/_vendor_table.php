<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Vendor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Telepon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. WA</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Komisi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($usersVendor)): ?>
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                        Tidak ada data vendor
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($usersVendor as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $user['id'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $user['owner_name'] ?? '-' ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $user['business_name'] ?? '-' ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $user['username'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $user['phone'] ?? '-' ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $user['whatsapp_number'] ?? '-' ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $user['email'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php if ($user['commission_type'] === 'nominal'): ?>
                                Rp <?= number_format($user['requested_commission_nominal'] ?? 0, 0, ',', '.') ?>
                            <?php else: ?>
                                <?= $user['requested_commission'] ?? 0 ?>%
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $status = $user['status'] ?? 'pending';
                            $statusClass = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'verified' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'inactive' => 'bg-gray-100 text-gray-800'
                            ];
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass[$status] ?? 'bg-gray-100 text-gray-800' ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <!-- Edit Button -->
                                <a href="javascript:void(0)" onclick="loadEditModal(<?= $user['id'] ?>, 'vendor')" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Verify Button -->
                                <?php if ($status !== 'verified'): ?>
                                    <a href="<?= site_url('admin/users/verifyVendor/'.$user['id']) ?>" class="text-green-600 hover:text-green-900" title="Verify Vendor">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Reject Button -->
                                <?php if ($status !== 'rejected'): ?>
                                    <a href="javascript:void(0)" onclick="showRejectModal(<?= $user['id'] ?>)" class="text-red-600 hover:text-red-900" title="Reject Vendor">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Delete Button -->
                                <a href="<?= site_url('admin/users/delete/'.$user['id']) ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus vendor ini?')" class="text-red-600 hover:text-red-900" title="Delete Vendor">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Reject Reason -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Alasan Penolakan Vendor</h3>
            <div class="mt-2 px-7 py-3">
                <form id="rejectForm" action="<?= site_url('admin/users/rejectVendor/') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" id="vendorId" name="vendorId" value="">
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700">Alasan</label>
                        <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" onclick="closeRejectModal()" class="mr-2 px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showRejectModal(vendorId) {
    document.getElementById('vendorId').value = vendorId;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

function loadEditModal(userId, role) {
    // Implementasi untuk memuat modal edit
    fetch(<?= site_url('admin/users/edit/') ?>${userId}?role=${role}, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Tampilkan modal dengan HTML yang diterima
        document.getElementById('editModalContent').innerHTML = html;
        document.getElementById('editModal').classList.remove('hidden');
    })
    .catch(error => console.error('Error:', error));
}
</script>