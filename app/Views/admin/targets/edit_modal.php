<!-- Modal Container dengan Backdrop -->
<div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
     style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="closeModal()"
     @click.self="closeModal()">
    
    <!-- Modal Content dengan struktur yang benar untuk scroll -->
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col mx-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <h3 class="text-xl font-semibold text-gray-900">Edit Target SEO</h3>
            <button @click="closeModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body dengan scroll -->
        <div class="flex-1 overflow-y-auto p-6">
            <form x-ref="targetForm" @submit.prevent="submitForm" class="space-y-5">
                <input type="hidden" name="id" x-model="form.id">

                <!-- Vendor Selection -->
                <div>
                    <label for="vendor_id" class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <select id="vendor_id" x-model="form.vendor_id" 
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" 
                            required>
                        <option value="">Pilih Vendor</option>
                        <?php foreach($vendors as $vendor): ?>
                            <option value="<?= $vendor['id'] ?>"><?= esc($vendor['business_name']) ?> (ID: <?= $vendor['id'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Project Name -->
                <div>
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                    <input type="text" id="project_name" x-model="form.project_name" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" 
                           required>
                </div>

                <!-- Keyword -->
                <div>
                    <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">Keyword</label>
                    <input type="text" id="keyword" x-model="form.keyword" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" 
                           required>
                </div>

                <!-- Current & Target -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="current_position" class="block text-sm font-medium text-gray-700 mb-1">Current Position</label>
                        <input type="number" id="current_position" x-model="form.current_position" min="1" 
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                    </div>
                    <div>
                        <label for="target_position" class="block text-sm font-medium text-gray-700 mb-1">Target Position</label>
                        <input type="number" id="target_position" x-model="form.target_position" min="1" 
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3" 
                               required>
                    </div>
                </div>

                <!-- Deadline -->
                <div>
                    <label for="deadline" class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                    <input type="date" id="deadline" x-model="form.deadline" 
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                </div>

                <!-- Priority & Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select id="priority" x-model="form.priority" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" x-model="form.status" 
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3">
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
                              class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-3"></textarea>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex flex-col sm:flex-row sm:justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <button type="button" @click="closeModal()" 
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors">
                Batal
            </button>
            <button type="submit" form="targetForm"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">
                Update
            </button>
        </div>
    </div>
</div>

<style>
/* PERBAIKAN: CSS khusus untuk modal agar tidak terpengaruh CSS global */
.modal-container {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
    z-index: 9999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    overflow: hidden !important;
}

.modal-content {
    position: relative !important;
    max-height: 90vh !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    width: 100% !important;
    max-width: 32rem !important;
    margin: 1rem !important;
}

.modal-header {
    flex-shrink: 0 !important;
}

.modal-body {
    flex: 1 !important;
    overflow-y: auto !important;
    padding: 1.5rem !important;
    min-height: 0 !important;
}

.modal-footer {
    flex-shrink: 0 !important;
}

/* Reset CSS yang mungkin mengganggu */
.modal-body * {
    box-sizing: border-box !important;
}

/* Pastikan scrollbar terlihat */
.modal-body::-webkit-scrollbar {
    width: 6px !important;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1 !important;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1 !important;
    border-radius: 3px !important;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8 !important;
}

/* Override CSS global yang mungkin mengganggu */
body.modal-open {
    overflow: hidden !important;
    height: 100vh !important;
    position: fixed !important;
    width: 100% !important;
}

[x-cloak] { 
    display: none !important; 
}
</style>