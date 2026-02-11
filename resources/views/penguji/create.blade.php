@extends('layouts.app')

@section('header')
    {{ __('Tambah Penguji Baru') }}
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
                    <i class="fas fa-user-plus text-indigo-600 mr-3"></i>
                    <h3 class="font-semibold text-gray-800">Form Tambah Dosen Penguji Baru</h3>
                </div>
            </div>

            {{-- Form Content --}}
            <form method="POST" action="{{ route('penguji.store') }}" class="p-6">
                @csrf

                <div class="space-y-6">
                    {{-- NIP Field --}}
                    <div>
                        <label for="nip" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-card text-gray-400 mr-2"></i>NIP
                        </label>
                        <input type="text" id="nip" name="nip" value="{{ old('nip') }}"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all"
                            placeholder="Masukkan NIP (Opsional)">
                        <p class="mt-1 text-xs text-gray-500">
                            * NIP bersifat opsional dan dapat diisi nanti
                        </p>
                        @error('nip') 
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span> 
                        @enderror
                    </div>

                    {{-- Nama Penguji Field --}}
                    <div>
                        <label for="nama" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-tie text-gray-400 mr-2"></i>Nama Penguji
                        </label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required autofocus
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all"
                            placeholder="Masukkan nama lengkap penguji">
                        @error('nama') 
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span> 
                        @enderror
                    </div>

                    {{-- Status Prioritas Section --}}
                    <div class="p-5 border-2 border-yellow-200 rounded-lg bg-gradient-to-r from-yellow-50 to-amber-50">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-star text-yellow-600 mr-2"></i>
                            <h3 class="text-base font-semibold text-gray-800">Status Prioritas</h3>
                        </div>

                        <div class="space-y-4">
                            {{-- Checkbox Prioritas --}}
                            <div class="flex items-start">
                                <input type="hidden" name="is_prioritas" value="0">
                                <input type="checkbox" id="is_prioritas" name="is_prioritas" value="1" {{ old('is_prioritas') ? 'checked' : '' }}
                                    class="mt-1 w-4 h-4 text-yellow-600 rounded border-gray-300 focus:ring-yellow-500 focus:ring-2">
                                <label for="is_prioritas" class="ml-3 text-sm font-medium text-gray-900">
                                    Penguji Prioritas (akan mendapat ruang di lantai 1)
                                </label>
                            </div>

                            {{-- Keterangan Prioritas --}}
                            <div>
                                <label for="keterangan_prioritas" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-comment-alt text-gray-400 mr-2"></i>Keterangan Prioritas
                                </label>
                                <textarea id="keterangan_prioritas" name="keterangan_prioritas" rows="3"
                                    placeholder="Contoh: Dosen senior, keterbatasan mobilitas, kondisi kesehatan, dll."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50 transition-all">{{ old('keterangan_prioritas') }}</textarea>
                                <p class="mt-2 text-xs text-gray-500 flex items-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Jelaskan alasan pemberian status prioritas (opsional)
                                </p>
                                @error('keterangan_prioritas') 
                                    <span class="text-red-500 text-xs mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                    </span> 
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Info Box --}}
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-lightbulb text-blue-500 mt-0.5 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-900 mb-1">Informasi Status Prioritas</h4>
                                <p class="text-xs text-blue-700">
                                    • Penguji prioritas akan mendapatkan ruang ujian di lantai 1<br>
                                    • Status ini biasanya diberikan untuk dosen senior atau dengan kondisi khusus<br>
                                    • Keterangan prioritas membantu panitia memahami alasan pemberian status
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('penguji.index') }}" class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-semibold text-sm transition-all duration-150 shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Penguji
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
