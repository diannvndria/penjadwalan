@extends('layouts.app')

@section('header')
    {{ __('Edit Dosen') }}
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
                    <i class="fas fa-user-edit text-indigo-600 mr-3"></i>
                    <h3 class="font-semibold text-gray-800">Form Edit Dosen Pembimbing</h3>
                </div>
            </div>

            {{-- Form Content --}}
            <form method="POST" action="{{ route('dosen.update', $dosen->nip) }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    {{-- Dosen Info Section --}}
                    <div class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    {{ strtoupper(substr($dosen->nama, 0, 2)) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Sedang mengedit data:</p>
                                <p class="font-semibold text-gray-800">{{ $dosen->nama }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- NIP Field --}}
                    <div>
                        <label for="nip" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-card text-gray-400 mr-2"></i>NIP
                        </label>
                        <input type="text" id="nip" name="nip" value="{{ old('nip', $dosen->nip) }}" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all"
                            placeholder="Masukkan NIP dosen">
                        <p class="mt-2 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            NIP wajib diisi
                        </p>
                        @error('nip')
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Nama Dosen Field --}}
                    <div>
                        <label for="nama" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-tie text-gray-400 mr-2"></i>Nama Dosen
                        </label>
                        <input type="text" id="nama" name="nama" value="{{ old('nama', $dosen->nama) }}" required autofocus
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all"
                            placeholder="Masukkan nama lengkap dosen">
                        @error('nama')
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Kapasitas Ampuan Field --}}
                    <div>
                        <label for="kapasitas_ampu" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-layer-group text-gray-400 mr-2"></i>Kapasitas Ampuan
                        </label>
                        <input type="number" id="kapasitas_ampu" name="kapasitas_ampu" value="{{ old('kapasitas_ampu', $dosen->kapasitas_ampu) }}" required min="0"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-all"
                            placeholder="0">
                        <p class="mt-2 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Masukkan 0 untuk kapasitas tidak terbatas
                        </p>
                        @error('kapasitas_ampu')
                            <span class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Current Status Info --}}
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-chart-bar text-blue-500 mt-0.5 mr-3"></i>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-blue-900 mb-2">Status Kapasitas Saat Ini</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Kapasitas Maksimal</p>
                                        <p class="text-lg font-bold text-blue-900">
                                            @if($dosen->kapasitas_ampu == 0)
                                                <i class="fas fa-infinity"></i> Tidak Terbatas
                                            @else
                                                {{ $dosen->kapasitas_ampu }} Mahasiswa
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Mahasiswa Dibimbing</p>
                                        <p class="text-lg font-bold text-blue-900">
                                            {{ $dosen->mahasiswas->count() }} Mahasiswa
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('dosen.index') }}" class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-colors duration-150">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-semibold text-sm transition-all duration-150 shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i>
                        Perbarui Dosen
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
