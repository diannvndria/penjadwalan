@extends('layouts.app')

@section('title', 'Pengaturan Akun')

@section('header')
    Pengaturan Akun
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Success Message --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center space-x-3">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
            </div>
            <p class="text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Error Messages --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-center space-x-3 mb-2">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                <p class="text-red-700 font-medium">Terdapat kesalahan:</p>
            </div>
            <ul class="list-disc list-inside text-red-600 text-sm ml-8">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Profile Information Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-edit text-blue-500 mr-3"></i>
                Informasi Profil
            </h3>
            <p class="text-sm text-gray-600 mt-1">Perbarui nama dan alamat email akun Anda.</p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PATCH')

            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user text-gray-400 mr-2"></i>Nama Lengkap
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $user->name) }}"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all @error('name') border-red-300 @enderror"
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-envelope text-gray-400 mr-2"></i>Alamat Email
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email', $user->email) }}"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all @error('email') border-red-300 @enderror"
                       required>
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-between items-center pt-4">
                <a href="{{ route('dashboard') }}" class="px-5 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-all flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-all shadow-md shadow-blue-200 flex items-center space-x-2">
                    <i class="fas fa-save"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Update Password Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
            <h3 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="fas fa-lock text-amber-500 mr-3"></i>
                Ubah Password
            </h3>
            <p class="text-sm text-gray-600 mt-1">Pastikan akun Anda menggunakan password yang kuat dan aman.</p>
        </div>

        <form method="POST" action="{{ route('profile.password') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- Current Password --}}
            <div>
                <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-key text-gray-400 mr-2"></i>Password Lama
                </label>
                <input type="password" 
                       id="current_password" 
                       name="current_password" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition-all @error('current_password') border-red-300 @enderror"
                       required>
                @error('current_password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- New Password --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock text-gray-400 mr-2"></i>Password Baru
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition-all @error('password') border-red-300 @enderror"
                       required>
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-check-circle text-gray-400 mr-2"></i>Konfirmasi Password Baru
                </label>
                <input type="password" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition-all"
                       required>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-3 bg-amber-600 text-white font-semibold rounded-xl hover:bg-amber-700 transition-all shadow-md shadow-amber-200 flex items-center space-x-2">
                    <i class="fas fa-key"></i>
                    <span>Perbarui Password</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
