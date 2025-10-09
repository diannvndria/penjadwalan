<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Penjadwalan Skripsi</title>
    {{-- Vite directive untuk mengkompilasi CSS dan JS --}}
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    {{-- Link Font Awesome CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Optional: Styling untuk scrollbar agar tampilan lebih konsisten di berbagai browser */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; /* Warna track scrollbar */
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #94a3b8; /* gray-400 */
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b; /* gray-600 */
        }
        /* Basic dropdown styling for demonstration (can be enhanced with JS if needed) */
        .group:hover .group-hover-show {
            display: block; /* Menampilkan dropdown saat group di-hover */
        }
        /* Penting untuk Font Awesome: Memastikan ikon memiliki lebar tetap untuk proporsionalitas yang lebih baik dalam daftar */
        .fa-fw {
            width: 1.2857142857em; /* Standar Font Awesome untuk fixed-width icons */
            text-align: center;
        }
        /* Penyesuaian tambahan untuk ikon Font Awesome agar center vertikal dengan teks */
        .fa-fw-aligned {
            display: flex;
            align-items: center;
            justify-content: center; /* Untuk ikon yang sudah memiliki lebar tetap, ini membuatnya center */
            height: 1em; /* Tinggi ikon agar sejajar dengan tinggi baris teks */
        }
        /* Smoother sidebar hover with staggered labels */
        :root {
            --sidebar-collapsed: 64px;
            --sidebar-expanded: 260px;
            --label-max-width: 400px;
            --ease: cubic-bezier(.18,.9,.32,1);
        }
        .sidebar {
            width: var(--sidebar-collapsed);
            transition: width 360ms var(--ease), box-shadow 220ms ease;
            overflow: visible;
            will-change: width;
            position: relative;
        }
        .sidebar:hover, .sidebar.expanded {
            width: var(--sidebar-expanded);
            box-shadow: 0 10px 30px rgba(2,6,23,0.06);
        }

        /* Labels: use max-width + opacity + transform for smooth reveal
           We'll stagger labels using nth-child delays */
        .sidebar .label {
            display: inline-block;
            max-width: 0;
            opacity: 0;
            transform: translateX(-8px);
            transition: max-width 320ms var(--ease), opacity 240ms ease, transform 320ms var(--ease);
            white-space: nowrap;
            overflow: hidden;
            vertical-align: middle;
        }
        .sidebar:hover .label, .sidebar.expanded .label {
            max-width: var(--label-max-width);
            opacity: 1;
            transform: translateX(0);
        }

        /* Stagger labels: each nav-item's label has increasing delay */
        .sidebar nav .nav-item:nth-child(1) .label{ transition-delay: 40ms; }
        .sidebar nav .nav-item:nth-child(2) .label{ transition-delay: 80ms; }
        .sidebar nav .nav-item:nth-child(3) .label{ transition-delay: 120ms; }
        .sidebar nav .nav-item:nth-child(4) .label{ transition-delay: 160ms; }
        .sidebar nav .nav-item:nth-child(5) .label{ transition-delay: 200ms; }
        .sidebar nav .nav-item:nth-child(6) .label{ transition-delay: 240ms; }
        .sidebar nav .nav-item:nth-child(7) .label{ transition-delay: 280ms; }
        .sidebar nav .nav-item:nth-child(8) .label{ transition-delay: 320ms; }

        /* Align icons: center when collapsed, left when expanded */
        .sidebar .nav-item{
            justify-content: center;
            gap: 0.5rem;
            transition: justify-content 260ms var(--ease), gap 260ms var(--ease);
        }
        .sidebar:hover .nav-item, .sidebar.expanded .nav-item{
            justify-content: flex-start;
            gap: 0.75rem;
        }

        .sidebar .icon{ transition: margin 260ms var(--ease); margin-right:0 }
        .sidebar:hover .icon, .sidebar.expanded .icon{ margin-right:12px }
        /* Pagination theme similar to screenshot: minimal, white/gray with blue active pill */
        .pagination-custom { display: flex; gap: 6px; align-items: center; font-weight: 500; }
        .pagination-custom .page-item { list-style: none; }
        .pagination-custom .page-link{
            display:inline-flex; align-items:center; justify-content:center;
            min-width:34px; height:34px; padding:0 10px; border-radius:8px;
            color: #374151; background: #ffffff; border: 1px solid rgba(31,41,55,0.04); text-decoration:none;
        }
        .pagination-custom .page-link:hover{ background:#f8fafc }
        .pagination-custom .page-item.disabled .page-link{ color:#cbd5e1 }
        .pagination-custom .page-item.active .page-link{ background:#3b82f6; color:white; box-shadow: 0 4px 10px rgba(59,130,246,0.12) }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">
    {{-- Main container: Mengisi seluruh tinggi layar dan menggunakan flexbox --}}
    <div class="flex h-screen p-4 space-x-4">

        <!-- Sidebar (Panel Navigasi Kiri) -->
        <aside class="sidebar bg-white shadow-lg py-6 flex flex-col justify-between overflow-y-auto rounded-xl">
            <div>
                <!-- Logo & Nama Aplikasi -->
                <div class="flex items-center px-4 mb-8">
                    <img src="{{ asset('images/logo-uin-suka.png') }}" alt="Logo UIN Sunan Kalijaga" class="h-9 w-9">
                    <span class="text-xl font-bold text-gray-800 label" style="margin-left:12px">SkripsiApp</span>
                </div>

                <!-- Main Navigation Menu -->
                <nav class="space-y-3 px-3">
                    <a href="{{ route('dashboard') }}" class="flex nav-item items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <i class="fas fa-home fa-fw icon"></i> {{-- Dashboard: Home --}}
                        <span class="label">Dashboard</span>
                    </a>

                    <a href="{{ route('mahasiswa.index') }}" class="flex nav-item items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('mahasiswa.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <i class="fas fa-user-graduate fa-fw icon"></i>
                        <span class="label">Data Mahasiswa</span>
                    </a>
                    <a href="{{ route('dosen.index') }}" class="flex nav-item items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('dosen.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <i class="fas fa-chalkboard-teacher fa-fw icon"></i>
                        <span class="label">Daftar Pembimbing</span>
                    </a>
                    <a href="{{ route('penguji.index') }}" class="flex nav-item items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('penguji.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <i class="fas fa-user-tie fa-fw icon"></i>
                        <span class="label">Daftar Penguji</span>
                    </a>
                    <a href="{{ route('ruang-ujian.index') }}" class="flex nav-item items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('ruang-ujian.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <i class="fas fa-door-open fa-fw icon"></i>
                        <span class="label">Ruang Ujian</span>
                    </a>
                    <a href="{{ route('jadwal-penguji.index') }}" class="flex nav-item items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('jadwal-penguji.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <i class="fas fa-calendar-alt fa-fw icon"></i>
                        <span class="label">Jadwal Penguji</span>
                    </a>
                    <a href="{{ route('munaqosah.index') }}" class="flex nav-item items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('munaqosah.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <i class="fas fa-book fa-fw icon"></i>
                        <span class="label">Jadwal Sidang</span>
                    </a>
                    <a href="{{ route('auto-schedule.index') }}" class="flex nav-item items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('auto-schedule.*') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <i class="fas fa-robot fa-fw icon"></i>
                        <span class="label">Auto-Schedule</span>
                    </a>
                </nav>
            </div>

            <!-- Login/Register Links (for Guest Users) -->
            <div class="mt-auto pt-6 border-t border-gray-200 px-3">
                @guest
                    <a href="{{ route('login') }}" class="flex nav-item items-center p-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                        <i class="fas fa-sign-in-alt fa-fw icon"></i> {{-- Login: Sign-in-alt --}}
                        <span class="label">Login</span>
                    </a>
                    <a href="{{ route('register') }}" class="flex nav-item items-center p-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 mt-2">
                        <i class="fas fa-user-plus fa-fw icon"></i> {{-- Register: User-plus --}}
                        <span class="label">Register</span>
                    </a>
                @endguest
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col space-y-4">
            <!-- Top Header Bar -->
            <header class="bg-white px-6 py-4 flex items-center justify-between shadow-sm rounded-xl">
                <div class="flex-1">
                    @hasSection('header')
                        <h1 class="text-2xl font-bold text-gray-800">@yield('header')</h1>
                    @endif
                </div>
                <!-- User Profile & Logout in Header (Dropdown) -->
                @auth
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none p-1 rounded-full hover:bg-gray-100">
                        <span class="text-base font-medium text-gray-700 hidden md:block">Hi, {{ Auth::user()->name }}</span>
                        <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-300" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ Auth::user()->name }}">
                        <i class="fas fa-chevron-down fa-fw ml-1 text-gray-500 text-sm"></i> {{-- Dropdown arrow --}}
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 origin-top-right">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</a>
                        <div class="border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt fa-fw mr-2"></i> {{-- Logout icon --}}
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50">Register</a>
                    </div>
                @endauth
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 bg-white rounded-xl shadow-md">
                @yield('content')
            </main>
        </div>
    </div>
    <!-- Alpine.js CDN (make sure it's loaded) -->
    <script src="//unpkg.com/alpinejs" defer></script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
