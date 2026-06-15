@extends('layouts.admin')
@section('title','Akses Ditolak')
@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
        <div class="w-20 h-20 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Akses Ditolak</h1>
        <p class="text-gray-500 text-sm mb-6">
            Anda tidak memiliki izin untuk mengakses halaman ini.<br>
            Hubungi Administrator jika ini adalah kesalahan.
        </p>
        <a href="{{ route('dashboard') }}" class="btn-primary inline-flex">
            ← Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
