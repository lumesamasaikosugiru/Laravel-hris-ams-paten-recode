<div class="p-4 space-y-4">

    {{-- Pilih Unit (jika punya tugas tambahan) --}}
    @if (count($schools) > 1)
        <div class="portal-card p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Unit Absensi</p>
            <div class="flex gap-2">
                @foreach ($schools as $sc)
                    <button wire:click="$set('selectedSchoolId', {{ $sc['id'] }})"
                        class="flex-1 py-2 px-3 rounded-xl text-sm font-medium transition
                           {{ $selectedSchoolId == $sc['id'] ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        {{ $sc['name'] }}
                        <span class="block text-xs opacity-70">{{ $sc['type'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Status Hari Ini --}}
    <div class="portal-card p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
            {{ now()->translatedFormat('l, d F Y') }}
        </p>

        @if ($isWeekend)
            <div class="text-center py-6">
                <p class="text-4xl mb-2">🏖️</p>
                <p class="font-semibold text-gray-600">Hari Libur</p>
                <p class="text-sm text-gray-400 mt-1">Absensi hanya Senin – Jumat</p>
            </div>
        @elseif($todayAttendance)
            <div class="space-y-3">
                {{-- Status badge --}}
                <div class="flex items-center justify-between">
                    <span
                        class="status-chip
                    {{ $todayAttendance->status === 'present'
                        ? 'bg-green-100 text-green-700'
                        : ($todayAttendance->status === 'late'
                            ? 'bg-amber-100 text-amber-700'
                            : ($todayAttendance->status === 'leave'
                                ? 'bg-blue-100 text-blue-700'
                                : 'bg-red-100 text-red-700')) }}">
                        @if ($todayAttendance->status === 'present')
                            ✅ Hadir
                        @elseif($todayAttendance->status === 'late')
                            ⏰ Terlambat {{ $todayAttendance->late_minutes }} menit
                        @elseif($todayAttendance->status === 'leave')
                            🌴 Cuti/Izin
                        @else
                            ❌ Tidak Hadir
                        @endif
                    </span>
                </div>

                {{-- Jam --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-green-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Jam Masuk</p>
                        <p class="text-xl font-bold text-green-700">
                            {{ $todayAttendance->check_in ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') : '—' }}
                        </p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Jam Keluar</p>
                        <p class="text-xl font-bold text-red-600">
                            {{ $todayAttendance->check_out ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i') : '—' }}
                        </p>
                    </div>
                </div>

                {{-- Tombol Check-out --}}
                @if ($todayAttendance->check_in && !$todayAttendance->check_out)
                    <button wire:click="$set('showCheckOutConfirm', true)" class="portal-btn-danger mt-2">
                        Check-Out Sekarang
                    </button>
                @elseif($todayAttendance->check_out)
                    <div class="text-center py-2 text-sm text-gray-400">
                        Absensi hari ini selesai ✓
                    </div>
                @endif
            </div>
        @else
            {{-- Belum absen --}}
            <div class="text-center py-4 space-y-4">
                <div>
                    <p class="text-5xl font-bold text-gray-800">{{ now()->format('H:i') }}</p>
                    <p class="text-sm text-gray-400 mt-1">
                        Jam masuk: {{ \App\Models\Attendance::WORK_START }} WIB
                    </p>
                </div>
                <button wire:click="$set('showCheckInConfirm', true)" class="portal-btn-primary">
                    Check-In Sekarang
                </button>
            </div>
        @endif
    </div>

    {{-- Riwayat --}}
    @if ($history->count() > 0)
        <div class="portal-card overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Riwayat Absensi</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($history as $att)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">
                                {{ $att->date->translatedFormat('d M') }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $att->date->translatedFormat('l') }}</p>
                        </div>
                        <div class="text-right">
                            <span
                                class="status-chip text-xs
                        {{ $att->status === 'present'
                            ? 'bg-green-100 text-green-700'
                            : ($att->status === 'late'
                                ? 'bg-amber-100 text-amber-700'
                                : ($att->status === 'leave'
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-red-100 text-red-700')) }}">
                                {{ $att->status === 'present'
                                    ? 'Hadir'
                                    : ($att->status === 'late'
                                        ? 'Terlambat'
                                        : ($att->status === 'leave'
                                            ? 'Cuti'
                                            : 'Tidak Hadir')) }}
                            </span>
                            @if ($att->check_in)
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }}
                                    @if ($att->check_out)
                                        — {{ \Carbon\Carbon::parse($att->check_out)->format('H:i') }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Check-In --}}
    @if ($showCheckInConfirm)
        <div class="fixed inset-0 flex items-center justify-center p-4"
            style="background:rgba(0,0,0,0.6); z-index:99999;">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
                <p class="text-lg font-bold text-gray-800 text-center mb-1">Konfirmasi Check-In</p>
                <p class="text-sm text-gray-500 text-center mb-4">
                    Pukul <strong>{{ now()->format('H:i') }}</strong> WIB
                </p>
                @if (now()->gt(now()->setTimeFromTimeString(\App\Models\Attendance::WORK_START)))
                    <div
                        class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4 text-xs text-amber-700 text-center">
                        ⚠ Kamu terlambat dari jam masuk {{ \App\Models\Attendance::WORK_START }}
                    </div>
                @endif
                <div class="flex gap-3">
                    <button wire:click="$set('showCheckInConfirm', false)"
                        class="portal-btn-ghost flex-1">Batal</button>
                    <button wire:click="checkIn" wire:loading.attr="disabled" class="portal-btn-primary flex-1">
                        <span wire:loading.remove wire:target="checkIn">Ya, Check-In</span>
                        <span wire:loading wire:target="checkIn">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Check-Out --}}
    @if ($showCheckOutConfirm)
        <div class="fixed inset-0 flex items-center justify-center p-4"
            style="background:rgba(0,0,0,0.6); z-index:99999;">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
                <p class="text-lg font-bold text-gray-800 text-center mb-1">Konfirmasi Check-Out</p>
                <p class="text-sm text-gray-500 text-center mb-4">
                    Pukul <strong>{{ now()->format('H:i') }}</strong> WIB
                </p>
                <div class="flex gap-3">
                    <button wire:click="$set('showCheckOutConfirm', false)"
                        class="portal-btn-ghost flex-1">Batal</button>
                    <button wire:click="checkOut" wire:loading.attr="disabled" class="portal-btn-danger flex-1">
                        <span wire:loading.remove wire:target="checkOut">Ya, Check-Out</span>
                        <span wire:loading wire:target="checkOut">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
