@extends('layouts.admin')
@section('title', 'Laporan SDM')
@section('subtitle', 'Export dan cetak laporan kepegawaian')
@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

    {{-- Laporan Pegawai --}}
    <div class="card p-6 hover:border-violet-300 transition">
        <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-800 mb-1">Laporan Data Pegawai</h3>
        <p class="text-xs text-gray-500 mb-4">Daftar lengkap pegawai aktif beserta jabatan, status, dan kontrak.</p>
        <a href="{{ route('admin.reports.employees') }}" class="btn-primary w-full justify-center text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>
    </div>

    {{-- Laporan Absensi --}}
    <div class="card p-6 hover:border-violet-300 transition">
        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-800 mb-1">Laporan Absensi Bulanan</h3>
        <p class="text-xs text-gray-500 mb-4">Rekap kehadiran, keterlambatan, dan persentase per pegawai.</p>
        <a href="{{ route('admin.attendance.report') }}" class="btn-primary w-full justify-center text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Lihat Laporan
        </a>
    </div>

    {{-- Laporan Cuti --}}
    <div class="card p-6 hover:border-violet-300 transition">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-800 mb-1">Laporan Cuti & Saldo</h3>
        <p class="text-xs text-gray-500 mb-4">Rekap penggunaan dan sisa saldo cuti per pegawai per tahun.</p>
        <a href="{{ route('admin.reports.leaves') }}" class="btn-primary w-full justify-center text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>
    </div>

    {{-- Laporan Rekrutmen --}}
    <div class="card p-6 hover:border-violet-300 transition">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-800 mb-1">Laporan Rekrutmen</h3>
        <p class="text-xs text-gray-500 mb-4">Statistik pelamar, pipeline seleksi, dan konversi ke pegawai.</p>
        <a href="{{ route('admin.reports.recruitment') }}" class="btn-primary w-full justify-center text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>
    </div>

    {{-- Laporan Masa Percobaan --}}
    <div class="card p-6 hover:border-violet-300 transition">
        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center mb-4">
            <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-800 mb-1">Laporan Masa Percobaan</h3>
        <p class="text-xs text-gray-500 mb-4">Status pegawai dalam masa percobaan dan jadwal evaluasi.</p>
        <a href="{{ route('admin.reports.probation') }}" class="btn-primary w-full justify-center text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>
    </div>
</div>
@endsection
