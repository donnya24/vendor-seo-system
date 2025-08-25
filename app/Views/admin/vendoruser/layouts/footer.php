<!-- Content wrapper start in each page, end here -->
</div> <!-- /.min-h-screen flex -->

<!-- Topbar (sticky) -->
<header class="fixed top-0 left-0 right-0 bg-white shadow z-20 md:pl-64">
  <div class="flex items-center justify-between px-4 py-3">
    <button class="hamburger-btn md:hidden" @click="sidebarOpen=true">
      <i class="fa-solid fa-bars text-gray-700"></i>
    </button>
    <div class="text-sm text-gray-500">Vendor Partnership & SEO Performance</div>
    <div class="flex items-center gap-3">
      <a href="<?= site_url('vendor/notifications'); ?>" class="text-gray-600 hover:text-blue-600">
        <i class="fa-regular fa-bell"></i>
      </a>
      <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
    </div>
  </div>
</header>
<div class="h-16"></div>

<!-- Logout Modal + Form (inline, no partial) -->
<div x-show="modalOpen==='logout'" x-transition.opacity class="fixed inset-0 z-50">
  <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
  <div class="relative mx-auto mt-40 w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
    <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center">
      <i class="fa-solid fa-right-from-bracket text-2xl"></i>
    </div>
    <h3 class="mt-4 text-center text-xl font-semibold text-gray-900">Keluar dari Sistem?</h3>
    <p class="mt-2 text-center text-sm text-gray-500">Anda akan keluar dari sesi saat ini.</p>

    <form id="logoutForm" method="post" action="<?= site_url('logout'); ?>" class="mt-6 flex justify-center gap-3">
      <?= csrf_field() ?>
      <button type="button" class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50" @click="closeModal()">Batal</button>
      <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Ya, Keluar</button>
    </form>
  </div>
</div>

</body>
</html>
