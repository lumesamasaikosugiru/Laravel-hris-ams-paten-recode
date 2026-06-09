@extends('layouts.public')
@section('title','Lowongan Kerja')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    {{-- Hero --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Bergabung Bersama Kami</h1>
        <p class="text-gray-500 mt-1 text-sm">
            Yayasan Fatahillah membuka kesempatan berkarir untuk putra-putri terbaik bangsa.
        </p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('careers.index') }}" class="flex flex-wrap gap-2 mb-6">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari posisi..."
               class="input w-64">
        <select name="type" class="input w-auto">
            <option value="">Semua Tipe</option>
            <option value="permanent" {{ request('type')==='permanent' ? 'selected':'' }}>Tetap</option>
            <option value="contract"  {{ request('type')==='contract'  ? 'selected':'' }}>Kontrak</option>
            <option value="intern"    {{ request('type')==='intern'    ? 'selected':'' }}>Magang</option>
        </select>
        <button type="submit" class="btn-primary">Cari</button>
        @if(request()->anyFilled(['search','type']))
        <a href="{{ route('careers.index') }}" class="btn-ghost">Reset</a>
        @endif
    </form>

    {{-- Stats bar --}}
    <p class="text-xs text-gray-400 mb-4">
        Menampilkan <strong class="text-gray-600">{{ $jobs->total() }}</strong> lowongan aktif
    </p>

    {{-- Job Cards --}}
    @forelse($jobs as $job)
    <div class="card mb-4 hover:border-violet-300 transition-colors duration-150">
        <div class="p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h2 class="text-base font-semibold text-gray-800">{{ $job->title }}</h2>
                        <span class="badge-purple">{{ $job->employment_type_label }}</span>
                        @if($job->quota > 1)
                        <span class="badge-gray">{{ $job->quota }} posisi</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ $job->position->name }}
                        <span class="mx-1 text-gray-300">·</span>
                        {{ $job->department->name }}
                        <span class="mx-1 text-gray-300">·</span>
                        {{ $job->school->name }}
                    </p>

                    @if($job->description)
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $job->description }}</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-3 mt-3 text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            Dibuka {{ $job->open_date->format('d M Y') }}
                        </span>
                        @if($job->close_date)
                        <span class="flex items-center gap-1 {{ $job->is_expired ? 'text-red-400' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Deadline {{ $job->close_date->format('d M Y') }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            {{ $job->applicants_count }} pelamar
                        </span>
                    </div>
                </div>

                <a href="{{ route('careers.show', $job) }}"
                   class="btn-primary shrink-0 text-sm">
                    Lihat & Lamar
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center">
        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
        </div>
        <p class="font-medium text-gray-500">Belum ada lowongan tersedia</p>
        <p class="text-sm text-gray-400 mt-1">Silakan cek kembali di lain waktu</p>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($jobs->hasPages())
    <div class="mt-6">{{ $jobs->links() }}</div>
    @endif
</div>
@endsection
