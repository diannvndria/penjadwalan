<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Penjadwalan Skripsi</title>
    {{-- Vite directive untuk mengkompilasi CSS dan JS --}}
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    {{-- Link Font Awesome CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @include('layouts.partials.styles')
</head>
<body class="bg-white font-sans antialiased text-gray-800">
    {{-- Main container --}}
    <div x-data="{
            mobileMenuOpen: false,
            sidebarExpanded: localStorage.getItem('sidebarExpanded') === 'true',
            toggleSidebar() {
                this.sidebarExpanded = !this.sidebarExpanded;
                localStorage.setItem('sidebarExpanded', this.sidebarExpanded);
            }
        }"
        class="relative flex h-screen overflow-hidden">

        <!-- Mobile Overlay -->
        <div class="mobile-overlay lg:hidden" :class="{ 'show': mobileMenuOpen }" @click="mobileMenuOpen = false"></div>

        @include('layouts.partials.sidebar')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            @include('layouts.partials.header')

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6 bg-slate-50">
                @yield('content')
            </main>
        </div>
    </div>
    <!-- Alpine.js CDN (make sure it's loaded) -->
    <script src="//unpkg.com/alpinejs" defer></script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
