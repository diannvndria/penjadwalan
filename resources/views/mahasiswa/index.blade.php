@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Data Mahasiswa') }}
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
            @if (session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('info') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('error') }}
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

            {{-- Filter Angkatan (bisa diakses semua user) --}}
            <form action="{{ route('mahasiswa.index') }}" method="GET" class="mb-4 flex items-center space-x-2">
                <label for="angkatan" class="block text-sm font-medium text-gray-700">Filter Angkatan:</label>
                <select name="angkatan" id="angkatan" onchange="this.form.submit()"
                    class="mt-1 block w-48 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">Semua Angkatan</option>
                    @foreach ($angkatans_tersedia as $a)
                        <option value="{{ $a }}" {{ (string)$angkatan === (string)$a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </form>

            {{-- Tombol Tambah Mahasiswa (Hanya untuk Admin) --}}
            @if (Auth::user()->isAdmin())
                <a href="{{ route('mahasiswa.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                    Tambah Mahasiswa Baru
                </a>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Angkatan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dospem</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Skripsi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siap Sidang</th>
                            @if (Auth::user()->isAdmin()) {{-- Kolom Aksi hanya untuk Admin --}}
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($mahasiswas as $mahasiswa)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $mahasiswa->nim }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mahasiswa->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mahasiswa->angkatan }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mahasiswa->dospem->nama ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">{{ $mahasiswa->judul_skripsi }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $mahasiswa->siap_sidang ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $mahasiswa->siap_sidang ? 'Ya' : 'Belum' }}
                                    </span>
                                </td>
                                @if (Auth::user()->isAdmin()) {{-- Tombol Aksi hanya untuk Admin --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('mahasiswa.edit', $mahasiswa->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                        <form action="{{ route('mahasiswa.destroy', $mahasiswa->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() ? '7' : '6' }}" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">Tidak ada data mahasiswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $mahasiswas->links() }}
            </div>

        </div>
    </div>
@endsection