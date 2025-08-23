
  <!-- Overlay untuk mobile ketika sidebar terbuka -->
  <div
    class="sidebar-overlay fixed inset-0 bg-black/40 md:hidden"
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    x-cloak
  ></div>

  </div><!-- /#appShell -->

<!-- Modal Logout dengan Alpine.js -->
<div x-show="showLogoutModal" 
     x-transition.opacity 
     x-cloak 
     class="fixed inset-0 z-50">
  <div class="min-h-screen flex items-center justify-center p-4 no-scrollbar">
    
    <!-- backdrop -->
    <div class="fixed inset-0 bg-black/40" @click="showLogoutModal = false"></div>

    <!-- card -->
    <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
      <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center">
        <i class="fa-solid fa-right-from-bracket text-2xl"></i>
      </div>

      <h3 class="mt-4 text-center text-xl font-semibold text-gray-900">Keluar dari Sistem?</h3>
      <p class="mt-2 text-center text-sm text-gray-500">Anda akan keluar dari sesi saat ini.</p>

      <!-- FORM POST logout -->
      <form action="<?= site_url('logout'); ?>" method="post" 
            class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
        <?= csrf_field() ?>
        <button type="button"
                class="w-full sm:w-auto px-4 py-3 sm:py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                @click="showLogoutModal = false">
          Batal
        </button>
        <button type="submit"
                class="w-full sm:w-auto px-4 py-3 sm:py-2 rounded-md bg-red-600 text-white hover:bg-red-700">
          Ya, Keluar
        </button>
      </form>
    </div>
  </div>
</div>

  <!-- Mobile Navigation Bottom Bar -->
  <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-20">
    <div class="flex justify-around py-2">
      <a href="<?= site_url('admin/dashboard'); ?>" class="flex flex-col items-center text-blue-600 p-2">
        <i class="fas fa-tachometer-alt mb-1"></i>
        <span class="text-xs">Dashboard</span>
      </a>
      <a href="<?= site_url('admin/leads'); ?>" class="flex flex-col items-center text-gray-600 p-2">
        <i class="fas fa-list mb-1"></i>
        <span class="text-xs">Leads</span>
      </a>
      <button @click="sidebarOpen = true" class="flex flex-col items-center text-gray-600 p-2">
        <i class="fas fa-bars mb-1"></i>
        <span class="text-xs">Menu</span>
      </button>
      <button @click="profileDropdownOpen = !profileDropdownOpen" class="flex flex-col items-center text-gray-600 p-2">
        <i class="fas fa-user mb-1"></i>
        <span class="text-xs">Profil</span>
      </button>
    </div>
  </div>

  <!-- Profile Dropdown Mobile -->
  <div x-show="profileDropdownOpen" x-cloak class="fixed inset-0 z-40 md:hidden" @click="profileDropdownOpen = false">
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-lg p-4 no-scrollbar" @click.stop>
      <div class="flex items-center space-x-3 mb-4">
        <img class="h-10 w-10 rounded-full" src="https://i.pravatar.cc/80" alt="Admin">
        <div>
          <p class="font-medium">Admin User</p>
          <p class="text-sm text-gray-500">admin@example.com</p>
        </div>
      </div>
      <div class="space-y-2">
        <a href="#" class="block py-2 px-4 rounded-lg hover:bg-gray-100">Profil Saya</a>
        <a href="#" class="block py-2 px-4 rounded-lg hover:bg-gray-100">Pengaturan</a>
        <button @click="showLogoutModal = true; profileDropdownOpen = false" class="w-full text-left py-2 px-4 rounded-lg hover:bg-gray-100 text-red-600">
          Logout
        </button>
      </div>
      <button @click="profileDropdownOpen = false" class="w-full mt-4 py-2 text-center text-gray-500">
        Tutup
      </button>
    </div>
  </div>

</body>
</html>

