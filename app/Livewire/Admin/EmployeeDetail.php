<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\EmployeeStatusHistory;
use App\Services\NipyGenerator;
use Illuminate\Support\Facades\DB;

class EmployeeDetail extends Component
{
    public Employee $employee;

    // ── Assignment modal ──────────────────────────────────────
    public bool $showAssignModal = false;
    public string $assign_type        = 'mutation';
    public int|string $assign_dept_id = '';
    public int|string $assign_pos_id  = '';
    public string $assign_start_date  = '';
    public string $assign_notes       = '';
    public $assignDepts    = [];
    public $assignPositions = [];

    // ── Probation evaluation modal ────────────────────────────
    public bool $showProbationModal = false;
    public string $probation_decision = 'passed';
    public string $probation_notes    = '';

    // ── Preview NIPY ──────────────────────────────────────────
    public string $nipyPreview = '';

    public function mount(Employee $employee): void
    {
        $this->employee = $employee->load([
            'school',
            'positionAssignments.position',
            'positionAssignments.department',
            'positionAssignments.school',
            'statusHistories.recordedBy',
            'activeAssignment.position',
            'activeAssignment.department',
        ]);

        $this->assign_start_date = now()->format('Y-m-d');
        $this->assignDepts = Department::active()
            ->where('school_id', $employee->school_id)
            ->orderBy('name')->get();

        // Preview NIPY
        $this->nipyPreview = $this->generateNipyPreview();
    }

    private function generateNipyPreview(): string
    {
        $year = $this->employee->join_date->format('y');
        $edu  = NipyGenerator::getEducationCode($this->employee->last_education);
        $emp  = NipyGenerator::getEmploymentCode(
            $this->employee->is_guru,
            $this->employee->employee_type
        );
        return $year . $edu . $emp . 'XXXX';
    }

    // ── Assignment ────────────────────────────────────────────
    public function updatedAssignDeptId($value): void
    {
        $this->assign_pos_id    = '';
        $this->assignPositions  = Position::active()
            ->where('department_id', $value)->orderBy('name')->get();
    }

    public function openAssignModal(): void
    {
        $this->reset(['assign_dept_id','assign_pos_id','assign_notes']);
        $this->assign_type       = 'mutation';
        $this->assign_start_date = now()->format('Y-m-d');
        $this->assignPositions   = [];
        $this->resetValidation();
        $this->showAssignModal   = true;
    }

    public function saveAssignment(): void
    {
        $this->validate([
            'assign_type'       => 'required|in:mutation,promotion,demotion',
            'assign_dept_id'    => 'required|exists:departments,id',
            'assign_pos_id'     => 'required|exists:positions,id',
            'assign_start_date' => 'required|date',
            'assign_notes'      => 'nullable|string|max:500',
        ], [
            'assign_dept_id.required'    => 'Departemen wajib dipilih.',
            'assign_pos_id.required'     => 'Jabatan wajib dipilih.',
            'assign_start_date.required' => 'Tanggal mulai wajib diisi.',
        ]);

        DB::transaction(function () {
            // Tutup assignment aktif
            PositionAssignment::where('employee_id', $this->employee->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date'  => $this->assign_start_date,
                ]);

            // Buat assignment baru
            PositionAssignment::create([
                'employee_id'   => $this->employee->id,
                'school_id'     => $this->employee->school_id,
                'department_id' => $this->assign_dept_id,
                'position_id'   => $this->assign_pos_id,
                'start_date'    => $this->assign_start_date,
                'is_active'     => true,
                'type'          => $this->assign_type,
                'notes'         => $this->assign_notes ?: null,
            ]);
        });

        // Reload
        $this->employee = $this->employee->fresh([
            'positionAssignments.position',
            'positionAssignments.department',
            'activeAssignment.position',
            'activeAssignment.department',
        ]);

        session()->flash('success', 'Penugasan jabatan berhasil disimpan.');
        $this->showAssignModal = false;
    }

    // ── Probation Evaluation ──────────────────────────────────
    public function openProbationModal(): void
    {
        $this->probation_decision = 'passed';
        $this->probation_notes    = '';
        $this->nipyPreview        = $this->generateNipyPreview();
        $this->showProbationModal = true;
    }

    public function submitEvaluation(): void
    {
        $this->validate([
            'probation_decision' => 'required|in:passed,failed',
            'probation_notes'    => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () {
            if ($this->probation_decision === 'passed') {
                $nipy = NipyGenerator::generate($this->employee);

                $this->employee->update([
                    'nipy'                   => $nipy,
                    'status'                 => 'active',
                    'probation_status'       => 'passed',
                    'probation_evaluated_at' => now()->format('Y-m-d'),
                    'probation_evaluated_by' => auth()->id(),
                    'probation_notes'        => $this->probation_notes ?: 'Lulus masa percobaan.',
                ]);

                EmployeeStatusHistory::create([
                    'employee_id'    => $this->employee->id,
                    'employee_type'  => $this->employee->employee_type,
                    'status'         => 'active',
                    'effective_date' => now()->format('Y-m-d'),
                    'recorded_by'    => auth()->id(),
                    'notes'          => "Lulus masa percobaan. NIPY diterbitkan: {$nipy}",
                ]);

                session()->flash('success',
                    "Pegawai lulus masa percobaan. NIPY: {$nipy}");

            } else {
                $this->employee->update([
                    'status'                 => 'terminated',
                    'probation_status'       => 'failed',
                    'probation_evaluated_at' => now()->format('Y-m-d'),
                    'probation_evaluated_by' => auth()->id(),
                    'probation_notes'        => $this->probation_notes ?: 'Tidak lulus masa percobaan.',
                ]);

                EmployeeStatusHistory::create([
                    'employee_id'    => $this->employee->id,
                    'employee_type'  => $this->employee->employee_type,
                    'status'         => 'terminated',
                    'effective_date' => now()->format('Y-m-d'),
                    'recorded_by'    => auth()->id(),
                    'notes'          => 'Tidak lulus masa percobaan. '
                        .($this->probation_notes ?? ''),
                ]);

                session()->flash('success', 'Evaluasi disimpan. Pegawai diberhentikan.');
            }
        });

        $this->employee           = $this->employee->fresh();
        $this->showProbationModal = false;
    }

    public function render()
    {
        return view('livewire.admin.employee-detail');
    }
}
