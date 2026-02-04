@extends('layouts.app')

@section('header')
    {{ __('Edit Mahasiswa') }}
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-lg shadow-sm flex items-start" role="alert">
                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                <span>{{ session('success') }}</span>
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

        {{-- Form Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Form Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/50">
                <div class="flex items-center">
                    <i class="fas fa-edit text-gray-600 mr-3"></i>
                    <h3 class="font-semibold text-gray-800">Form Edit Mahasiswa</h3>
                </div>
            </div>

            {{-- Form Content --}}
            <form method="POST" action="{{ route('mahasiswa.update', $mahasiswa->id) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- NIM Field --}}
                    <div>
                        <label for="nim" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-card text-gray-400 mr-2"></i>NIM
                        </label>
                        <input type="text" id="nim" name="nim" value="{{ old('nim', $mahasiswa->nim) }}" required autofocus
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all">
                        @error('nim') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>

                    {{-- Nama Lengkap Field --}}
                    <div>
                        <label for="nama" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-gray-400 mr-2"></i>Nama Lengkap
                        </label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama', $mahasiswa->nama) }}" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all">
                        @error('nama') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>

                    {{-- Angkatan Field --}}
                    <div>
                        <label for="angkatan" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-gray-400 mr-2"></i>Angkatan
                        </label>
                        <input type="number" id="angkatan" name="angkatan" value="{{ old('angkatan', $mahasiswa->angkatan) }}" required min="2000" max="{{ date('Y') }}"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all">
                        @error('angkatan') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>

                    {{-- Dosen Pembimbing Field --}}
                    <div>
                        <label for="id_dospem" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-chalkboard-teacher text-gray-400 mr-2"></i>Dosen Pembimbing
                        </label>
                        <select id="id_dospem" name="id_dospem" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all">
                            <option value="">Pilih Dosen</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('id_dospem', $mahasiswa->id_dospem) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_dospem') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Judul Skripsi Field --}}
                <div class="mt-6">
                    <label for="judul_skripsi" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-book text-gray-400 mr-2"></i>Judul Skripsi
                    </label>
                    <textarea id="judul_skripsi" name="judul_skripsi" rows="3" required
                        class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all">{{ old('judul_skripsi', $mahasiswa->judul_skripsi) }}</textarea>
                    @error('judul_skripsi') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    {{-- Profil Lulusan Field --}}
                    <div>
                        <label for="profil_lulusan" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-graduation-cap text-gray-400 mr-2"></i>Profil Lulusan
                        </label>
                        <select id="profil_lulusan" name="profil_lulusan"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all">
                            <option value="">Pilih Profil Lulusan</option>
                            <option value="Ilmuwan" {{ old('profil_lulusan', $mahasiswa->profil_lulusan) == 'Ilmuwan' ? 'selected' : '' }}>Ilmuwan</option>
                            <option value="Wirausaha" {{ old('profil_lulusan', $mahasiswa->profil_lulusan) == 'Wirausaha' ? 'selected' : '' }}>Wirausaha</option>
                            <option value="Profesional" {{ old('profil_lulusan', $mahasiswa->profil_lulusan) == 'Profesional' ? 'selected' : '' }}>Profesional</option>
                        </select>
                        @error('profil_lulusan') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>

                    {{-- Penjurusan Field --}}
                    <div>
                        <label for="penjurusan" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-code-branch text-gray-400 mr-2"></i>Penjurusan
                        </label>
                        <select id="penjurusan" name="penjurusan"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all">
                            <option value="">Pilih Penjurusan</option>
                            <option value="Sistem Informasi" {{ old('penjurusan', $mahasiswa->penjurusan) == 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                            <option value="Perekayasa Perangkat Lunak" {{ old('penjurusan', $mahasiswa->penjurusan) == 'Perekayasa Perangkat Lunak' ? 'selected' : '' }}>Perekayasa Perangkat Lunak</option>
                            <option value="Perekayasa Jaringan Komputer" {{ old('penjurusan', $mahasiswa->penjurusan) == 'Perekayasa Jaringan Komputer' ? 'selected' : '' }}>Perekayasa Jaringan Komputer</option>
                            <option value="Sistem Cerdas" {{ old('penjurusan', $mahasiswa->penjurusan) == 'Sistem Cerdas' ? 'selected' : '' }}>Sistem Cerdas</option>
                        </select>
                        @error('penjurusan') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Siap Sidang Checkbox --}}
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <input type="hidden" name="siap_sidang" value="0">
                        <input type="checkbox" id="siap_sidang" name="siap_sidang" value="1" {{ old('siap_sidang', $mahasiswa->siap_sidang) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 focus:ring-2">
                        <label for="siap_sidang" class="ml-3 flex items-center text-sm font-medium text-gray-900">
                            <i class="fas fa-check-circle text-blue-500 mr-2"></i>
                            Siap Sidang
                        </label>
                    </div>
                    @error('siap_sidang') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>

                {{-- Status Prioritas Section --}}
                <div class="mt-6 p-6 border border-yellow-300 rounded-xl bg-gradient-to-br from-yellow-50 to-amber-50">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-800">Status Prioritas</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start p-3 bg-white rounded-lg border border-yellow-200">
                            <input type="hidden" name="is_prioritas" value="0">
                            <input type="checkbox" id="is_prioritas" name="is_prioritas" value="1" {{ old('is_prioritas', $mahasiswa->is_prioritas) ? 'checked' : '' }}
                                class="w-5 h-5 text-yellow-600 rounded border-gray-300 focus:ring-yellow-500 focus:ring-2 mt-0.5">
                            <label for="is_prioritas" class="ml-3 text-sm font-medium text-gray-900">
                                Mahasiswa Prioritas (akan mendapat ruang di lantai 1)
                            </label>
                        </div>

                        <div>
                            <label for="keterangan_prioritas" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-comment-alt text-gray-400 mr-2"></i>Keterangan Prioritas
                            </label>
                            <textarea id="keterangan_prioritas" name="keterangan_prioritas" rows="3"
                                placeholder="Contoh: Disabilitas fisik (kursi roda), kondisi kesehatan khusus, dll."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50">{{ old('keterangan_prioritas', $mahasiswa->keterangan_prioritas) }}</textarea>
                            <p class="mt-2 text-xs text-gray-500 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Jelaskan alasan pemberian status prioritas (opsional)
                            </p>
                            @error('keterangan_prioritas') <span class="text-red-500 text-xs mt-1 flex items-center"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('mahasiswa.index') }}" class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-semibold text-sm transition-all duration-150 shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i>
                        Perbarui Mahasiswa
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
