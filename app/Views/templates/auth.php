<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Vendor SEO System' ?></title>
    <link href="<?= base_url('assets/css/vendor/auth.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <?= $this->renderSection('content') ?>
    
    <script src="<?= base_url('assets/js/vendor/auth.js') ?>"></script>
</body>
</html>