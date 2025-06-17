@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
    </h2>
@endsection

@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            Selamat datang di Sistem Penjadwalan Skripsi, {{ Auth::user()->name }}!
            <div class="mt-4 text-sm text-gray-600">
                Anda bisa memulai dengan mengelola data mahasiswa, dosen, penguji, atau membuat jadwal munaqosah dari menu di atas.
            </div>
        </div>
    </div>
@endsection