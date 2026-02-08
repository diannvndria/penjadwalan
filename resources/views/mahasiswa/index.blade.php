@extends('layouts.app')

@section('header')
    {{ __('Data Mahasiswa') }}
@endsection

@section('styles')
<style>
    /* Limit penjurusan column width and apply ellipsis */
    table th:nth-child(7),
    table td:nth-child(7) {
        max-width: 150px !important;
        min-width: 150px !important;
        width: 150px !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }
    
    .truncate-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
        width: 100%;
    }
</style>
@endsection

@section('content')
    <div class="space-y-6" x-data='{
        selected: [],
        items: @json($allIds ?? []).map(id => String(id)),
        storageKey: "mahasiswa_selected",
        
        init() {
            // Load saved selections from localStorage
            const saved = localStorage.getItem(this.storageKey);
            if (saved) {
                try {
                    const savedArray = JSON.parse(saved);
                    // Filter out any IDs that no longer exist in items (deleted students)
                    this.selected = savedArray.filter(id => this.items.includes(String(id)));
                    // Save the filtered selection back to localStorage
                    this.saveToStorage();
                } catch (e) {
                    this.selected = [];
                }
            }
        },
        
        get allSelected() {
            return this.items.length > 0 && this.selected.length === this.items.length;
        },
        
        toggleAll() {
            if (this.allSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.items];
            }
            this.saveToStorage();
        },
        
        toggle(id) {
            id = String(id);
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(item => item !== id);
            } else {
                this.selected.push(id);
            }
            this.saveToStorage();
        },
        
        saveToStorage() {
            localStorage.setItem(this.storageKey, JSON.stringify(this.selected));
        },
        
        clearSelections() {
            this.selected = [];
            this.saveToStorage();
        }
    }'>
        {{-- Alert Messages --}}
        @if (session('success'))
            <div id="successAlert" class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-lg shadow-sm flex items-start transition-all duration-500" role="alert">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('info'))
            <div id="infoAlert" class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 px-6 py-4 rounded-lg shadow-sm flex items-start transition-all duration-500" role="alert">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div id="errorAlert" class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-lg shadow-sm flex items-start transition-all duration-500" role="alert">
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-6">
                {{-- Filters --}}
                <form id="filterForm" action="{{ route('mahasiswa.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
                    {{-- Filter Angkatan --}}
                    <div class="relative">
                        <label for="angkatan" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            <i class="fas fa-filter mr-1"></i> Angkatan
                        </label>
                        <select name="angkatan" id="angkatan" onchange="this.form.submit()"
                            class="block w-full border-gray-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm py-2">
                            <option value="">Semua Angkatan</option>
                            @foreach ($angkatans_tersedia as $a)
                                <option value="{{ $a }}" {{ (string)$angkatan === (string)$a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Dosen --}}
                    <div class="relative">
                        <label for="dospem_id" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            <i class="fas fa-user-tie mr-1"></i> Dosen Pembimbing
                        </label>
                        <select name="dospem_id" id="dospem_id" onchange="this.form.submit()"
                            class="block w-full border-gray-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm py-2">
                            <option value="">Semua Dosen</option>
                            @foreach ($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ request('dospem_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Status Sidang --}}
                    <div class="relative">
                        <label for="status_sidang" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            <i class="fas fa-clipboard-check mr-1"></i> Status Sidang
                        </label>
                        <select name="status_sidang" id="status_sidang" onchange="this.form.submit()"
                            class="block w-full border-gray-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm py-2">
                            <option value="">Semua Status</option>
                            <option value="Belum" {{ request('status_sidang') == 'Belum' ? 'selected' : '' }}>Belum Siap</option>
                            <option value="Siap" {{ request('status_sidang') == 'Siap' ? 'selected' : '' }}>Siap Sidang</option>
                        </select>
                    </div>

                    {{-- Search --}}
                    <div class="relative">
                        <label for="search" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            <i class="fas fa-search mr-1"></i> Pencarian
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Cari Nama / NIM..." 
                                   autocomplete="off"
                                   class="block w-full border-gray-200 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm py-2 pr-10">
                            @if(request('search'))
                                <button type="button" 
                                        onclick="clearSearch()" 
                                        class="absolute inset-y-0 right-0 top-0 bottom-0 px-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-search"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Hidden inputs to maintain state when searching via enter key --}}
                    @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                    @if(request('direction')) <input type="hidden" name="direction" value="{{ request('direction') }}"> @endif
                </form>

                {{-- Action Buttons --}}
                @if (Auth::user()->isAdmin())
                    <div class="flex flex-col sm:flex-row gap-3 pt-1 xl:pt-0 w-full xl:w-auto">
                        <button type="button" onclick="showImportModal()" 
                            class="flex-1 xl:flex-none inline-flex items-center justify-center px-4 py-2 bg-white border border-green-600 rounded-lg font-semibold text-sm text-green-700 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-150 shadow-sm group">
                            <i class="fas fa-file-import mr-2 text-green-600 group-hover:text-green-700"></i>
                            <span>Import CSV</span>
                        </button>
                        <a href="{{ route('mahasiswa.create') }}" 
                            class="flex-1 xl:flex-none inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-150 shadow-sm shadow-indigo-200">
                            <i class="fas fa-plus mr-2"></i>
                            <span>Tambah Data</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Bulk Actions --}}
        <div x-show="selected.length > 0" x-transition.opacity class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-indigo-800">
                <i class="fas fa-check-square"></i>
                <span class="font-semibold" x-text="selected.length + ' Data Dipilih'"></span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="clearSelections()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-semibold transition">
                     Batal
                </button>
                <button type="button" @click="showBulkDeleteModal(selected)" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-trash"></i> Hapus
                </button>
                <form action="{{ route('mahasiswa.bulk-export') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="ids" :value="selected.join(',')">
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition flex items-center gap-2">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                </form>
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
                            <th scope="col" class="px-2 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-10">
                                <input type="checkbox" @change="toggleAll" :checked="allSelected" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <a href="{{ route('mahasiswa.index', ['sort' => 'nim', 'direction' => $sortField === 'nim' && $sortDirection === 'asc' ? 'desc' : 'asc', 'angkatan' => request('angkatan'), 'dospem_id' => request('dospem_id'), 'status_sidang' => request('status_sidang'), 'search' => request('search')]) }}" class="flex items-center gap-2 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-id-card text-gray-400 text-sm"></i>
                                    NIM
                                    @if($sortField === 'nim')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-300 text-xs"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <a href="{{ route('mahasiswa.index', ['sort' => 'nama', 'direction' => $sortField === 'nama' && $sortDirection === 'asc' ? 'desc' : 'asc', 'angkatan' => request('angkatan'), 'dospem_id' => request('dospem_id'), 'status_sidang' => request('status_sidang'), 'search' => request('search')]) }}" class="flex items-center gap-2 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-user text-gray-400 text-sm"></i>
                                    Nama
                                    @if($sortField === 'nama')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-300 text-xs"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <a href="{{ route('mahasiswa.index', ['sort' => 'angkatan', 'direction' => $sortField === 'angkatan' && $sortDirection === 'asc' ? 'desc' : 'asc', 'angkatan' => request('angkatan'), 'dospem_id' => request('dospem_id'), 'status_sidang' => request('status_sidang'), 'search' => request('search')]) }}" class="flex items-center justify-center gap-2 hover:text-blue-600 transition-colors">
                                    <i class="fas fa-calendar text-gray-400 text-sm"></i>
                                    Angkatan
                                    @if($sortField === 'angkatan')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-600 text-xs"></i>
                                    @else
                                        <i class="fas fa-sort text-gray-300 text-xs"></i>
                                    @endif
                                </a>
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
                                <td class="px-2 py-4 text-center">
                                    <input type="checkbox" @change="toggle({{ $mahasiswa->id }})" :checked="selected.includes('{{ $mahasiswa->id }}')" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900 font-mono">{{ $mahasiswa->nim }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center">
                                        <div>
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
                                <td class="px-4 py-4">
                                    <span class="text-sm text-gray-600 truncate-text" title="{{ $mahasiswa->penjurusan ?? '-' }}">{{ $mahasiswa->penjurusan ?? '-' }}</span>
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
                                            <button type="button" 
                                                    onclick="showDeleteModal({{ $mahasiswa->id }}, '{{ $mahasiswa->nama }}')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-medium transition-colors duration-150"
                                                    title="Hapus">
                                                <i class="fas fa-trash-alt mr-1"></i>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? '10' : '9' }}" class="px-6 py-12 text-center">
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
                    {{ $mahasiswas->appends(['sort' => $sortField, 'direction' => $sortDirection, 'angkatan' => request('angkatan'), 'dospem_id' => request('dospem_id'), 'status_sidang' => request('status_sidang'), 'search' => request('search')])->links('vendor.pagination.custom') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="relative mx-auto p-5 border w-96 shadow-2xl rounded-xl bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900" id="deleteModalTitle">Konfirmasi Hapus</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="deleteModalMessage">
                        Apakah Anda yakin ingin menghapus mahasiswa <span id="deleteItemName" class="font-semibold"></span>? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="items-center px-4 py-3 flex justify-center space-x-4">
                    <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-auto hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Ya, Hapus
                    </button>
                    <button id="cancelDeleteBtn" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-auto hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form tersembunyi untuk submit penghapusan --}}
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Import CSV Modal --}}
    <div id="importModal" class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300">
        <div class="relative mx-auto bg-white w-full max-w-lg shadow-2xl rounded-2xl overflow-hidden transform transition-all scale-100">
            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                        <i class="fas fa-file-csv text-sm"></i>
                    </div>
                    Import Data Mahasiswa
                </h3>
                <button type="button" onclick="hideImportModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-2 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="importForm" action="{{ route('mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="p-6 space-y-6">
                    <!-- Info Alert -->
                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                        <div class="flex gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                            <div class="text-sm text-blue-800 space-y-2">
                                <p class="font-medium">Panduan Import:</p>
                                <p class="text-blue-600/80 leading-relaxed">
                                    Pastikan file Anda sesuai format template. NIP Dosen harus sesuai dengan data database.
                                </p>
                                <a href="{{ route('mahasiswa.download-template') }}" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-700 hover:underline mt-1 group">
                                    <span>Download Template CSV</span>
                                    <i class="fas fa-arrow-right text-xs ml-1 transform group-hover:translate-x-0.5 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload Area -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700">Upload File CSV</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-400 hover:bg-gray-50 transition-all group relative cursor-pointer">
                            <div class="space-y-1 text-center">
                                <div class="mx-auto w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-cloud-upload-alt text-indigo-500 text-xl"></i>
                                </div>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="csv_file" class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                        <span>Pilih file</span>
                                        <input id="csv_file" name="csv_file" type="file" accept=".csv" class="sr-only" required onchange="updateFileName(this)">
                                    </label>
                                    <p class="pl-1">atau drag & drop</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">File CSV hingga 10MB</p>
                                <p id="file-name" class="text-sm font-medium text-green-600 mt-3 hidden flex items-center justify-center bg-green-50 py-1 px-3 rounded-full border border-green-100">
                                    <i class="fas fa-check-circle mr-1.5"></i> <span id="file-name-text"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Accordion for Detailed Requirements -->
                     <div x-data="{ expanded: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                        <button type="button" @click="expanded = !expanded" class="w-full px-4 py-3 bg-gray-50 flex justify-between items-center text-xs font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                            <span>Lihat Detail Kolom yang Dibutuhkan</span>
                            <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'transform rotate-180': expanded }"></i>
                        </button>
                        <div x-show="expanded" x-collapse class="px-4 py-3 bg-white border-t border-gray-200 text-xs">
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                <div class="flex items-center gap-2"><span class="font-semibold text-gray-700 w-20">NIM</span> <span class="text-gray-500">Wajib, Unik</span></div>
                                <div class="flex items-center gap-2"><span class="font-semibold text-gray-700 w-20">Nama</span> <span class="text-gray-500">Wajib</span></div>
                                <div class="flex items-center gap-2"><span class="font-semibold text-gray-700 w-20">Angkatan</span> <span class="text-gray-500">Wajib (Tahun)</span></div>
                                <div class="flex items-center gap-2"><span class="font-semibold text-gray-700 w-20">NIP Dospem</span> <span class="text-gray-500">Wajib (DB)</span></div>
                                <div class="flex items-center gap-2"><span class="font-semibold text-gray-700 w-20">Judul</span> <span class="text-gray-500">Wajib</span></div>
                                <div class="flex items-center gap-2"><span class="font-semibold text-gray-700 w-20">Lainnya</span> <span class="text-gray-500">Opsional</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button type="button" 
                            onclick="hideImportModal()"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all shadow-sm">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 border border-transparent text-white rounded-lg text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-md shadow-indigo-200 flex items-center">
                        <i class="fas fa-file-import mr-2"></i>
                        Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Delete Modal --}}
    <div id="bulkDeleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="relative mx-auto p-5 border w-96 shadow-2xl rounded-xl bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Konfirmasi Hapus</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="bulkDeleteMessage">
                        Apakah Anda yakin ingin menghapus <span id="bulkDeleteCount" class="font-semibold"></span> data mahasiswa yang dipilih? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="items-center px-4 py-3 flex justify-center space-x-4">
                    <button id="confirmBulkDeleteBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-auto hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Ya, Hapus
                    </button>
                    <button id="cancelBulkDeleteBtn" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-auto hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form tersembunyi untuk bulk delete --}}
    <form id="bulkDeleteForm" method="POST" style="display: none;" action="{{ route('mahasiswa.bulk-delete') }}">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>
@endsection

@section('scripts')
<script>
    let mahasiswaToDeleteId = null;
    let selectedIdsForBulkDelete = [];

    // Clear localStorage BEFORE Alpine initializes if there was a successful deletion
    @if(session('success') && strpos(session('success'), 'Berhasil menghapus') !== false)
        localStorage.removeItem('mahasiswa_selected');
    @endif

    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('search');
        const filterForm = document.getElementById('filterForm');
        
        if (searchInput && filterForm) {
            // Submit form when Enter is pressed in search field
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterForm.submit();
                }
            });
        }

        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const deleteForm = document.getElementById('deleteForm');

        const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
        const cancelBulkDeleteBtn = document.getElementById('cancelBulkDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        // Auto dismiss alerts after 2 seconds
        const successAlert = document.getElementById('successAlert');
        const infoAlert = document.getElementById('infoAlert');
        const errorAlert = document.getElementById('errorAlert');
        
        function dismissAlert(alertElement) {
            if (alertElement) {
                setTimeout(() => {
                    alertElement.style.opacity = '0';
                    alertElement.style.transform = 'translateY(-20px)';
                    setTimeout(() => {
                        alertElement.remove();
                    }, 500);
                }, 2000);
            }
        }
        
        dismissAlert(successAlert);
        dismissAlert(infoAlert);
        dismissAlert(errorAlert);

        // Trigger Alpine.js to reload selections after localStorage is cleared
        @if(session('success') && strpos(session('success'), 'Berhasil menghapus') !== false)
            window.dispatchEvent(new Event('storage'));
        @endif

        confirmDeleteBtn.addEventListener('click', function() {
            if (mahasiswaToDeleteId) {
                // Set action form dan submit
                deleteForm.action = `/mahasiswa/${mahasiswaToDeleteId}`;
                deleteForm.submit();
            }
            hideDeleteModal();
        });

        cancelDeleteBtn.addEventListener('click', function() {
            hideDeleteModal();
        });

        confirmBulkDeleteBtn.addEventListener('click', function() {
            if (selectedIdsForBulkDelete.length > 0) {
                document.getElementById('bulkDeleteIds').value = selectedIdsForBulkDelete.join(',');
                bulkDeleteForm.submit();
            }
            hideBulkDeleteModal();
        });

        cancelBulkDeleteBtn.addEventListener('click', function() {
            hideBulkDeleteModal();
        });
    });

    function showDeleteModal(id, itemName) {
        mahasiswaToDeleteId = id;
        document.getElementById('deleteItemName').textContent = itemName;
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        mahasiswaToDeleteId = null;
    }

    function showBulkDeleteModal(ids) {
        selectedIdsForBulkDelete = ids;
        document.getElementById('bulkDeleteCount').textContent = ids.length;
        const modal = document.getElementById('bulkDeleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideBulkDeleteModal() {
        const modal = document.getElementById('bulkDeleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        selectedIdsForBulkDelete = [];
    }

    function showImportModal() {
        const modal = document.getElementById('importModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideImportModal() {
        const modal = document.getElementById('importModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('importForm').reset();
    }

    function clearSearch() {
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.value = '';
            document.getElementById('filterForm').submit();
        }
    }

    function updateFileName(input) {
        const fileNameElement = document.getElementById('file-name');
        const fileNameText = document.getElementById('file-name-text');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes

            if (file.size > maxSize) {
                alert('Ukuran file melebihi batas maksimum 10MB. Silakan pilih file yang lebih kecil.');
                input.value = ''; // Reset the input
                fileNameElement.classList.add('hidden');
                return;
            }

            fileNameText.textContent = file.name;
            fileNameElement.classList.remove('hidden');
        } else {
            fileNameElement.classList.add('hidden');
        }
    }
</script>
@endsection
