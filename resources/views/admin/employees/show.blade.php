@extends('layouts.admin')
@section('title', 'Detail Pegawai')
@section('subtitle', $employee->name . ' · ' . ($employee->nipy ?? $employee->nik))
@section('content')

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
        <a href="{{ route('admin.employees.index') }}" class="hover:text-violet-600 transition">
            Data Pegawai
        </a>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span class="text-gray-600">{{ $employee->name }}</span>
    </div>

    @livewire('admin.employee-detail', ['employee' => $employee])

@endsection
