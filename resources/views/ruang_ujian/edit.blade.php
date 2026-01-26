@extends('layouts.app')

@section('header')
    {{ __('Edit Ruang Ujian') }}
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
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-gray-100/50">
                <div class="flex items-center">
                    <i class="fas fa-edit text-gray-600 mr-3"></i>
                    <h3 class="font-semibold text-gray-800">Form Edit Ruang Ujian</h3>
                </div>
            </div>

            {{-- Form Content --}}
            <form method="POST" action="{{ route('ruang-ujian.update', $ruangUjian->id) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    {{-- Ruang Info Section --}}
                    <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    <i class="fas fa-door-open"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Sedang mengedit ruang:</p>
                                <p class="font-semibold text-gray-800">{{ $ruangUjian->nama }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Nama Ruang Field --}}
                    <div>
                        <label for="nama" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-door-closed text-gray-400 mr-2"></i>Nama Ruang
                        </label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama', $ruangUjian->nama) }}" required autofocus
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all"
                            placeholder="Contoh: Ruang 101, Lab Komputer A">
                        @error('nama') 
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span> 
                        @enderror
                    </div>

                    {{-- Lokasi Field --}}
                    <div>
                        <label for="lokasi" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>Lokasi
                        </label>
                        <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi', $ruangUjian->lokasi) }}"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all"
                            placeholder="Contoh: Gedung A, Sayap Kanan">
                        @error('lokasi') 
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span> 
                        @enderror
                    </div>

                    {{-- 2-Column Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Kapasitas Field --}}
                        <div>
                            <label for="kapasitas" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-users text-gray-400 mr-2"></i>Kapasitas
                            </label>
                            <input type="number" id="kapasitas" name="kapasitas" value="{{ old('kapasitas', $ruangUjian->kapasitas) }}" required min="1"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all"
                                placeholder="1">
                            <p class="mt-2 text-xs text-gray-500 flex items-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Jumlah maksimal orang dalam ruangan
                            </p>
                            @error('kapasitas') 
                                <span class="text-red-500 text-xs mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </span> 
                            @enderror
                        </div>

                        {{-- Lantai Field --}}
                        <div>
                            <label for="lantai" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-layer-group text-gray-400 mr-2"></i>Lantai
                            </label>
                            <select id="lantai" name="lantai" required
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ old('lantai', $ruangUjian->lantai) == $i ? 'selected' : '' }}>Lantai {{ $i }}</option>
                                @endfor
                            </select>
                            @error('lantai') 
                                <span class="text-red-500 text-xs mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </span> 
                            @enderror
                        </div>
                    </div>

                    {{-- Status Aktif Checkbox --}}
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <input type="hidden" name="is_aktif" value="0">
                        <label class="flex items-start">
                            <input type="checkbox" name="is_aktif" value="1" {{ old('is_aktif', $ruangUjian->is_aktif) ? 'checked' : '' }}
                                class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 focus:ring-2">
                            <span class="ml-3 text-sm font-medium text-gray-900">
                                <i class="fas fa-check-circle text-blue-600 mr-1"></i>
                                Aktif (Tersedia untuk digunakan)
                            </span>
                        </label>
                        <p class="ml-7 mt-1 text-xs text-gray-600">
                            Centang jika ruang siap digunakan untuk ujian
                        </p>
                        @error('is_aktif') 
                            <span class="text-red-500 text-xs mt-1 flex items-center ml-7">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span> 
                        @enderror
                    </div>

                    {{-- Status Prioritas Section --}}
                    <div class="p-5 border-2 border-yellow-200 rounded-lg bg-gradient-to-r from-yellow-50 to-amber-50">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-star text-yellow-600 mr-2"></i>
                            <h4 class="text-sm font-semibold text-gray-800">Status Prioritas</h4>
                        </div>
                        <input type="hidden" name="is_prioritas" value="0">
                        <label class="flex items-start">
                            <input type="checkbox" id="is_prioritas" name="is_prioritas" value="1" {{ old('is_prioritas', $ruangUjian->is_prioritas) ? 'checked' : '' }}
                                class="mt-1 w-4 h-4 rounded border-gray-300 text-yellow-600 shadow-sm focus:ring-yellow-500 focus:ring-2">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-900">
                                    Ruang Prioritas (untuk mahasiswa/penguji prioritas)
                                </span>
                                <p class="mt-1 text-xs text-gray-600">
                                    Ruang prioritas akan diprioritaskan untuk mahasiswa/penguji dengan status prioritas (disabilitas, kondisi khusus, dll.)
                                </p>
                            </div>
                        </label>
                        @error('is_prioritas') 
                            <span class="text-red-500 text-xs mt-1 flex items-center ml-7">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span> 
                        @enderror
                    </div>

                    {{-- Current Usage Info --}}
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-gray-500 mt-0.5 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 mb-1">Informasi Ruang</h4>
                                <p class="text-xs text-gray-600">
                                    • Ruang ini saat ini berada di <strong>Lantai {{ $ruangUjian->lantai }}</strong><br>
                                    • Status: <strong>{{ $ruangUjian->is_aktif ? 'Tersedia' : 'Tidak Tersedia' }}</strong><br>
                                    • Prioritas: <strong>{{ $ruangUjian->is_prioritas ? 'Ya' : 'Tidak' }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('ruang-ujian.index') }}" class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-semibold text-sm transition-all duration-150 shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i>
                        Update Ruang Ujian
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
