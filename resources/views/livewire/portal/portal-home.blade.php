<div class="p-4 space-y-4">

    {{-- Greeting --}}
    <div class="portal-card p-5" style="background: linear-gradient(135deg, #1a1040, #7c3aed);">
        <p class="text-white/70 text-sm">Selamat
            {{ now()->hour < 12 ? 'Pagi' : (now()->hour < 17 ? 'Siang' : 'Malam') }},</p>
        <p class="text-white font-bold text-xl mt-0.5">{{ $employee?->name ?? auth()->user()->name }}</p>
        @if ($employee)
            <p class="text-white/60 text-sm mt-1">
                {{ $employee->activeAssignment?->position->name ?? '—' }} ·
                {{ $employee->school->name }}
            </p>
            @if ($employee->additionalAssignment)
                <p class="text-white/50 text-xs mt-0.5">
                    + Tugas Tambahan: {{ $employee->additionalAssignment->school->name }}
                </p>
            @endif
        @endif
        <p class="text-white/50 text-xs mt-3">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Status Absensi Hari Ini --}}
    <div class="portal-card p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Absensi Hari Ini</p>
        @if ($todayAttendance)
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-700">
                        @if ($todayAttendance->status === 'present')
                            ✅ Hadir
                        @elseif($todayAttendance->status === 'late')
                            ⏰ Terlambat
                        @elseif($todayAttendance->status === 'leave')
                            🌴 Cuti/Izin
                        @else
                            ❌ Tidak Hadir
                        @endif
                    </p>
                    <div class="flex gap-3 mt-1 text-xs text-gray-400">
                        @if ($todayAttendance->check_in)
                            <span>Masuk: {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') }}</span>
                        @endif
                        @if ($todayAttendance->check_out)
                            <span>Keluar: {{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i') }}</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('portal.attendance') }}"
                    class="text-xs text-violet-600 font-medium bg-violet-50 px-3 py-1.5 rounded-lg">
                    Detail →
                </a>
            </div>
        @else
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-400">Belum absen hari ini</p>
                <a href="{{ route('portal.attendance') }}"
                    class="text-xs text-white font-medium bg-violet-600 px-3 py-1.5 rounded-lg">
                    Absen Sekarang
                </a>
            </div>
        @endif
    </div>

    {{-- Statistik Bulan Ini --}}
    <div class="portal-card p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
            Bulan {{ now()->translatedFormat('F Y') }}
        </p>
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-3 bg-green-50 rounded-xl">
                <p class="text-2xl font-bold text-green-600">{{ $monthlyAttendance->get('present', 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Hadir</p>
            </div>
            <div class="text-center p-3 bg-amber-50 rounded-xl">
                <p class="text-2xl font-bold text-amber-600">{{ $monthlyAttendance->get('late', 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Terlambat</p>
            </div>
            <div class="text-center p-3 bg-violet-50 rounded-xl">
                <p class="text-2xl font-bold text-violet-600">{{ $monthlyAttendance->get('leave', 0) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Cuti</p>
            </div>
        </div>
    </div>

    {{-- Saldo Cuti & Pending --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="portal-card p-4 text-center">
            <p class="text-3xl font-bold text-violet-600">{{ $leaveBalance?->remaining ?? '—' }}</p>
            <p class="text-xs text-gray-500 mt-1">Sisa {{ $leaveBalance?->leaveType->name ?? 'Cuti' }}</p>
            <p class="text-xs text-gray-400">dari {{ $leaveBalance?->quota ?? '—' }} hari</p>
        </div>
        <div class="portal-card p-4 text-center">
            <p class="text-3xl font-bold {{ $pendingLeave > 0 ? 'text-amber-500' : 'text-gray-400' }}">
                {{ $pendingLeave }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Pengajuan Pending</p>
            <a href="{{ route('portal.leave') }}" class="text-xs text-violet-600 mt-1 block">Lihat →</a>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('portal.attendance') }}"
            class="portal-card p-4 flex flex-col items-center gap-2 text-center">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-gray-700">Absensi</p>
        </a>
        <a href="{{ route('portal.leave') }}" class="portal-card p-4 flex flex-col items-center gap-2 text-center">
            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-gray-700">Ajukan Cuti</p>
        </a>
    </div>
</div>
