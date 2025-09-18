@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Jadwal Munaqosah') }}
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

            <form method="POST" action="{{ route('munaqosah.update', $munaqosah->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="id_mahasiswa" class="block text-sm font-medium text-gray-700">Mahasiswa</label>
                    <select id="id_mahasiswa" name="id_mahasiswa" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @foreach($mahasiswasSiapSidang as $mahasiswa)
                            <option value="{{ $mahasiswa->id }}" {{ old('id_mahasiswa', $munaqosah->id_mahasiswa) == $mahasiswa->id ? 'selected' : '' }}>
                                {{ $mahasiswa->nama }} (NIM: {{ $mahasiswa->nim }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_mahasiswa') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="tanggal_munaqosah" class="block text-sm font-medium text-gray-700">Tanggal Munaqosah</label>
                    <input type="date" id="tanggal_munaqosah" name="tanggal_munaqosah" value="{{ old('tanggal_munaqosah', $munaqosah->tanggal_munaqosah->format('Y-m-d')) }}" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('tanggal_munaqosah') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">Waktu Mulai</label>
                        <input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai', substr($munaqosah->waktu_mulai, 0, 5)) }}" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @error('waktu_mulai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex-1">
                        <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">Waktu Selesai</label>
                        <input type="time" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai', substr($munaqosah->waktu_selesai, 0, 5)) }}" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @error('waktu_selesai') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="id_ruang_ujian" class="block text-sm font-medium text-gray-700">Ruang Uji</label>
                    <select id="id_ruang_ujian" name="id_ruang_ujian" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @foreach($ruangUjians as $ruang)
                            <option value="{{ $ruang->id }}" {{ old('id_ruang_ujian', $munaqosah->id_ruang_ujian) == $ruang->id ? 'selected' : '' }}>
                                {{ $ruang->nama }}{{ $ruang->lokasi ? ' - ' . $ruang->lokasi : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ruang_ujian') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="id_penguji1" class="block text-sm font-medium text-gray-700">Penguji 1</label>
                    <select id="id_penguji1" name="id_penguji1" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Pilih Penguji</option>
                        @foreach($pengujis as $penguji)
                            <option value="{{ $penguji->id }}" {{ old('id_penguji1', $munaqosah->id_penguji1) == $penguji->id ? 'selected' : '' }}>{{ $penguji->nama }}</option>
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
                            <option value="{{ $penguji->id }}" {{ old('id_penguji2', $munaqosah->id_penguji2) == $penguji->id ? 'selected' : '' }}>{{ $penguji->nama }}</option>
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
                            <option value="{{ $penguji->id }}" {{ old('id_penguji_utama', $munaqosah->id_penguji_utama) == $penguji->id ? 'selected' : '' }}>{{ $penguji->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_penguji_utama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                --}}

                <div>
                    <label for="status_konfirmasi" class="block text-sm font-medium text-gray-700">Status Konfirmasi</label>
                    <select id="status_konfirmasi" name="status_konfirmasi" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="pending" {{ old('status_konfirmasi', $munaqosah->status_konfirmasi) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="dikonfirmasi" {{ old('status_konfirmasi', $munaqosah->status_konfirmasi) == 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="ditolak" {{ old('status_konfirmasi', $munaqosah->status_konfirmasi) == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    @error('status_konfirmasi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Perbarui Jadwal Munaqosah
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection