@extends('layouts.app')

@section('header')
    {{ __('Daftar Pembimbing') }}
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
                <a href="{{ route('dosen.create') }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-150 shadow-sm hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Dosen Baru
                </a>
            </div>
        @endif

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Table Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chalkboard-teacher text-gray-600 mr-3"></i>
                        <h3 class="font-semibold text-gray-800">Daftar Dosen Pembimbing</h3>
                        <span class="ml-3 px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                            {{ $dosens->total() }} Total
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
                                    <i class="fas fa-user-tie text-gray-400 text-sm"></i>
                                    Nama Dosen
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-users text-gray-400 text-sm"></i>
                                    Jumlah Diampu
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-layer-group text-gray-400 text-sm"></i>
                                    Kapasitas Ampuan
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
                        @forelse ($dosens as $dosen)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($dosen->nama, 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-semibold text-gray-900">{{ $dosen->nama }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $dosen->jumlah_diampu_sekarang }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ $dosen->kapasitas_ampu > 0 ? $dosen->kapasitas_ampu : '∞' }}
                                    </span>
                                    @if ($dosen->kapasitas_ampu == 0)
                                        <span class="block text-xs text-gray-500">Tidak Terbatas</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($dosen->kapasitas_ampu > 0 && $dosen->jumlah_diampu_sekarang >= $dosen->kapasitas_ampu)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Full
                                        </span>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Tersedia
                                            </span>
                                            @if ($dosen->kapasitas_ampu > 0)
                                                <span class="text-xs text-gray-500">(Sisa: {{ $dosen->kapasitas_ampu - $dosen->jumlah_diampu_sekarang }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                @if (Auth::user()->isAdmin())
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('dosen.edit', $dosen->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition-colors duration-150"
                                               title="Edit">
                                                <i class="fas fa-edit mr-1"></i>
                                                Edit
                                            </a>
                                            <form action="{{ route('dosen.destroy', $dosen->id) }}" method="POST" 
                                                  onsubmit="return confirm('Yakin ingin menghapus dosen ini?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-medium transition-colors duration-150"
                                                        title="Hapus">
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? '5' : '4' }}" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500 font-medium">Tidak ada data dosen</p>
                                        <p class="text-gray-400 text-sm mt-1">Silakan tambahkan data dosen baru</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($dosens->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 flex justify-end">
                    {{ $dosens->links('vendor.pagination.custom') }}
                </div>
            @endif
        </div>
    </div>
@endsection