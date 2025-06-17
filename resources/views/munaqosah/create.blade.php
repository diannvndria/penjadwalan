@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Buat Jadwal Munaqosah') }}
    </h2>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

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

            <form method="POST" action="{{ route('munaqosah.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="id_mahasiswa" class="block text-sm font-medium text-gray-700">Mahasiswa Siap Sidang</label>
                    <select id="id_mahasiswa" name="id_mahasiswa" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Pilih Mahasiswa</option>
                        @foreach($mahasiswasSiapSidang as $mahasiswa)
                            <option value="{{ $mahasiswa->id }}" {{ old('id_mahasiswa') == $mahasiswa->id ? 'selected' : '' }}>
                                {{ $mahasiswa->nama }} (NIM: {{ $mahasiswa->nim }}, Dospem: {{ $mahasiswa->dospem->nama ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_mahasiswa') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="tanggal_munaqosah" class="block text-sm font-medium text-gray-700">Tanggal Munaqosah</label>
                    <input type="date" id="tanggal_munaqosah" name="tanggal_munaqosah" value="{{ old('tanggal_munaqosah') }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('tanggal_munaqosah') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">Waktu Mulai</label>
                        <input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @error('waktu_mulai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex-1">
                        <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">Waktu Selesai</label>
                        <input type="time" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @error('waktu_selesai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="id_penguji1" class="block text-sm font-medium text-gray-700">Penguji 1</label>
                    <select id="id_penguji1" name="id_penguji1" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Pilih Penguji</option>
                        @foreach($pengujis as $penguji)
                            <option value="{{ $penguji->id }}" {{ old('id_penguji1') == $penguji->id ? 'selected' : '' }}>{{ $penguji->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_penguji1') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="id_penguji2" class="block text-sm font-medium text-gray-700">Penguji 2 (Opsional)</label>
                    <select id="id_penguji2" name="id_penguji2"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Tidak Ada</option>
                        @foreach($pengujis as $penguji)
                            <option value="{{ $penguji->id }}" {{ old('id_penguji2') == $penguji->id ? 'selected' : '' }}>{{ $penguji->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_penguji2') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- --- Hapus input Penguji Utama --- --}}
                {{--
                <div class="mt-4">
                    <label for="id_penguji_utama" class="block text-sm font-medium text-gray-700">Penguji Utama (Opsional)</label>
                    <select id="id_penguji_utama" name="id_penguji_utama"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Tidak Ada</option>
                        @foreach($pengujis as $penguji)
                            <option value="{{ $penguji->id }}" {{ old('id_penguji_utama') == $penguji->id ? 'selected' : '' }}>{{ $penguji->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_penguji_utama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                --}}

                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Simpan Jadwal Munaqosah
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection