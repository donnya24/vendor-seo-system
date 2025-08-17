<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="<?= base_url('assets/css/styles.css') ?>" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.js" defer></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <h2 class="text-3xl font-semibold text-blue-800">Admin Dashboard</h2>

        <!-- Vendors Table -->
        <div class="mt-8" x-data="tableData">
            <h3 class="text-2xl font-semibold text-blue-800">Vendors</h3>
            <button @click="openModal('create')" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Add New Vendor</button>

            <table class="min-w-full mt-4 bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-blue-800 text-white">
                        <th class="py-3 px-4 text-left">Vendor Name</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vendors as $vendor): ?>
                    <tr>
                        <td class="py-3 px-4"><?= $vendor['business_name']; ?></td>
                        <td class="py-3 px-4"><?= $vendor['status'] == 1 ? 'Active' : 'Inactive'; ?></td>
                        <td class="py-3 px-4">
                            <button @click="openModal('edit', <?= $vendor['id']; ?>)" class="bg-yellow-500 text-white px-4 py-2 rounded">Edit</button>
                            <button @click="deleteVendor(<?= $vendor['id']; ?>)" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- SEO Reports Section -->
        <div class="mt-8">
            <h3 class="text-2xl font-semibold text-blue-800">SEO Reports</h3>
            <table class="min-w-full mt-4 bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-blue-800 text-white">
                        <th class="py-3 px-4 text-left">Keyword</th>
                        <th class="py-3 px-4 text-left">Ranking Position</th>
                        <th class="py-3 px-4 text-left">Traffic</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seoReports as $report): ?>
                    <tr>
                        <td class="py-3 px-4"><?= $report['keyword']; ?></td>
                        <td class="py-3 px-4"><?= $report['ranking_position']; ?></td>
                        <td class="py-3 px-4"><?= $report['traffic']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add more sections like Leads and Commissions here as needed -->
    </div>

    <!-- Modal for Vendor -->
    <div x-show="modalOpen" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-xl font-semibold" x-text="modalTitle"></h3>
            <form x-show="isCreate" @submit.prevent="submitForm">
                <div class="mt-4">
                    <label for="vendorName" class="block">Vendor Name</label>
                    <input type="text" id="vendorName" x-model="formData.business_name" class="w-full px-4 py-2 border rounded" required>
                </div>
                <div class="mt-4">
                    <label for="status" class="block">Status</label>
                    <select id="status" x-model="formData.status" class="w-full px-4 py-2 border rounded" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button @click="closeModal" type="button" class="bg-gray-300 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
            <div x-show="isEdit">
                <!-- Additional fields for edit -->
                <p>Edit Vendor Form here.</p>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/Dashboard_admin.js') ?>"></script>
</body>

</html>
