<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Penjadwalan Munaqosah</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex flex-col items-center justify-center min-h-screen font-sans py-10">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg border border-slate-100 z-10 relative">
        <div class="text-center mb-10">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Sistem Penjadwalan</h1>
            <p class="text-slate-500 mt-2 text-sm">Masuk untuk mengelola jadwal skripsi</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded-r flex items-start" role="alert">
                <i class="fas fa-check-circle mt-0.5 mr-3"></i>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

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

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                        <i class="fas fa-envelope text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="email" id="email" name="email"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-slate-700 placeholder-slate-400 text-sm"
                           placeholder="nama@email.com"
                           value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex justify-between items-center mb-1.5">
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                    </div>
                    <input type="password" id="password" name="password"
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-slate-700 placeholder-slate-400 text-sm"
                           placeholder="••••••••"
                           required>
                </div>
            </div>

            <div class="flex items-center justify-between mb-8">
                <label class="inline-flex items-center cursor-pointer select-none group">
                    <div class="relative">
                        <input type="checkbox" name="remember" class="peer sr-only">
                        <div class="w-4 h-4 border border-slate-300 rounded bg-white peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-colors"></div>
                        <i class="fas fa-check text-white text-[10px] absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                    </div>
                    <span class="ml-2 text-sm text-slate-600 group-hover:text-slate-800 transition-colors">Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-lg shadow-indigo-200 transform transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 text-sm tracking-wide">
                Masuk
            </button>

            <div class="mt-8 text-center pt-6 border-t border-slate-100">
                <p class="text-sm text-slate-500">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline transition-colors ml-1">
                        Daftar sekarang
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
