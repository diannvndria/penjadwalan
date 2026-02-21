@extends('layouts.app')

@section('header')
    {{ __('Daftar Penguji') }}
@endsection

@section('styles')


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
                <a href="{{ route('penguji.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-150 shadow-sm hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Penguji Baru
                </a>
            </div>
        @endif

        {{-- Bulk Actions --}}
        <div x-cloak x-show="selected.length > 0" x-transition.opacity class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-2 text-indigo-800 justify-center sm:justify-start">
                <i class="fas fa-check-square"></i>
                <span class="font-semibold text-sm sm:text-base" x-text="selected.length + ' Data Dipilih'"></span>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <button type="button" @click="clearSelections()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-semibold transition">
                     Batal
                </button>
                <button type="button" @click="showBulkDeleteModal(selected)" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2">
                    <i class="fas fa-trash"></i> Hapus
                </button>
                <form action="{{ route('penguji.bulk-export') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="ids" :value="selected.join(',')">
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                </form>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Table Header --}}
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-chalkboard-teacher text-gray-600 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Daftar Dosen Penguji</h3>
                        <span class="ml-2 sm:ml-3 px-2 sm:px-3 py-0.5 sm:py-1 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                            {{ $pengujis->total() }} Total
                        </span>
                    </div>
                </div>
            </div>


            {{-- Table View --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th scope="col" class="px-2 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-10">
                                <input type="checkbox" @change="toggleAll" :checked="allSelected" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-id-card text-gray-400 text-sm"></i>
                                    NIP
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user-tie text-gray-400 text-sm"></i>
                                    Nama Penguji
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-1">
                                    <i class="fas fa-user-check text-gray-400 text-sm"></i>
                                    Riwayat Penguji 1
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-1">
                                    <i class="fas fa-user-check text-gray-400 text-sm"></i>
                                    Riwayat Penguji 2
                                </div>
                            </th>
                            @if (Auth::user()->isAdmin())
                                <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center justify-center gap-1">
                                        <i class="fas fa-cog text-gray-400 text-sm"></i>
                                        Aksi
                                    </div>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($pengujis as $penguji)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-2 py-4 text-center">
                                    <input type="checkbox" @change="toggle('{{ $penguji->nip }}')" :checked="selected.includes('{{ $penguji->nip }}')" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $penguji->nip ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-semibold text-gray-900">{{ $penguji->nama }}</div>
                                            @if($penguji->is_prioritas)
                                                <span class="px-2.5 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200" title="{{ $penguji->keterangan_prioritas }}">
                                                    <i class="fas fa-star mr-1 text-yellow-600"></i>
                                                    Prioritas
                                                </span>
                                            @endif
                                        </div>
                                        @if($penguji->is_prioritas && $penguji->keterangan_prioritas)
                                            <div class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-info-circle mr-1"></i>{{ $penguji->keterangan_prioritas }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="inline-flex items-center justify-center">
                                        <span class="px-3 py-1.5 bg-green-50 text-green-700 text-sm font-semibold rounded-lg border border-green-200">
                                            {{ $penguji->munaqosahs_as_penguji1_count ?? 0 }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="inline-flex items-center justify-center">
                                        <span class="px-3 py-1.5 bg-purple-50 text-purple-700 text-sm font-semibold rounded-lg border border-purple-200">
                                            {{ $penguji->munaqosahs_as_penguji2_count ?? 0 }}
                                        </span>
                                    </div>
                                </td>
                                @if (Auth::user()->isAdmin())
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('penguji.edit', $penguji->nip) }}" class="inline-flex items-center px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-xs font-medium transition-colors duration-150 border border-blue-200">
                                                <i class="fas fa-edit mr-1"></i>
                                                Edit
                                            </a>
                                            <button type="button"
                                                onclick="showDeleteModal('{{ $penguji->nip }}', '{{ $penguji->nama }}')"
                                                class="inline-flex items-center px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 rounded text-xs font-medium transition-colors duration-150 border border-red-200">
                                                <i class="fas fa-trash-alt mr-1"></i>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? '6' : '5' }}" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-inbox text-4xl mb-3"></i>
                                        <p class="text-sm font-medium">Tidak ada data penguji</p>
                                        <p class="text-xs mt-1">Tambahkan penguji baru untuk memulai</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>




            {{-- Pagination --}}
            @if($pengujis->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <div class="flex justify-center sm:justify-end">
                        {{ $pengujis->links('vendor.pagination.custom') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="relative mx-auto p-4 sm:p-5 border w-full max-w-md shadow-2xl rounded-xl bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-base sm:text-lg font-medium text-gray-900" id="deleteModalTitle">Konfirmasi Hapus</h3>
                <div class="mt-2 px-4 sm:px-7 py-3">
                    <p class="text-sm text-gray-500" id="deleteModalMessage">
                        Apakah Anda yakin ingin menghapus penguji <span id="deleteItemName" class="font-semibold"></span>? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="items-center px-4 py-3 flex flex-col sm:flex-row justify-center gap-2 sm:gap-4">
                    <button id="confirmDeleteBtn" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white text-sm sm:text-base font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Ya, Hapus
                    </button>
                    <button id="cancelDeleteBtn" class="w-full sm:w-auto px-4 py-2 bg-gray-500 text-white text-sm sm:text-base font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Modal --}}
    <div id="bulkDeleteModal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="relative mx-auto p-4 sm:p-5 border w-full max-w-md shadow-2xl rounded-xl bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                </div>
                <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900 mt-4">Konfirmasi Hapus Masal</h3>
                <div class="mt-2 px-4 sm:px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Apakah Anda yakin ingin menghapus <span id="bulkDeleteCount" class="font-bold text-gray-800"></span> data penguji yang dipilih? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="items-center px-4 py-3 flex flex-col sm:flex-row justify-center gap-2 sm:gap-4 mt-4">
                    <button id="confirmBulkDeleteBtn" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white text-sm sm:text-base font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Ya, Hapus Semua
                    </button>
                    <button id="cancelBulkDeleteBtn" class="w-full sm:w-auto px-4 py-2 bg-gray-500 text-white text-sm sm:text-base font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden Form for Single Delete --}}
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Hidden Form for Bulk Delete --}}
    <form id="bulkDeleteForm" action="{{ route('penguji.bulk-delete') }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>
@endsection

@section('scripts')
<script>
    let pengujiToDeleteId = null;

    document.addEventListener('DOMContentLoaded', function() {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const deleteForm = document.getElementById('deleteForm');

        confirmDeleteBtn.addEventListener('click', function() {
            if (pengujiToDeleteId) {
                deleteForm.action = `/penguji/${pengujiToDeleteId}`;
                deleteForm.submit();
            }
            hideDeleteModal();
        });

        cancelDeleteBtn.addEventListener('click', function() {
            hideDeleteModal();
        });

        // Bulk Delete Logic
        const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
        const cancelBulkDeleteBtn = document.getElementById('cancelBulkDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        confirmBulkDeleteBtn.addEventListener('click', function() {
            bulkDeleteForm.submit();
        });

        cancelBulkDeleteBtn.addEventListener('click', function() {
            hideBulkDeleteModal();
        });

        // Expose function to global scope due to Alpine/Inline binding
        window.showBulkDeleteModal = function(selectedIds) {
            document.getElementById('bulkDeleteCount').textContent = selectedIds.length;
            document.getElementById('bulkDeleteIds').value = selectedIds.join(',');

            const modal = document.getElementById('bulkDeleteModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        window.hideBulkDeleteModal = function() {
            const modal = document.getElementById('bulkDeleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };
    });

    function showDeleteModal(id, itemName) {
        pengujiToDeleteId = id;
        document.getElementById('deleteItemName').textContent = itemName;
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pengujiToDeleteId = null;
    }
</script>
@endsection
