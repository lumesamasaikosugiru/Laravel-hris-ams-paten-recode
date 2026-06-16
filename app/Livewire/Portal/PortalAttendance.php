<?php
namespace App\Livewire\Portal;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Employee;

class PortalAttendance extends Component
{
    public ?int $selectedSchoolId = null;
    public bool $showSchoolModal  = false;
    public bool $showCheckInConfirm  = false;
    public bool $showCheckOutConfirm = false;

    public function mount(): void
    {
        $employee = $this->getEmployee();
        if ($employee) {
            $this->selectedSchoolId = $employee->school_id;
        }
    }

    private function getEmployee(): ?Employee
    {
        return Employee::where('user_id', auth()->id())
            ->with(['school','additionalAssignment.school'])
            ->first();
    }

    public function checkIn(): void
    {
        $employee = $this->getEmployee();
        if (!$employee) return;

        $today = now()->format('Y-m-d');

        // Cek apakah sudah absen hari ini di unit ini
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->where('school_id', $this->selectedSchoolId)
            ->first();

        if ($existing && $existing->check_in) {
            session()->flash('error', 'Kamu sudah check-in hari ini.');
            $this->showCheckInConfirm = false;
            return;
        }

        // Tentukan status
        $checkInTime  = now();
        $workStart    = now()->setTimeFromTimeString(Attendance::WORK_START);
        $status       = $checkInTime->gt($workStart) ? 'late' : 'present';
        $lateMinutes  = $status === 'late' ? (int) $workStart->diffInMinutes($checkInTime) : 0;

        Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date'        => $today,
                'school_id'   => $this->selectedSchoolId,
            ],
            [
                'check_in'     => $checkInTime->format('H:i:s'),
                'status'       => $status,
                'late_minutes' => $lateMinutes,
                'recorded_by'  => auth()->id(),
            ]
        );

        session()->flash('success', 'Check-in berhasil pukul '.$checkInTime->format('H:i').'.');
        $this->showCheckInConfirm = false;
    }

    public function checkOut(): void
    {
        $employee = $this->getEmployee();
        if (!$employee) return;

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

        $checkOutTime = now();
        $checkIn      = \Carbon\Carbon::parse($attendance->check_in);
        $workMinutes  = (int) $checkIn->diffInMinutes($checkOutTime);

        $attendance->update([
            'check_out'    => $checkOutTime->format('H:i:s'),
            'work_minutes' => $workMinutes,
        ]);

        session()->flash('success', 'Check-out berhasil pukul '.$checkOutTime->format('H:i').'.');
        $this->showCheckOutConfirm = false;
    }

    public function render()
    {
        $employee = $this->getEmployee();
        $today    = now()->format('Y-m-d');

        $todayAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->where('school_id', $this->selectedSchoolId)
                ->first()
            : null;

        // Riwayat 7 hari terakhir
        $history = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->where('school_id', $this->selectedSchoolId)
                ->orderBy('date', 'desc')
                ->limit(14)->get()
            : collect();

        $schools = [];
        if ($employee) {
            $schools[] = ['id' => $employee->school_id, 'name' => $employee->school->name, 'type' => 'Induk'];
            if ($employee->additionalAssignment) {
                $schools[] = [
                    'id'   => $employee->additionalAssignment->school_id,
                    'name' => $employee->additionalAssignment->school->name,
                    'type' => 'Tugas Tambahan',
                ];
            }
        }

        $isWeekend = now()->isWeekend();

        return view('livewire.portal.portal-attendance',
            compact('employee','todayAttendance','history','schools','isWeekend'));
    }
}
