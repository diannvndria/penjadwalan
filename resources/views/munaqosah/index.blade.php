@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Jadwal Sidang') }}
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
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Oops!</strong> Ada beberapa masalah dengan input Anda.
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (Auth::user()->isAdmin())
                <a href="{{ route('munaqosah.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                    Buat Jadwal Sidang
                </a>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penguji 1</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penguji 2</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($munaqosahs as $munaqosah)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $munaqosah->mahasiswa->nama ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $munaqosah->tanggal_munaqosah->format('d-m-Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ substr($munaqosah->waktu_mulai, 0, 5) }} - {{ substr($munaqosah->waktu_selesai, 0, 5) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $munaqosah->penguji1->nama ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $munaqosah->penguji2->nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $statusClass = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'dikonfirmasi' => 'bg-green-100 text-green-800',
                                            'ditolak' => 'bg-red-100 text-red-800',
                                        ][$munaqosah->status_konfirmasi] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst($munaqosah->status_konfirmasi) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if (Auth::user()->isAdmin())
                                        <a href="{{ route('munaqosah.edit', $munaqosah->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                        {{-- Ganti form onsubmit dengan panggilan fungsi JavaScript --}}
                                        <button type="button" onclick="showDeleteModal({{ $munaqosah->id }}, '{{ $munaqosah->mahasiswa->nama ?? 'Jadwal ini' }}')" class="text-red-600 hover:text-red-900 mr-2">Hapus</button>
                                    @endif
                                    <a href="{{ route('munaqosah.histori', $munaqosah->id) }}" class="text-blue-600 hover:text-blue-900">Histori</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">Tidak ada jadwal munaqosah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 bg-opacity-0 hidden flex items-start justify-center z-50 pt-20">
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
        // Gunakan kelas yang persis sama dengan yang Anda berikan
        document.getElementById('deleteModal').classList.remove('hidden'); 
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        munaqosahToDeleteId = null; // Reset ID setelah modal ditutup
    }
</script>
@endsection