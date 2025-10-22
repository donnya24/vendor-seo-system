<!-- ===== Sidebar ===== -->
<div
    id="adminSidebar"
    data-turbo-permanent
    class="sidebar z-40 text-white w-64 fixed inset-y-0 left-0 flex flex-col
           bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900
           transform transition-transform duration-250 ease-in-out shadow-2xl"
    :class="$store.ui.sidebar ? 'translate-x-0' : '-translate-x-full'"
    x-cloak
    role="navigation"
    aria-label="Sidebar utama"
    :aria-hidden="$store.ui.sidebar ? 'false' : 'true'"
    @click.outside="if (!$store.layout.isDesktop) $store.ui.sidebar = false"
        x-data="{
            activeMenu: '<?= 
                url_is("admin/dashboard*") ? "dashboard" :
                (url_is("admin/userseo*") || url_is("admin/uservendor*") ? "users" :
                (url_is("admin/vendors*") || url_is("admin/services*") || url_is("admin/areas*") ? "vendors" :
                (url_is("admin/activities/vendor*") || url_is("admin/activities/seo*") ? "activities" :
                (url_is("admin/targets*") ? "targets" :
                (url_is("admin/reports*") ? "reports" :
                (url_is("admin/leads*") ? "leads" :
                (url_is("admin/commissions*") ? "commissions" :
                (url_is("admin/announcements*") ? "announcements" :
                (url_is("admin/notification-management*") ? "notifications" :
                (url_is("admin/activity-logs*") ? "activity-logs" : ""))))))))))
            ?>',
            userSubmenu: <?= (url_is('admin/userseo*') || url_is('admin/uservendor*')) ? 'true' : 'false' ?>,
            vendorSubmenu: <?= (url_is('admin/vendors*') || url_is('admin/services*') || url_is('admin/areas*')) ? 'true' : 'false' ?>,
            activitySubmenu: <?= (url_is('admin/activities/vendor*') || url_is('admin/activities/seo*')) ? 'true' : 'false' ?>,
            init(){
                // Auto-detect based on current path
                const path = window.location.pathname;
                
                // Reset submenus first
                this.userSubmenu = false;
                this.vendorSubmenu = false;
                this.activitySubmenu = false;
                
                // Set based on current page
                if(path.includes('/admin/userseo') || path.includes('/admin/uservendor')) {
                    this.activeMenu = 'users';
                    this.userSubmenu = true;
                }
                else if(path.includes('/admin/vendors') || path.includes('/admin/services') || path.includes('/admin/areas')) {
                    this.activeMenu = 'vendors';
                    this.vendorSubmenu = true;
                }
                else if(path.includes('/admin/activities/vendor') || path.includes('/admin/activities/seo')) {
                    this.activeMenu = 'activities';
                    this.activitySubmenu = true;
                }
                else if(path.includes('/admin/targets')) {
                    this.activeMenu = 'targets';
                }
                else if(path.includes('/admin/reports')) {
                    this.activeMenu = 'reports';
                }
                else if(path.includes('/admin/leads')) {
                    this.activeMenu = 'leads';
                }
                else if(path.includes('/admin/commissions')) {
                    this.activeMenu = 'commissions';
                }
                else if(path.includes('/admin/announcements')) {
                    this.activeMenu = 'announcements';
                }
                else if(path.includes('/admin/notification-management')) {
                    this.activeMenu = 'notifications';
                }
                else if(path.includes('/admin/activity-logs')) {
                    this.activeMenu = 'activity-logs';
                }
            },
            toggleUserSubmenu(){
                this.userSubmenu = !this.userSubmenu;
                this.vendorSubmenu = false;
                this.activitySubmenu = false;
            },
            toggleVendorSubmenu(){
                this.vendorSubmenu = !this.vendorSubmenu;
                this.userSubmenu = false;
                this.activitySubmenu = false;
            },
            toggleActivitySubmenu(){
                this.activitySubmenu = !this.activitySubmenu;
                this.userSubmenu = false;
                this.vendorSubmenu = false;
            },
            closeSidebarOnMobile(){
                if (!$store.layout.isDesktop) {
                    $store.ui.sidebar = false;
                }
            }
        }"
    @keydown.escape.window="if (!$store.layout.isDesktop) $store.ui.close()"
