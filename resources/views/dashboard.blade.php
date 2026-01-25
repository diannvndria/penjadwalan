@extends('layouts.app')

@section('header')
    {{ __('Dashboard') }}
@endsection

@section('content')
    <div class="space-y-8">
        <!-- Welcome Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-white shadow-lg">
            <div class="relative z-10">
                <h3 class="text-3xl font-bold mb-2">
                    Selamat Datang, {{ Auth::user()->name }}!
                </h3>
                <p class="text-blue-100 text-lg max-w-2xl">
                    Sistem Penjadwalan Sidang Munaqosah. Pantau jadwal, kelola mahasiswa, dan atur penguji dengan mudah.
                </p>
            </div>
            <div class="absolute right-0 top-0 h-full w-1/3 bg-white/10 transform skew-x-12 translate-x-12"></div>
            <div class="absolute right-10 bottom-[-20px] text-9xl text-white/10">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Mahasiswa -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Mahasiswa</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $total_mahasiswa }}</p>
                    </div>
                    <div class="h-12 w-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <a href="{{ route('mahasiswa.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center">
                        Lihat Semua <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- Jadwal Mendatang -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sidang Mendatang</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $upcoming_schedules_count }}</p>
                    </div>
                    <div class="h-12 w-12 bg-green-50 rounded-full flex items-center justify-center text-green-600">
                        <i class="fas fa-calendar-day text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <a href="{{ route('munaqosah.index') }}" class="text-green-600 hover:text-green-700 font-medium flex items-center">
                        Lihat Jadwal <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- Siap Sidang -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Menunggu Jadwal</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $ready_for_defense_count }}</p>
                    </div>
                    <div class="h-12 w-12 bg-orange-50 rounded-full flex items-center justify-center text-orange-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('auto-schedule.index') }}" class="text-orange-600 hover:text-orange-700 font-medium flex items-center">
                            Auto Schedule <i class="fas fa-magic ml-1 text-xs"></i>
                        </a>
                    @else
                        <span class="text-gray-400">Perlu tindakan admin</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Upcoming Schedule Table -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h4 class="font-semibold text-gray-800">Jadwal Sidang Terdekat</h4>
                        <a href="{{ route('munaqosah.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Lihat Semua</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-3">Waktu</th>
                                    <th class="px-6 py-3">Mahasiswa</th>
                                    <th class="px-6 py-3">Ruangan</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($upcoming_munaqosahs as $munaqosah)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($munaqosah->tanggal_munaqosah)->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($munaqosah->waktu_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($munaqosah->waktu_selesai)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $munaqosah->mahasiswa->nama }}</div>
                                        <div class="text-xs text-gray-500 truncate max-w-[150px]">{{ $munaqosah->mahasiswa->nim }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $munaqosah->ruangUjian->nama ?? 'TBA' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($munaqosah->status_konfirmasi == 'dikonfirmasi')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Confirmed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-calendar-times text-3xl mb-2 text-gray-300"></i>
                                            <p>Belum ada jadwal sidang mendatang.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Actions & Ready Students -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                @if(Auth::user()->isAdmin())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h4 class="font-semibold text-gray-800 mb-4">Aksi Cepat</h4>
                    <div class="space-y-3">
                        <a href="{{ route('mahasiswa.create') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition group">
                            <div class="h-10 w-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Tambah Mahasiswa</p>
                                <p class="text-xs text-gray-500">Input data mahasiswa baru</p>
                            </div>
                        </a>
                        <a href="{{ route('auto-schedule.index') }}" class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition group">
                            <div class="h-10 w-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Auto Schedule</p>
                                <p class="text-xs text-gray-500">Generate jadwal otomatis</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Ready Students List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h4 class="font-semibold text-gray-800">Siap Sidang Terbaru</h4>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($ready_students as $student)
                        <div class="p-4 hover:bg-gray-50 transition flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-bold">
                                    {{ substr($student->nama, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 truncate max-w-[120px]">{{ $student->nama }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->nim }}</p>
                                </div>
                            </div>
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('auto-schedule.index') }}" class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100">
                                Jadwalkan
                            </a>
                            @endif
                        </div>
                        @empty
                        <div class="p-6 text-center text-sm text-gray-500">
                            Tidak ada antrian mahasiswa.
                        </div>
                        @endforelse
                    </div>
                    @if($ready_students->count() > 0)
                    <div class="p-3 bg-gray-50 text-center border-t border-gray-100">
                        <a href="{{ route('mahasiswa.index', ['siap_sidang' => 1]) }}" class="text-xs font-medium text-gray-600 hover:text-gray-900">Lihat Semua Antrian</a>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
