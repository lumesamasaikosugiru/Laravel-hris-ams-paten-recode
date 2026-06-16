<?php

namespace App\Livewire\Portal;

use App\Models\Attendance;
use App\Models\Employee;
use App\Services\GeofenceService;
use Carbon\Carbon;
use Livewire\Component;

class PortalAttendance extends Component
{
    // ── State UI (sudah ada sebelumnya) ──────────────────────────────────────
    public ?int $selectedSchoolId = null;
    public bool $showCheckInConfirm = false;
    public bool $showCheckOutConfirm = false;

    // ── GPS — ditambahkan ─────────────────────────────────────────────────────
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $gpsError = '';
    public bool $gpsReady = false;
    public bool $gpsLoading = false;

    // ─────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $employee = $this->getEmployee();
        if ($employee) {
            $this->selectedSchoolId = $employee->school_id;
        }
    }

    // ── GPS — dipanggil dari Alpine.js ────────────────────────────────────────

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

    // ── Check-In ─────────────────────────────────────────────────────────────

    public function checkIn(): void
    {
        $employee = $this->getEmployee();
        if (!$employee)
            return;

        // Validasi GPS
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

        // Validasi geofence
        $geo = app(GeofenceService::class)->check($this->latitude, $this->longitude);
        $strict = config('geofence.strict', true);

        if ($strict && !$geo['valid']) {
            session()->flash(
                'error',
                "Check-in gagal. Anda berada ±{$geo['distance']} m dari lokasi terdekat ({$geo['nearest']}). "
                . "Maksimum " . config('geofence.radius') . " m."
            );
            $this->showCheckInConfirm = false;
            return;
        }

        // Hitung status & keterlambatan
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
                // Kolom GPS baru
                'checkin_latitude' => $this->latitude,
                'checkin_longitude' => $this->longitude,
                'checkin_location_valid' => $geo['valid'],
                'checkin_location_name' => $geo['location_name'],
            ]
        );

        $locMsg = $geo['valid']
            ? " 📍 {$geo['location_name']}"
            : " ⚠️ Lokasi di luar area (dicatat untuk audit).";

        session()->flash('success', "Check-in berhasil pukul {$checkInTime->format('H:i')}.{$locMsg}");
        $this->showCheckInConfirm = false;
    }

    // ── Check-Out ─────────────────────────────────────────────────────────────

    public function checkOut(): void
    {
        $employee = $this->getEmployee();
        if (!$employee)
            return;

        // Validasi GPS
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

        // Validasi geofence
        $geo = app(GeofenceService::class)->check($this->latitude, $this->longitude);
        $strict = config('geofence.strict', true);

        if ($strict && !$geo['valid']) {
            session()->flash(
                'error',
                "Check-out gagal. Anda berada ±{$geo['distance']} m dari lokasi terdekat ({$geo['nearest']}). "
                . "Maksimum " . config('geofence.radius') . " m."
            );
            $this->showCheckOutConfirm = false;
            return;
        }

        $checkOutTime = now();
        $workMinutes = (int) Carbon::parse($attendance->check_in)->diffInMinutes($checkOutTime);

        $attendance->update([
            'check_out' => $checkOutTime->format('H:i:s'),
            'work_minutes' => $workMinutes,
            // Kolom GPS baru
            'checkout_latitude' => $this->latitude,
            'checkout_longitude' => $this->longitude,
            'checkout_location_valid' => $geo['valid'],
            'checkout_location_name' => $geo['location_name'],
        ]);

        $locMsg = $geo['valid']
            ? " 📍 {$geo['location_name']}"
            : " ⚠️ Lokasi di luar area (dicatat untuk audit).";

        session()->flash('success', "Check-out berhasil pukul {$checkOutTime->format('H:i')}.{$locMsg}");
        $this->showCheckOutConfirm = false;
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

        $isWeekend = now()->isWeekend();

        return view(
            'livewire.portal.portal-attendance',
            compact('employee', 'todayAttendance', 'history', 'schools', 'isWeekend')
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