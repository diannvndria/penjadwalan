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
            background: #94a3b8; /* Warna thumb scrollbar (gray-400) */
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b; /* Warna thumb scrollbar saat hover (gray-600) */
        }
        /* Basic dropdown styling for demonstration (can be enhanced with JS if needed) */
        .group:hover .group-hover-show {
            display: block; /* Menampilkan dropdown saat group di-hover */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">
    {{-- Main container: Mengisi seluruh tinggi layar dan menggunakan flexbox --}}
    <div class="flex h-screen">

        <!-- Sidebar (Panel Navigasi Kiri) -->
        {{-- Sidebar sebagai kartu mengambang:
             w-64: lebar tetap 256px
             bg-white: latar belakang putih
             shadow-lg: bayangan besar
             py-6: padding vertikal 24px
             flex flex-col justify-between: flex container, elemen bertumpuk vertikal, dorong ke atas/bawah
             overflow-y-auto: memungkinkan scroll jika konten sidebar panjang
             rounded-3xl: sudut membulat 24px
             m-4: margin 16px di semua sisi untuk efek mengambang --}}
        <aside class="w-64 bg-white shadow-lg py-6 flex flex-col justify-between overflow-y-auto rounded-3xl m-4">
            <div>
                <!-- Logo & Nama Aplikasi -->
                {{-- px-6: padding horizontal 24px untuk konsistensi dengan elemen lain
                     mb-8: margin bawah 32px untuk pemisah dengan navigasi --}}
                <div class="flex items-center px-6 mb-8">
                    {{-- Sumber gambar logo UIN Sunan Kalijaga --}}
                    <img src="{{ asset('images/logo-uin-suka.png') }}" alt="Logo UIN Sunan Kalijaga" class="h-9 w-9 mr-3">
                    <span class="text-xl font-bold text-gray-800">SkripsiApp</span>
                </div>

                <!-- Menu Navigasi Utama -->
                {{-- space-y-3: jarak vertikal 12px antar link
                     px-5: padding horizontal 20px untuk item menu --}}
                <nav class="space-y-3 px-5">
                    {{-- Setiap link navigasi:
                         flex items-center: ikon dan teks sejajar
                         p-3: padding 12px
                         rounded-lg: sudut membulat 8px
                         text-gray-700: warna teks default
                         hover:bg-blue-50 hover:text-blue-700: efek hover
                         {{ request()->routeIs('...') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}: gaya untuk link aktif --}}
                    <a href="{{ route('dashboard') }}" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m0 0l-7 7m7-7v10a1 1 0 01-1 1h-3m-6-13a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('mahasiswa.index') }}" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('mahasiswa.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-4a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v11a2 2 0 01-2 2z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M10 9a2 2 0 11-4 0 2 2 0 014 0zM7 13.5a3 3 0 100-6 3 3 0 000 6z"></path></svg>
                        Data Mahasiswa
                    </a>
                    <a href="{{ route('dosen.index') }}" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('dosen.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354c-1.189-1.189-3.08-1.583-4.524-.913C6.313 3.86 4 6.313 4 8.78V16c0 1.25.75 2.5 2 2.5h12c1.25 0 2-1.25 2-2.5V8.78c0-2.467-2.313-4.92-3.476-6.345-1.444-.67-3.335-.276-4.524.913z"></path></svg>
                        Daftar Dosen
                    </a>
                    <a href="{{ route('penguji.index') }}" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('penguji.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Daftar Penguji
                    </a>
                    <a href="{{ route('jadwal-penguji.index') }}" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('jadwal-penguji.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Jadwal Penguji
                    </a>
                    <a href="{{ route('munaqosah.index') }}" class="flex items-center p-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 {{ request()->routeIs('munaqosah.index') ? 'bg-blue-100 text-blue-700 font-semibold' : '' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        Jadwal Sidang
                    </a>

                    @if (Auth::user()->isAdmin())
                        {{-- Admin-specific links if any --}}
                    @endif
                </nav>
            </div>

            <!-- Login/Register Links (for Guest Users) -->
            {{-- Bagian ini hanya tampil jika user belum login --}}
            <div class="mt-auto pt-6 border-t border-gray-200 px-6">
                @guest {{-- Only show login/register if not authenticated --}}
                    <a href="{{ route('login') }}" class="flex items-center p-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center p-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 mt-2">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Register
                    </a>
                @endguest
            </div>
        </aside>

        <!-- Main Content Area (Sisa ruang di sebelah kanan sidebar) -->
        {{-- ml-[calc(16rem+1rem)]: Memberi margin kiri seukuran lebar sidebar + margin sidebar.
             Ini mendorong area konten utama ke kanan untuk memberi ruang sidebar mengambang. --}}
        <div class="flex-1 flex flex-col ml-[calc(16rem+1rem)]">
            <!-- Top Header Bar -->
            {{-- Header sebagai kartu mengambang:
                 bg-white shadow-sm: latar belakang putih dengan bayangan kecil
                 px-6 py-4: padding internal
                 flex items-center justify-between: layout flex untuk judul dan profil
                 rounded-xl m-4: sudut membulat dan margin eksternal agar mengambang --}}
            <header class="bg-white px-6 py-4 flex items-center justify-between shadow-sm rounded-xl m-4">
                <div class="flex-1">
                    {{-- Judul halaman dari yield('header') --}}
                    @hasSection('header')
                        <h1 class="text-2xl font-bold text-gray-800">@yield('header')</h1>
                    @endif
                </div>
                <!-- Profil Pengguna & Logout di Header (Dropdown) -->
                @auth
                {{-- relative positioning untuk dropdown, x-data Alpine.js untuk state dropdown --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    {{-- Tombol trigger dropdown: flex, space-x, padding, rounded-full, hover effect --}}
                    <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none p-1 rounded-full hover:bg-gray-100">
                        <span class="text-base font-medium text-gray-700 hidden md:block">Hi, {{ Auth::user()->name }}</span>
                        <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-300" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF" alt="{{ Auth::user()->name }}">
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    {{-- Konten dropdown: x-show Alpine.js, transisi, posisi absolut, styling --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-20 origin-top-right">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</a>
                        <div class="border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">Logout</button>
                        </form>
                    </div>
                </div>
                @else
                    {{-- Tombol Login/Register untuk user yang belum login --}}
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50">Register</a>
                    </div>
                @endauth
            </header>

            <!-- Main Content Area (Dapat di-scroll) -->
            {{-- Konten utama sebagai kartu mengambang:
                 flex-1: mengambil sisa ruang
                 overflow-y-auto: memungkinkan scroll vertikal
                 p-4: padding internal
                 m-4: margin eksternal (mengambang)
                 bg-white rounded-xl shadow-md: styling kartu --}}
            <main class="flex-1 overflow-y-auto p-4 m-4 bg-white rounded-xl shadow-md">
                @yield('content')
            </main>
        </div>
    </div>
    <!-- Alpine.js CDN (pastikan dimuat) -->
    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
