@extends('layouts.app')

@section('header')
    {{ __('Tambah Jadwal Penguji') }}
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Alert Messages --}}
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
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                <div class="flex items-center">
                    <i class="fas fa-calendar-plus text-indigo-600 mr-3"></i>
                    <h3 class="font-semibold text-gray-800">Form Tambah Jadwal Penguji</h3>
                </div>
            </div>

            {{-- Form Content --}}
            <form method="POST" action="{{ route('jadwal-penguji.store') }}" class="p-6">
                @csrf

                <div class="space-y-6">
                    {{-- Penguji Field --}}
                    <div>
                        <label for="id_penguji" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-tie text-gray-400 mr-2"></i>Penguji
                        </label>
                        <select id="id_penguji" name="id_penguji" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all">
                            <option value="">Pilih Penguji</option>
                            @foreach($pengujis as $penguji)
                                <option value="{{ $penguji->nip }}" {{ old('id_penguji') == $penguji->nip ? 'selected' : '' }}>{{ $penguji->nama }}</option>
                            @endforeach
                        </select>
                        @error('id_penguji')
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Tanggal Field --}}
                    <div>
                        <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-gray-400 mr-2"></i>Tanggal
                        </label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal') }}" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all">
                        @error('tanggal')
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- 2-Column Grid for Time --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="waktu_mulai" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-clock text-gray-400 mr-2"></i>Waktu Mulai
                            </label>
                            <input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all">
                            @error('waktu_mulai')
                                <span class="text-red-500 text-xs mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_selesai" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-clock text-gray-400 mr-2"></i>Waktu Selesai
                            </label>
                            <input type="time" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all">
                            @error('waktu_selesai')
                                <span class="text-red-500 text-xs mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    {{-- Deskripsi Field --}}
                    <div>
                        <label for="deskripsi" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-alt text-gray-400 mr-2"></i>Deskripsi (Opsional)
                        </label>
                        <input type="text" id="deskripsi" name="deskripsi" value="{{ old('deskripsi') }}"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all"
                            placeholder="Tambahkan catatan atau keterangan">
                        @error('deskripsi')
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Info Box --}}
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-lightbulb text-blue-500 mt-0.5 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-900 mb-1">Informasi Jadwal</h4>
                                <p class="text-xs text-blue-700">
                                    • Pilih penguji dari daftar yang tersedia<br>
                                    • Masukkan waktu kegiatan yang tidak tersedia untuk melakukan pengujian<br>
                                    • Deskripsi dapat digunakan untuk menambahkan catatan khusus
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('jadwal-penguji.index') }}" class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-semibold text-sm transition-all duration-150 shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