>
    <!-- Header -->
    <div class="px-5 py-4 border-b border-blue-600/30">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fas fa-shield-alt text-white"></i>
                </div>
                <div>
                    <h2 class="text-white font-semibold text-sm">Admin Panel</h2>
                    <p class="text-blue-200 text-xs">Control Center</p>
                </div>
            </div>
            <button
                class="md:hidden text-blue-200 hover:text-white p-2 rounded-lg hover:bg-blue-600/50 transition-all"
                @click="$store.ui.close()"
                aria-label="Tutup sidebar"
                type="button"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3">
        <!-- Dashboard -->
        <a href="<?= site_url('admin/dashboard'); ?>"
           class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                  hover:bg-blue-700/50 text-blue-100 hover:text-white"
           :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'dashboard'}"
           @click="closeSidebarOnMobile()">
            <i class="fas fa-tachometer-alt w-5 text-center"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        <!-- Divider -->
        <div class="my-4 border-t border-blue-600/30"></div>

        <!-- Management Users -->
        <div class="mb-1">
            <button 
                @click="toggleUserSubmenu()"
                class="group w-full flex items-center gap-3 py-2.5 px-3 rounded-lg transition-all
                       hover:bg-blue-700/50 text-blue-100 hover:text-white"
                :class="{'bg-blue-700/50 text-white': activeMenu === 'users'}"
            >
                <i class="fas fa-user-shield w-5 text-center"></i>
                <span class="text-sm font-medium flex-1 text-left">Management Users</span>
                <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                   :class="{'rotate-180': userSubmenu}"></i>
            </button>
            
            <div x-show="userSubmenu" x-transition class="mt-1 space-y-1">
                <!-- User Tim SEO -->
                <a href="<?= site_url('admin/userseo'); ?>"
                   class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                          hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                   :class="{'bg-blue-600/50 text-white': window.location.pathname.includes('/admin/userseo')}"
                   @click="closeSidebarOnMobile()">
                    <i class="fas fa-users w-4 text-center text-xs"></i>
                    <span>User Tim SEO</span>
                </a>
                
                <!-- User Vendor -->
                <a href="<?= site_url('admin/uservendor'); ?>"
                   class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                          hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                   :class="{'bg-blue-600/50 text-white': window.location.pathname.includes('/admin/uservendor')}"
                   @click="closeSidebarOnMobile()">
                    <i class="fas fa-store w-4 text-center text-xs"></i>
                    <span>User Vendor</span>
                </a>
            </div>
        </div>

        <!-- Management Vendor -->
        <div class="mb-1">
            <button 
                @click="toggleVendorSubmenu()"
                class="group w-full flex items-center gap-3 py-2.5 px-3 rounded-lg transition-all
                       hover:bg-blue-700/50 text-blue-100 hover:text-white"
                :class="{'bg-blue-700/50 text-white': activeMenu === 'vendors'}"
            >
                <i class="fas fa-store-alt w-5 text-center"></i>
                <span class="text-sm font-medium flex-1 text-left">Management Vendor</span>
                <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
                   :class="{'rotate-180': vendorSubmenu}"></i>
            </button>
            
            <div x-show="vendorSubmenu" x-transition class="mt-1 space-y-1">
                <a href="<?= site_url('admin/areas'); ?>"
                   class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                          hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                   :class="{'bg-blue-600/50 text-white': window.location.pathname.includes('/admin/areas')}"
                   @click="closeSidebarOnMobile()">
                    <i class="fas fa-map-marked-alt w-4 text-center text-xs"></i>
                    <span>Area Vendor</span>
                </a>
                <a href="<?= site_url('admin/services'); ?>"
                   class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                          hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                   :class="{'bg-blue-600/50 text-white': window.location.pathname.includes('/admin/services')}"
                   @click="closeSidebarOnMobile()">
                    <i class="fas fa-box-open w-4 text-center text-xs"></i>
                    <span>Layanan & Produk Vendor</span>
                </a>
            </div>
        </div>

        <!-- Management Activity -->
        <div class="mb-1">
            <button 
                @click="toggleActivitySubmenu()"
                class="group w-full flex items-center gap-3 py-2.5 px-3 rounded-lg transition-all
                       hover:bg-blue-700/50 text-blue-100 hover:text-white"
                :class="{'bg-blue-700/50 text-white': activeMenu === 'activities'}"
            >
                <i class="fas fa-tasks w-5 text-center"></i>
                <span class="text-sm font-medium flex-1 text-left">Management Activity</span>
                <i class="fas fa-chevron-down text-xs transition-transform duration-200"
                   :class="{'rotate-180': activitySubmenu}"></i>
            </button>

            <div x-show="activitySubmenu" x-transition class="mt-1 space-y-1">
                <a href="<?= site_url('admin/activities/vendor'); ?>"
                   class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                          hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                   :class="{'bg-blue-600/50 text-white': window.location.pathname.includes('/admin/activities/vendor')}"
                   @click="closeSidebarOnMobile()">
                    <i class="fas fa-briefcase w-4 text-center text-xs"></i>
                    <span>Activity Vendor</span>
                </a>
                <a href="<?= site_url('admin/activities/seo'); ?>"
                   class="flex items-center gap-3 py-2 pl-11 pr-3 rounded-lg transition-all
                          hover:bg-blue-700/50 text-blue-200 hover:text-white text-sm"
                   :class="{'bg-blue-600/50 text-white': window.location.pathname.includes('/admin/activities/seo')}"
                   @click="closeSidebarOnMobile()">
                    <i class="fas fa-user-cog w-4 text-center text-xs"></i>
                    <span>Activity Tim SEO</span>
                </a>
            </div>
        </div>

        <!-- Targets -->
        <a href="<?= site_url('admin/targets'); ?>"
        class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                hover:bg-blue-700/50 text-blue-100 hover:text-white"
        :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'targets'}"
        @click="closeSidebarOnMobile()">
            <i class="fas fa-bullseye w-5 text-center"></i>
            <span class="text-sm font-medium">Targets</span>
        </a>

        <!-- Reports -->
        <a href="<?= site_url('admin/reports'); ?>"
        class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                hover:bg-blue-700/50 text-blue-100 hover:text-white"
        :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'reports'}"
        @click="closeSidebarOnMobile()">
            <i class="fas fa-chart-line w-5 text-center"></i>
            <span class="text-sm font-medium">Reports</span>
        </a>

        <!-- Leads -->
        <a href="<?= site_url('admin/leads'); ?>"
           class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                  hover:bg-blue-700/50 text-blue-100 hover:text-white"
           :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'leads'}"
           @click="closeSidebarOnMobile()">
            <i class="fas fa-list w-5 text-center"></i>
            <span class="text-sm font-medium">Leads</span>
        </a>

        <!-- Commissions -->
        <a href="<?= site_url('admin/commissions'); ?>"
           class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                  hover:bg-blue-700/50 text-blue-100 hover:text-white"
           :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'commissions'}"
           @click="closeSidebarOnMobile()">
            <i class="fas fa-coins w-5 text-center"></i>
            <span class="text-sm font-medium">Commissions</span>
        </a>

        <!-- Divider -->
        <div class="my-4 border-t border-blue-600/30"></div>

        <!-- Announcements -->
        <a href="<?= site_url('admin/announcements'); ?>"
           class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                  hover:bg-blue-700/50 text-blue-100 hover:text-white"
           :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'announcements'}"
           @click="closeSidebarOnMobile()">
            <i class="fas fa-bullhorn w-5 text-center"></i>
            <span class="text-sm font-medium">Announcements</span>
        </a>

        <!-- Kelola Notifikasi -->
        <a href="<?= site_url('admin/notification-management'); ?>"
           class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                  hover:bg-blue-700/50 text-blue-100 hover:text-white"
           :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'notifications'}"
           @click="closeSidebarOnMobile()">
            <i class="fas fa-bell w-5 text-center"></i>
            <span class="text-sm font-medium">Kelola Notifikasi</span>
        </a>

        <!-- Activity Logs -->
        <a href="<?= site_url('admin/activity-logs'); ?>"
           class="group flex items-center gap-3 py-2.5 px-3 rounded-lg mb-1 transition-all
                  hover:bg-blue-700/50 text-blue-100 hover:text-white"
           :class="{'bg-blue-600 text-white shadow-lg shadow-blue-600/30': activeMenu === 'activity-logs'}"
           @click="closeSidebarOnMobile()">
            <i class="fas fa-history w-5 text-center"></i>
            <span class="text-sm font-medium">Activity Logs</span>
        </a>
    </nav>

    <!-- Footer -->
    <div class="border-t border-blue-600/30 p-3">
        <div x-data="{ showConfirm: false }">
            <button
                type="button"
                class="group w-full flex items-center gap-3 py-2.5 px-3 rounded-lg transition-all
                       hover:bg-red-600/20 text-blue-100 hover:text-red-300"
                @click="showConfirm = true; if (!$store.layout?.isDesktop) $store.ui.sidebar = false"
            >
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span class="text-sm font-medium flex-1 text-left">Logout</span>
            </button>

            <!-- Logout Modal -->
            <template x-teleport="body">
                <div
                    x-show="showConfirm"
                    x-transition.opacity
                    @keydown.escape.window="showConfirm = false"
                    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                    role="dialog"
                    aria-modal="true"
                >
                    <div class="absolute inset-0" @click="showConfirm = false"></div>

                    <div class="relative bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm"
                         x-transition.scale.origin.center>
                        <div class="flex items-center justify-center w-14 h-14 bg-red-100 rounded-full mx-auto mb-4">
                            <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 text-center mb-2">Konfirmasi Logout</h2>
                        <p class="text-center text-gray-600 mb-6">Anda akan keluar dari panel admin</p>
                        
                        <div class="flex gap-3">
                            <button
                                type="button"
                                class="flex-1 px-4 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition-colors"
                                @click="showConfirm = false"
                            >Batal</button>

                            <form method="post" action="<?= site_url('logout'); ?>" class="flex-1">
                                <?= csrf_field() ?>
                                <button
                                    type="submit"
                                    class="w-full px-4 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors"
                                >Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <p class="text-[10px] text-blue-200/70 mt-3 text-center">
            © <?= date('Y'); ?> Imersa
        </p>
    </div>
</div>

<!-- Overlay mobile -->
<div
    x-show="$store.ui.sidebar"
    @click="$store.ui.sidebar = false"
    class="fixed inset-0 z-30 md:hidden bg-black/40"
    x-transition.opacity
    style="display:none;"
    aria-hidden="true">
</div>