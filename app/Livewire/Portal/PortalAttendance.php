<?php

namespace App\Livewire\Portal;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\GeofenceService;
use Carbon\Carbon;
use Livewire\Component;

class PortalAttendance extends Component
{
    // ── State UI ──────────────────────────────────────────────────────────────
    public ?int $selectedSchoolId = null;
    public bool $showCheckInConfirm = false;
    public bool $showCheckOutConfirm = false;

    // ── GPS ───────────────────────────────────────────────────────────────────
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $gpsError = '';
    public bool $gpsReady = false;
    public bool $gpsLoading = false;

    // ── Off-site flow ─────────────────────────────────────────────────────────
    // Muncul saat GPS valid tapi di luar radius
    public bool $showOffsiteModal = false;
    public string $offsiteReason = '';   // pilihan dari dropdown
    public string $offsiteNote = '';   // keterangan bebas
    public string $offsiteAction = '';   // 'checkin' | 'checkout'

    // Daftar alasan yang tersedia
    public array $offsiteReasons = [
        'Lomba / Kompetisi',
        'Rapat Dinas / Eksternal',
        'Kunjungan Industri',
        'Pelatihan / Workshop',
        'Kegiatan Yayasan',
        'Lainnya',
    ];

    // ─────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $employee = $this->getEmployee();
        if ($employee) {
            $this->selectedSchoolId = $employee->school_id;
        }
    }

    // ── GPS callbacks (dipanggil dari Alpine.js) ──────────────────────────────

    public function setCoordinates(float $lat, float $lng): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->gpsReady = true;
        $this->gpsLoading = false;
        $this->gpsError = '';
    }

    public function setGpsError(string $error): void
    {
        $this->gpsError = $error;
        $this->gpsReady = false;
        $this->gpsLoading = false;
    }

    public function setGpsLoading(): void
    {
        $this->gpsLoading = true;
        $this->gpsError = '';
    }

    // ── Off-site modal ────────────────────────────────────────────────────────

    /** Tutup modal dan reset state off-site */
    public function cancelOffsite(): void
    {
        $this->showOffsiteModal = false;
        $this->offsiteReason = '';
        $this->offsiteNote = '';
        $this->offsiteAction = '';
    }

    /** Dipanggil dari modal — simpan check-in/out off-site */
    public function confirmOffsite(): void
    {
        $this->validate([
            'offsiteReason' => 'required',
            'offsiteNote' => $this->offsiteReason === 'Lainnya' ? 'required|min:5' : 'nullable',
        ], [
            'offsiteReason.required' => 'Pilih alasan kegiatan luar.',
            'offsiteNote.required' => 'Isi keterangan untuk alasan "Lainnya".',
            'offsiteNote.min' => 'Keterangan minimal 5 karakter.',
        ]);

        if ($this->offsiteAction === 'checkin') {
            $this->doCheckIn(offsite: true);
        } else {
            $this->doCheckOut(offsite: true);
        }

        $this->cancelOffsite();
    }

    // ── Check-In ─────────────────────────────────────────────────────────────

    public function checkIn(): void
    {
        $employee = $this->getEmployee();
        if (!$employee)
            return;

        if (!$this->gpsReady || is_null($this->latitude)) {
            session()->flash('error', 'Izinkan akses lokasi terlebih dahulu.');
            $this->showCheckInConfirm = false;
            return;
        }

        $today = now()->format('Y-m-d');
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->where('school_id', $this->selectedSchoolId)
            ->first();

        if ($existing && $existing->check_in) {
            session()->flash('error', 'Kamu sudah check-in hari ini.');
            $this->showCheckInConfirm = false;
            return;
        }

        $geo = app(GeofenceService::class)->check($this->latitude, $this->longitude);

        if (!$geo['valid']) {
            // Di luar radius → tampilkan modal off-site
            $this->showCheckInConfirm = false;
            $this->offsiteAction = 'checkin';
            $this->showOffsiteModal = true;
            return;
        }

        $this->showCheckInConfirm = false;
        $this->doCheckIn(offsite: false, geo: $geo);
    }

    public function checkOut(): void
    {
        $employee = $this->getEmployee();
        if (!$employee)
            return;

        if (!$this->gpsReady || is_null($this->latitude)) {
            session()->flash('error', 'Izinkan akses lokasi terlebih dahulu.');
            $this->showCheckOutConfirm = false;
            return;
        }

        $today = now()->format('Y-m-d');
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->where('school_id', $this->selectedSchoolId)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            session()->flash('error', 'Kamu belum check-in hari ini.');
            $this->showCheckOutConfirm = false;
            return;
        }

        if ($attendance->check_out) {
            session()->flash('error', 'Kamu sudah check-out hari ini.');
            $this->showCheckOutConfirm = false;
            return;
        }

        $geo = app(GeofenceService::class)->check($this->latitude, $this->longitude);

        if (!$geo['valid']) {
            $this->showCheckOutConfirm = false;
            $this->offsiteAction = 'checkout';
            $this->showOffsiteModal = true;
            return;
        }

        $this->showCheckOutConfirm = false;
        $this->doCheckOut(offsite: false, geo: $geo);
    }

    // ── Internal: simpan check-in ─────────────────────────────────────────────

    private function doCheckIn(bool $offsite, array $geo = []): void
    {
        $employee = $this->getEmployee();
        $today = now()->format('Y-m-d');
        $checkInTime = now();
        $workStart = now()->setTimeFromTimeString(Attendance::WORK_START);
        $status = $checkInTime->gt($workStart) ? 'late' : 'present';
        $lateMinutes = $status === 'late' ? (int) $workStart->diffInMinutes($checkInTime) : 0;

        Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $today,
                'school_id' => $this->selectedSchoolId,
            ],
            [
                'check_in' => $checkInTime->format('H:i:s'),
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'recorded_by' => auth()->id(),

                // GPS
                'checkin_latitude' => $this->latitude,
                'checkin_longitude' => $this->longitude,
                'checkin_location_valid' => !$offsite,
                'checkin_location_name' => $offsite ? 'Kegiatan Luar' : ($geo['location_name'] ?? '-'),

                // Off-site
                'is_offsite' => $offsite,
                'offsite_reason' => $offsite ? $this->offsiteReason : null,
                'offsite_note' => $offsite ? ($this->offsiteNote ?: null) : null,
                'offsite_status' => $offsite ? 'pending' : null,
            ]
        );

        if ($offsite) {
            session()->flash(
                'success',
                "Check-in berhasil pukul {$checkInTime->format('H:i')}. "
                . "⏳ Kegiatan luar menunggu persetujuan HR."
            );
        } else {
            session()->flash(
                'success',
                "Check-in berhasil pukul {$checkInTime->format('H:i')}. 📍 {$geo['location_name']}"
            );
        }
    }

    private function doCheckOut(bool $offsite, array $geo = []): void
    {
        $employee = $this->getEmployee();
        $today = now()->format('Y-m-d');
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->where('school_id', $this->selectedSchoolId)
            ->first();

        $checkOutTime = now();
        $workMinutes = (int) Carbon::parse($attendance->check_in)->diffInMinutes($checkOutTime);

        $attendance->update([
            'check_out' => $checkOutTime->format('H:i:s'),
            'work_minutes' => $workMinutes,

            // GPS
            'checkout_latitude' => $this->latitude,
            'checkout_longitude' => $this->longitude,
            'checkout_location_valid' => !$offsite,
            'checkout_location_name' => $offsite ? 'Kegiatan Luar' : ($geo['location_name'] ?? '-'),

            // Off-site (hanya update jika check-out yang off-site, bukan check-in)
            'is_offsite' => $attendance->is_offsite || $offsite,
            'offsite_reason' => $attendance->offsite_reason ?? ($offsite ? $this->offsiteReason : null),
            'offsite_note' => $attendance->offsite_note ?? ($offsite ? ($this->offsiteNote ?: null) : null),
            'offsite_status' => ($attendance->is_offsite || $offsite) ? ($attendance->offsite_status ?? 'pending') : null,
        ]);

        if ($offsite) {
            session()->flash(
                'success',
                "Check-out berhasil pukul {$checkOutTime->format('H:i')}. "
                . "⏳ Kegiatan luar menunggu persetujuan HR."
            );
        } else {
            session()->flash(
                'success',
                "Check-out berhasil pukul {$checkOutTime->format('H:i')}. 📍 {$geo['location_name']}"
            );
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $employee = $this->getEmployee();
        $today = now()->format('Y-m-d');

        $todayAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->where('school_id', $this->selectedSchoolId)
                ->first()
            : null;

        $history = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->where('school_id', $this->selectedSchoolId)
                ->orderBy('date', 'desc')
                ->limit(14)->get()
            : collect();

        $schools = [];
        if ($employee) {
            $schools[] = [
                'id' => $employee->school_id,
                'name' => $employee->school->name,
                'type' => 'Induk',
            ];
            if ($employee->additionalAssignment) {
                $schools[] = [
                    'id' => $employee->additionalAssignment->school_id,
                    'name' => $employee->additionalAssignment->school->name,
                    'type' => 'Tugas Tambahan',
                ];
            }
        }

        return view(
            'livewire.portal.portal-attendance',
            compact('employee', 'todayAttendance', 'history', 'schools')
            + ['isWeekend' => now()->isWeekend()]
        );
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function getEmployee(): ?Employee
    {
        return Employee::where('user_id', auth()->id())
            ->with(['school', 'additionalAssignment.school'])
            ->first();
    }
}