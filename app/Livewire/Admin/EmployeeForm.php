<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Employee;
use App\Models\School;
use App\Models\Department;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\EmployeeStatusHistory;
use App\Services\NipyGenerator;
use Illuminate\Support\Facades\DB;

class EmployeeForm extends Component
{
    use WithFileUploads;

    public ?Employee $employee = null;
    public bool $isEdit = false;
    public string $activeTab = 'identity';

    // ── Identity ─────────────────────────────────────────────
    public string $nik = '';
    public string $name = '';
    public string $national_id = '';
    public string $gender = 'male';
    public string $place_of_birth = '';
    public string $date_of_birth = '';
    public string $religion = 'islam';
    public string $marital_status = 'single';
    public string $nationality = 'Indonesia';

    // ── Contact ──────────────────────────────────────────────
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public string $emergency_contact_relation = '';

    // ── Employment ───────────────────────────────────────────
    public int|string $school_id = '';
    public bool $is_guru = false;
    public string $join_date = '';
    public string $employee_type = 'contract';
    public string $contract_start = '';
    public string $contract_end = '';
    public string $status = 'active';

    // ── Education ────────────────────────────────────────────
    public string $last_education = 's1';
    public string $last_education_major = '';
    public string $last_education_institution = '';

    // ── Assignment (only create) ─────────────────────────────
    public int|string $department_id = '';
    public int|string $position_id = '';
    public string $assignment_start = '';
    public string $assignment_notes = '';
    public $modalDepts = [];
    public $modalPositions = [];

    // ── Photo ────────────────────────────────────────────────
    public $photo = null;

    // ── Tab order ────────────────────────────────────────────
    private array $tabs = ['identity', 'contact', 'employment', 'education', 'assignment'];

    protected function rules(): array
    {
        $nikUnique = 'unique:employees,nik' . ($this->isEdit ? ",{$this->employee->id}" : '');

        return [
            'nik' => "required|string|max:30|{$nikUnique}",
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'school_id' => 'required|exists:schools,id',
            'join_date' => 'required|date',
            'employee_type' => 'required|in:permanent,contract,intern',
            'status' => 'required|in:probation,active,inactive,resigned,terminated',
            'national_id' => 'nullable|string|max:16',
            'date_of_birth' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after:contract_start',
            'department_id' => $this->isEdit ? 'nullable' : 'required|exists:departments,id',
            'position_id' => $this->isEdit ? 'nullable' : 'required|exists:positions,id',
            'assignment_start' => $this->isEdit ? 'nullable' : 'required|date',
            'photo' => 'nullable|image|max:2048',
        ];
    }

    protected $messages = [
        'nik.required' => 'NIK wajib diisi.',
        'nik.unique' => 'NIK sudah digunakan.',
        'name.required' => 'Nama pegawai wajib diisi.',
        'school_id.required' => 'Unit/sekolah wajib dipilih.',
        'join_date.required' => 'Tanggal masuk wajib diisi.',
        'department_id.required' => 'Departemen wajib dipilih.',
        'position_id.required' => 'Jabatan wajib dipilih.',
        'assignment_start.required' => 'Tanggal mulai jabatan wajib diisi.',
        'contract_end.after' => 'Tanggal selesai kontrak harus setelah tanggal mulai.',
    ];

    // ── Mount ─────────────────────────────────────────────────
    public function mount(?int $employeeId = null): void
    {
        if ($employeeId) {
            $this->isEdit = true;
            $this->employee = Employee::findOrFail($employeeId);
            $this->fillForm();
        } else {
            $this->join_date = now()->format('Y-m-d');
            $this->assignment_start = now()->format('Y-m-d');
            $this->status = 'active';
        }
    }

    private function fillForm(): void
    {
        $e = $this->employee;
        $this->nik = $e->nik;
        $this->name = $e->name;
        $this->national_id = $e->national_id ?? '';
        $this->gender = $e->gender;
        $this->place_of_birth = $e->place_of_birth ?? '';
        $this->date_of_birth = $e->date_of_birth?->format('Y-m-d') ?? '';
        $this->religion = $e->religion ?? 'islam';
        $this->marital_status = $e->marital_status ?? 'single';
        $this->nationality = $e->nationality;
        $this->email = $e->email ?? '';
        $this->phone = $e->phone ?? '';
        $this->address = $e->address ?? '';
        $this->emergency_contact_name = $e->emergency_contact_name ?? '';
        $this->emergency_contact_phone = $e->emergency_contact_phone ?? '';
        $this->emergency_contact_relation = $e->emergency_contact_relation ?? '';
        $this->school_id = $e->school_id;
        $this->is_guru = $e->is_guru;
        $this->join_date = $e->join_date->format('Y-m-d');
        $this->employee_type = $e->employee_type;
        $this->contract_start = $e->contract_start?->format('Y-m-d') ?? '';
        $this->contract_end = $e->contract_end?->format('Y-m-d') ?? '';
        $this->status = $e->status;
        $this->last_education = $e->last_education ?? 's1';
        $this->last_education_major = $e->last_education_major ?? '';
        $this->last_education_institution = $e->last_education_institution ?? '';
    }

    // ── Dropdown cascade ──────────────────────────────────────
    public function updatedSchoolId($value): void
    {
        $this->department_id = '';
        $this->position_id = '';
        $this->modalPositions = [];
        $this->modalDepts = Department::active()
            ->where('school_id', $value)->orderBy('name')->get();
    }

    public function updatedDepartmentId($value): void
    {
        $this->position_id = '';
        $this->modalPositions = Position::active()
            ->where('department_id', $value)->orderBy('name')->get();
    }

    // ── Tab navigation ────────────────────────────────────────
    public function nextTab(): void
    {
        $current = array_search($this->activeTab, $this->tabs);
        if ($current !== false && $current < count($this->tabs) - 1) {
            $this->activeTab = $this->tabs[$current + 1];
        }
    }

    public function prevTab(): void
    {
        $current = array_search($this->activeTab, $this->tabs);
        if ($current > 0) {
            $this->activeTab = $this->tabs[$current - 1];
        }
    }

    // ── Save ─────────────────────────────────────────────────
    public function save(): void
    {
        // Validasi + arahkan ke tab yang error
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $tabFields = [
                'identity' => [
                    'nik',
                    'name',
                    'gender',
                    'national_id',
                    'place_of_birth',
                    'date_of_birth',
                    'religion',
                    'marital_status',
                    'photo'
                ],
                'contact' => [
                    'email',
                    'phone',
                    'address',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                    'emergency_contact_relation'
                ],
                'employment' => [
                    'school_id',
                    'join_date',
                    'employee_type',
                    'contract_start',
                    'contract_end',
                    'status'
                ],
                'education' => ['last_education', 'last_education_major', 'last_education_institution'],
                'assignment' => ['department_id', 'position_id', 'assignment_start'],
            ];

            foreach ($tabFields as $tab => $fields) {
                foreach ($fields as $field) {
                    if (isset($e->errors()[$field])) {
                        $this->activeTab = $tab;
                        throw $e;
                    }
                }
            }

            throw $e;
        }

        $employeeId = null;

        DB::transaction(function () use (&$employeeId) {
            $photoPath = $this->employee?->photo;
            if ($this->photo) {
                $photoPath = $this->photo->store('employees/photos', 'public');
            }

            $data = [
                'school_id' => $this->school_id,
                'nik' => $this->nik,
                'name' => $this->name,
                'national_id' => $this->national_id ?: null,
                'gender' => $this->gender,
                'place_of_birth' => $this->place_of_birth ?: null,
                'date_of_birth' => $this->date_of_birth ?: null,
                'religion' => $this->religion,
                'marital_status' => $this->marital_status,
                'nationality' => $this->nationality,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'address' => $this->address ?: null,
                'emergency_contact_name' => $this->emergency_contact_name ?: null,
                'emergency_contact_phone' => $this->emergency_contact_phone ?: null,
                'emergency_contact_relation' => $this->emergency_contact_relation ?: null,
                'is_guru' => $this->is_guru,
                'join_date' => $this->join_date,
                'employee_type' => $this->employee_type,
                'contract_start' => $this->contract_start ?: null,
                'contract_end' => $this->contract_end ?: null,
                'status' => $this->status,
                'probation_status' => $this->status === 'active' ? 'not_applicable' : 'on_probation',
                'last_education' => $this->last_education ?: null,
                'last_education_major' => $this->last_education_major ?: null,
                'last_education_institution' => $this->last_education_institution ?: null,
                'photo' => $photoPath,
            ];

            if ($this->isEdit) {
                $this->employee->update($data);

                $last = $this->employee->statusHistories()->latest()->first();
                if (
                    !$last || $last->status !== $this->status
                    || $last->employee_type !== $this->employee_type
                ) {
                    EmployeeStatusHistory::create([
                        'employee_id' => $this->employee->id,
                        'employee_type' => $this->employee_type,
                        'status' => $this->status,
                        'effective_date' => now()->format('Y-m-d'),
                        'recorded_by' => auth()->id(),
                        'notes' => 'Diperbarui melalui form edit.',
                    ]);
                }

                $employeeId = $this->employee->id;
                session()->flash('success', "Data {$this->name} berhasil diperbarui.");

            } else {
                $employee = Employee::create($data);

                EmployeeStatusHistory::create([
                    'employee_id' => $employee->id,
                    'employee_type' => $this->employee_type,
                    'status' => $this->status,
                    'effective_date' => $this->join_date,
                    'recorded_by' => auth()->id(),
                    'notes' => 'Pegawai ditambahkan secara manual.',
                ]);

                PositionAssignment::create([
                    'employee_id' => $employee->id,
                    'school_id' => $this->school_id,
                    'department_id' => $this->department_id,
                    'position_id' => $this->position_id,
                    'start_date' => $this->assignment_start,
                    'is_active' => true,
                    'type' => 'assignment',
                    'notes' => $this->assignment_notes ?: null,
                ]);

                $employeeId = $employee->id;
                session()->flash('success', "{$this->name} berhasil ditambahkan.");
            }
        });

        // Redirect ke halaman detail pegawai
        $this->redirect(route('admin.employees.show', $employeeId));
    }

    // ── Render ────────────────────────────────────────────────
    public function render()
    {
        $schools = School::active()->orderBy('name')->get();

        if ($this->isEdit && $this->school_id && empty($this->modalDepts)) {
            $this->modalDepts = Department::active()
                ->where('school_id', $this->school_id)->orderBy('name')->get();
        }
        if ($this->isEdit && $this->department_id && empty($this->modalPositions)) {
            $this->modalPositions = Position::active()
                ->where('department_id', $this->department_id)->orderBy('name')->get();
        }

        return view('livewire.admin.employee-form', compact('schools'));
    }
}