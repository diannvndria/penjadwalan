<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Penjadwalan Skripsi</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm p-4">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex-shrink-0">
                    <a href="{{ url('/dashboard') }}" class="text-xl font-bold text-gray-800">Sistem Skripsi</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        {{-- Navigasi umum untuk semua user yang login --}}
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('dashboard') ? 'font-semibold' : '' }}">Dashboard</a>

                        {{-- Navigasi yang dapat dilihat oleh semua user (Admin & User Biasa) --}}
                        <a href="{{ route('mahasiswa.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('mahasiswa.index') ? 'font-semibold' : '' }}">Data Mahasiswa</a>
                        <a href="{{ route('dosen.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('dosen.index') ? 'font-semibold' : '' }}">Daftar Dosen</a>
                        <a href="{{ route('penguji.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('penguji.index') ? 'font-semibold' : '' }}">Daftar Penguji</a>
                        <a href="{{ route('jadwal-penguji.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('jadwal-penguji.index') ? 'font-semibold' : '' }}">Jadwal Penguji</a>
                        <a href="{{ route('munaqosah.index') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('munaqosah.index') ? 'font-semibold' : '' }}">Jadwal Sidang</a>

                        {{-- Navigasi KHUSUS ADMIN --}}
                        @if (Auth::user()->isAdmin())
                            {{-- Tambahkan link khusus admin jika ada yang tidak termasuk di atas --}}
                            {{-- Misal: Link untuk mengelola user, atau halaman setting admin --}}
                        @endif

                        {{-- Tombol Logout dan informasi user --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline ml-4">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">Logout ({{ Auth::user()->name }} - {{ ucfirst(Auth::user()->role) }})</button>
                        </form>
                    @else
                        {{-- Jika belum login, tampilkan link login/register di navbar --}}
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('login') ? 'font-semibold' : '' }}">Login</a>
                        <a href="{{ route('register') }}" class="text-gray-600 hover:text-gray-900 {{ request()->routeIs('register') ? 'font-semibold' : '' }}">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        @hasSection('header')
        <header class="bg-white shadow py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('header')
            </div>
        </header>
        @endif

        <main class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>