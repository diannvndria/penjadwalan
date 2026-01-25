@extends('layouts.app')

@section('header')
    {{ __('Tambah Ruang Ujian Baru') }}
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

            {{-- Menampilkan error validasi --}}
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

            <form method="POST" action="{{ route('ruang-ujian.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Ruang</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required autofocus
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700">Lokasi</label>
                    <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi') }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('lokasi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="kapasitas" class="block text-sm font-medium text-gray-700">Kapasitas</label>
                    <input type="number" id="kapasitas" name="kapasitas" value="{{ old('kapasitas', 1) }}" required min="1"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('kapasitas') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="lantai" class="block text-sm font-medium text-gray-700">Lantai</label>
                    <select id="lantai" name="lantai" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" {{ old('lantai', 1) == $i ? 'selected' : '' }}>Lantai {{ $i }}</option>
                        @endfor
                    </select>
                    @error('lantai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <input type="hidden" name="is_aktif" value="0">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Aktif (Tersedia untuk digunakan)</span>
                    </label>
                    @error('is_aktif') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Status Prioritas Section --}}
                <div class="mt-4 p-4 border border-yellow-300 rounded-lg bg-yellow-50">
                    <input type="hidden" name="is_prioritas" value="0">
                    <label class="flex items-center">
                        <input type="checkbox" id="is_prioritas" name="is_prioritas" value="1" {{ old('is_prioritas') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-medium text-gray-900">
                            Ruang Prioritas (untuk mahasiswa/penguji prioritas)
                        </span>
                    </label>
                    <p class="mt-2 text-xs text-gray-600">
                        Ruang prioritas akan diprioritaskan untuk mahasiswa/penguji dengan status prioritas (disabilitas, kondisi khusus, dll.)
                    </p>
                    @error('is_prioritas') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end">
                    <a href="{{ route('ruang-ujian.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Simpan Ruang Ujian
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection
