<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Penjadwalan Munaqosah</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex flex-col items-center justify-center min-h-screen font-sans py-10">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg border border-slate-100 z-10 relative">
        <div class="text-center mb-10">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Buat Akun Baru</h1>
            <p class="text-slate-500 mt-2 text-sm">Daftar untuk mengakses sistem penjadwalan</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded-r" role="alert">
                <div class="flex items-center mb-1">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <p class="font-bold">Terjadi Kesalahan</p>
                </div>
                <ul class="list-disc list-inside ml-1 text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                        <i class="fas fa-user text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="text" id="name" name="name"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-slate-700 placeholder-slate-400 text-sm"
                           placeholder="Nama Lengkap Anda"
                           value="{{ old('name') }}" required autofocus>
                </div>
            </div>

            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                        <i class="fas fa-envelope text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="email" id="email" name="email"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-slate-700 placeholder-slate-400 text-sm"
                           placeholder="nama@email.com"
                           value="{{ old('email') }}" required>
                </div>
            </div>

            <div class="mb-5">
                <label for="role" class="block text-sm font-medium text-slate-700 mb-1.5">Daftar Sebagai</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                        <i class="fas fa-id-badge text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <select id="role" name="role" required
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-slate-700 text-sm appearance-none cursor-pointer">
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-500">
                        <i class="fas fa-chevron-down text-xs mr-2"></i>
                    </div>
                </div>
                @error('role') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mb-5">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                        <i class="fas fa-lock text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="password" id="password" name="password"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-slate-700 placeholder-slate-400 text-sm"
                           placeholder="••••••••"
                           required>
                </div>
            </div>

            <div class="mb-8">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                        <i class="fas fa-check-double text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-slate-700 placeholder-slate-400 text-sm"
                           placeholder="••••••••"
                           required>
                </div>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-lg shadow-indigo-200 transform transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 text-sm tracking-wide">
                Registrasi
            </button>

            <div class="mt-8 text-center pt-6 border-t border-slate-100">
                <p class="text-sm text-slate-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline transition-colors ml-1">
                        Masuk disini
                    </a>
                </p>
            </div>
        </form>
    </div>

    <div class="mt-8 text-slate-400 text-xs text-center w-full">
        &copy; {{ date('Y') }} Sistem Penjadwalan Munaqosah
    </div>
</body>
</html>
