@extends('layouts.app')

@section('header')
    {{ __('Daftar Pembimbing') }}
@endsection

@section('content')
    <div class="space-y-6" x-data='{
        selected: [],
        items: @json($allIds ?? []).map(id => String(id)),

        get allSelected() {
            return this.items.length > 0 && this.selected.length === this.items.length;
        },

        toggleAll() {
            if (this.allSelected) {
                this.selected = [];
            } else {
                this.selected = [...this.items];
            }
        },

        toggle(id) {
            id = String(id);
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(item => item !== id);
            } else {
                this.selected.push(id);
            }
        },

        clearSelections() {
            this.selected = [];
        }
    }'>
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

        {{-- Bulk Actions --}}
        <div x-cloak x-show="selected.length > 0" x-transition.opacity class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
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
                <form action="{{ route('dosen.bulk-export') }}" method="POST" target="_blank">
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
                            <th scope="col" class="px-2 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-10">
                                <input type="checkbox" @change="toggleAll" :checked="allSelected" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-id-card text-gray-400 text-sm"></i>
                                    NIP
                                </div>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user-tie text-gray-400 text-sm"></i>
                                    Nama Dosen
                                </div>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-1">
                                    <i class="fas fa-users text-gray-400 text-sm"></i>
                                    Jumlah Diampu
                                </div>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-1">
                                    <i class="fas fa-gavel text-gray-400 text-sm"></i>
                                    Riwayat Ketua Sidang
                                </div>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-1">
                                    <i class="fas fa-layer-group text-gray-400 text-sm"></i>
                                    Kapasitas
                                </div>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-1">
                                    <i class="fas fa-info-circle text-gray-400 text-sm"></i>
                                    Status
                                </div>
                            </th>
                            @if (Auth::user()->isAdmin())
                                <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center justify-center gap-1">
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
                                <td class="px-2 py-4 text-center">
                                    <input type="checkbox" @change="toggle({{ $dosen->id }})" :checked="selected.includes('{{ $dosen->id }}')" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-3 py-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $dosen->nip ?? '-' }}</p>
                                </td>
                                <td class="px-3 py-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $dosen->nama }}</p>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        {{ $dosen->jumlah_diampu_sekarang }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                        {{ $dosen->munaqosahs_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ $dosen->kapasitas_ampu > 0 ? $dosen->kapasitas_ampu : '∞' }}
                                    </span>
                                    @if ($dosen->kapasitas_ampu == 0)
                                        <span class="block text-xs text-gray-500">Tidak Terbatas</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                    @if ($dosen->kapasitas_ampu > 0 && $dosen->jumlah_diampu_sekarang >= $dosen->kapasitas_ampu)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Full
                                        </span>
                                    @else
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Tersedia
                                            </span>
                                            @if ($dosen->kapasitas_ampu > 0)
                                                <span class="text-xs text-gray-500">({{ $dosen->kapasitas_ampu - $dosen->jumlah_diampu_sekarang }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                @if (Auth::user()->isAdmin())
                                    <td class="px-3 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('dosen.edit', $dosen->id) }}"
                                               class="inline-flex items-center px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-xs font-medium transition-colors duration-150"
                                               title="Edit">
                                                <i class="fas fa-edit mr-1"></i>
                                                Edit
                                            </a>
                                            <button type="button"
                                                    onclick="showDeleteModal({{ $dosen->id }}, '{{ $dosen->nama }}')"
                                                    class="inline-flex items-center px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded text-xs font-medium transition-colors duration-150"
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
                                <td colspan="{{ Auth::user()->isAdmin() ? '8' : '7' }}" class="px-6 py-12 text-center">
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

    {{-- Delete Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="relative mx-auto p-5 border w-96 shadow-2xl rounded-xl bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900" id="deleteModalTitle">Konfirmasi Hapus</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="deleteModalMessage">
                        Apakah Anda yakin ingin menghapus dosen <span id="deleteItemName" class="font-semibold"></span>? Tindakan ini tidak dapat dibatalkan.
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

    {{-- Bulk Delete Modal --}}
    <div id="bulkDeleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="relative mx-auto p-5 border w-96 shadow-2xl rounded-xl bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900">Konfirmasi Hapus Multiple</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Apakah Anda yakin ingin menghapus <span id="bulkDeleteCount" class="font-semibold text-red-600"></span> data dosen? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="items-center px-4 py-3 flex justify-center space-x-4">
                    <button id="confirmBulkDeleteBtn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-auto hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Ya, Hapus Semua
                    </button>
                    <button id="cancelBulkDeleteBtn" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-auto hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form tersembunyi untuk bulk delete --}}
    <form id="bulkDeleteForm" method="POST" action="{{ route('dosen.bulk-delete') }}" style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>
@endsection

@section('scripts')
<script>
    let dosenToDeleteId = null;
    let selectedIdsForBulkDelete = [];

    document.addEventListener('DOMContentLoaded', function() {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const deleteForm = document.getElementById('deleteForm');

        const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
        const cancelBulkDeleteBtn = document.getElementById('cancelBulkDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        confirmDeleteBtn.addEventListener('click', function() {
            if (dosenToDeleteId) {
                // Set action form dan submit
                deleteForm.action = `/dosen/${dosenToDeleteId}`;
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
        dosenToDeleteId = id;
        document.getElementById('deleteItemName').textContent = itemName;
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        dosenToDeleteId = null;
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
</script>
@endsection
