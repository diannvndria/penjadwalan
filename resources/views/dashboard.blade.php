@extends('layouts.app')

@section('header')
    {{ __('Dashboard') }}
@endsection

@section('content')
    <div class="space-y-6">

        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Selamat datang di Sistem Penjadwalan Skripsi,
                @auth
                    <span class="text-blue-700">{{ Auth::user()->name }}</span>!
                @else
                    Guest!
                @endauth
            </h3>
            <p class="text-lg text-gray-600">
                Peran Anda saat ini: <span class="font-semibold text-blue-700">{{ ucfirst(Auth::user()->role ?? 'Guest') }}</span>.
                Gunakan menu navigasi di sidebar kiri untuk menjelajahi fitur.
            </p>
        </div>

        <!-- Overview Cards Section (Grid 3 Kolom) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Card 1: Total Mahasiswa (Blue Accent) -->
            <div class="bg-blue-50 rounded-xl shadow-md p-6 border border-blue-200 flex flex-col justify-between h-40">
                <div class="flex items-start mb-3">
                    <div class="flex-shrink-0 bg-blue-100 text-blue-700 rounded-full p-2">
                        <i class="fas fa-user-graduate fa-fw"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-800">Total Mahasiswa</h4>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-blue-800">
                    {{ \App\Models\Mahasiswa::count() }}
                </p>
                <div class="mt-auto text-right">
                    <a href="{{ route('mahasiswa.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Lihat Detail &rarr;</a>
                </div>
            </div>

            <!-- Card 2: Jadwal Sidang Mendatang (Green Accent) -->
            <div class="bg-green-50 rounded-xl shadow-md p-6 border border-green-200 flex flex-col justify-between h-40">
                <div class="flex items-start mb-3">
                    <div class="flex-shrink-0 bg-green-100 text-green-700 rounded-full p-2">
                        <i class="fas fa-calendar-alt fa-fw"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-800">Jadwal Sidang Mendatang</h4>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-green-800">
                    {{ \App\Models\Munaqosah::whereIn('status_konfirmasi', ['pending', 'dikonfirmasi'])->where('tanggal_munaqosah', '>=', \Carbon\Carbon::today())->count() }}
                </p>
                <div class="mt-auto text-right">
                    <a href="{{ route('munaqosah.index') }}" class="text-sm font-medium text-green-600 hover:text-green-800">Lihat Jadwal &rarr;</a>
                </div>
            </div>

            <!-- Card 3: Mahasiswa Siap Sidang (Menunggu Jadwal) - Admin Only (Yellow/Orange Accent for Warning) -->
            @if (Auth::user()->isAdmin())
            <div class="bg-yellow-50 rounded-xl shadow-md p-6 border border-yellow-200 flex flex-col justify-between h-40">
                <div class="flex items-start mb-3">
                    <div class="flex-shrink-0 bg-yellow-100 text-yellow-700 rounded-full p-2">
                        <i class="fas fa-exclamation-triangle fa-fw"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-800">Siap Sidang (Menunggu)</h4>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-yellow-800">
                    {{ \App\Models\Mahasiswa::where('siap_sidang', true)->doesntHave('munaqosah')->count() }}
                </p>
                <div class="mt-auto text-right">
                    <a href="{{ route('mahasiswa.index', ['siap_sidang' => true]) }}" class="text-sm font-medium text-yellow-600 hover:text-yellow-800">Lihat Daftar &rarr;</a>
                </div>
            </div>
            @endif {{-- Ini adalah penutup untuk Card 3 --}}
        </div>

        <!-- Quick Actions Section (Admin Only) -->
        @if (Auth::user()->isAdmin())
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Aksi Cepat Admin</h3>
            <div class="flex flex-wrap gap-4 justify-start">
                <a href="{{ route('mahasiswa.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    <i class="fas fa-plus fa-fw mr-3"></i>
                    Tambah Mahasiswa Baru
                </a>
                <a href="{{ route('munaqosah.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    <i class="fas fa-calendar-plus fa-fw mr-3"></i>
                    Buat Jadwal Sidang
                </a>
            </div>
        </div>
        @endif {{-- Ini adalah penutup untuk Quick Actions Section --}}

    </div>
@endsection
