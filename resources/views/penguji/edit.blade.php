@extends('layouts.app')

@section('header')
    {{ __('Edit Penguji') }}
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

            <form method="POST" action="{{ route('penguji.update', $penguji->id) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Penguji</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $penguji->nama) }}" required autofocus
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Status Prioritas Section --}}
                <div class="mt-6 p-4 border border-yellow-300 rounded-lg bg-yellow-50">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Status Prioritas</h3>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="hidden" name="is_prioritas" value="0">
                            <input type="checkbox" id="is_prioritas" name="is_prioritas" value="1" {{ old('is_prioritas', $penguji->is_prioritas) ? 'checked' : '' }}
                                class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            <label for="is_prioritas" class="ml-2 text-sm font-medium text-gray-900">
                                Penguji Prioritas (akan mendapat ruang di lantai 1)
                            </label>
                        </div>

                        <div>
                            <label for="keterangan_prioritas" class="block text-sm font-medium text-gray-700 mb-1">
                                Keterangan Prioritas
                            </label>
                            <textarea id="keterangan_prioritas" name="keterangan_prioritas" rows="3"
                                placeholder="Contoh: Dosen senior, keterbatasan mobilitas, kondisi kesehatan, dll."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('keterangan_prioritas', $penguji->keterangan_prioritas) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Jelaskan alasan pemberian status prioritas (opsional)
                            </p>
                            @error('keterangan_prioritas') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Perbarui Penguji
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection
