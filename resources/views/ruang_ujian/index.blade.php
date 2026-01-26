@extends('layouts.app')

@section('header')
    {{ __('Daftar Ruang Ujian') }}
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-lg shadow-sm flex items-start" role="alert">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-lg shadow-sm flex items-start" role="alert">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Action Bar --}}
        @if (Auth::user()->isAdmin())
            <div class="flex justify-end">
                <a href="{{ route('ruang-ujian.create') }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-150 shadow-sm hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Ruang Ujian Baru
                </a>
            </div>
        @endif

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Table Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-door-open text-gray-600 mr-3"></i>
                        <h3 class="font-semibold text-gray-800">Daftar Ruang Ujian</h3>
                        <span class="ml-3 px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                            {{ $ruang->total() }} Total
                        </span>
                    </div>
                </div>
            </div>

            {{-- Table Container --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-door-closed text-gray-400 text-sm"></i>
                                    Nama Ruang
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                                    Lokasi
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-layer-group text-gray-400 text-sm"></i>
                                    Lantai
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-users text-gray-400 text-sm"></i>
                                    Kapasitas
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-info-circle text-gray-400 text-sm"></i>
                                    Status
                                </div>
                            </th>
                            @if (Auth::user()->isAdmin())
                                <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center justify-center gap-2">
                                        <i class="fas fa-cog text-gray-400 text-sm"></i>
                                        Aksi
                                    </div>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($ruang as $r)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-semibold text-gray-900">{{ $r->nama }}</div>
                                        @if($r->is_prioritas)
                                            <span class="px-2.5 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <i class="fas fa-star mr-1 text-yellow-600"></i>
                                                Prioritas
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $r->lokasi ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 inline-flex items-center text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-building mr-1"></i>
                                        Lantai {{ $r->lantai }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                        {{ $r->kapasitas }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($r->is_aktif)
                                        <span class="px-3 py-1 inline-flex items-center text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Tersedia
                                        </span>
                                    @else
                                        <span class="px-3 py-1 inline-flex items-center text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Tidak Tersedia
                                        </span>
                                    @endif
                                </td>
                                @if (Auth::user()->isAdmin())
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('ruang-ujian.edit', $r->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition-colors duration-150 border border-blue-200">
                                                <i class="fas fa-edit mr-1.5"></i>
                                                Edit
                                            </a>
                                            <form action="{{ route('ruang-ujian.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus ruang ujian ini? Tindakan ini tidak dapat dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-medium transition-colors duration-150 border border-red-200">
                                                    <i class="fas fa-trash-alt mr-1.5"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? '6' : '5' }}" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-inbox text-4xl mb-3"></i>
                                        <p class="text-sm font-medium">Tidak ada data ruang ujian</p>
                                        <p class="text-xs mt-1">Tambahkan ruang ujian baru untuk memulai</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($ruang->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <div class="flex justify-end">
                        {{ $ruang->links('vendor.pagination.custom') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
