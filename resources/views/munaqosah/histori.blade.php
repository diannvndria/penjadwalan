@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Histori Perubahan Jadwal Munaqosah') }} - {{ $munaqosah->mahasiswa->nama ?? 'N/A' }}
    </h2>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

            <h3 class="text-lg font-bold mb-4 text-gray-800">Detail Munaqosah:</h3>
            <div class="mb-6 text-gray-700">
                <p><strong>Mahasiswa:</strong> {{ $munaqosah->mahasiswa->nama ?? 'N/A' }} (NIM: {{ $munaqosah->mahasiswa->nim ?? 'N/A' }})</p>
                <p><strong>Judul Skripsi:</strong> {{ $munaqosah->mahasiswa->judul_skripsi ?? 'N/A' }}</p>
                <p><strong>Tanggal:</strong> {{ $munaqosah->tanggal_munaqosah->format('d-m-Y') }}</p>
                <p><strong>Waktu:</strong> {{ substr($munaqosah->waktu_mulai, 0, 5) }} - {{ substr($munaqosah->waktu_selesai, 0, 5) }}</p>
                <p><strong>Penguji 1:</strong> {{ $munaqosah->penguji1->nama ?? 'N/A' }}</p>
                <p><strong>Penguji 2:</strong> {{ $munaqosah->penguji2->nama ?? '-' }}</p>
                {{-- --- Hapus Penguji Utama dari detail histori --- --}}
                {{-- <p><strong>Penguji Utama:</strong> {{ $munaqosah->pengujiUtama->nama ?? '-' }}</p> --}}
                <p><strong>Status:</strong> <span class="font-semibold">{{ ucfirst($munaqosah->status_konfirmasi) }}</span></p>
            </div>

            <h3 class="text-lg font-bold mb-4 text-gray-800">Histori Perubahan:</h3>
            @forelse ($histories as $history)
                <div class="border-b border-gray-200 pb-4 mb-4 last:border-b-0 last:pb-0">
                    <p class="text-sm text-gray-600 mb-1">
                        {{ $history->created_at->format('d-m-Y H:i:s') }}
                        @if ($history->user)
                            oleh <strong class="text-gray-800">{{ $history->user->name }}</strong>
                        @else
                            (Admin tidak diketahui)
                        @endif
                    </p>
                    <p class="text-base text-gray-800">{{ $history->perubahan }}</p>
                </div>
            @empty
                <p class="text-gray-500">Tidak ada histori perubahan untuk jadwal munaqosah ini.</p>
            @endforelse

            <div class="mt-6">
                <a href="{{ route('munaqosah.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Kembali ke Daftar Jadwal Munaqosah
                </a>
            </div>

        </div>
    </div>
@endsection