<?= $this->extend('templates/auth') ?>

<?= $this->section('content') ?>
<div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Register Vendor</h1>
        <p class="text-gray-600">Create your account</p>
    </div>

    <?php if (session('errors')): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php foreach (session('errors') as $error): ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('register') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">Email</label>
            <input type="email" name="email" id="email" 
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   value="<?= old('email') ?>" required>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">Password</label>
            <input type="password" name="password" id="password" 
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <div class="mb-6">
            <label for="password_confirm" class="block text-gray-700 mb-2">Confirm Password</label>
            <input type="password" name="password_confirm" id="password_confirm" 
                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        <button type="submit" 
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
            Register
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="<?= base_url('login') ?>" class="text-blue-600 hover:text-blue-800">
            Already have an account? Sign In
        </a>
    </div>
</div>
<?= $this->endSection() ?>