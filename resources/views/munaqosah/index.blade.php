@extends('layouts.app')

@section('header')
    {{ __('Jadwal Sidang') }}
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-8">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative mb-6 flex items-center" role="alert">
                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                        <strong class="font-bold">Oops!</strong>
                        <span class="ml-1">Ada beberapa masalah dengan input Anda.</span>
                    </div>
                    <ul class="mt-2 ml-6 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (Auth::user()->isAdmin())
                <a href="{{ route('munaqosah.create') }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-150 shadow-sm hover:shadow-md mb-6">
                    <i class="fas fa-plus mr-2"></i>
                    Buat Jadwal Sidang
                </a>
            @endif

            <div class="bg-gradient-to-r from-gray-50 to-gray-100/50 rounded-lg p-6 mb-6 border border-gray-200">
                <form method="GET" action="{{ route('munaqosah.index') }}">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div>
                            <label for="start_date" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-2 text-indigo-600"></i>
                                Tanggal Mulai
                            </label>
                            <input type="date" id="start_date" name="start_date" value="{{ $startDate ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        </div>
                        <div>
                            <label for="end_date" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-check mr-2 text-indigo-600"></i>
                                Tanggal Selesai
                            </label>
                            <input type="date" id="end_date" name="end_date" value="{{ $endDate ?? '' }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                            <a href="{{ route('munaqosah.index') }}" class="inline-flex items-center px-5 py-2.5 bg-gray-500 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 shadow-sm transition">
                                <i class="fas fa-redo mr-2"></i>Reset
                            </a>
                            <a href="{{ route('munaqosah.downloadReport', ['start_date' => $startDate ?? '', 'end_date' => $endDate ?? '']) }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm transition">
                                <i class="fas fa-file-pdf mr-2"></i>Download
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-visible shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100/50">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-graduate text-gray-400 text-sm"></i>
                                    Mahasiswa
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar-day text-gray-400 text-sm"></i>
                                    Tanggal
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-gray-400 text-sm"></i>
                                    Waktu
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-chalkboard-teacher text-gray-400 text-sm"></i>
                                    Penguji 1
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-tie text-gray-400 text-sm"></i>
                                    Penguji 2
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-door-open text-gray-400 text-sm"></i>
                                    Ruang
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-info-circle text-gray-400 text-sm"></i>
                                    Status
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-cog text-gray-400 text-sm"></i>
                                    Aksi
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($munaqosahs as $munaqosah)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                            {{ strtoupper(substr($munaqosah->mahasiswa->nama ?? 'N', 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-semibold text-gray-900">{{ $munaqosah->mahasiswa->nama ?? 'N/A' }}</div>
                                            @if($munaqosah->mahasiswa && $munaqosah->mahasiswa->is_prioritas)
                                                <div class="mt-1">
                                                    <span class="px-2.5 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                        <i class="fas fa-star mr-1 text-yellow-600"></i>
                                                        Prioritas
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200">
                                        <i class="fas fa-calendar-day mr-1.5"></i>{{ $munaqosah->tanggal_munaqosah->format('d-m-Y') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-green-50 text-green-800 border border-green-200">
                                        <i class="fas fa-clock mr-1.5"></i>{{ substr($munaqosah->waktu_mulai, 0, 5) }} - {{ substr($munaqosah->waktu_selesai, 0, 5) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                            {{ strtoupper(substr($munaqosah->penguji1->nama ?? 'N', 0, 1)) }}
                                        </div>
                                        <span class="ml-2 text-sm text-gray-900">{{ $munaqosah->penguji1->nama ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($munaqosah->penguji2)
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                                {{ strtoupper(substr($munaqosah->penguji2->nama, 0, 1)) }}
                                            </div>
                                            <span class="ml-2 text-sm text-gray-900">{{ $munaqosah->penguji2->nama }}</span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $munaqosah->ruangUjian->nama ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-200', 'icon' => 'fa-hourglass-half'],
                                            'dikonfirmasi' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200', 'icon' => 'fa-check-circle'],
                                            'ditolak' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200', 'icon' => 'fa-times-circle'],
                                        ][$munaqosah->status_konfirmasi] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-200', 'icon' => 'fa-question-circle'];
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} border {{ $statusConfig['border'] }}">
                                        <i class="fas {{ $statusConfig['icon'] }} mr-2"></i>{{ ucfirst($munaqosah->status_konfirmasi) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="relative" x-data="{ open: false, dropUp: false }" x-init="$watch('open', value => { if(value) { const rect = $el.getBoundingClientRect(); dropUp = (window.innerHeight - rect.bottom) < 200; } })">
                                        <button @click="open = !open" class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" title="Menu">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                            </svg>
                                        </button>

                                        <div x-show="open"
                                             x-cloak
                                             @click.away="open = false"
                                             :class="dropUp ? 'origin-bottom-right bottom-full mb-2' : 'origin-top-right top-full mt-2'"
                                             class="absolute right-0 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50"
                                             role="menu"
                                             aria-orientation="vertical"
                                             aria-labelledby="menu-button"
                                             tabindex="-1">
                                            <div class="py-1" role="none">
                                                <a href="{{ route('munaqosah.histori', $munaqosah->id) }}"
                                                   class="flex items-center px-4 py-2.5 text-sm text-blue-600 hover:bg-blue-50 transition"
                                                   role="menuitem">
                                                    <i class="fas fa-history mr-3 w-4"></i>Histori
                                                </a>
                                                @if (Auth::user()->isAdmin())
                                                    <a href="{{ route('munaqosah.edit', $munaqosah->id) }}"
                                                       class="flex items-center px-4 py-2.5 text-sm text-indigo-600 hover:bg-indigo-50 transition"
                                                       role="menuitem">
                                                        <i class="fas fa-edit mr-3 w-4"></i>Edit
                                                    </a>
                                                    <button type="button"
                                                            onclick="showDeleteModal({{ $munaqosah->id }}, '{{ $munaqosah->mahasiswa->nama ?? 'Jadwal ini' }}')"
                                                            class="flex items-center w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition"
                                                            role="menuitem">
                                                        <i class="fas fa-trash mr-3 w-4"></i>Hapus
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i class="fas fa-calendar-times text-5xl mb-3"></i>
                                        <p class="text-sm font-medium">Tidak ada jadwal sidang.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 flex justify-end">
        {{ $munaqosahs->links('vendor.pagination.custom') }}
    </div>

    <div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-start justify-center z-50 pt-20">
        <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg font-medium text-gray-900" id="deleteModalTitle">Konfirmasi Hapus</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="deleteModalMessage">
                        Apakah Anda yakin ingin menghapus jadwal sidang untuk <span id="deleteItemName" class="font-semibold"></span>? Tindakan ini tidak dapat dibatalkan.
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

@endsection

@section('scripts')
<script>
    let munaqosahToDeleteId = null;

    document.addEventListener('DOMContentLoaded', function() {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const deleteForm = document.getElementById('deleteForm');

        confirmDeleteBtn.addEventListener('click', function() {
            if (munaqosahToDeleteId) {
                // Set action form dan submit
                // Pastikan route ini sesuai dengan route delete di web.php Anda
                deleteForm.action = `/munaqosah/${munaqosahToDeleteId}`;
                deleteForm.submit();
            }
            hideDeleteModal();
        });

        cancelDeleteBtn.addEventListener('click', function() {
            hideDeleteModal();
        });
    });

    function showDeleteModal(id, itemName) {
        munaqosahToDeleteId = id;
        document.getElementById('deleteItemName').textContent = itemName;
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        munaqosahToDeleteId = null; // Reset ID setelah modal ditutup
    }
</script>
@endsection
