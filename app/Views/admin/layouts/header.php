<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#3b82f6">
  <title><?= esc($title ?? 'Admin') ?> | Imersa</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <!-- Helper x-cloak -->
  <style>
    [x-cloak] { display: none !important; }
    
    /* Touch-friendly tap targets */
    @media (max-width: 768px) {
      .nav-item, button, a.btn {
        min-height: 44px;
        display: flex;
        align-items: center;
      }
    }
    
    /* Hilangkan scrollbar pada desktop tetapi tetap bisa discroll */
    .no-scrollbar {
      -ms-overflow-style: none;  /* IE and Edge */
      scrollbar-width: none;     /* Firefox */
    }
    
    .no-scrollbar::-webkit-scrollbar {
      display: none; /* Chrome, Safari and Opera */
    }
    
    /* Pastikan overlay sidebar tidak menutupi tombol menu */
    .sidebar-overlay {
      z-index: 29; /* Kurang dari z-index tombol menu (40) */
    }
    
    /* Sidebar styling */
    .sidebar {
      z-index: 30;
    }
  </style>

  <!-- Alpine v3 -->
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('adminApp', () => ({
        sidebarOpen: false,
        showLogoutModal: false,
        profileDropdownOpen: false,
        searchOpen: false,

        init(){
          // Set initial state based on screen size and saved preference
          const savedState = localStorage.getItem('sidebarOpen');
          const isMobile = window.innerWidth < 768;
          
          if (savedState !== null) {
            this.sidebarOpen = isMobile ? false : (savedState === 'true');
          } else {
            this.sidebarOpen = !isMobile;
          }

          // Save state to localStorage when changed
          this.$watch('sidebarOpen', value => {
            if (window.innerWidth >= 768) {
              localStorage.setItem('sidebarOpen', value);
            }
          });

          // Responsive behavior
          window.addEventListener('resize', () => {
            const nowMobile = window.innerWidth < 768;
            if (nowMobile) {
              this.sidebarOpen = false;
              this.searchOpen = false;
            } else {
              // Restore sidebar state on desktop
              const saved = localStorage.getItem('sidebarOpen');
              if (saved !== null) {
                this.sidebarOpen = (saved === 'true');
              }
            }
          });

          // Close dropdowns when clicking outside
          document.addEventListener('click', (e) => {
            if (this.profileDropdownOpen && !e.target.closest('.profile-dropdown')) {
              this.profileDropdownOpen = false;
            }
          });
        },

        toggleSearch() {
          this.searchOpen = !this.searchOpen;
          if (this.searchOpen) {
            this.$nextTick(() => {
              this.$refs.searchInput.focus();
            });
          }
        },
        
        // Function to handle sidebar toggle
        toggleSidebar() {
          this.sidebarOpen = !this.sidebarOpen;
        }
      }));
    });
  </script>
</head>

<body x-data="adminApp()" x-init="init()" class="min-h-screen bg-gray-50 flex flex-col">
  <div id="appShell" class="flex flex-1">