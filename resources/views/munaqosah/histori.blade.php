@extends('layouts.app')

@section('header')
    {{-- Judul halaman akan menampilkan nama mahasiswa yang bersangkutan --}}
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Histori Jadwal Munaqosah - {{ $munaqosah->mahasiswa->nama ?? 'N/A' }}
    </h2>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

            {{-- Munaqosah Details Section --}}
            <div class="mb-8 p-6 border border-blue-200 rounded-xl bg-blue-50 shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Detail Jadwal Munaqosah</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-gray-700 text-base">
                    <p><span class="font-semibold text-gray-900">Mahasiswa:</span> {{ $munaqosah->mahasiswa->nama ?? 'N/A' }} (NIM: {{ $munaqosah->mahasiswa->nim ?? 'N/A' }})</p>
                    <p><span class="font-semibold text-gray-900">Judul Skripsi:</span> {{ $munaqosah->mahasiswa->judul_skripsi ?? 'N/A' }}</p>
                    <p><span class="font-semibold text-gray-900">Tanggal:</span> {{ $munaqosah->tanggal_munaqosah->format('d M Y') }}</p>
                    <p><span class="font-semibold text-gray-900">Waktu:</span> {{ substr($munaqosah->waktu_mulai, 0, 5) }} - {{ substr($munaqosah->waktu_selesai, 0, 5) }}</p>
                    <p><span class="font-semibold text-gray-900">Penguji 1:</span> {{ $munaqosah->penguji1->nama ?? 'N/A' }}</p>
                    <p><span class="font-semibold text-gray-900">Penguji 2:</span> {{ $munaqosah->penguji2->nama ?? '-' }}</p>
                    <p><span class="font-semibold text-gray-900">Status Konfirmasi:</span>
                        @php
                            $statusClass = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'dikonfirmasi' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                            ][$munaqosah->status_konfirmasi] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-3 py-1.5 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusClass }}">
                            {{ ucfirst($munaqosah->status_konfirmasi) }}
                        </span>
                    </p>
                </div>
            </div>

            {{-- History of Changes Section --}}
            <h3 class="text-xl font-bold text-gray-800 mb-4">Riwayat Perubahan</h3>

            {{-- Container untuk semua item histori, dengan sedikit space-y --}}
            <div class="space-y-4">
                @forelse ($histories as $history)
                    {{-- Individual history item card (gaya mirip detail jadwal) --}}
                    <div class="p-6 border border-blue-200 rounded-xl bg-blue-50 shadow-md"> {{-- Padding, border biru, bg biru muda, shadow --}}
                        <div class="flex justify-between items-center pb-2 mb-2 border-b border-blue-100"> {{-- Garis bawah dengan warna biru muda --}}
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold text-gray-800"><i class="fas fa-clock fa-fw mr-2"></i> Waktu:</span> {{ $history->created_at->format('d M Y, H:i:s') }} {{-- mr-2 pada ikon --}}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold text-gray-800"><i class="fas fa-user-circle fa-fw mr-2"></i> Oleh:</span> {{ $history->user->name ?? 'Sistem' }} {{-- mr-2 pada ikon --}}
                            </p>
                        </div>
                        <p class="text-gray-800 leading-relaxed font-medium">
                            {{ $history->perubahan }}
                        </p>
                    </div>
                @empty
                    {{-- Message when no history is available --}}
                    <div class="p-6 border border-gray-200 rounded-xl bg-gray-50 text-gray-600 text-center shadow-sm">
                        <p>Tidak ada riwayat perubahan untuk jadwal munaqosah ini.</p>
                    </div>
                @endforelse
            </div>


            {{-- Back button --}}
            <div class="mt-8 text-right">
                <a href="{{ route('munaqosah.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left fa-fw mr-3"></i> Kembali ke Daftar Jadwal
                </a>
            </div>

        </div>
    </div>
@endsection
