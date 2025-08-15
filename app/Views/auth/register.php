<!DOCTYPE html>
<html lang="id" x-data="{ showPass: false, showConfirm: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | Vendor SEO System</title>
    <!-- Link ke Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Link ke Alpine.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <!-- Link ke Custom CSS -->
    <link href="<?= base_url('assets/css/auth/auth.css') ?>" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 to-slate-950 text-slate-100 font-ui">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="w-[800px] h-[800px] rounded-full bg-brand/10 blur-3xl absolute -top-40 -left-40"></div>
        <div class="w-[700px] h-[700px] rounded-full bg-accent/10 blur-3xl absolute -bottom-40 -right-40"></div>
    </div>

    <main class="relative z-10 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-xl">
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-8 pt-8 pb-6 text-center">
                    <h1 class="text-2xl font-semibold tracking-tight">Buat Akun Baru</h1>
                    <p class="mt-1 text-sm text-slate-300">Daftar untuk mulai mengelola data vendor dan laporan.</p>
                </div>

                <form action="<?= site_url('auth/attempt-register') ?>" method="post" class="px-8 pb-8 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?= csrf_field() ?>

                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="<?= old('name') ?>" class="w-full rounded-xl bg-slate-800/70 border border-white/10 focus:border-brand focus:ring-2 focus:ring-brand/40 px-4 py-3 outline-none" placeholder="Nama lengkap" required>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Email</label>
                        <input type="email" name="email" value="<?= old('email') ?>" class="w-full rounded-xl bg-slate-800/70 border border-white/10 focus:border-brand focus:ring-2 focus:ring-brand/40 px-4 py-3 outline-none" placeholder="you@example.com" required>
                    </div>

                    <div>
                        <label class="block text-sm mb-1">No. Telepon</label>
                        <input type="tel" name="phone" value="<?= old('phone') ?>" class="w-full rounded-xl bg-slate-800/70 border border-white/10 focus:border-brand focus:ring-2 focus:ring-brand/40 px-4 py-3 outline-none" placeholder="08xxxxxxx">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Perusahaan (opsional)</label>
                        <input type="text" name="company" value="<?= old('company') ?>" class="w-full rounded-xl bg-slate-800/70 border border-white/10 focus:border-brand focus:ring-2 focus:ring-brand/40 px-4 py-3 outline-none" placeholder="Nama perusahaan">
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm mb-1">Kata Sandi</label>
                        <div class="flex items-center gap-2 rounded-xl bg-slate-800/70 border border-white/10 px-4">
                            <input :type="showPass ? 'text' : 'password'" name="password" class="flex-1 bg-transparent outline-none py-3" placeholder="••••••••" required>
                            <button type="button" @click="showPass = !showPass" class="text-xs text-slate-300 hover:text-white px-2 py-1 rounded-md border border-white/10">Tampilkan</button>
                        </div>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm mb-1">Ulangi Kata Sandi</label>
                        <div class="flex items-center gap-2 rounded-xl bg-slate-800/70 border border-white/10 px-4">
                            <input :type="showConfirm ? 'text' : 'password'" name="password_confirm" class="flex-1 bg-transparent outline-none py-3" placeholder="••••••••" required>
                            <button type="button" @click="showConfirm = !showConfirm" class="text-xs text-slate-300 hover:text-white px-2 py-1 rounded-md border border-white/10">Tampilkan</button>
                        </div>
                    </div>

                    <div class="md:col-span-2 flex items-start gap-3 text-sm">
                        <input type="checkbox" required class="mt-1 rounded border-white/20 bg-slate-800/70">
                        <p>Saya menyetujui <a class="text-brand hover:underline" href="#">Ketentuan Layanan</a> dan <a class="text-brand hover:underline" href="#">Kebijakan Privasi</a>.</p>
                    </div>

                    <div class="md:col-span-2">
                        <button class="w-full py-3 rounded-xl bg-brand hover:bg-brand/90 transition font-semibold shadow-lg shadow-brand/20">
                            Daftar
                        </button>
                    </div>

                    <p class="md:col-span-2 text-center text-sm text-slate-300">
                        Sudah punya akun?
                        <a href="<?= site_url('auth/login') ?>" class="text-brand hover:underline">Masuk</a>
                    </p>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                © <?= date('Y') ?> Vendor SEO System. All rights reserved.
            </p>
        </div>
    </main>

    <!-- Link ke Custom JS -->
    <script src="<?= base_url('assets/js/auth/auth.js') ?>"></script>
</body>
</html>
