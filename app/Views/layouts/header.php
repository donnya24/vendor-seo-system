<!DOCTYPE html>
<html lang="id" x-data="adminDashboard()" :class="{'overflow-hidden': modalOpen}">
<head>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="X-CSRF-TOKEN">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= esc($title ?? ('Admin | ' . ($page ?? ''))) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    [x-cloak]{display:none!important}.sidebar{transition:all .3s ease}
    .nav-item{position:relative;transition:transform 140ms ease, box-shadow 140ms ease, background 140ms ease}
    .nav-item:hover{transform:translateX(2px)}
    .nav-item.active{background:linear-gradient(90deg,rgba(59,130,246,.25),rgba(37,99,235,.35));
      box-shadow: inset 0 0 0 1px rgba(255,255,255,.08), 0 0 0 2px rgba(59,130,246,.20), 0 8px 28px rgba(30,64,175,.35)}
    .nav-item.active::before{content:"";position:absolute;left:-4px;top:10%;bottom:10%;width:6px;border-radius:9999px;
      background:radial-gradient(10px 60% at 50% 50%, rgba(191,219,254,.95), rgba(59,130,246,.4) 60%, transparent 70%);filter:blur(.2px)}
    .hamburger-btn{transition:all .2s ease}
    .hamburger-btn:hover{transform:scale(1.1);opacity:.8}
  </style>
</head>
<body class="bg-gray-50 font-sans" x-cloak>
<div class="flex h-screen overflow-hidden">
