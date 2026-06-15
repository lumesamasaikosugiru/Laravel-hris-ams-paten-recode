@extends('layouts.admin')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan kondisi SDM Yayasan Fatahillah')
@section('content')

    @php
        use App\Models\Employee;
        use App\Models\Attendance;
        use App\Models\LeaveRequest;
        use App\Models\Applicant;
        use App\Models\JobVacancy;

        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');
        $thisYear = now()->year;

        // ── Pegawai ──────────────────────────────────────────────────
        $totalActive = Employee::where('status', 'active')->count();
        $totalProbation = Employee::where('status', 'probation')->count();
        $totalGuru = Employee::where('status', 'active')->where('is_guru', true)->count();
        $totalStaff = Employee::where('status', 'active')->where('is_guru', false)->count();
        $overdueProb = Employee::probationEnding()->count();
        $endingSoon = Employee::probationEndingSoon(7)->count();

        // ── Absensi Hari Ini ─────────────────────────────────────────
        $attToday = [
            'present' => Attendance::where('date', $today)->where('status', 'present')->count(),
            'late' => Attendance::where('date', $today)->where('status', 'late')->count(),
            'absent' => Attendance::where('date', $today)->where('status', 'absent')->count(),
            'leave' => Attendance::where('date', $today)->where('status', 'leave')->count(),
        ];
        $attPct = $totalActive > 0 ? round((($attToday['present'] + $attToday['late']) / $totalActive) * 100) : 0;

        // ── Cuti ─────────────────────────────────────────────────────
        $leavePending = LeaveRequest::where('status', 'pending')->count();
        $leaveApproved = LeaveRequest::where('status', 'approved')->whereYear('start_date', $thisYear)->count();

        // ── Rekrutmen ────────────────────────────────────────────────
        $activeJobs = JobVacancy::where('status', 'open')->count();
        $newApplicants = Applicant::where('status', 'submitted')->whereMonth('created_at', now()->month)->count();
        $pendingPipeline = Applicant::whereIn('status', ['tes_berkas', 'tes_potensi'])->count();

        // ── Grafik absensi 7 hari terakhir ───────────────────────────
        $last7 = collect(range(6, 0))->map(function ($i) {
            $date = now()->subDays($i)->format('Y-m-d');
            return [
                'date' => now()->subDays($i)->format('d/m'),
                'present' => Attendance::where('date', $date)->where('status', 'present')->count(),
                'late' => Attendance::where('date', $date)->where('status', 'late')->count(),
                'absent' => Attendance::where('date', $date)->where('status', 'absent')->count(),
            ];
        });

        // ── Distribusi pegawai per unit ──────────────────────────────
        $bySchool = Employee::where('status', 'active')
            ->selectRaw('school_id, COUNT(*) as total')
            ->groupBy('school_id')
            ->with('school')
            ->get();

        // ── Kontrak hampir habis ─────────────────────────────────────
        $expiringContracts = Employee::where('status', 'active')
            ->where('employee_type', 'contract')
            ->whereNotNull('contract_end')
            ->where('contract_end', '<=', now()->addDays(30)->format('Y-m-d'))
            ->where('contract_end', '>=', $today)
            ->orderBy('contract_end')
            ->with(['school', 'activeAssignment.position'])
            ->limit(5)
            ->get();

        // ── Pengajuan cuti pending ────────────────────────────────────
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->with(['employee', 'leaveType'])
            ->latest()
            ->limit(5)
            ->get();

        // Pegawai mendekati pensiun (dalam 2 tahun)
        $retiringSoon = Employee::whereIn('status', ['active', 'probation'])
            ->whereNotNull('date_of_birth')
            ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?', [58])
            ->orderByRaw('date_of_birth ASC')
            ->with(['school', 'activeAssignment.position'])
            ->limit(5)
            ->get();
    @endphp

    {{-- ── Row 1: Stats Utama ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai Aktif</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalActive }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $totalGuru }} guru · {{ $totalStaff }} staf</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Hadir Hari Ini</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $attToday['present'] + $attToday['late'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $attPct }}% dari total · {{ $attToday['absent'] }} tidak
                        hadir</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Cuti Pending</p>
                    <p class="text-3xl font-bold text-amber-600 mt-1">{{ $leavePending }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $leaveApproved }} disetujui tahun ini</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Masa Percobaan</p>
                    <p class="text-3xl font-bold {{ $overdueProb > 0 ? 'text-red-600' : 'text-gray-800' }} mt-1">
                        {{ $totalProbation }}</p>
                    <p class="text-xs {{ $overdueProb > 0 ? 'text-red-500 font-medium' : 'text-gray-400' }} mt-1">
                        @if ($overdueProb > 0)
                            {{ $overdueProb }} perlu evaluasi segera!
                        @elseif($endingSoon > 0)
                            {{ $endingSoon }} berakhir dalam 7 hari
                        @else
                            Semua on-track
                        @endif
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Grafik + Distribusi ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

        {{-- Grafik Absensi 7 Hari --}}
        <div class="card p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Absensi 7 Hari Terakhir</h3>
                <a href="{{ route('admin.attendance.index') }}" class="text-xs text-violet-600 hover:underline">Lihat semua
                    →</a>
            </div>
            <div id="chart-attendance" style="height:200px"></div>
        </div>

        {{-- Distribusi per Unit --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Pegawai per Unit</h3>
            </div>
            <div class="space-y-3">
                @foreach ($bySchool as $item)
                    @php $pct = $totalActive > 0 ? round($item->total/$totalActive*100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600 truncate max-w-[150px]">{{ $item->school->name }}</span>
                            <span class="font-semibold text-gray-700 shrink-0 ml-2">{{ $item->total }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-violet-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
                @if ($bySchool->isEmpty())
                    <p class="text-xs text-gray-400 text-center py-4">Belum ada data</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Row 3: Alerts + Quick Actions ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

        {{-- Kontrak Hampir Habis --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Kontrak Hampir Habis</h3>
                <span class="badge-red text-xs">30 hari</span>
            </div>
            @forelse($expiringContracts as $emp)
                <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                    <div
                        class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-xs font-bold shrink-0">
                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->activeAssignment?->position->name ?? '—' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs font-semibold text-red-600">
                            {{ now()->diffInDays($emp->contract_end) }}h
                        </p>
                        <p class="text-xs text-gray-400">{{ $emp->contract_end->format('d/m') }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-xs text-gray-400">Tidak ada kontrak yang hampir habis</p>
                </div>
            @endforelse
        </div>

        {{-- Mendekati Pensiun --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Mendekati Pensiun</h3>
                <span class="badge bg-orange-100 text-orange-700 text-xs">≤ 2 tahun</span>
            </div>
            @forelse($retiringSoon as $emp)
                <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                    <div
                        class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs font-bold shrink-0">
                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->activeAssignment?->position->name ?? '—' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs font-semibold text-orange-600">
                            {{ $emp->age }} thn
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $emp->retirement_date?->format('Y') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-xs text-gray-400">Tidak ada yang mendekati pensiun</p>
                </div>
            @endforelse
        </div>

        {{-- Cuti Pending --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700">Pengajuan Cuti Pending</h3>
                <a href="{{ route('admin.leaves.index') }}" class="text-xs text-violet-600 hover:underline">Semua →</a>
            </div>
            @forelse($pendingLeaves as $leave)
                <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                    <div
                        class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-xs font-bold shrink-0">
                        {{ strtoupper(substr($leave->employee->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $leave->employee->name }}</p>
                        <p class="text-xs text-gray-400">{{ $leave->leaveType->name }} · {{ $leave->days }} hari</p>
                    </div>
                    <span class="badge bg-yellow-100 text-yellow-700 shrink-0 text-xs">Pending</span>
                </div>
            @empty
                <div class="text-center py-6">
                    <p class="text-xs text-gray-400">Tidak ada pengajuan pending</p>
                </div>
            @endforelse
        </div>

        {{-- Quick Links --}}
        {{-- Quick Links --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Aksi Cepat</h3>
            <div class="space-y-2">
                @can('employee.create')
                    <a href="{{ route('admin.employees.create') }}"
                        class="flex items-center gap-3 p-2.5 rounded-lg text-violet-600 bg-violet-50 hover:opacity-80 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        <span class="text-sm font-medium">Tambah Pegawai</span>
                    </a>
                @endcan
                @can('attendance.create')
                    <a href="{{ route('admin.attendance.index') }}"
                        class="flex items-center gap-3 p-2.5 rounded-lg text-green-600 bg-green-50 hover:opacity-80 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="text-sm font-medium">Input Absensi</span>
                    </a>
                @endcan
                @can('leave.view')
                    <a href="{{ route('admin.leaves.index') }}"
                        class="flex items-center gap-3 p-2.5 rounded-lg text-amber-600 bg-amber-50 hover:opacity-80 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        <span class="text-sm font-medium">Kelola Cuti</span>
                    </a>
                @endcan
                @can('recruitment.pipeline')
                    <a href="{{ route('admin.applicants.index') }}"
                        class="flex items-center gap-3 p-2.5 rounded-lg text-blue-600 bg-blue-50 hover:opacity-80 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span class="text-sm font-medium">Pipeline Rekrutmen</span>
                    </a>
                @endcan
                @can('attendance.report')
                    <a href="{{ route('admin.attendance.report') }}"
                        class="flex items-center gap-3 p-2.5 rounded-lg text-gray-600 bg-gray-50 hover:opacity-80 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <span class="text-sm font-medium">Laporan Absensi</span>
                    </a>
                @endcan
                @can('report.view')
                    <a href="{{ route('admin.reports.index') }}"
                        class="flex items-center gap-3 p-2.5 rounded-lg text-pink-600 bg-pink-50 hover:opacity-80 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="text-sm font-medium">Laporan SDM</span>
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- ── Row 4: Rekrutmen Stats ── --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="card p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Lowongan Aktif</p>
                <p class="text-2xl font-bold text-gray-800">{{ $activeJobs }}</p>
            </div>
        </div>
        <div class="card p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Pelamar Bulan Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newApplicants }}</p>
            </div>
        </div>
        <div class="card p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Dalam Pipeline</p>
                <p class="text-2xl font-bold text-gray-800">{{ $pendingPipeline }}</p>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('chart-attendance');
            if (!ctx) return;

            const labels = @json($last7->pluck('date'));
            const present = @json($last7->pluck('present'));
            const late = @json($last7->pluck('late'));
            const absent = @json($last7->pluck('absent'));

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                            label: 'Hadir',
                            data: present,
                            backgroundColor: '#4ade80',
                            borderRadius: 4
                        },
                        {
                            label: 'Terlambat',
                            data: late,
                            backgroundColor: '#fbbf24',
                            borderRadius: 4
                        },
                        {
                            label: 'Tidak Hadir',
                            data: absent,
                            backgroundColor: '#f87171',
                            borderRadius: 4
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: '#f3f4f6'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
