<!doctype html>
<html lang="id" x-data="vendorApp()" x-init="init()" :class="{'overflow-hidden': modalOpen!==null}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($page ?? 'Vendor Dashboard'); ?></title>

  <!-- Tailwind & Alpine (CDN) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    body{font-family:'Montserrat',system-ui,Arial}
    .nav-item{transition:background .2s}
    .active{background:rgba(255,255,255,.18)}
    .hamburger-btn i{font-size:1.125rem}
  </style>
  <script>
    function vendorApp(){
      return {
        sidebarOpen: true,
        modalOpen: null,
        init(){
          const pref = localStorage.getItem('vendorSidebarOpen');
          this.sidebarOpen = pref !== null ? pref === 'true' : (window.innerWidth >= 768);
          window.addEventListener('resize',()=>{ if(window.innerWidth<768) this.sidebarOpen=false });
          this.$watch('sidebarOpen', v => localStorage.setItem('vendorSidebarOpen', v));
        },
        openLogout(){ this.modalOpen='logout' },
        closeModal(){ this.modalOpen=null },
        doLogout(){ document.getElementById('logoutForm')?.submit(); }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-gray-50">
<div class="min-h-screen flex">
