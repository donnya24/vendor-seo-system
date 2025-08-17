<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Partnership & SEO Performance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="font-sans bg-gray-50">

    <!-- Landing Section -->
    <section class="relative h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('/assets/img/logo/background.png');">
        <!-- Overlay gradasi -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent"></div>
        
        <!-- Header Logo + Text -->
        <div class="absolute top-8 left-8 z-20 flex items-center space-x-3">
            <img src="/assets/img/logo/icon.png" alt="Logo" class="h-10 w-10 rounded-full shadow-md">
            <span class="text-black text-2xl font-bold drop-shadow-lg">Vendor Partnership & SEO Performance</span>
        </div>

        <!-- Content -->
        <div class="relative z-10 container mx-auto px-6 flex flex-col md:flex-row items-center">
            <!-- Left Text -->
            <div class="text-black md:w-1/2 space-y-6 font-['Montserrat']">
                <h1 class="text-5xl font-extrabold leading-tight">
                    Kerja Sama Vendor yang Transparan & Efektif
                </h1>
                <p class="text-lg text-black-200">
                    Kelola profil, layanan, leads, dan laporan komisi dalam satu platform yang mudah digunakan.
                    Tingkatkan kolaborasi dan performa SEO bisnis Anda sekarang.
                </p>
            </div>

            <!-- Right Image -->
            <div class="md:w-1/2 flex justify-center mt-8 md:mt-0">
                <img src="/assets/img/logo/elemen.png" 
                    alt="Dashboard Mockup" 
                    class="w-full max-w-lg md:max-w-2xl rounded-lg shadow-none border-none">
            </div>
        </div>

        <!-- Floating Navigation Buttons -->
        <div class="absolute top-8 right-8 z-20 flex space-x-4">
            <!-- Tombol Masuk -->
            <a href="<?= site_url('Login'); ?>" 
               class="bg-transparent border-2 border-white text-white py-2 px-6 rounded-lg hover:bg-white hover:text-blue-700 text-lg shadow-md transition">
               Masuk
            </a>

            <!-- Tombol Daftar -->
            <a href="<?= site_url('Register'); ?>" 
               class="bg-white text-blue-700 py-2 px-6 rounded-lg hover:bg-blue-50 text-lg shadow-lg transition">
               Daftar
            </a>
        </div>

    </section>

    <!-- Footer Section -->
    <footer class="bg-gray-800 text-white py-6">
        <div class="text-center">
            <p>&copy; 2025 Vendor Partnership & SEO Performance. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
