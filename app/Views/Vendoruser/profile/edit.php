<div x-data="{ open: false }">
  <!-- Tombol -->
  <button @click="open = true"
          class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
    Ubah Password
  </button>

  <!-- Modal -->
  <div x-show="open"
       class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div @click.away="open = false"
         class="bg-white rounded-xl p-6 shadow max-w-md w-full">
      <h2 class="text-xl font-semibold mb-4">Ubah Password</h2>

      <form action="<?= site_url('vendor/profile/changepassword'); ?>" method="post" class="space-y-4">
        <?= csrf_field() ?>

        <div>
          <label class="block text-sm font-semibold mb-2">Password Lama</label>
          <input type="password" name="old_password"
                 class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200" required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Password Baru</label>
          <input type="password" name="new_password"
                 class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200" required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-2">Konfirmasi Password Baru</label>
          <input type="password" name="confirm_password"
                 class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200" required>
        </div>

        <div class="pt-4 flex justify-end gap-2">
          <button type="button" @click="open = false" class="px-4 py-2 bg-gray-300 rounded-lg">Batal</button>
          <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
