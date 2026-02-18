@extends('layouts.app')

@section('header')
    {{ __('Edit Jadwal Sidang') }}
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6 rounded-t-xl">
            <h3 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-edit mr-3"></i>
                Edit Jadwal Sidang
            </h3>
            <p class="text-indigo-100 text-sm mt-1">Perbarui informasi jadwal sidang mahasiswa</p>
        </div>

        <div class="p-8">
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

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900 mb-1">Jadwal Saat Ini</h4>
                        <p class="text-sm text-blue-800">{{ $munaqosah->mahasiswa->nama ?? 'N/A' }} - {{ $munaqosah->tanggal_munaqosah->format('d M Y') }}, {{ substr($munaqosah->waktu_mulai, 0, 5) }}-{{ substr($munaqosah->waktu_selesai, 0, 5) }}</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('munaqosah.update', $munaqosah->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="id_mahasiswa" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user-graduate mr-2 text-indigo-600"></i>
                        Mahasiswa
                    </label>
                    <select id="id_mahasiswa" name="id_mahasiswa" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        @foreach($mahasiswasSiapSidang as $mahasiswa)
                            <option value="{{ $mahasiswa->nim }}" {{ old('id_mahasiswa', $munaqosah->id_mahasiswa) == $mahasiswa->nim ? 'selected' : '' }}>
                                {{ $mahasiswa->nama }} (NIM: {{ $mahasiswa->nim }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_mahasiswa') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="tanggal_munaqosah" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-day mr-2 text-indigo-600"></i>
                        Tanggal Sidang
                    </label>
                    <input type="date" id="tanggal_munaqosah" name="tanggal_munaqosah" value="{{ old('tanggal_munaqosah', $munaqosah->tanggal_munaqosah->format('Y-m-d')) }}" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                    @error('tanggal_munaqosah') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="waktu_mulai" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock mr-2 text-indigo-600"></i>
                            Waktu Mulai
                        </label>
                        <input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai', substr($munaqosah->waktu_mulai, 0, 5)) }}" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        @error('waktu_mulai') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="waktu_selesai" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock mr-2 text-indigo-600"></i>
                            Waktu Selesai
                        </label>
                        <input type="time" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai', substr($munaqosah->waktu_selesai, 0, 5)) }}" required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        @error('waktu_selesai') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="id_ruang_ujian" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-door-open mr-2 text-indigo-600"></i>
                        Ruang Ujian
                    </label>
                    <select id="id_ruang_ujian" name="id_ruang_ujian" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        @foreach($ruangUjians as $ruang)
                            <option value="{{ $ruang->id }}" {{ old('id_ruang_ujian', $munaqosah->id_ruang_ujian) == $ruang->id ? 'selected' : '' }}>
                                {{ $ruang->nama }}{{ $ruang->lokasi ? ' - ' . $ruang->lokasi : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ruang_ujian') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="id_penguji1" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-chalkboard-teacher mr-2 text-indigo-600"></i>
                        Penguji 1
                    </label>
                    <select id="id_penguji1" name="id_penguji1" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        <option value="">Pilih Penguji</option>
                        @foreach($pengujis as $penguji)
                            <option value="{{ $penguji->id }}" {{ old('id_penguji1', $munaqosah->id_penguji1) == $penguji->id ? 'selected' : '' }}>{{ $penguji->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_penguji1') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="id_penguji2" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user-tie mr-2 text-indigo-600"></i>
                        Penguji 2 <span class="text-gray-500 text-xs ml-1">(Opsional)</span>
                    </label>
                    <select id="id_penguji2" name="id_penguji2"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        <option value="">Tidak Ada</option>
                        @foreach($pengujis as $penguji)
                            <option value="{{ $penguji->id }}" {{ old('id_penguji2', $munaqosah->id_penguji2) == $penguji->id ? 'selected' : '' }}>{{ $penguji->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_penguji2') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>

                {{-- --- Hapus input Penguji Utama --- --}}
                {{--
                <div class="mt-4">
                    <label for="id_penguji_utama" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-star mr-2 text-indigo-600"></i>
                        Penguji Utama (Opsional)
                    </label>
                    <select id="id_penguji_utama" name="id_penguji_utama"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        <option value="">Tidak Ada</option>
                        @foreach($pengujis as $penguji)
                            <option value="{{ $penguji->nip }}" {{ old('id_penguji_utama', $munaqosah->id_penguji_utama) == $penguji->nip ? 'selected' : '' }}>{{ $penguji->nama }}</option>
                        @endforeach
                    </select>
                    @error('id_penguji_utama') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>
                --}}

                <div>
                    <label for="status_konfirmasi" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-check-circle mr-2 text-indigo-600"></i>
                        Status Konfirmasi
                    </label>
                    <select id="status_konfirmasi" name="status_konfirmasi" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                        <option value="pending" {{ old('status_konfirmasi', $munaqosah->status_konfirmasi) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="dikonfirmasi" {{ old('status_konfirmasi', $munaqosah->status_konfirmasi) == 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="ditolak" {{ old('status_konfirmasi', $munaqosah->status_konfirmasi) == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    @error('status_konfirmasi') <span class="text-red-500 text-xs mt-1 block"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('munaqosah.index') }}" class="inline-flex items-center px-6 py-3 bg-gray-500 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 shadow-sm transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-sm transition">
                        <i class="fas fa-save mr-2"></i>
                        Perbarui Jadwal Sidang
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection