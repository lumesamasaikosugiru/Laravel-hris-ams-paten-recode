@extends('layouts.public')
@section('title', $job->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header --}}
            <div class="card p-6">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="badge-purple">{{ $job->employment_type_label }}</span>
                    @if($job->quota > 1)
                    <span class="badge-gray">{{ $job->quota }} posisi</span>
                    @endif
                </div>
                <h1 class="text-xl font-bold text-gray-800 mb-1">{{ $job->title }}</h1>
                <p class="text-sm text-gray-500">
                    {{ $job->position->name }} — {{ $job->department->name }} — {{ $job->school->name }}
                </p>

                <div class="flex flex-wrap gap-4 mt-4 text-xs text-gray-400 border-t border-gray-100 pt-4">
                    <span>Dibuka: <strong class="text-gray-600">{{ $job->open_date->format('d M Y') }}</strong></span>
                    @if($job->close_date)
                    <span>Deadline: <strong class="{{ $job->is_expired ? 'text-red-500' : 'text-gray-600' }}">{{ $job->close_date->format('d M Y') }}</strong></span>
                    @endif
                    <span>{{ $job->applicants_count }} orang sudah melamar</span>
                </div>
            </div>

            {{-- Deskripsi --}}
            @if($job->description)
            <div class="card p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Deskripsi Pekerjaan</h2>
                <div class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $job->description }}</div>
            </div>
            @endif

            {{-- Persyaratan --}}
            @if($job->requirements)
            <div class="card p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Persyaratan</h2>
                <div class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $job->requirements }}</div>
            </div>
            @endif
        </div>

        {{-- Sidebar CTA --}}
        <div class="space-y-4">
            <div class="card p-5 sticky top-20">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Tertarik dengan posisi ini?</h3>

                @if($job->is_expired)
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-600 mb-4">
                    Maaf, lowongan ini sudah melewati batas waktu pendaftaran.
                </div>
                @endif

                <a href="{{ route('careers.apply', $job) }}"
                   class="btn-primary w-full justify-center text-sm
                          {{ $job->is_expired ? 'opacity-50 pointer-events-none' : '' }}">
                    Lamar Sekarang
                </a>

                <a href="{{ route('careers.index') }}"
                   class="btn-ghost w-full justify-center text-sm mt-2">
                    ← Lihat Lowongan Lain
                </a>

                <div class="border-t border-gray-100 mt-4 pt-4 space-y-2 text-xs text-gray-500">
                    <div class="flex justify-between">
                        <span>Unit</span>
                        <span class="font-medium text-gray-700">{{ $job->school->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Departemen</span>
                        <span class="font-medium text-gray-700">{{ $job->department->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tipe</span>
                        <span class="font-medium text-gray-700">{{ $job->employment_type_label }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kuota</span>
                        <span class="font-medium text-gray-700">{{ $job->quota }} posisi</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
