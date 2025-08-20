<!DOCTYPE html>
<html lang="id" x-data="seoDashboard()" :class="{'overflow-hidden': modalOpen}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SEO Dashboard | Vendor Partnership SEO Performance</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    [x-cloak]{display:none!important}
    .sidebar{transition:all .3s ease}
    .nav-item{position:relative;transition:transform .14s ease, box-shadow .14s ease, background .14s ease}
    .nav-item:hover{transform:translateX(2px)}
    .nav-item.active{background:linear-gradient(90deg,rgba(59,130,246,.25),rgba(37,99,235,.35));box-shadow:inset 0 0 0 1px rgba(255,255,255,.08),0 0 0 2px rgba(59,130,246,.2),0 8px 28px rgba(30,64,175,.35)}
    .nav-item.active::before{content:"";position:absolute;left:-4px;top:10%;bottom:10%;width:6px;border-radius:9999px;background:radial-gradient(10px 60% at 50% 50%,rgba(191,219,254,.95),rgba(59,130,246,.4) 60%,transparent 70%)}
    .badge{font-size:.65rem;padding:.15rem .35rem}
  </style>
</head>
<body class="bg-gray-50 font-sans" x-cloak>

<!-- Hidden POST logout form (CSRF) -->
<form id="logoutForm" action="<?= site_url('logout') ?>" method="post" class="hidden">
  <?= csrf_field() ?>
</form>

<div class="flex h-screen overflow-hidden">
  <!-- Sidebar -->
  <aside class="sidebar text-white w-64 fixed h-full p-4 flex flex-col bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900"
         :class="{'-ml-64': !sidebarOpen}" x-show="sidebarOpen" @click.away="sidebarOpen=false">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl font-bold flex items-center"><i class="fas fa-chart-line mr-2"></i> Imersa SEO</h1>
      <button @click="sidebarOpen=false" class="md:hidden"><i class="fas fa-times"></i></button>
    </div>

    <div class="border-b border-white/20 my-3"></div>

    <nav class="flex-1">
      <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Main</p>
      <a href="#" @click.prevent="setActive('Dashboard')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='Dashboard'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-gauge-high mr-3"></i> Dashboard
        <span class="badge bg-blue-500 rounded-full ml-auto" x-text="stats.newLeads"></span>
      </a>
      <a href="#" @click.prevent="setActive('VendorApprovals')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='VendorApprovals'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-user-check mr-3"></i> Approval Vendor
        <span class="badge bg-yellow-500 rounded-full ml-auto" x-text="pendingVendors.length"></span>
      </a>
      <a href="#" @click.prevent="setActive('LeadDistribution')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='LeadDistribution'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-share-alt mr-3"></i> Distribusi Leads
      </a>
      <a href="#" @click.prevent="setActive('SEOReports')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='SEOReports'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-chart-line mr-3"></i> Laporan SEO
      </a>
      <a href="#" @click.prevent="setActive('KeywordAnalysis')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='KeywordAnalysis'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-key mr-3"></i> Analisis Keyword
      </a>

      <p class="text-blue-200 uppercase text-xs font-semibold mt-6 mb-2">Kolaborasi</p>
      <a href="#" @click.prevent="setActive('VendorChat')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='VendorChat'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-comments mr-3"></i> Pesan Vendor
        <span class="badge bg-blue-500 rounded-full ml-auto" x-text="stats.unreadMessages"></span>
      </a>

      <p class="text-blue-200 uppercase text-xs font-semibold mt-6 mb-2">Komisi</p>
      <a href="#" @click.prevent="setActive('Commission')" class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
         :class="activeMenu==='Commission'?'active':'hover:bg-blue-700/70'">
        <i class="fas fa-money-bill-wave mr-3"></i> Terima Komisi
        <span class="badge bg-yellow-500 rounded-full ml-auto" x-text="stats.pendingCommissions"></span>
      </a>
    </nav>

    <div class="pt-4 border-t border-blue-700/60">
      <!-- Logout: pakai modal seperti vendor -->
      <a href="#" @click.prevent="openLogoutModal" class="block py-2 px-3 rounded-lg flex items-center nav-item hover:bg-blue-700/70">
        <i class="fas fa-sign-out-alt mr-3"></i> Logout
      </a>
    </div>
  </aside>

  <!-- Main -->
  <div class="flex-1 flex flex-col overflow-hidden" :class="{'md:ml-64': sidebarOpen}">
    <!-- Topbar -->
    <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen?'md:ml-64':''">
      <div class="flex items-center justify-between p-4">
        <button @click="sidebarOpen=!sidebarOpen" class="hover:opacity-80"><i class="fas fa-bars text-gray-700"></i></button>
        <div class="relative w-full max-w-md mx-4">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" x-model="searchKeyword" @keyup.enter="searchKeywordHandler"
                 class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                 placeholder="Cari keyword, vendor, atau leads...">
        </div>
        <div class="flex items-center space-x-4">
          <button @click="openModal('notifications')" class="relative text-gray-600 hover:text-gray-900">
            <i class="fas fa-bell text-xl"></i>
            <span x-show="stats.unreadNotifications>0"
                  class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
                  x-text="stats.unreadNotifications"></span>
          </button>
          <div class="relative ml-3">
            <button @click="profileDropdownOpen=!profileDropdownOpen" class="flex text-sm rounded-full focus:outline-none">
              <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=256&h=256&fit=facearea&facepad=2" alt="">
            </button>
            <div x-show="profileDropdownOpen" @click.away="profileDropdownOpen=false"
                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black/5">
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
              <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Pengaturan</a>
              <!-- Logout via modal -->
              <a href="#" @click.prevent="openLogoutModal" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="h-16"></div>

    <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
      <!-- ==== HALAMAN2 (TIDAK ADA lagi template 'Logout' halaman penuh) ==== -->

      <!-- DASHBOARD -->
      <template x-if="activeMenu==='Dashboard'">
        <div>
          <!-- Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4"><i class="fas fa-bolt text-lg"></i></div>
                <div><p class="text-sm text-gray-500">Leads Baru</p><p class="text-2xl font-semibold" x-text="stats.newLeads"></p></div>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4"><i class="fas fa-check-circle text-lg"></i></div>
                <div><p class="text-sm text-gray-500">Leads Terdistribusi</p><p class="text-2xl font-semibold" x-text="stats.distributedLeads"></p></div>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4"><i class="fas fa-chart-line text-lg"></i></div>
                <div><p class="text-sm text-gray-500">Keyword Ranking Naik</p><p class="text-2xl font-semibold" x-text="stats.rankingUp"></p></div>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4"><i class="fas fa-money-bill-wave text-lg"></i></div>
                <div><p class="text-sm text-gray-500">Komisi Belum Diterima</p><p class="text-2xl font-semibold" x-text="stats.pendingCommissions"></p></div>
              </div>
            </div>
          </div>

          <!-- (konten lain dipertahankan persis punyamu) -->
          <!-- ... SEMUA BAGIAN LAIN TETAP ... -->
        </div>
      </template>

      <!-- VendorApprovals / LeadDistribution / SEOReports / KeywordAnalysis / VendorChat / Commission -->
      <!-- ... SELURUH BAGIAN YANG SUDAH ADA TETAP TANPA PERUBAHAN ... -->

    </main>
  </div>

  <!-- ===== MODALS LAIN (assignLead, addReport, dst) TETAP ===== -->
  <!-- ... MODALS lain persis seperti kode kamu ... -->

  <!-- ===== MODAL LOGOUT (baru, sama dengan vendor) ===== -->
  <div
    x-show="modalOpen==='logout'"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    aria-modal="true" role="dialog"
  >
    <div class="absolute inset-0 bg-black/50"></div>

    <div
      x-transition
      class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6"
    >
      <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
        <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
      </div>
      <h3 class="text-xl font-bold text-gray-800 text-center">Keluar dari Sistem?</h3>
      <p class="text-gray-600 text-center mt-1">Anda akan keluar dari sesi saat ini.</p>

      <div class="mt-6 flex justify-center gap-3">
        <button @click="modalOpen=null" class="px-4 py-2 rounded-md border bg-white hover:bg-gray-50">Batal</button>
        <button @click="performLogout" class="px-4 py-2 rounded-md text-white bg-red-600 hover:bg-red-700">Ya, Keluar</button>
      </div>
    </div>
  </div>

