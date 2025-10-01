<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div id="pageWrap"
     class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out"
     :class="(sidebarOpen && (typeof isDesktop==='undefined' || isDesktop)) ? 'md:ml-64' : 'ml-0'">

  <!-- HEADER -->
  <div class="px-4 md:px-6 pt-4 md:pt-6 max-w-screen-lg mx-auto w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-100 text-blue-600">
            <i class="fa-solid fa-user-plus"></i>
          </span>
          <h1 class="text-xl md:text-2xl font-bold text-gray-900">Add SEO</h1>
        </div>
        <p class="text-xs md:text-sm text-gray-500 mt-1">Buat akun baru untuk Tim SEO</p>
      </div>

      <a href="<?= site_url('admin/users?tab=seo'); ?>"
         class="inline-flex items-center gap-2 bg-white/80 border border-gray-200 hover:bg-white text-gray-700 font-medium text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
        <i class="fa-solid fa-arrow-left text-[11px]"></i> Kembali
      </a>
    </div>

    <!-- Flash Message -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mt-3 p-3 rounded-lg bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
        <?= esc(session()->getFlashdata('success')) ?>
      </div>
    <?php elseif (session()->getFlashdata('error')): ?>
      <div class="mt-3 p-3 rounded-lg bg-rose-50 text-rose-800 text-sm border border-rose-200">
        <?= esc(session()->getFlashdata('error')) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()): ?>
      <div class="mt-3 p-3 rounded-lg bg-amber-50 text-amber-800 text-sm border border-amber-200">
        <ul class="list-disc list-inside">
          <?php foreach ($validation->getErrors() as $err): ?>
            <li><?= esc($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>

  <!-- MAIN -->
  <main class="flex-1 px-4 md:px-6 pb-10 mt-3 max-w-screen-lg mx-auto w-full">
    <section class="relative overflow-hidden rounded-2xl border border-gray-100 shadow-sm bg-white">
      <!-- Header Section -->
      <div class="h-3 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700"></div>
      <div class="px-4 md:px-6 py-3 bg-white">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-blue-50 text-blue-600">
            <i class="fa-solid fa-users"></i>
          </span>
          <h2 class="text-sm md:text-base font-semibold text-gray-800">Form SEO Baru</h2>
        </div>
      </div>

      <!-- Form SEO -->
      <div id="formContainer">
        <?= $this->include('admin/users/_form_seo'); ?>
      </div>
    </section>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
