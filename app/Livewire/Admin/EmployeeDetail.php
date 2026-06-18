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
    public string $assign_type = 'mutation';
    public int|string $assign_dept_id = '';
    public int|string $assign_pos_id = '';
    public string $assign_start_date = '';
    public string $assign_notes = '';
    public $assignDepts = [];
    public $assignPositions = [];

    public bool $showDeleteModal = false;

    public function confirmDelete(): void
    {
        // Pengaman backend: tombol Hapus sudah disembunyikan di tampilan
        // untuk role tanpa employee.delete, tapi tetap dicek ulang di
        // sini supaya tidak bisa dipicu lewat manipulasi state Livewire.
        abort_unless(auth()->user()->can('employee.delete'), 403, 'Anda tidak memiliki izin untuk menghapus data pegawai.');

        $this->showDeleteModal = true;
    }

    // ── Probation evaluation modal ────────────────────────────
    public bool $showProbationModal = false;
    public string $probation_decision = 'passed';
    public string $probation_notes = '';

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

    /**
     * Method ini SENGAJA diberi nama deleteEmployee(), bukan delete().
     *
     * Alasan: "delete" adalah nama yang berpotensi konflik di Livewire —
     * baik dengan method internal framework, atau dengan binding magic
     * lain yang dikenali Livewire secara khusus pada nama-nama umum
     * tertentu (create/update/delete/save dianggap "reserved-like" di
     * banyak versi Livewire meski tidak selalu didokumentasikan jelas).
     * Simtom yang terjadi sebelumnya — tombol macet permanen di status
     * "loading" tanpa request baru tercatat di Network tab dan tanpa
     * log error apa pun — konsisten dengan request yang gagal di-dispatch
     * Livewire SEBELUM sampai ke server, bukan gagal di server.
     */
    public function deleteEmployee(): void
    {
        // Pengaman kedua (defense in depth): cek ulang di sini untuk
        // menutup kemungkinan request langsung ke method ini tanpa
        // melalui confirmDelete() yang normal.
        abort_unless(auth()->user()->can('employee.delete'), 403, 'Anda tidak memiliki izin untuk menghapus data pegawai.');

        $emp = $this->employee;

        if ($emp->attendances()->exists() || $emp->leaveRequests()->exists()) {
            session()->flash(
                'error',
                'Pegawai ini memiliki riwayat absensi atau cuti. ' .
                'Ubah status menjadi Nonaktif/Diberhentikan sebagai gantinya.'
            );
            $this->showDeleteModal = false;
            return;
        }

        $name = $emp->name;

        try {
            DB::transaction(function () use ($emp) {
                // Lepas link ke akun User (jika ada) SEBELUM menghapus,
                // supaya tidak ditolak oleh foreign key constraint
                // employees.user_id -> users.id.
                if ($emp->user_id) {
                    $emp->update(['user_id' => null]);
                }

                $emp->delete();
            });

            session()->flash('success', "{$name} berhasil dihapus.");
            $this->redirect(route('admin.employees.index'));

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Gagal menghapus pegawai: ' . $e->getMessage());
            session()->flash(
                'error',
                "Gagal menghapus {$name}. Data ini masih memiliki keterkaitan dengan data lain di sistem."
            );
            $this->showDeleteModal = false;
        }
    }

    private function generateNipyPreview(): string
    {
        $year = $this->employee->join_date->format('y');
        $edu = NipyGenerator::getEducationCode($this->employee->last_education);
        $emp = NipyGenerator::getEmploymentCode(
            $this->employee->is_guru,
            $this->employee->employee_type
        );
        return $year . $edu . $emp . 'XXXX';
    }

    // ── Assignment ────────────────────────────────────────────
    public function updatedAssignDeptId($value): void
    {
        $this->assign_pos_id = '';
        $this->assignPositions = Position::active()
            ->where('department_id', $value)->orderBy('name')->get();
    }

    public function openAssignModal(): void
    {
        $this->reset(['assign_dept_id', 'assign_pos_id', 'assign_notes']);
        $this->assign_type = 'mutation';
        $this->assign_start_date = now()->format('Y-m-d');
        $this->assignPositions = [];
        $this->resetValidation();
        $this->showAssignModal = true;
    }

    public function saveAssignment(): void
    {
        $this->validate([
            'assign_type' => 'required|in:mutation,promotion,demotion',
            'assign_dept_id' => 'required|exists:departments,id',
            'assign_pos_id' => 'required|exists:positions,id',
            'assign_start_date' => 'required|date',
            'assign_notes' => 'nullable|string|max:500',
        ], [
            'assign_dept_id.required' => 'Departemen wajib dipilih.',
            'assign_pos_id.required' => 'Jabatan wajib dipilih.',
            'assign_start_date.required' => 'Tanggal mulai wajib diisi.',
        ]);

        DB::transaction(function () {
            // Tutup assignment aktif
            PositionAssignment::where('employee_id', $this->employee->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => $this->assign_start_date,
                ]);

            // Buat assignment baru
            PositionAssignment::create([
                'employee_id' => $this->employee->id,
                'school_id' => $this->employee->school_id,
                'department_id' => $this->assign_dept_id,
                'position_id' => $this->assign_pos_id,
                'start_date' => $this->assign_start_date,
                'is_active' => true,
                'type' => $this->assign_type,
                'notes' => $this->assign_notes ?: null,
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
        $this->probation_notes = '';
        $this->nipyPreview = $this->generateNipyPreview();
        $this->showProbationModal = true;
    }

    public function submitEvaluation(): void
    {
        $this->validate([
            'probation_decision' => 'required|in:passed,failed',
            'probation_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () {
            if ($this->probation_decision === 'passed') {
                $nipy = NipyGenerator::generate($this->employee);

                $this->employee->update([
                    'nipy' => $nipy,
                    'status' => 'active',
                    'probation_status' => 'passed',
                    'probation_evaluated_at' => now()->format('Y-m-d'),
                    'probation_evaluated_by' => auth()->id(),
                    'probation_notes' => $this->probation_notes ?: 'Lulus masa percobaan.',
                ]);

                EmployeeStatusHistory::create([
                    'employee_id' => $this->employee->id,
                    'employee_type' => $this->employee->employee_type,
                    'status' => 'active',
                    'effective_date' => now()->format('Y-m-d'),
                    'recorded_by' => auth()->id(),
                    'notes' => "Lulus masa percobaan. NIPY diterbitkan: {$nipy}",
                ]);

                session()->flash(
                    'success',
                    "Pegawai lulus masa percobaan. NIPY: {$nipy}"
                );

            } else {
                $this->employee->update([
                    'status' => 'terminated',
                    'probation_status' => 'failed',
                    'probation_evaluated_at' => now()->format('Y-m-d'),
                    'probation_evaluated_by' => auth()->id(),
                    'probation_notes' => $this->probation_notes ?: 'Tidak lulus masa percobaan.',
                ]);

                EmployeeStatusHistory::create([
                    'employee_id' => $this->employee->id,
                    'employee_type' => $this->employee->employee_type,
                    'status' => 'terminated',
                    'effective_date' => now()->format('Y-m-d'),
                    'recorded_by' => auth()->id(),
                    'notes' => 'Tidak lulus masa percobaan. '
                        . ($this->probation_notes ?? ''),
                ]);

                session()->flash('success', 'Evaluasi disimpan. Pegawai diberhentikan.');
            }
        });

        $this->employee = $this->employee->fresh();
        $this->showProbationModal = false;
    }

    public function render()
    {
        return view('livewire.admin.employee-detail');
    }
}