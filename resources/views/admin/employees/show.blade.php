@extends('layouts.admin')
@section('title','Detail Pegawai')
@section('subtitle', $employee->name)
@section('content')
<div class="card p-8 text-center">
    <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    <p class="text-sm font-medium text-gray-500">Halaman detail pegawai akan dibangun di tahap berikutnya</p>
    <a href="{{ route('admin.employees.index') }}" class="btn-ghost inline-flex mt-4">← Kembali ke Daftar</a>
</div>
@endsection
