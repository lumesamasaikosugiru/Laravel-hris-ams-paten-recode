{{-- resources/views/livewire/portal/portal-attendance.blade.php --}}
<div class="p-4 space-y-4">

    {{-- GPS tracker (auto-request saat mount) --}}
    <div x-data="gpsTracker()" x-init="init()" wire:ignore></div>

    {{-- ── GPS STATUS BADGE ──────────────────────────────────────────────── --}}
    <div>
        @if ($gpsLoading)
            <div class="flex items-center gap-2 text-xs text-blue-600 bg-blue-50 rounded-xl px-3 py-2">
                <svg class="animate-spin h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                Mendeteksi lokasi Anda...
            </div>
        @elseif ($gpsError)
            <div class="text-xs text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2 leading-relaxed">
                ⚠️ {{ $gpsError }}
            </div>
        @elseif ($gpsReady)
            <div class="flex items-center gap-1.5 text-xs text-green-700 bg-green-50 rounded-xl px-3 py-2">
                <svg class="h-3.5 w-3.5 fill-green-600 shrink-0" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                        clip-rule="evenodd" />
                </svg>
                Lokasi terdeteksi — siap absensi
            </div>
        @else
            <div class="text-xs text-gray-400 bg-gray-50 rounded-xl px-3 py-2">
                📡 Meminta izin lokasi dari perangkat...
            </div>
        @endif
    </div>

    {{-- ── FLASH MESSAGE ─────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="text-sm text-green-800 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="text-sm text-red-800 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── PILIH UNIT ────────────────────────────────────────────────────── --}}
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

    {{-- ── KARTU STATUS HARI INI ─────────────────────────────────────────── --}}
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
        @elseif ($todayAttendance)
            <div class="space-y-3">

                {{-- Status badge + off-site indicator --}}
                <div class="flex items-center justify-between flex-wrap gap-2">
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
                        @elseif ($todayAttendance->status === 'late')
                            ⏰ Terlambat {{ $todayAttendance->late_minutes }} menit
                        @elseif ($todayAttendance->status === 'leave')
                            🌴 Cuti/Izin
                        @else
                            ❌ Tidak Hadir
                        @endif
                    </span>

                    {{-- Off-site approval badge --}}
                    @if ($todayAttendance->is_offsite)
                        <span
                            class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $todayAttendance->offsite_status === 'approved'
                                ? 'bg-green-100 text-green-700'
                                : ($todayAttendance->offsite_status === 'rejected'
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-yellow-100 text-yellow-700') }}">
                            🗺️
                            {{ $todayAttendance->offsite_status === 'approved'
                                ? 'Kegiatan Luar Disetujui'
                                : ($todayAttendance->offsite_status === 'rejected'
                                    ? 'Ditolak HR'
                                    : 'Menunggu Persetujuan HR') }}
                        </span>
                    @endif
                </div>

                {{-- Info kegiatan luar jika pending/rejected --}}
                @if ($todayAttendance->is_offsite && $todayAttendance->offsite_status !== 'approved')
                    <div
                        class="bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2 text-xs text-yellow-800 space-y-0.5">
                        <p><span class="font-medium">Alasan:</span> {{ $todayAttendance->offsite_reason }}</p>
                        @if ($todayAttendance->offsite_note)
                            <p><span class="font-medium">Keterangan:</span> {{ $todayAttendance->offsite_note }}</p>
                        @endif
                        @if ($todayAttendance->offsite_status === 'rejected' && $todayAttendance->offsite_rejection_note)
                            <p class="text-red-700"><span class="font-medium">Catatan HR:</span>
                                {{ $todayAttendance->offsite_rejection_note }}</p>
                        @endif
                    </div>
                @endif

                {{-- Jam masuk / keluar --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-green-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Jam Masuk</p>
                        <p class="text-xl font-bold text-green-700">
                            {{ $todayAttendance->check_in ? \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') : '—' }}
                        </p>
                        @if ($todayAttendance->checkin_location_valid)
                            <p class="text-[10px] text-green-500 mt-0.5 leading-tight">
                                📍 {{ Str::limit($todayAttendance->checkin_location_name, 26) }}
                            </p>
                        @elseif ($todayAttendance->is_offsite)
                            <p class="text-[10px] text-yellow-600 mt-0.5"> Kegiatan Luar</p>
                        @endif
                    </div>
                    <div class="bg-red-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Jam Keluar</p>
                        <p class="text-xl font-bold text-red-600">
                            {{ $todayAttendance->check_out ? \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i') : '—' }}
                        </p>
                        @if ($todayAttendance->checkout_location_valid)
                            <p class="text-[10px] text-green-500 mt-0.5 leading-tight">
                                📍 {{ Str::limit($todayAttendance->checkout_location_name, 26) }}
                            </p>
                        @endif
                    </div>
                </div>

                @if ($todayAttendance->check_in && !$todayAttendance->check_out)
                    <button wire:click="$set('showCheckOutConfirm', true)" @class([
                        'portal-btn-danger mt-2',
                        'opacity-50 cursor-not-allowed' => !$gpsReady,
                    ])
                        @if (!$gpsReady) disabled @endif>
                        {{ $gpsReady ? 'Check-Out Sekarang' : '📡 Menunggu Lokasi...' }}
                    </button>
                @elseif ($todayAttendance->check_out)
                    <div class="text-center py-2 text-sm text-gray-400">Absensi hari ini selesai ✓</div>
                @endif
            </div>
        @else
            <div class="text-center py-4 space-y-4">
                <div>
                    <p class="text-5xl font-bold text-gray-800">{{ now()->format('H:i') }}</p>
                    <p class="text-sm text-gray-400 mt-1">Jam masuk: {{ \App\Models\Attendance::WORK_START }} WIB</p>
                </div>
                <button wire:click="$set('showCheckInConfirm', true)" @class([
                    'portal-btn-primary',
                    'opacity-50 cursor-not-allowed' => !$gpsReady,
                ])
                    @if (!$gpsReady) disabled @endif>
                    {{ $gpsReady ? 'Check-In Sekarang' : '📡 Menunggu Lokasi...' }}
                </button>
            </div>
        @endif
    </div>

    {{-- ── RIWAYAT ────────────────────────────────────────────────────────── --}}
    @if ($history->count() > 0)
        <div class="portal-card overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Riwayat Absensi</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($history as $att)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $att->date->translatedFormat('d M') }}</p>
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
                            {{-- Off-site badge di riwayat --}}
                            @if ($att->is_offsite)
                                <p
                                    class="text-[10px] mt-0.5
                                    {{ $att->offsite_status === 'approved'
                                        ? 'text-green-500'
                                        : ($att->offsite_status === 'rejected'
                                            ? 'text-red-500'
                                            : 'text-yellow-500') }}">
                                    🗺️
                                    {{ $att->offsite_status === 'approved'
                                        ? 'Disetujui'
                                        : ($att->offsite_status === 'rejected'
                                            ? 'Ditolak'
                                            : 'Pending HR') }}
                                </p>
                            @elseif ($att->checkin_location_valid)
                                <p class="text-[10px] text-green-400">📍 Valid</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================================================================
         MODAL KONFIRMASI CHECK-IN (normal)
    ================================================================ --}}
    @if ($showCheckInConfirm)
        <div class="fixed inset-0 flex items-center justify-center p-4"
            style="background:rgba(0,0,0,0.6);z-index:99999;">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
                <p class="text-lg font-bold text-gray-800 text-center mb-1">Konfirmasi Check-In</p>
                <p class="text-sm text-gray-500 text-center mb-3">Pukul <strong>{{ now()->format('H:i') }}</strong> WIB
                </p>
                @if ($gpsReady)
                    <p class="text-xs text-green-600 text-center mb-3">📍 Lokasi terdeteksi</p>
                @endif
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

    {{-- ================================================================
         MODAL KONFIRMASI CHECK-OUT (normal)
    ================================================================ --}}
    @if ($showCheckOutConfirm)
        <div class="fixed inset-0 flex items-center justify-center p-4"
            style="background:rgba(0,0,0,0.6);z-index:99999;">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
                <p class="text-lg font-bold text-gray-800 text-center mb-1">Konfirmasi Check-Out</p>
                <p class="text-sm text-gray-500 text-center mb-3">Pukul <strong>{{ now()->format('H:i') }}</strong> WIB
                </p>
                @if ($gpsReady)
                    <p class="text-xs text-green-600 text-center mb-3">📍 Lokasi terdeteksi</p>
                @endif
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

    {{-- ================================================================
         MODAL KEGIATAN LUAR (muncul otomatis jika GPS di luar radius)
    ================================================================ --}}
    @if ($showOffsiteModal)
        <div class="fixed inset-0 flex items-center justify-center p-4"
            style="background:rgba(0,0,0,0.6);z-index:99999;">
            <div class="bg-white rounded-2xl p-6 w-full max-w-sm">

                {{-- Header --}}
                <div class="text-center mb-4">
                    <p class="text-2xl mb-1"></p>
                    <p class="text-lg font-bold text-gray-800">Kegiatan di Luar Lokasi</p>
                    <p class="text-sm text-gray-500 mt-1">
                        Anda berada di luar area yayasan.<br>
                        Pilih alasan untuk melanjutkan
                        {{ $offsiteAction === 'checkin' ? 'check-in' : 'check-out' }}.
                    </p>
                </div>

                {{-- Form --}}
                <div class="space-y-3">

                    {{-- Pilih alasan --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Kegiatan <span
                                class="text-red-500">*</span></label>
                        <select wire:model.live="offsiteReason"
                            class="w-full rounded-xl border border-gray-200 text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400 bg-white">
                            <option value="">-- Pilih alasan --</option>
                            @foreach ($offsiteReasons as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                        @error('offsiteReason')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Keterangan (selalu tampil, wajib jika Lainnya) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Keterangan
                            @if ($offsiteReason === 'Lainnya')
                                <span class="text-red-500">*</span>
                            @else
                                <span class="text-gray-400">(opsional)</span>
                            @endif
                        </label>
                        <textarea wire:model="offsiteNote" rows="2"
                            placeholder="{{ $offsiteReason === 'Lainnya' ? 'Jelaskan kegiatan...' : 'Nama kegiatan, lokasi, dll.' }}"
                            class="w-full rounded-xl border border-gray-200 text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none"></textarea>
                        @error('offsiteNote')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Info approval --}}
                    <div class="bg-blue-50 rounded-xl px-3 py-2 text-xs text-blue-700">
                        ℹ️ Absensi akan tersimpan dan dikirim ke SDM untuk disetujui.
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex gap-3 mt-4">
                    <button wire:click="cancelOffsite" class="portal-btn-ghost flex-1">Batal</button>
                    <button wire:click="confirmOffsite" wire:loading.attr="disabled"
                        class="portal-btn-primary flex-1">
                        <span wire:loading.remove wire:target="confirmOffsite">
                            {{ $offsiteAction === 'checkin' ? 'Check-In Kegiatan Luar' : 'Check-Out Kegiatan Luar' }}
                        </span>
                        <span wire:loading wire:target="confirmOffsite">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

{{-- GPS Tracker --}}
<script>
    function gpsTracker() {
        return {
            init() {
                this.requestLocation();
            },
            requestLocation() {
                if (!navigator.geolocation) {
                    @this.call('setGpsError', 'Perangkat tidak mendukung GPS.');
                    return;
                }
                @this.call('setGpsLoading');
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        @this.call('setCoordinates', pos.coords.latitude, pos.coords.longitude);
                    },
                    (err) => {
                        const m = {
                            1: 'Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan browser, lalu muat ulang halaman.',
                            2: 'Lokasi tidak tersedia. Pastikan GPS aktif.',
                            3: 'Waktu habis mendeteksi lokasi. Muat ulang halaman.',
                        };
                        @this.call('setGpsError', m[err.code] ?? 'Gagal mendapatkan lokasi.');
                    }, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 60000
                    }
                );
            }
        }
    }
</script>
