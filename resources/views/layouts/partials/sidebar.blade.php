        <!-- Sidebar (Panel Navigasi Kiri) -->
        <aside id="sidebar" class="sidebar bg-white border-r border-gray-100 flex flex-col justify-between"
               :class="{ 'expanded': sidebarExpanded, 'mobile-show': mobileMenuOpen }">

            <div class="flex flex-col h-full">
                <!-- Brand / Logo -->
                <div class="brand-area shrink-0 border-b border-gray-50 mb-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center h-10 w-10 bg-blue-600 rounded-xl shadow-lg shadow-blue-100 shrink-0">
                             <img src="{{ asset('images/logo-uin-suka.png') }}" alt="Logo" class="h-6 w-6 brightness-0 invert">
                        </div>
                        <span class="ml-3 text-xl font-bold text-gray-800 tracking-tight label">Skripsi<span class="text-blue-600">App</span></span>
                    </div>
                    <!-- Mobile Close Button -->
                    <button @click="mobileMenuOpen = false" class="lg:hidden p-2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Main Navigation Menu -->
                <nav class="flex-1 overflow-y-auto pt-2 pb-6">
                    <div class="sidebar-separator"></div>
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home fa-fw icon"></i>
                        <span class="label">Dashboard</span>
                    </a>

                    <div class="sidebar-separator"></div>
                    <a href="{{ route('mahasiswa.index') }}" class="nav-item {{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate fa-fw icon"></i>
                        <span class="label">Data Mahasiswa</span>
                    </a>
                    <a href="{{ route('dosen.index') }}" class="nav-item {{ request()->routeIs('dosen.*') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher fa-fw icon"></i>
                        <span class="label">Daftar Pembimbing</span>
                    </a>
                    <a href="{{ route('penguji.index') }}" class="nav-item {{ request()->routeIs('penguji.*') ? 'active' : '' }}">
                        <i class="fas fa-user-tie fa-fw icon"></i>
                        <span class="label">Daftar Penguji</span>
                    </a>
                    <a href="{{ route('ruang-ujian.index') }}" class="nav-item {{ request()->routeIs('ruang-ujian.*') ? 'active' : '' }}">
                        <i class="fas fa-door-open fa-fw icon"></i>
                        <span class="label">Ruang Ujian</span>
                    </a>

                    <div class="sidebar-separator"></div>
                    <a href="{{ route('jadwal-penguji.index') }}" class="nav-item {{ request()->routeIs('jadwal-penguji.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt fa-fw icon"></i>
                        <span class="label">Jadwal Penguji</span>
                    </a>
                    <a href="{{ route('munaqosah.index') }}" class="nav-item {{ request()->routeIs('munaqosah.*') ? 'active' : '' }}">
                        <i class="fas fa-book fa-fw icon"></i>
                        <span class="label">Jadwal Sidang</span>
                    </a>
                    <a href="{{ route('auto-schedule.index') }}" class="nav-item {{ request()->routeIs('auto-schedule.*') ? 'active' : '' }}">
                        <i class="fas fa-wand-magic-sparkles fa-fw icon"></i>
                        <span class="label">Auto-Schedule</span>
                    </a>
                </nav>

                <!-- Toggle Sidebar Button (Desktop Only) -->
                <div class="hidden lg:block px-3 pb-3">
                    <button @click="toggleSidebar()" class="w-full flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors">
                        <i id="sidebarToggleIcon" class="fas fa-angle-double-right transition-transform duration-300 transform"
                           :class="{ 'rotate-180': sidebarExpanded }"></i>
                    </button>
                </div>

                <!-- Login/Register Links (for Guest Users) -->
                @guest
                <div class="mt-auto p-3 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl">
                    <a href="{{ route('login') }}" class="flex nav-item items-center p-2.5 rounded-lg text-gray-700 hover:bg-white hover:shadow-sm">
                        <i class="fas fa-sign-in-alt fa-fw icon"></i>
                        <span class="label">Login</span>
                    </a>
                </div>
                @endguest
            </div>

            {{-- Inline Script to Prevent FOUC (Flash of Unstyled Content) for Sidebar State & Toggle Rotation --}}
            <script>
                // Check localStorage immediately
                (function() {
                    var sidebar = document.getElementById('sidebar');
                    var toggleIcon = document.getElementById('sidebarToggleIcon');
                    var isExpanded = localStorage.getItem('sidebarExpanded') === 'true';

                    if (isExpanded) {
                        // Expand Sidebar instantly without transition
                        sidebar.classList.add('expanded');
                        // Rotate Toggle Icon instantly
                        if (toggleIcon) {
                            toggleIcon.classList.add('rotate-180');
                        }
                    }

                    // Enable animations in the next frame
                    requestAnimationFrame(function() {
                        sidebar.classList.add('ready-to-animate');
                    });
                })();
            </script>
        </aside>
