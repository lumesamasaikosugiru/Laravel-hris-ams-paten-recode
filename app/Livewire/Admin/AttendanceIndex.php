<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\School;
use Livewire\Attributes\On;

class AttendanceIndex extends Component
{
    use WithPagination;

    public string $dateFilter = '';
    public string $schoolFilter = '';
    public string $statusFilter = '';
    public string $search = '';
    public bool $showModal = false;
    public bool $showEditModal = false;
    public ?int $selectedEmployeeId = null;
    public string $manualDate = '';
    public string $manualCheckIn = '';
    public string $manualCheckOut = '';
    public string $manualNotes = '';
    public ?int $editingId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('attendance.view'), 403);
        $this->dateFilter = now()->format('Y-m-d');
        $this->manualDate = now()->format('Y-m-d');
        $this->manualCheckIn = now()->format('H:i');
    }

    public function openModal(): void
    {
        abort_unless(auth()->user()->can('attendance.create'), 403);
        $this->selectedEmployeeId = null;
        $this->manualDate = now()->format('Y-m-d');
        $this->manualCheckIn = now()->format('H:i');
        $this->manualCheckOut = '';
        $this->manualNotes = '';
        $this->editingId = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveManual(): void
    {
        abort_unless(auth()->user()->can('attendance.create'), 403);
        $this->validate([
            'selectedEmployeeId' => 'required|exists:employees,id',
            'manualDate' => 'required|date',
            'manualCheckIn' => 'required',
        ], [
            'selectedEmployeeId.required' => 'Pilih pegawai terlebih dahulu.',
            'manualDate.required' => 'Tanggal wajib diisi.',
            'manualCheckIn.required' => 'Jam masuk wajib diisi.',
        ]);

        $employee = Employee::findOrFail($this->selectedEmployeeId);
        $calc = Attendance::calculate($this->manualCheckIn, $this->manualCheckOut ?: null);

        Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $this->manualDate],
            [
                'school_id' => $employee->school_id,
                'check_in' => $this->manualCheckIn,
                'check_out' => $this->manualCheckOut ?: null,
                'status' => $calc['status'],
                'late_minutes' => $calc['lateMinutes'],
                'work_minutes' => $calc['workMinutes'],
                'notes' => $this->manualNotes ?: null,
                'recorded_by' => auth()->id(),
            ]
        );

        session()->flash('success', 'Absensi berhasil disimpan.');
        $this->showModal = false;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('attendance.edit'), 403);
        $att = Attendance::with('employee')->findOrFail($id);
        $this->editingId = $id;
        $this->selectedEmployeeId = $att->employee_id;
        $this->manualDate = $att->date->format('Y-m-d');
        $this->manualCheckIn = $att->check_in ?? '';
        $this->manualCheckOut = $att->check_out ?? '';
        $this->manualNotes = $att->notes ?? '';
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function updateAttendance(): void
    {
        abort_unless(auth()->user()->can('attendance.edit'), 403);
        $calc = $this->manualCheckIn
            ? Attendance::calculate($this->manualCheckIn, $this->manualCheckOut ?: null)
            : ['status' => 'absent', 'lateMinutes' => 0, 'workMinutes' => 0];

        Attendance::findOrFail($this->editingId)->update([
            'check_in' => $this->manualCheckIn ?: null,
            'check_out' => $this->manualCheckOut ?: null,
            'status' => $calc['status'],
            'late_minutes' => $calc['lateMinutes'],
            'work_minutes' => $calc['workMinutes'],
            'notes' => $this->manualNotes ?: null,
            'recorded_by' => auth()->id(),
        ]);

        session()->flash('success', 'Data absensi diperbarui.');
        $this->showEditModal = false;
    }

    public function render()
    {
        $attendances = Attendance::with(['employee.activeAssignment.position', 'school'])
            ->when($this->dateFilter, fn($q) => $q->where('date', $this->dateFilter))
            ->when($this->schoolFilter, fn($q) => $q->where('school_id', $this->schoolFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($eq) =>
                $eq->where('name', 'like', "%{$this->search}%")
                    ->orWhere('nik', 'like', "%{$this->search}%")))
            ->orderBy('check_in')
            ->paginate(20);

        $todaySummary = [
            'present' => Attendance::where('date', $this->dateFilter)->where('status', 'present')->count(),
            'late' => Attendance::where('date', $this->dateFilter)->where('status', 'late')->count(),
            'absent' => Attendance::where('date', $this->dateFilter)->where('status', 'absent')->count(),
            'total' => Employee::where('status', 'active')->count(),
        ];

        $schools = School::active()->orderBy('name')->get();
        $activeEmployees = Employee::whereIn('status', ['active', 'probation'])->orderBy('name')->get();
        $editingEmployee = $this->editingId
            ? Attendance::with('employee')->find($this->editingId)?->employee
            : null;

        return view(
            'livewire.admin.attendance-index',
            compact('attendances', 'todaySummary', 'schools', 'activeEmployees', 'editingEmployee')
        );
    }
    #[On('setEmployee')]
    public function setEmployee(?int $id): void
    {
        $this->selectedEmployeeId = $id;
    }
}