</div>

<script>
function seoDashboard(){
  return {
    // UI
    sidebarOpen: window.innerWidth>768,
    profileDropdownOpen:false,
    modalOpen:null,
    activeMenu:'Dashboard',
    activeChat:null,
    searchKeyword:'',
    // filters
    leadFilter:'all', reportFilter:'all', keywordFilter:'all', commissionFilter:'all',
    // stats
    stats:{ newLeads:24, distributedLeads:156, rankingUp:38, pendingCommissions:12,
            unreadMessages:5, unreadNotifications:3, totalKeywords:128,
            keywordsPage1:42, keywordsPage2:35, keywordsPage3:51, keywordsUp:28, keywordsDown:15 },
    // data (dummy → ganti via controller CI4)
    pendingVendors:[
      {id:11, name:'CV Sinar Abadi', email:'sinar@vendor.id', area:'Jawa Timur'},
      {id:12, name:'PT Maju Jaya', email:'admin@majujaya.co', area:'DIY'}
    ],
    newLeads:[
      {id:1, project:'Label Baju Sidoarjo', customer:'Ridzy', keyword:'label baju murah', date:'2025-08-15'},
      {id:2, project:'Villa Kaliurang', customer:'Nasma', keyword:'villa di kaliurang', date:'2025-08-15'}
    ],
    recentMessages:[
      {id:1, vendorId:1, vendor:'Labelin Aksara', text:'Baik, akan segera saya follow up leadnya', time:'10:30'},
      {id:2, vendorId:2, vendor:'Yasin Barokah', text:'Ukuran A5 bisa ya?', time:'09:45'}
    ],
    needOptimization:[
      {id:1, keyword:'baju kaos distro', position:'15', project:'Label Baju Stratlaya', trend:'down'},
      {id:2, keyword:'villa murah di jogja', position:'8', project:'Villa Kaliurang', trend:'up'}
    ],
    pendingCommissions:[
      {id:1, vendor:'Labelin Aksara', project:'Label Baju Stratlaya', leads:8, amount:1200000},
      {id:2, vendor:'Yasin Barokah', project:'Cetak Yasin Bangkalan', leads:5, amount:750000}
    ],
    leads:[
      {id:1001, customer:'Ridzy', project:'Label Baju Sidoarjo', vendor:'Labelin Aksara', status:'converted', date:'2025-08-15'},
      {id:1002, customer:'Nasma', project:'Villa Kaliurang', vendor:'Villa Asri', status:'assigned', date:'2025-08-14'},
      {id:1003, customer:'Sisman', project:'Kursus Bahasa Inggris', vendor:'Kursus Pro', status:'new', date:'2025-08-14'}
    ],
    seoReports:[
      {id:1, date:'2025-08-15', project:'Label Baju Stratlaya', keyword:'label baju murah', position:'5', trend:'up', change:'2'},
      {id:2, date:'2025-08-14', project:'Cetak Yasin Bangkalan', keyword:'cetak yasin hardcover', position:'12', trend:'down', change:'3'}
    ],
    keywords:[
      {id:1, text:'label baju murah', project:'Label Baju Stratlaya', position:'5', trend:'up', change:'2', volume:'1200'},
      {id:2, text:'cetak yasin hardcover', project:'Cetak Yasin Bangkalan', position:'12', trend:'down', change:'3', volume:'850'}
    ],
    vendorChats:[
      {id:1, vendor:'Labelin Aksara', lastMessage:'Baik, segera follow up', time:'10:30', status:'Online',
       messages:[{id:1,sender:'vendor',text:'Ada lead baru?',time:'10:00'},{id:2,sender:'me',text:'Ada 1 lead label baju',time:'10:15'}]},
      {id:2, vendor:'Yasin Barokah', lastMessage:'A5 bisa?', time:'09:45', status:'Offline',
       messages:[{id:1,sender:'vendor',text:'Cetak yasin A5 bisa?',time:'09:45'}]}
    ],
    commissions:[
      {id:1, vendor:'Labelin Aksara', period:'2025-08-01 - 2025-08-15', leads:8, amount:1200000, status:'pending'},
      {id:2, vendor:'Yasin Barokah', period:'2025-08-01 - 2025-08-15', leads:5, amount:750000, status:'pending'},
      {id:3, vendor:'Kursus Pro', period:'2025-07-01 - 2025-07-31', leads:12, amount:1800000, status:'received'}
    ],
    vendors:[{id:1,name:'Labelin Aksara'},{id:2,name:'Yasin Barokah'},{id:3,name:'Kursus Pro'}],

    // computed
    get filteredLeads(){ if(this.leadFilter==='all') return this.leads; return this.leads.filter(l=>l.status===this.leadFilter); },
    get filteredReports(){ if(this.reportFilter==='all') return this.seoReports; return this.seoReports.filter(r=>r.project.toLowerCase().includes(this.reportFilter)); },
    get filteredKeywords(){
      if(this.keywordFilter==='all') return this.keywords;
      if(this.keywordFilter==='page1') return this.keywords.filter(k=>k.position<=10);
      if(this.keywordFilter==='page2') return this.keywords.filter(k=>k.position>10&&k.position<=20);
      if(this.keywordFilter==='page3') return this.keywords.filter(k=>k.position>20);
      if(this.keywordFilter==='up') return this.keywords.filter(k=>k.trend==='up');
      if(this.keywordFilter==='down') return this.keywords.filter(k=>k.trend==='down');
      return this.keywords;
    },
    get filteredCommissions(){ if(this.commissionFilter==='all') return this.commissions; return this.commissions.filter(c=>c.status===this.commissionFilter); },

    // methods
    setActive(n){ this.activeMenu=n; },
    openModal(name,id=null){ this.modalOpen=name; if(id && name==='assignLead'){ this.selectedLead=this.leads.find(l=>l.id===id)||{}; } },
    searchKeywordHandler(){ console.log('search:', this.searchKeyword); },
    approveVendor(id){ this.pendingVendors = this.pendingVendors.filter(v=>v.id!==id); },
    rejectVendor(id){  this.pendingVendors = this.pendingVendors.filter(v=>v.id!==id); },
    assignLead(id){ this.openModal('assignLead', id); },
    confirmAssign(){ this.modalOpen=null; },
    viewLeadDetail(){},
    receiveCommission(id){
      const i=this.commissions.findIndex(c=>c.id===id);
      if(i!==-1){ this.commissions[i].status='received'; }
    },
    selectChat(id){ this.activeChat=id; },
    getActiveChat(){ return this.vendorChats.find(c=>c.id===this.activeChat)||{messages:[]}; },
    deleteReport(id){ this.seoReports=this.seoReports.filter(r=>r.id!==id); },

    // ==== LOGOUT (sesuai vendor) ====
    openLogoutModal(){ this.modalOpen='logout'; },
    performLogout(){ document.getElementById('logoutForm')?.submit(); },

    selectedLead:{},

    init(){
      const pref=localStorage.getItem('sidebarOpen');
      this.sidebarOpen = pref!==null ? pref==='true' : window.innerWidth>768;
      window.addEventListener('resize',()=>{ if(window.innerWidth<=768){ this.sidebarOpen=false; } });
      this.$watch('sidebarOpen',v=>localStorage.setItem('sidebarOpen',v));
    }
  }
}
</script>
</body>
</html>
