@extends('layouts.app')

@section('header')
    {{-- Judul halaman akan menampilkan nama mahasiswa yang bersangkutan --}}
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Histori Jadwal Sidang - {{ $munaqosah->mahasiswa->nama ?? 'N/A' }}
    </h2>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 rounded-t-xl">
            <h3 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-history mr-3"></i>
                Histori Jadwal Sidang
            </h3>
            <p class="text-indigo-100 text-sm mt-1">Riwayat perubahan jadwal sidang {{ $munaqosah->mahasiswa->nama ?? 'N/A' }}</p>
        </div>

        <div class="p-8">
            {{-- Sidang Details Section --}}
            <div class="mb-8 p-6 border border-indigo-200 rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-indigo-600"></i>
                    Detail Jadwal Sidang
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-gray-700 text-sm">
                    <div class="flex items-start">
                        <i class="fas fa-user-graduate mr-3 text-indigo-600 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-gray-900 block">Mahasiswa</span>
                            <span>{{ $munaqosah->mahasiswa->nama ?? 'N/A' }} (NIM: {{ $munaqosah->mahasiswa->nim ?? 'N/A' }})</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-book mr-3 text-indigo-600 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-gray-900 block">Judul Skripsi</span>
                            <span>{{ $munaqosah->mahasiswa->judul_skripsi ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-calendar-day mr-3 text-indigo-600 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-gray-900 block">Tanggal</span>
                            <span>{{ $munaqosah->tanggal_munaqosah->format('d M Y') }}</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-clock mr-3 text-indigo-600 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-gray-900 block">Waktu</span>
                            <span>{{ substr($munaqosah->waktu_mulai, 0, 5) }} - {{ substr($munaqosah->waktu_selesai, 0, 5) }}</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-chalkboard-teacher mr-3 text-indigo-600 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-gray-900 block">Penguji 1</span>
                            <span>{{ $munaqosah->penguji1->nama ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user-tie mr-3 text-indigo-600 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-gray-900 block">Penguji 2</span>
                            <span>{{ $munaqosah->penguji2->nama ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-door-open mr-3 text-indigo-600 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-gray-900 block">Ruang Ujian</span>
                            <span>{{ $munaqosah->ruangUjian->nama ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle mr-3 text-indigo-600 mt-0.5"></i>
                        <div>
                            <span class="font-semibold text-gray-900 block">Status Konfirmasi</span>
                            @php
                                $statusConfig = [
                                    'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-200', 'icon' => 'fa-hourglass-half'],
                                    'dikonfirmasi' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200', 'icon' => 'fa-check-circle'],
                                    'ditolak' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'fa-times-circle'],
                                ][$munaqosah->status_konfirmasi] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-200', 'icon' => 'fa-question-circle'];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} border {{ $statusConfig['border'] }}">
                                <i class="fas {{ $statusConfig['icon'] }} mr-2"></i>{{ ucfirst($munaqosah->status_konfirmasi) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- History of Changes Section --}}
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-list-ul mr-2 text-indigo-600"></i>
                Riwayat Perubahan
            </h3>

            {{-- Container untuk semua item histori --}}
            <div class="space-y-4">
                @forelse ($histories as $history)
                    {{-- Individual history item card --}}
                    <div class="p-6 border border-indigo-200 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 shadow-sm hover:shadow-md transition">
                        <div class="flex flex-wrap justify-between items-center pb-3 mb-3 border-b border-indigo-100">
                            <p class="text-sm text-gray-600 flex items-center">
                                <i class="fas fa-clock mr-2 text-indigo-600"></i>
                                <span class="font-semibold text-gray-800">Waktu:</span>
                                <span class="ml-2">{{ $history->created_at->format('d M Y, H:i:s') }}</span>
                            </p>
                            <p class="text-sm text-gray-600 flex items-center">
                                <i class="fas fa-user-circle mr-2 text-indigo-600"></i>
                                <span class="font-semibold text-gray-800">Oleh:</span>
                                <span class="ml-2">{{ $history->user->name ?? 'Sistem' }}</span>
                            </p>
                        </div>
                        <p class="text-gray-800 leading-relaxed">
                            <i class="fas fa-edit mr-2 text-indigo-500"></i>
                            {{ $history->perubahan }}
                        </p>
                    </div>
                @empty
                    {{-- Message when no history is available --}}
                    <div class="p-8 border border-gray-200 rounded-lg bg-gray-50 text-center shadow-sm">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-600">Tidak ada riwayat perubahan untuk jadwal sidang ini.</p>
                    </div>
                @endforelse
            </div>


            {{-- Back button --}}
            <div class="mt-8 flex justify-end border-t border-gray-200 pt-6">
                <a href="{{ route('munaqosah.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Daftar Jadwal
                </a>
            </div>

        </div>
    </div>
@endsection
