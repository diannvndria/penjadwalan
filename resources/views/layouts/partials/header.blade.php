            <!-- Top Header Bar -->
            <header class="bg-white px-4 lg:px-6 py-2.5 flex items-center justify-between sticky top-0 z-40 border-b border-gray-100">
                <div class="flex items-center flex-1 min-w-0">
                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden mr-3 p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Title Area -->
                    <div class="flex items-center space-x-6 flex-1 min-w-0">
                        <h1 class="text-xl lg:text-2xl font-extrabold text-gray-900 truncate tracking-tight shrink-0">
                            @yield('header', 'AutoSchedule')
                        </h1>
                    </div>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-3 lg:space-x-5">
                    <!-- User Profile & Logout Dropdown -->
                    @auth
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center space-x-2 lg:space-x-3 focus:outline-none p-1.5 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="text-right hidden lg:block leading-tight">
                                <div class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</div>
                            </div>
                            <img class="h-9 w-9 lg:h-10 lg:w-10 rounded-full object-cover ring-2 ring-gray-100"
                                 src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=3b82f6&background=eff6ff&bold=true"
                                 alt="{{ Auth::user()->name }}">
                            <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
                        </button>

                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl py-2 z-50 origin-top-right border border-gray-100">

                            <div class="px-4 py-2 mb-2 lg:hidden">
                                <div class="text-sm font-bold text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</div>
                            </div>

                            <a href="#" class="user-dropdown-item">
                                <i class="far fa-user"></i>
                                Profil Saya
                            </a>
                            <a href="#" class="user-dropdown-item">
                                <i class="fas fa-cog"></i>
                                Pengaturan Akun
                            </a>

                            <div class="my-1 border-t border-gray-100 mx-3"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="user-dropdown-item w-full text-left text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt text-red-400"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('login') }}" class="px-5 py-2 text-sm font-semibold text-blue-600 border border-blue-200 rounded-xl hover:bg-blue-50 transition-all">Masuk</a>
                            <a href="{{ route('register') }}" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl shadow-md shadow-blue-200 hover:bg-blue-700 transition-all">Daftar</a>
                        </div>
                    @endauth
                </div>
            </header>
