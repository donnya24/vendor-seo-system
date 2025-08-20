<!DOCTYPE html>
<html lang="id" x-data="adminDashboard()" :class="{'overflow-hidden': modalOpen}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Vendor Partnership SEO Performance</title>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    [x-cloak] { display: none !important; }
    .sidebar { transition: all 0.3s ease; }

    /* ====== Sidebar Nav Lighting (Active/Click) ====== */
    .nav-item {
      position: relative;
      transition: transform 140ms ease, box-shadow 140ms ease, background 140ms ease;
    }
    .nav-item:hover { transform: translateX(2px); }
    .nav-item.active {
      background: linear-gradient(90deg, rgba(59,130,246,0.25), rgba(37,99,235,0.35));
      box-shadow:
        inset 0 0 0 1px rgba(255,255,255,0.08),
        0 0 0 2px rgba(59,130,246,0.20),
        0 8px 28px rgba(30,64,175,0.35);
    }
    .nav-item.active::before {
      content: "";
      position: absolute;
      left: -4px;
      top: 10%;
      bottom: 10%;
      width: 6px;
      border-radius: 9999px;
      background: radial-gradient(10px 60% at 50% 50%, rgba(191,219,254,0.95), rgba(59,130,246,0.4) 60%, transparent 70%);
      filter: blur(0.2px);
    }
    
    /* Tambahan style untuk hamburger button */
    .hamburger-btn {
      transition: all 0.2s ease;
    }
    .hamburger-btn:hover {
      transform: scale(1.1);
      opacity: 0.8;
    }
  </style>
</head>
<body class="bg-gray-50 font-sans" x-cloak>
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <div
      class="sidebar text-white w-64 fixed h-full p-4 flex flex-col
             bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900"
      :class="{ '-ml-64': !sidebarOpen }"
      x-show="sidebarOpen"
      @click.away="sidebarOpen = false"
    >
      <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold flex items-center">
          <i class="fas fa-chart-line mr-2"></i> Imersa
        </h1>
        <button @click="sidebarOpen = false" class="md:hidden">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="border-b border-white/20 my-3"></div>

      <nav class="flex-1">
        <div class="mb-6">
          <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Main Menu</p>

          <a href="#"
             @click.prevent="setActive('Dashboard')"
             class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
             :class="activeMenu === 'Dashboard' ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
          </a>

          <a href="#"
             @click.prevent="setActive('Manage Users')"
             class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
             :class="activeMenu === 'Manage Users' ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i class="fas fa-users mr-3"></i> Manage Users
          </a>

          <a href="#"
             @click.prevent="setActive('Projects')"
             class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
             :class="activeMenu === 'Projects' ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i class="fas fa-project-diagram mr-3"></i> Projects
          </a>

          <a href="#"
             @click.prevent="setActive('Analytics')"
             class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
             :class="activeMenu === 'Analytics' ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i class="fas fa-chart-pie mr-3"></i> Analytics
          </a>
        </div>

        <div class="mb-6">
          <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Configuration</p>

          <a href="#"
             @click.prevent="setActive('Commission Rates')"
             class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
             :class="activeMenu === 'Commission Rates' ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i class="fas fa-percentage mr-3"></i> Commission Rates
          </a>

          <a href="#"
             @click.prevent="setActive('Announcements')"
             class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
             :class="activeMenu === 'Announcements' ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i class="fas fa-bullhorn mr-3"></i> Announcements
          </a>

          <a href="#"
             @click.prevent="setActive('Audit Logs')"
             class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item"
             :class="activeMenu === 'Audit Logs' ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40'">
            <i class="fas fa-history mr-3"></i> Audit Logs
          </a>
        </div>
      </nav>

      <div class="pt-4 border-t border-blue-700/60">
        <a href="#"
        @click.prevent="openLogoutModal()"
        class="block py-2 px-3 rounded-lg flex items-center nav-item hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40">
        <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
      <!-- Top Navigation (solid, tanpa efek kaca) -->
      <header class="bg-white shadow z-20 fixed top-0 left-0 right-0" :class="sidebarOpen ? 'md:ml-64' : ''">
        <div class="flex items-center justify-between p-4">
          <!-- Tombol hamburger yang selalu terlihat -->
          <button @click="sidebarOpen = !sidebarOpen" class="hamburger-btn">
            <i class="fas fa-bars text-gray-700"></i>
          </button>

          <div class="relative w-full max-w-md mx-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
              type="text"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500
                     focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              placeholder="Search vendors, projects, leads...">
          </div>

          <div class="flex items-center">
            <div class="relative ml-3">
              <button @click="profileDropdownOpen = !profileDropdownOpen" class="flex text-sm rounded-full focus:outline-none">
                <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Admin profile">
              </button>
              <div x-show="profileDropdownOpen" @click.away="profileDropdownOpen = false"
                   class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black/5">
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Spacer untuk fixed header -->
      <div class="h-16"></div>

      <!-- Main Content Area -->
      <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
        <!-- Dynamic Content Based on Active Menu -->
        <div x-show="activeMenu === 'Dashboard'">
          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                  <i class="fas fa-users text-lg"></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Total Vendors</p>
                  <p class="text-2xl font-semibold" x-text="stats.totalVendors">312</p>
                </div>
              </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                  <i class="fas fa-bolt text-lg"></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Today's Leads</p>
                  <p class="text-2xl font-semibold" x-text="stats.todayLeads">96</p>
                </div>
              </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                  <i class="fas fa-handshake text-lg"></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Monthly Deals</p>
                  <p class="text-2xl font-semibold" x-text="stats.monthlyDeals">54</p>
                </div>
              </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                  <i class="fas fa-key text-lg"></i>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Top Keywords</p>
                  <p class="text-2xl font-semibold" x-text="stats.topKeywords">428</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity Section -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Vendors -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                  <i class="fas fa-user-plus mr-2 text-blue-600"></i> Recent Vendors
                </h3>
              </div>
              <div class="divide-y divide-gray-200">
                <template x-for="vendor in recentVendors" :key="vendor.id">
                  <div class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                          <i class="fas fa-store text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                          <p class="text-sm font-medium text-gray-900" x-text="vendor.name"></p>
                          <p class="text-sm text-gray-500" x-text="vendor.location"></p>
                        </div>
                      </div>
                      <div class="ml-2 flex-shrink-0 flex">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                              :class="vendor.status === 'Verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                              x-text="vendor.status"></span>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
              <div class="px-4 py-4 sm:px-6 border-t border-gray-200 bg-gray-50">
                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">View all vendors</a>
              </div>
            </div>

            <!-- Recent Projects -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                  <i class="fas fa-project-diagram mr-2 text-purple-600"></i> Active Projects
                </h3>
              </div>
              <div class="divide-y divide-gray-200">
                <template x-for="project in activeProjects" :key="project.id">
                  <div class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="project.name"></p>
                        <p class="text-sm text-gray-500 truncate" x-text="'Location: ' + project.location"></p>
                      </div>
                      <div class="ml-4 flex-shrink-0">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                              x-text="project.vendors + ' vendors'"></span>
                      </div>
                    </div>
                    <div class="mt-2">
                      <div class="flex justify-between text-sm text-gray-500">
                        <span x-text="'Created: ' + project.created"></span>
                        <span class="flex items-center" :class="project.status === 'Active' ? 'text-green-600' : 'text-red-600'">
                          <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                          <span x-text="project.status"></span>
                        </span>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
              <div class="px-4 py-4 sm:px-6 border-t border-gray-200 bg-gray-50">
                <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">Create new project</a>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
              <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                  <i class="fas fa-bolt mr-2 text-yellow-600"></i> Quick Actions
                </h3>
              </div>
              <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4">
                  <button @click="openModal('addUser')" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-user-plus mr-2"></i> Add New User
                  </button>
                  <button @click="openModal('createProject')" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                    <i class="fas fa-project-diagram mr-2"></i> Create Project
                  </button>
                  <button @click="openModal('announcement')" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <i class="fas fa-bullhorn mr-2"></i> Post Announcement
                  </button>
                  <button @click="openModal('commission')" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fas fa-percentage mr-2"></i> Set Commission Rate
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Leads Table -->
          <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
              <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                <i class="fas fa-list mr-2 text-blue-600"></i> Recent Leads
              </h3>
              <div class="flex items-center space-x-2">
                <div class="relative">
                  <select class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-1 px-3 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-gray-500 text-sm">
                    <option>All Status</option>
                    <option>New</option>
                    <option>Assigned</option>
                    <option>Converted</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <i class="fas fa-chevron-down text-xs"></i>
                  </div>
                </div>
                <button class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm flex items-center">
                  <i class="fas fa-download mr-1"></i> Export
                </button>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <template x-for="lead in recentLeads" :key="lead.id">
                    <tr>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="'#' + lead.id"></td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="lead.customer"></td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="lead.project"></td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="lead.vendor"></td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                              :class="{
                                'bg-green-100 text-green-800': lead.status === 'Converted',
                                'bg-blue-100 text-blue-800': lead.status === 'Assigned',
                                'bg-yellow-100 text-yellow-800': lead.status === 'New'
                              }"
                              x-text="lead.status"></span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="lead.date"></td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i></button>
                        <button class="text-green-600 hover:text-green-900"><i class="fas fa-edit"></i></button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
            <div class="px-4 py-4 sm:px-6 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
              <div class="text-sm text-gray-500">
                Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">24</span> results
              </div>
              <div class="flex space-x-2">
                <button class="inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                  <i class="fas fa-chevron-left"></i>
                </button>
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</button>
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">2</button>
                <button class="inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Manage Users Content -->
        <div x-show="activeMenu === 'Manage Users'" class="bg-white shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-users mr-2 text-blue-600"></i> User Management
            </h2>
            <button @click="openModal('addUser')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center">
              <i class="fas fa-user-plus mr-2"></i> Add User
            </button>
          </div>

          <div class="mb-4 flex justify-between items-center">
            <div class="relative w-64">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search users...">
            </div>
            <div class="flex space-x-2">
              <select class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-gray-500 text-sm">
                <option>All Roles</option>
                <option>Admin</option>
                <option>SEO Team</option>
                <option>Vendor</option>
              </select>
              <select class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-gray-500 text-sm">
                <option>All Status</option>
                <option>Active</option>
                <option>Pending</option>
                <option>Suspended</option>
              </select>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/1.jpg" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Jane Cooper</div>
                        <div class="text-sm text-gray-500">jane.cooper@example.com</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">jane.cooper@example.com</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Admin</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 hours ago</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/1.jpg" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">John Smith</div>
                        <div class="text-sm text-gray-500">john.smith@example.com</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">john.smith@example.com</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">SEO Team</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1 day ago</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/2.jpg" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Robert Johnson</div>
                        <div class="text-sm text-gray-500">robert.johnson@example.com</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">robert.johnson@example.com</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Vendor</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3 days ago</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-check-circle"></i> Verify</button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Projects Content -->
        <div x-show="activeMenu === 'Projects'" class="bg-white shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-project-diagram mr-2 text-purple-600"></i> Project Management
            </h2>
            <button @click="openModal('createProject')" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 flex items-center">
              <i class="fas fa-plus mr-2"></i> New Project
            </button>
          </div>

          <div class="mb-4 flex justify-between items-center">
            <div class="relative w-64">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm" placeholder="Search projects...">
            </div>
            <div class="flex space-x-2">
              <select class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-gray-500 text-sm">
                <option>All Status</option>
                <option>Active</option>
                <option>Completed</option>
                <option>Pending</option>
              </select>
              <select class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-gray-500 text-sm">
                <option>All Services</option>
                <option>SEO Website</option>
                <option>Marketplace Optimization</option>
                <option>Social Media</option>
              </select>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendors</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">E-commerce SEO Optimization</div>
                    <div class="text-sm text-gray-500">Tokopedia Seller</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/2.jpg" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Sarah Johnson</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Marketplace Optimization</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex -space-x-2">
                      <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white" src="https://randomuser.me/api/portraits/men/3.jpg" alt="">
                      <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white" src="https://randomuser.me/api/portraits/women/3.jpg" alt="">
                      <span class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-gray-200 text-xs">+2</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-09-15</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i></button>
                    <button class="text-purple-600 hover:text-purple-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">Website SEO Audit</div>
                    <div class="text-sm text-gray-500">Travel Agency</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/4.jpg" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Michael Brown</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">SEO Website</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex -space-x-2">
                      <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white" src="https://randomuser.me/api/portraits/men/5.jpg" alt="">
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">In Progress</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-08-30</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i></button>
                    <button class="text-purple-600 hover:text-purple-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">Instagram Growth</div>
                    <div class="text-sm text-gray-500">Fashion Brand</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/4.jpg" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Emily Davis</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Social Media</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex -space-x-2">
                      <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white" src="https://randomuser.me/api/portraits/women/5.jpg" alt="">
                      <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white" src="https://randomuser.me/api/portraits/men/6.jpg" alt="">
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-09-10</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i></button>
                    <button class="text-purple-600 hover:text-purple-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Analytics Content -->
        <div x-show="activeMenu === 'Analytics'" class="bg-white shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-chart-pie mr-2 text-green-600"></i> System Analytics
            </h2>
            <div class="flex items-center space-x-2">
              <select class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-gray-500 text-sm">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>Last 90 Days</option>
                <option>This Year</option>
              </select>
              <button class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center">
                <i class="fas fa-download mr-2"></i> Export
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-4 rounded-lg border border-gray-200">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Vendor Growth</h3>
              <div class="h-64 bg-gray-50 rounded-md flex items-center justify-center">
                <p class="text-gray-500">Vendor growth chart will be displayed here</p>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Lead Conversion</h3>
              <div class="h-64 bg-gray-50 rounded-md flex items-center justify-center">
                <p class="text-gray-500">Lead conversion chart will be displayed here</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-4 rounded-lg border border-gray-200">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Project Status</h3>
              <div class="h-64 bg-gray-50 rounded-md flex items-center justify-center">
                <p class="text-gray-500">Project status pie chart will be displayed here</p>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Commission Earnings</h3>
              <div class="h-64 bg-gray-50 rounded-md flex items-center justify-center">
                <p class="text-gray-500">Commission earnings chart will be displayed here</p>
              </div>
            </div>
            <div class="bg-white p-4 rounded-lg border border-gray-200">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performing Services</h3>
              <div class="h-64 bg-gray-50 rounded-md flex items-center justify-center">
                <p class="text-gray-500">Top services bar chart will be displayed here</p>
              </div>
            </div>
          </div>

          <div class="bg-white p-4 rounded-lg border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Summary</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metric</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last 7 Days</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last 30 Days</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">New Vendors</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">24</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">98</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+12%</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">New Leads</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">56</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">210</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+8%</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Projects Started</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">18</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">72</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+15%</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Projects Completed</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">12</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">45</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">-5%</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Commission Earned</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp 12.4jt</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp 48.7jt</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+22%</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Commission Rates Content -->
        <div x-show="activeMenu === 'Commission Rates'" class="bg-white shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-percentage mr-2 text-indigo-600"></i> Commission Rates
            </h2>
            <button @click="openModal('commission')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 flex items-center">
              <i class="fas fa-plus mr-2"></i> Add Rate
            </button>
          </div>

          <div class="mb-4">
            <div class="relative w-64">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search services...">
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service Type</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor Rate</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company Rate</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Rate</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">SEO Website</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">60%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">40%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">100%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-07-15</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Marketplace Optimization</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">70%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">30%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">100%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-08-01</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Social Media Management</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">50%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">50%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">100%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-06-20</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Content Writing</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">65%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">35%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">100%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-07-30</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i></button>
                    <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Announcements Content -->
        <div x-show="activeMenu === 'Announcements'" class="bg-white shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-bullhorn mr-2 text-green-600"></i> Announcements
            </h2>
            <button @click="openModal('announcement')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center">
              <i class="fas fa-plus mr-2"></i> New Announcement
            </button>
          </div>

          <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
              <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">New Commission Structure Update</h3>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Active</span>
              </div>
              <div class="p-4">
                <p class="text-gray-700 mb-2">We've updated our commission structure effective August 1, 2025. Please review the new rates in the Commission Rates section.</p>
                <div class="flex justify-between items-center text-sm text-gray-500">
                  <span>Posted: 2025-07-25</span>
                  <span>Expires: 2025-08-31</span>
                </div>
              </div>
              <div class="px-4 py-3 bg-gray-50 text-right">
                <button class="text-green-600 hover:text-green-900 mr-3"><i class="fas fa-edit"></i> Edit</button>
                <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i> Delete</button>
              </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
              <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Vendor Verification Process Update</h3>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Published</span>
              </div>
              <div class="p-4">
                <p class="text-gray-700 mb-2">Starting next week, we'll implement a new vendor verification process to improve quality control. All new vendors will need to complete additional documentation.</p>
                <div class="flex justify-between items-center text-sm text-gray-500">
                  <span>Posted: 2025-07-15</span>
                  <span>Expires: 2025-08-15</span>
                </div>
              </div>
              <div class="px-4 py-3 bg-gray-50 text-right">
                <button class="text-green-600 hover:text-green-900 mr-3"><i class="fas fa-edit"></i> Edit</button>
                <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i> Delete</button>
              </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
              <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">System Maintenance Notification</h3>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Archived</span>
              </div>
              <div class="p-4">
                <p class="text-gray-700 mb-2">There will be scheduled system maintenance on July 30, 2025 from 1:00 AM to 3:00 AM WIB. The system will be unavailable during this time.</p>
                <div class="flex justify-between items-center text-sm text-gray-500">
                  <span>Posted: 2025-07-10</span>
                  <span>Expired: 2025-07-30</span>
                </div>
              </div>
              <div class="px-4 py-3 bg-gray-50 text-right">
                <button class="text-green-600 hover:text-green-900 mr-3"><i class="fas fa-edit"></i> Edit</button>
                <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash-alt"></i> Delete</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Audit Logs Content -->
        <div x-show="activeMenu === 'Audit Logs'" class="bg-white shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-history mr-2 text-purple-600"></i> Audit Logs
            </h2>
            <div class="flex items-center space-x-2">
              <select class="appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-gray-500 text-sm">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>Last 90 Days</option>
                <option>All Time</option>
              </select>
              <button class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 flex items-center">
                <i class="fas fa-download mr-2"></i> Export
              </button>
            </div>
          </div>

          <div class="mb-4">
            <div class="relative w-64">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm" placeholder="Search logs...">
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entity</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-08-16 10:23:45</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Admin User</div>
                        <div class="text-sm text-gray-500">admin@imersa.com</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Created</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Project</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Created new project "E-commerce SEO Optimization"</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">192.168.1.1</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-08-16 09:45:12</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/women/1.jpg" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Jane Cooper</div>
                        <div class="text-sm text-gray-500">jane.cooper@example.com</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Updated</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">User</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Updated user profile information</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">192.168.1.2</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-08-15 16:30:22</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Admin User</div>
                        <div class="text-sm text-gray-500">admin@imersa.com</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Verified</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Vendor</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Verified vendor "Labelin Aksara"</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">192.168.1.1</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-08-15 14:12:05</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://randomuser.me/api/portraits/men/1.jpg" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">John Smith</div>
                        <div class="text-sm text-gray-500">john.smith@example.com</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Assigned</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Project</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Assigned vendor to project "Website SEO Audit"</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">192.168.1.3</td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2025-08-15 11:05:33</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">Admin User</div>
                        <div class="text-sm text-gray-500">admin@imersa.com</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Deleted</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Announcement</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Deleted announcement "Old Policy Update"</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">192.168.1.1</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Logout Confirmation Modal -->
         <form id="logoutForm" action="<?= site_url('logout'); ?>" method="post" class="hidden">
        <?= csrf_field() ?>
    </form>
        <div x-show="modalOpen === 'logout'" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen === 'logout'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="modalOpen === 'logout'" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 class="text-lg font-semibold mb-4">Konfirmasi Logout</h2>
            <p class="mb-6 text-gray-600">Apakah Anda yakin ingin keluar?</p>
            <div class="flex justify-end space-x-3">
            <button @click="modalOpen = null" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Batal</button>
            <button @click="performLogout" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Logout</button>
            </div>
        </div>
        </div>

        </div>
        </div>
      </main>
    </div>

    <!-- Modals -->
    <!-- Add User Modal -->
    <div x-show="modalOpen === 'addUser'" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modalOpen === 'addUser'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="modalOpen === 'addUser'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div>
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
              <i class="fas fa-user-plus text-blue-600"></i>
            </div>
            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Add New User</h3>
              <div class="mt-2">
                <form class="space-y-4">
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 text-left">Full Name</label>
                    <input type="text" id="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  </div>
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 text-left">Email</label>
                    <input type="email" id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                  </div>
                  <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 text-left">Role</label>
                    <select id="role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                      <option>Admin</option>
                      <option>SEO Team</option>
                      <option>Vendor</option>
                    </select>
                  </div>
                  <div x-show="selectedRole === 'Vendor'">
                    <label for="vendor-type" class="block text-sm font-medium text-gray-700 text-left">Vendor Type</label>
                    <select id="vendor-type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                      <option>SEO Specialist</option>
                      <option>Content Writer</option>
                      <option>Social Media Expert</option>
                    </select>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
              Add User
            </button>
            <button @click="modalOpen = null" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Project Modal -->
    <div x-show="modalOpen === 'createProject'" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modalOpen === 'createProject'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="modalOpen === 'createProject'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
          <div>
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
              <i class="fas fa-project-diagram text-purple-600"></i>
            </div>
            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create New Project</h3>
              <div class="mt-2">
                <form class="space-y-4">
                  <div>
                    <label for="project-name" class="block text-sm font-medium text-gray-700 text-left">Project Name</label>
                    <input type="text" id="project-name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label for="client" class="block text-sm font-medium text-gray-700 text-left">Client</label>
                      <select id="client" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option>Select Client</option>
                        <option>Sarah Johnson</option>
                        <option>Michael Brown</option>
                        <option>Emily Davis</option>
                      </select>
                    </div>
                    <div>
                      <label for="service-type" class="block text-sm font-medium text-gray-700 text-left">Service Type</label>
                      <select id="service-type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        <option>SEO Website</option>
                        <option>Marketplace Optimization</option>
                        <option>Social Media Management</option>
                        <option>Content Writing</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 text-left">Description</label>
                    <textarea id="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm"></textarea>
                  </div>
                  <div>
                    <label for="vendors" class="block text-sm font-medium text-gray-700 text-left">Assign Vendors</label>
                    <select id="vendors" multiple class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                      <option>SEO Specialist Team</option>
                      <option>Content Writing Pro</option>
                      <option>Social Media Experts</option>
                      <option>Marketplace Optimization</option>
                    </select>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label for="start-date" class="block text-sm font-medium text-gray-700 text-left">Start Date</label>
                      <input type="date" id="start-date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                    </div>
                    <div>
                      <label for="due-date" class="block text-sm font-medium text-gray-700 text-left">Due Date</label>
                      <input type="date" id="due-date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:col-start-2 sm:text-sm">
              Create Project
            </button>
            <button @click="modalOpen = null" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:col-start-1 sm:text-sm">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Announcement Modal -->
    <div x-show="modalOpen === 'announcement'" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modalOpen === 'announcement'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="modalOpen === 'announcement'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
          <div>
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
              <i class="fas fa-bullhorn text-green-600"></i>
            </div>
            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Create New Announcement</h3>
              <div class="mt-2">
                <form class="space-y-4">
                  <div>
                    <label for="announcement-title" class="block text-sm font-medium text-gray-700 text-left">Title</label>
                    <input type="text" id="announcement-title" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                  </div>
                  <div>
                    <label for="announcement-content" class="block text-sm font-medium text-gray-700 text-left">Content</label>
                    <textarea id="announcement-content" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label for="start-date" class="block text-sm font-medium text-gray-700 text-left">Publish Date</label>
                      <input type="date" id="start-date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    <div>
                      <label for="expiry-date" class="block text-sm font-medium text-gray-700 text-left">Expiry Date</label>
                      <input type="date" id="expiry-date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                  </div>
                  <div>
                    <label for="target-audience" class="block text-sm font-medium text-gray-700 text-left">Target Audience</label>
                    <select id="target-audience" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                      <option>All Users</option>
                      <option>Admins Only</option>
                      <option>SEO Team</option>
                      <option>Vendors Only</option>
                    </select>
                  </div>
                  <div>
                    <label class="inline-flex items-center">
                      <input type="checkbox" class="form-checkbox h-4 w-4 text-green-600">
                      <span class="ml-2 text-sm text-gray-700">Pin this announcement to the top</span>
                    </label>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:col-start-2 sm:text-sm">
              Publish Announcement
            </button>
            <button @click="modalOpen = null" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:col-start-1 sm:text-sm">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Commission Rate Modal -->
    <div x-show="modalOpen === 'commission'" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="modalOpen === 'commission'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="modalOpen === 'commission'" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div>
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
              <i class="fas fa-percentage text-indigo-600"></i>
            </div>
            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Set Commission Rate</h3>
              <div class="mt-2">
                <form class="space-y-4">
                  <div>
                    <label for="service-type" class="block text-sm font-medium text-gray-700 text-left">Service Type</label>
                    <select id="service-type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      <option>SEO Website</option>
                      <option>Marketplace Optimization</option>
                      <option>Social Media Management</option>
                      <option>Content Writing</option>
                    </select>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label for="vendor-rate" class="block text-sm font-medium text-gray-700 text-left">Vendor Rate (%)</label>
                      <input type="number" id="vendor-rate" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                      <label for="company-rate" class="block text-sm font-medium text-gray-700 text-left">Company Rate (%)</label>
                      <input type="number" id="company-rate" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                  </div>
                  <div>
                    <label for="effective-date" class="block text-sm font-medium text-gray-700 text-left">Effective Date</label>
                    <input type="date" id="effective-date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                  </div>
                  <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 text-left">Notes</label>
                    <textarea id="notes" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
              Save Commission Rate
            </button>
            <button @click="modalOpen = null" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
    function adminDashboard() {
      return {
        sidebarOpen: window.innerWidth > 768,
        profileDropdownOpen: false,
        modalOpen: null,
        activeMenu: 'Dashboard',
        selectedRole: '',

        stats: {
          totalVendors: 312,
          todayLeads: 96,
          monthlyDeals: 54,
          topKeywords: 428
        },
        recentVendors: [
          { id: 1, name: 'Labelin Aksara', location: 'Surabaya', status: 'Verified' },
          { id: 2, name: 'Yasin Barokah', location: 'Bangkalan', status: 'Pending' },
          { id: 3, name: 'Kursus Pro', location: 'Semarang', status: 'Verified' },
          { id: 4, name: 'Villa Kaliurang', location: 'Yogyakarta', status: 'Verified' },
          { id: 5, name: 'Cetak Yasin Probolinggo', location: 'Probolinggo', status: 'Pending' }
        ],
        activeProjects: [
          { id: 1, name: 'Label Baju Stratlaya', location: 'Surabaya, Malang', vendors: 12, created: '2025-08-01', status: 'Active' },
          { id: 2, name: 'Cetak Yasin Bangkalan', location: 'Bangkalan', vendors: 8, created: '2025-08-05', status: 'Active' },
          { id: 3, name: 'Kursus Bahasa Inggris', location: 'Semarang', vendors: 5, created: '2025-07-28', status: 'Active' },
          { id: 4, name: 'Sewa Villa Kaliurang', location: 'Yogyakarta', vendors: 3, created: '2025-08-10', status: 'Active' }
        ],
        recentLeads: [
          { id: 1001, customer: 'Ridzy', project: 'Label Baju Sidoarjo', vendor: 'Labelin Aksara', status: 'Converted', date: '2025-08-15' },
          { id: 1002, customer: 'Nasma', project: 'Villa Kaliurang', vendor: 'Villa Asri', status: 'Assigned', date: '2025-08-14' },
          { id: 1003, customer: 'Sisman', project: 'Kursus Bahasa Inggris', vendor: 'Kursus Pro', status: 'New', date: '2025-08-14' },
          { id: 1004, customer: 'TanBah', project: 'Cetak Yasin', vendor: 'Yasin Barokah', status: 'Assigned', date: '2025-08-13' },
          { id: 1005, customer: 'Rama Usaba', project: 'Label Baju', vendor: 'Labelin Aksara', status: 'Converted', date: '2025-08-12' }
        ],
        
        setActive(name) { 
          this.activeMenu = name; 
        },
        
        openModal(modal) { 
          this.modalOpen = modal; 
        },
        
    openLogoutModal() {
    this.modalOpen = 'logout'; // buka modal konfirmasi logout
    },

    performLogout() {
    // Submit hidden form dengan CSRF
    document.getElementById('logoutForm')?.submit();
    },

            updateSelectedRole(event) {
          this.selectedRole = event.target.value;
        },

        init() {
          // Cek apakah ada preferensi sidebar di localStorage
          const sidebarPref = localStorage.getItem('sidebarOpen');
          if (sidebarPref !== null) {
            this.sidebarOpen = sidebarPref === 'true';
          } else {
            // Default: buka di desktop, tutup di mobile
            this.sidebarOpen = window.innerWidth > 768;
          }
          
          window.addEventListener('resize', () => {
            // Di mobile, tetap tutup sidebar saat resize
            if (window.innerWidth <= 768) {
              this.sidebarOpen = false;
            }
          });
          
          // Simpan preferensi saat sidebar di-toggle
          this.$watch('sidebarOpen', (value) => {
            localStorage.setItem('sidebarOpen', value);
          });

          // Initialize role selection for add user modal
          document.getElementById('role')?.addEventListener('change', (e) => {
            this.updateSelectedRole(e);
          });
        }
      }
    }
  </script>
</body>
</html>