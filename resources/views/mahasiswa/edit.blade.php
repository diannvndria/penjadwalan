@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Edit Mahasiswa') }}
    </h2>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative mb-4 text-sm" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative mb-4 text-sm" role="alert">
                    <strong class="font-bold">Oops!</strong> Ada beberapa masalah dengan input Anda.
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.update', $mahasiswa->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="nim" class="block text-sm font-medium text-gray-700">NIM</label>
                    <input type="text" id="nim" name="nim" value="{{ old('nim', $mahasiswa->nim) }}" required autofocus
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    @error('nim') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $mahasiswa->nama) }}" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    @error('nama') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="angkatan" class="block text-sm font-medium text-gray-700">Angkatan</label>
                    <input type="number" id="angkatan" name="angkatan" value="{{ old('angkatan', $mahasiswa->angkatan) }}" required min="2000" max="{{ date('Y') }}"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                    @error('angkatan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="judul_skripsi" class="block text-sm font-medium text-gray-700">Judul Skripsi</label>
                    <textarea id="judul_skripsi" name="judul_skripsi" rows="3" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">{{ old('judul_skripsi', $mahasiswa->judul_skripsi) }}</textarea>
                    @error('judul_skripsi') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Input Profil Lulusan --}}
                <div>
                    <label for="profil_lulusan" class="block text-sm font-medium text-gray-700">Profil Lulusan</label>
                    <select id="profil_lulusan" name="profil_lulusan"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <option value="">Pilih Profil Lulusan</option>
                        <option value="Ilmuwan" {{ old('profil_lulusan', $mahasiswa->profil_lulusan) == 'Ilmuwan' ? 'selected' : '' }}>Ilmuwan</option>
                        <option value="Wirausaha" {{ old('profil_lulusan', $mahasiswa->profil_lulusan) == 'Wirausaha' ? 'selected' : '' }}>Wirausaha</option>
                        <option value="Profesional" {{ old('profil_lulusan', $mahasiswa->profil_lulusan) == 'Profesional' ? 'selected' : '' }}>Profesional</option>
                    </select>
                    @error('profil_lulusan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Input Penjurusan --}}
                <div>
                    <label for="penjurusan" class="block text-sm font-medium text-gray-700">Penjurusan</label>
                    <select id="penjurusan" name="penjurusan"
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <option value="">Pilih Penjurusan</option>
                        <option value="Sistem Informasi" {{ old('penjurusan', $mahasiswa->penjurusan) == 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                        <option value="Perekayasa Perangkat Lunak" {{ old('penjurusan', $mahasiswa->penjurusan) == 'Perekayasa Perangkat Lunak' ? 'selected' : '' }}>Perekayasa Perangkat Lunak</option>
                        <option value="Perekayasa Jaringan Komputer" {{ old('penjurusan', $mahasiswa->penjurusan) == 'Perekayasa Jaringan Komputer' ? 'selected' : '' }}>Perekayasa Jaringan Komputer</option>
                        <option value="Sistem Cerdas" {{ old('penjurusan', $mahasiswa->penjurusan) == 'Sistem Cerdas' ? 'selected' : '' }}>Sistem Cerdas</option>
                    </select>
                    @error('penjurusan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="id_dospem" class="block text-sm font-medium text-gray-700">Dosen Pembimbing</label>
                    <select id="id_dospem" name="id_dospem" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <option value="">Pilih Dosen</option>
                        @foreach($dosens as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('id_dospem', $mahasiswa->id_dospem) == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_dospem') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="siap_sidang" name="siap_sidang" value="1" {{ old('siap_sidang', $mahasiswa->siap_sidang) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="siap_sidang" class="ml-2 block text-sm text-gray-900">Siap Sidang</label>
                    @error('siap_sidang') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Status Prioritas Section --}}
                <div class="mt-6 p-4 border border-yellow-300 rounded-lg bg-yellow-50">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Status Prioritas</h3>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="hidden" name="is_prioritas" value="0">
                            <input type="checkbox" id="is_prioritas" name="is_prioritas" value="1" {{ old('is_prioritas', $mahasiswa->is_prioritas) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <label for="is_prioritas" class="ml-2 text-sm font-medium text-gray-900">
                                Mahasiswa Prioritas (akan mendapat ruang di lantai 1)
                            </label>
                        </div>

                        <div>
                            <label for="keterangan_prioritas" class="block text-sm font-medium text-gray-700 mb-1">
                                Keterangan Prioritas
                            </label>
                            <textarea id="keterangan_prioritas" name="keterangan_prioritas" rows="3"
                                placeholder="Contoh: Disabilitas fisik (kursi roda), kondisi kesehatan khusus, dll."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">{{ old('keterangan_prioritas', $mahasiswa->keterangan_prioritas) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Jelaskan alasan pemberian status prioritas (opsional)
                            </p>
                            @error('keterangan_prioritas') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Perbarui Mahasiswa
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection
