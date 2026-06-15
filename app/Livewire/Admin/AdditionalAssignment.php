<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\School;
use Illuminate\Support\Facades\DB;

class AdditionalAssignment extends Component
{
    public Employee $employee;

    // Modal tambah
    public bool $showAddModal    = false;
    public bool $showEndModal    = false;
    public bool $showDetailModal = false;

    // Form
    public int|string $school_id     = '';
    public int|string $department_id = '';
    public int|string $position_id   = '';
    public string     $start_date    = '';
    public string     $notes         = '';

    // Dropdown cascade
    public $depts     = [];
    public $positions = [];

    // Ending
    public string $end_notes = '';

    protected function rules(): array
    {
        return [
            'school_id'     => 'required|exists:schools,id',
            'department_id' => 'required|exists:departments,id',
            'position_id'   => 'required|exists:positions,id',
            'start_date'    => 'required|date',
            'notes'         => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'school_id.required'     => 'Unit wajib dipilih.',
        'department_id.required' => 'Departemen wajib dipilih.',
        'position_id.required'   => 'Jabatan wajib dipilih.',
        'start_date.required'    => 'Tanggal mulai wajib diisi.',
    ];

    public function mount(Employee $employee): void
    {
        $this->employee   = $employee;
        $this->start_date = now()->format('Y-m-d');
    }

    public function updatedSchoolId($value): void
    {
        $this->department_id = '';
        $this->position_id   = '';
        $this->positions     = [];
        $this->depts = Department::active()
            ->where('school_id', $value)
            ->orderBy('name')->get();
    }

    public function updatedDepartmentId($value): void
    {
        $this->position_id = '';
        $this->positions   = Position::active()
            ->where('department_id', $value)
            ->orderBy('name')->get();
    }

    public function openAddModal(): void
    {
        // Validasi: hanya boleh satu tugas tambahan
        if ($this->employee->has_additional_assignment) {
            session()->flash('error', 'Pegawai ini sudah memiliki tugas tambahan aktif.');
            return;
        }

        $this->reset(['school_id','department_id','position_id','notes','depts','positions']);
        $this->start_date = now()->format('Y-m-d');
        $this->resetValidation();
        $this->showAddModal = true;
    }

    public function saveAdditional(): void
    {
        $this->validate();

        // Pastikan unit berbeda dari unit induk
        if ($this->school_id == $this->employee->school_id) {
            $this->addError('school_id', 'Tugas tambahan harus di unit yang berbeda dari unit induk.');
            return;
        }

        DB::transaction(function () {
            PositionAssignment::create([
                'employee_id'     => $this->employee->id,
                'school_id'       => $this->school_id,
                'department_id'   => $this->department_id,
                'position_id'     => $this->position_id,
                'start_date'      => $this->start_date,
                'is_active'       => true,
                'type'            => 'assignment',
                'assignment_type' => 'additional',
                'notes'           => $this->notes ?: 'Tugas tambahan.',
            ]);
        });

        session()->flash('success', 'Tugas tambahan berhasil ditambahkan.');
        $this->showAddModal = false;
        $this->employee->refresh();
    }

    public function openEndModal(): void
    {
        $this->end_notes = '';
        $this->resetValidation();
        $this->showEndModal = true;
    }

    public function endAdditional(): void
    {
        $additional = $this->employee->additionalAssignment;
        if (!$additional) return;

        DB::transaction(function () use ($additional) {
            $additional->update([
                'is_active' => false,
                'end_date'  => now()->format('Y-m-d'),
                'notes'     => $this->end_notes
                    ? 'Tugas tambahan diakhiri: '.$this->end_notes
                    : 'Tugas tambahan diakhiri.',
            ]);
        });

        session()->flash('success', 'Tugas tambahan berhasil diakhiri.');
        $this->showEndModal = false;
        $this->employee->refresh();
    }

    public function render()
    {
        $schools    = School::active()
            ->where('id', '!=', $this->employee->school_id) // exclude unit induk
            ->orderBy('name')->get();

        $additional = $this->employee->additionalAssignment()
            ->with(['school','department','position'])->first();

        $history = PositionAssignment::where('employee_id', $this->employee->id)
            ->where('assignment_type', 'additional')
            ->with(['school','department','position'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('livewire.admin.additional-assignment',
            compact('schools','additional','history'));
    }
}
