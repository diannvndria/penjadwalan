{{-- resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Inventory App</title>
    @vite('resources/css/app.css') {{-- Menyertakan CSS dari Tailwind --}}
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <h1 class="text-3xl text-center text-blue-600 mt-10">Halo, Laravel dan Tailwind!</h1>
</body>
</html>