<?php
// siapkan payload JSON aman untuk Alpine
$JSON = static function($arr){
  return json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
};

// Data default untuk menghindari error
$stats = $stats ?? [
    'leads_new' => 0,
    'leads_inprogress' => 0,
    'keywords_total' => 0,
    'unread' => 0
];
$recentLeads = $recentLeads ?? [];
$topKeywords = $topKeywords ?? [];
?>
<!DOCTYPE html>
<html lang="id" x-data :class="{'overflow-hidden': $store.ui.modal}">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vendor Dashboard | Vendor Partnership SEO Performance</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

  <style>
    [x-cloak]{display:none!important}
    .sidebar{transition:all .25s ease}
    .nav-item{position:relative;transition:transform .14s ease,box-shadow .14s ease, background .14s ease}
    .nav-item:hover{transform:translateX(2px)}
    .nav-item.active{
      background:linear-gradient(90deg, rgba(59,130,246,.25), rgba(37,99,235,.35));
      box-shadow:inset 0 0 0 1px rgba(255,255,255,.08), 0 0 0 2px rgba(59,130,246,.2), 0 8px 28px rgba(30,64,175,.35)
    }
    .nav-item.active::before{
      content:"";position:absolute;left:-4px;top:10%;bottom:10%;width:6px;border-radius:9999px;
      background:radial-gradient(10px 60% at 50% 50%, rgba(191,219,254,.95), rgba(59,130,246,.4) 60%, transparent 70%);
      filter:blur(.2px)
    }
    .badge{font-size:.65rem;padding:.15rem .35rem}
  </style>
</head>

<body class="bg-gray-50 font-sans" x-cloak>
<div class="flex h-screen overflow-hidden" x-init="$store.app.init()">