@extends('layouts.app')

{{-- @section('header') section is now empty in layout, so remove it here --}}
{{-- Remove the @section('header') block entirely from here --}}

@section('content')
    <div class="space-y-6"> {{-- Consistent vertical spacing between major sections --}}

        {{-- Background putih, rounded-xl, shadow, dan padding yang konsisten --}}
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Selamat datang di Sistem Penjadwalan Skripsi,
                @auth
                    <span class="text-green-700">{{ Auth::user()->name }}</span>!
                @else
                    Guest!
                @endauth
            </h3>
            <p class="text-lg text-gray-600">
                Peran Anda saat ini: <span class="font-semibold text-green-700">{{ ucfirst(Auth::user()->role ?? 'Guest') }}</span>.
                Gunakan menu navigasi di sidebar kiri untuk menjelajahi fitur.
            </p>
        </div>

        {{-- Gap 6px antar kartu --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Background hijau-50, padding 6, border, flex-col, h-40 untuk tinggi seragam --}}
            <div class="bg-green-50 rounded-xl shadow-md p-6 border border-green-200 flex flex-col justify-between h-40">
                <div class="flex items-start mb-3">
                    <div class="flex-shrink-0 bg-green-100 text-green-700 rounded-full p-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354c-1.189-1.189-3.08-1.583-4.524-.913C6.313 3.86 4 6.313 4 8.78V16c0 1.25.75 2.5 2 2.5h12c1.25 0 2-1.25 2-2.5V8.78c0-2.467-2.313-4.92-3.476-6.345-1.444-.67-3.335-.276-4.524.913z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-800">Total Mahasiswa</h4>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-green-800">
                    {{ \App\Models\Mahasiswa::count() }}
                </p>
                <div class="mt-auto text-right">
                    <a href="{{ route('mahasiswa.index') }}" class="text-sm font-medium text-green-600 hover:text-green-800">Lihat Detail &rarr;</a>
                </div>
            </div>

            {{-- Background kuning-50, p-6, border, flex-col, h-40 --}}
            <div class="bg-yellow-50 rounded-xl shadow-md p-6 border border-yellow-200 flex flex-col justify-between h-40">
                <div class="flex items-start mb-3">
                    <div class="flex-shrink-0 bg-yellow-100 text-yellow-700 rounded-full p-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-800">Jadwal Sidang Mendatang</h4>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-yellow-800">
                    {{ \App\Models\Munaqosah::whereIn('status_konfirmasi', ['pending', 'dikonfirmasi'])->where('tanggal_munaqosah', '>=', \Carbon\Carbon::today())->count() }}
                </p>
                <div class="mt-auto text-right">
                    <a href="{{ route('munaqosah.index') }}" class="text-sm font-medium text-yellow-600 hover:text-yellow-800">Lihat Jadwal &rarr;</a>
                </div>
            </div>

            @if (Auth::user()->isAdmin())
            {{-- Background merah-50, p-6, border, flex-col, h-40 --}}
            <div class="bg-red-50 rounded-xl shadow-md p-6 border border-red-200 flex flex-col justify-between h-40">
                <div class="flex items-start mb-3">
                    <div class="flex-shrink-0 bg-red-100 text-red-700 rounded-full p-2">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-800">Siap Sidang (Menunggu)</h4>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-red-800">
                    {{ \App\Models\Mahasiswa::where('siap_sidang', true)->doesntHave('munaqosah')->count() }}
                </p>
                <div class="mt-auto text-right">
                    <a href="{{ route('mahasiswa.index', ['siap_sidang' => true]) }}" class="text-sm font-medium text-red-600 hover:text-red-800">Lihat Daftar &rarr;</a>
                </div>
            </div>
            @endif
        </div>

        {{-- Background putih, rounded-xl, shadow, padding konsisten --}}
        @if (Auth::user()->isAdmin())
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Aksi Cepat Admin</h3>
            <div class="flex flex-wrap gap-4 justify-start">
                <a href="{{ route('mahasiswa.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Tambah Mahasiswa Baru
                </a>
                <a href="{{ route('munaqosah.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Buat Jadwal Sidang
                </a>
            </div>
        </div>
        @endif

    </div>
@endsection