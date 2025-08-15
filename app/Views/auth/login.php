<!DOCTYPE html>
<html lang="id" x-data="{ showPass: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Vendor SEO System</title>
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
        <div class="w-full max-w-md">
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-8 pt-8 pb-6 text-center">
                    <div class="mx-auto w-12 h-12 rounded-xl grid place-items-center bg-brand/20 text-brand">
                        <span class="text-xl font-semibold">VS</span>
                    </div>
                    <h1 class="mt-4 text-2xl font-semibold tracking-tight">Selamat Datang</h1>
                    <p class="mt-1 text-sm text-slate-300">Masuk untuk mengelola vendor, leads, dan laporan.</p>
                </div>

                <form action="<?= site_url('auth/attempt-login') ?>" method="post" class="px-8 pb-8 space-y-5">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-sm mb-1">Email</label>
                        <input type="email" name="email" value="<?= old('email') ?>" class="w-full rounded-xl bg-slate-800/70 border border-white/10 focus:border-brand focus:ring-2 focus:ring-brand/40 px-4 py-3 outline-none" placeholder="you@example.com" required>
                    </div>

                    <div class="input-group px-4 py-3 rounded-xl bg-slate-800/70 border border-white/10">
                        <div class="flex-1">
                            <label class="block text-sm mb-1">Kata Sandi</label>
                            <input :type="showPass ? 'text' : 'password'" id="password" name="password" class="w-full bg-transparent outline-none" placeholder="••••••••" required>
                        </div>
                        <button type="button" id="togglePassword" class="text-xs text-slate-300 hover:text-white px-2 py-1 rounded-md border border-white/10">
                            Tampilkan
                        </button>
                    </div>

                    <button class="w-full py-3 rounded-xl bg-brand hover:bg-brand/90 transition font-semibold shadow-lg shadow-brand/20">
                        Masuk
                    </button>

                    <p class="text-center text-sm text-slate-300">
                        Belum punya akun?
                        <a href="<?= site_url('auth/register') ?>" class="text-brand hover:underline">Daftar</a>
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
