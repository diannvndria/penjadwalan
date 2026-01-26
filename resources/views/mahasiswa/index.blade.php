@extends('layouts.app')

@section('header')
    {{ __('Data Mahasiswa') }}
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
        @if (session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 px-6 py-4 rounded-lg shadow-sm flex items-start" role="alert">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-lg shadow-sm flex items-start" role="alert">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-lg shadow-sm" role="alert">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                    <strong class="font-semibold">Oops!</strong>
                </div>
                <ul class="ml-6 mt-2 space-y-1 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filter and Action Bar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                {{-- Filters --}}
                <form action="{{ route('mahasiswa.index') }}" method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-filter text-gray-400"></i>
                        <label for="angkatan" class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter Angkatan:</label>
                        <select name="angkatan" id="angkatan" onchange="this.form.submit()"
                            class="block w-40 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 text-sm">
                            <option value="">Semua</option>
                            @foreach ($angkatans_tersedia as $a)
                                <option value="{{ $a }}" {{ (string)$angkatan === (string)$a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-tie text-gray-400"></i>
                        <label for="dospem_id" class="text-sm font-medium text-gray-700 whitespace-nowrap">Dosen Pembimbing:</label>
                        <select name="dospem_id" id="dospem_id" onchange="this.form.submit()"
                            class="block w-48 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 text-sm">
                            <option value="">Semua</option>
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ request('dospem_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                {{-- Add Button --}}
                @if (Auth::user()->isAdmin())
                    <a href="{{ route('mahasiswa.create') }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-150 shadow-sm hover:shadow-md">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Mahasiswa
                    </a>
                @endif
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Table Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-table text-gray-600 mr-3"></i>
                        <h3 class="font-semibold text-gray-800">Data Mahasiswa</h3>
                        <span class="ml-3 px-3 py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                            {{ $mahasiswas->total() }} Total
                        </span>
                    </div>
                </div>
            </div>

            {{-- Table Container --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-id-card text-gray-400 text-sm"></i>
                                    NIM
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user text-gray-400 text-sm"></i>
                                    Nama
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-calendar text-gray-400 text-sm"></i>
                                    Angkatan
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-chalkboard-teacher text-gray-400 text-sm"></i>
                                    Dospem
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-book text-gray-400 text-sm"></i>
                                    Judul Skripsi
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-graduation-cap text-gray-400 text-sm"></i>
                                    Profil Lulusan
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-code-branch text-gray-400 text-sm"></i>
                                    Penjurusan
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle text-gray-400 text-sm"></i>
                                    Status
                                </div>
                            </th>
                            @if (Auth::user()->isAdmin())
                                <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center justify-center gap-2">
                                        <i class="fas fa-cog text-gray-400 text-sm"></i>
                                        Aksi
                                    </div>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($mahasiswas as $mahasiswa)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900 font-mono">{{ $mahasiswa->nim }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($mahasiswa->nama, 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $mahasiswa->nama }}</p>
                                            @if($mahasiswa->is_prioritas)
                                                <span class="px-2.5 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200" title="{{ $mahasiswa->keterangan_prioritas }}">
                                                    <i class="fas fa-star mr-1 text-yellow-600"></i>
                                                    Prioritas
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $mahasiswa->angkatan }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-tie text-gray-400 mr-2"></i>
                                        <span class="text-sm text-gray-700">{{ $mahasiswa->dospem->nama ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-sm text-gray-700 line-clamp-2" title="{{ $mahasiswa->judul_skripsi }}">
                                        {{ $mahasiswa->judul_skripsi }}
                                    </p>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600">{{ $mahasiswa->profil_lulusan ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600">{{ $mahasiswa->penjurusan ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if($mahasiswa->siap_sidang)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Siap
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                            <i class="fas fa-clock mr-1"></i>
                                            Belum
                                        </span>
                                    @endif
                                </td>
                                @if (Auth::user()->isAdmin())
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('mahasiswa.edit', $mahasiswa->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition-colors duration-150"
                                               title="Edit">
                                                <i class="fas fa-edit mr-1"></i>
                                                Edit
                                            </a>
                                            <form action="{{ route('mahasiswa.destroy', $mahasiswa->id) }}" method="POST" 
                                                  onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini?');"
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
                                <td colspan="{{ Auth::user()->isAdmin() ? '9' : '8' }}" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500 font-medium">Tidak ada data mahasiswa</p>
                                        <p class="text-gray-400 text-sm mt-1">Silakan tambahkan data mahasiswa baru</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($mahasiswas->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 flex justify-end">
                    {{ $mahasiswas->links('vendor.pagination.custom') }}
                </div>
            @endif
        </div>
    </div>
@endsection
