<?php 
include_once(APPPATH . 'Views/vendoruser/layouts/header.php');
include_once(APPPATH . 'Views/vendoruser/layouts/sidebar.php');
?>

<!-- Main -->
<div class="flex-1 flex flex-col overflow-hidden" :class="{'md:ml-64': $store.ui.sidebar}">
  <!-- Topbar -->
  <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="$store.ui.sidebar ? 'md:ml-64' : ''">
    <div class="flex items-center justify-between p-4">
      <button class="hover:opacity-80" @click="$store.ui.sidebar=!$store.ui.sidebar" aria-label="Toggle sidebar">
        <i class="fas fa-bars text-gray-700"></i>
      </button>

      <div class="flex items-center gap-4">
        <button class="relative text-gray-600 hover:text-gray-900">
          <i class="fas fa-bell text-xl"></i>
          <span x-show="$store.app.unread>0"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
                x-text="$store.app.unread"></span>
        </button>
      </div>
    </div>
  </header>
  <div class="h-16"></div>

  <!-- CONTENT -->
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <!-- Konten halaman leads di sini -->
    <h1 class="text-2xl font-bold mb-6">Leads Saya</h1>
    <!-- ... -->
  </main>
</div>

<?php include_once(APPPATH . 'Views/vendoruser/layouts/footer.php'); ?>