<?php // pastikan $sp & $userEmail tersedia dari layout ?>
<!-- Modal Edit Profil -->
<div x-show="$store.ui.modal==='seoProfileEdit'"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
     @click.self="$store.ui.modal=null"
     style="display:none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl"
       @click.stop
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-1">
    <div class="flex items-center justify-between px-4 py-3 border-b">
      <h3 class="text-lg font-semibold">Edit Profil</h3>
      <button type="button" class="text-gray-500 hover:text-gray-700" @click="$store.ui.modal=null">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="p-4">
      <?= $this->include('Seo/profile/edit') ?>
    </div>
  </div>
</div>

<!-- Modal Ubah Password -->
<div x-show="$store.ui.modal==='seoPasswordEdit'"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
     @click.self="$store.ui.modal=null"
     style="display:none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg"
       @click.stop
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-1">
    <div class="flex items-center justify-between px-4 py-3 border-b">
      <h3 class="text-lg font-semibold">Ubah Password</h3>
      <button type="button" class="text-gray-500 hover:text-gray-700" @click="$store.ui.modal=null">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="p-4">
      <?= $this->include('Seo/profile/ubahpassword') ?>
    </div>
  </div>
</div>
