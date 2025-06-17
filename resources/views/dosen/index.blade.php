@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Daftar Dosen') }}
    </h2>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Tombol Tambah Dosen (Hanya untuk Admin) --}}
            @if (Auth::user()->isAdmin())
                <a href="{{ route('dosen.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                    Tambah Dosen Baru
                </a>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Dosen</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Diampu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas Amputan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            @if (Auth::user()->isAdmin()) {{-- Kolom Aksi hanya untuk Admin --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($dosens as $dosen)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $dosen->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $dosen->jumlah_diampu_sekarang }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $dosen->kapasitas_ampu > 0 ? $dosen->kapasitas_ampu : 'Tidak Terbatas' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($dosen->kapasitas_ampu > 0 && $dosen->jumlah_diampu_sekarang >= $dosen->kapasitas_ampu)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Full</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Tersedia</span>
                                        @if ($dosen->kapasitas_ampu > 0)
                                            <span class="text-xs text-gray-500">(Sisa: {{ $dosen->kapasitas_ampu - $dosen->jumlah_diampu_sekarang }})</span>
                                        @endif
                                    @endif
                                </td>
                                @if (Auth::user()->isAdmin()) {{-- Tombol Aksi hanya untuk Admin --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('dosen.edit', $dosen->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                        <form action="{{ route('dosen.destroy', $dosen->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus dosen ini? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? '5' : '4' }}" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">Tidak ada data dosen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection