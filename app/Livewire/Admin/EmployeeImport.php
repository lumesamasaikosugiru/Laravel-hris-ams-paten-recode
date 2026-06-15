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
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeImport extends Component
{
    use WithFileUploads;

    public $file = null;
    public bool $previewing = false;
    public bool $importing = false;
    public bool $done = false;
    public array $rows = [];
    public array $errors = [];
    public int $successCount = 0;
    public int $errorCount = 0;
    public string $fileError = '';

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls|max:5120',
    ];

    protected $messages = [
        'file.required' => 'File Excel wajib diupload.',
        'file.mimes' => 'File harus berformat .xlsx atau .xls.',
        'file.max' => 'Ukuran file maksimal 5MB.',
    ];

    public function updatedFile(): void
    {
        $this->fileError = '';
        if (!$this->file)
            return;

        $ext = strtolower($this->file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->fileError = 'File harus berformat .xlsx atau .xls.';
            $this->file = null;
            return;
        }

        if ($this->file->getSize() > 5 * 1024 * 1024) {
            $this->fileError = 'Ukuran file maksimal 5MB.';
            $this->file = null;
            return;
        }

        $this->preview();
    }

    public function preview(): void
    {
        $this->rows = [];
        $this->errors = [];

        try {
            $path = $this->file->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);

            $schools = School::active()->pluck('id', 'name');
            $departments = Department::active()->pluck('id', 'name');
            $positions = Position::active()->pluck('id', 'name');

            foreach ($data as $rowNum => $row) {
                if ($rowNum === 1)
                    continue;   // skip header
                if (empty($row['A']))
                    break;   // stop at empty row

                $rowErrors = [];

                // ── Validasi kolom wajib ──────────────────────
                // A = NIPY, C = Nama, D = Gender
                // I = Unit, J = Tanggal Masuk
                // N = Departemen, O = Jabatan
                if (empty($row['A']))
                    $rowErrors[] = 'NIPY kosong';
                if (empty($row['C']))
                    $rowErrors[] = 'Nama kosong';
                if (empty($row['D']))
                    $rowErrors[] = 'Jenis kelamin kosong';
                if (empty($row['I']))
                    $rowErrors[] = 'Unit/Sekolah kosong';
                if (empty($row['J']))
                    $rowErrors[] = 'Tanggal masuk kosong';
                if (empty($row['N']))
                    $rowErrors[] = 'Departemen kosong';
                if (empty($row['O']))
                    $rowErrors[] = 'Jabatan kosong';

                // ── Validasi gender ───────────────────────────
                $gender = strtolower(trim($row['D'] ?? ''));
                if (!in_array($gender, ['male', 'female', 'laki-laki', 'perempuan'])) {
                    $rowErrors[] = 'Gender tidak valid (isi: male/female)';
                }

                // ── Validasi status ───────────────────────────
                $status = strtolower(trim($row['L'] ?? 'active'));
                if (!in_array($status, ['active', 'probation'])) {
                    $rowErrors[] = 'Status tidak valid (isi: active/probation)';
                }

                // ── Validasi NIPY unik ────────────────────────
                if (!empty($row['A']) && Employee::where('nipy', trim($row['A']))->exists()) {
                    $rowErrors[] = 'NIPY sudah terdaftar';
                }

                // ── Cari school_id ────────────────────────────
                $schoolName = trim($row['I'] ?? '');
                $schoolId = $schools->get($schoolName);
                if (!$schoolId && !empty($schoolName)) {
                    $rowErrors[] = "Sekolah '{$schoolName}' tidak ditemukan";
                }

                // ── Cari department_id ────────────────────────
                $deptName = trim($row['N'] ?? '');
                $deptId = $departments->get($deptName);
                if (!$deptId && !empty($deptName)) {
                    $rowErrors[] = "Departemen '{$deptName}' tidak ditemukan";
                }

                // ── Cari position_id ──────────────────────────
                $posName = trim($row['O'] ?? '');
                $posId = $positions->get($posName);
                if (!$posId && !empty($posName)) {
                    $rowErrors[] = "Jabatan '{$posName}' tidak ditemukan";
                }

                $this->rows[] = [
                    'row' => $rowNum,
                    'nipy' => trim($row['A'] ?? ''),
                    'national_id' => trim($row['B'] ?? '') ?: null,  // NIK KTP opsional
                    'name' => trim($row['C'] ?? ''),
                    'gender' => $gender,
                    'place_of_birth' => trim($row['E'] ?? ''),
                    'date_of_birth' => trim($row['F'] ?? ''),
                    'last_education' => strtolower(trim($row['G'] ?? 's1')),
                    'phone' => trim($row['H'] ?? ''),
                    'school_name' => $schoolName,
                    'school_id' => $schoolId,
                    'join_date' => trim($row['J'] ?? ''),
                    'employee_type' => strtolower(trim($row['K'] ?? 'contract')),
                    'status' => $status,
                    'is_guru' => strtolower(trim($row['M'] ?? 'tidak')) === 'ya',
                    'dept_name' => $deptName,
                    'dept_id' => $deptId,
                    'pos_name' => $posName,
                    'pos_id' => $posId,
                    'email' => trim($row['P'] ?? ''),
                    'address' => trim($row['Q'] ?? ''),
                    'errors' => $rowErrors,
                    'valid' => empty($rowErrors),
                ];
            }

            $this->previewing = true;

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membaca file: ' . $e->getMessage());
        }
    }

    public function import(): void
    {
        $this->importing = true;
        $this->successCount = 0;
        $this->errorCount = 0;
        $this->errors = [];

        foreach ($this->rows as $row) {
            if (!$row['valid']) {
                $this->errorCount++;
                $this->errors[] = "Baris {$row['row']} ({$row['name']}): "
                    . implode(', ', $row['errors']);
                continue;
            }

            try {
                DB::transaction(function () use ($row) {

                    // Normalize gender
                    $gender = in_array($row['gender'], ['laki-laki', 'male'])
                        ? 'male' : 'female';

                    // Parse date_of_birth
                    $dob = null;
                    if (!empty($row['date_of_birth'])) {
                        try {
                            $dob = \Carbon\Carbon::parse($row['date_of_birth'])
                                ->format('Y-m-d');
                        } catch (\Exception $e) {
                            $dob = null;
                        }
                    }

                    // probation_status berdasarkan status
                    $probationStatus = $row['status'] === 'active'
                        ? 'not_applicable'
                        : 'on_probation';

                    $employee = Employee::create([
                        'school_id' => $row['school_id'],
                        'nipy' => $row['nipy'],
                        'nik' => $row['nipy'],   // nik = nipy untuk backward compat
                        'national_id' => $row['national_id'],
                        'name' => $row['name'],
                        'gender' => $gender,
                        'place_of_birth' => $row['place_of_birth'] ?: null,
                        'date_of_birth' => $dob,
                        'last_education' => $row['last_education'] ?: 's1',
                        'phone' => $row['phone'] ?: null,
                        'email' => $row['email'] ?: null,
                        'address' => $row['address'] ?: null,
                        'is_guru' => $row['is_guru'],
                        'join_date' => $row['join_date'],
                        'employee_type' => $row['employee_type'],
                        'status' => $row['status'],
                        'probation_status' => $probationStatus,
                    ]);

                    EmployeeStatusHistory::create([
                        'employee_id' => $employee->id,
                        'employee_type' => $row['employee_type'],
                        'status' => $row['status'],
                        'effective_date' => $row['join_date'],
                        'recorded_by' => auth()->id(),
                        'notes' => 'Diimport dari Excel.',
                    ]);

                    PositionAssignment::create([
                        'employee_id' => $employee->id,
                        'school_id' => $row['school_id'],
                        'department_id' => $row['dept_id'],
                        'position_id' => $row['pos_id'],
                        'start_date' => $row['join_date'],
                        'is_active' => true,
                        'type' => 'assignment',
                        'assignment_type' => 'primary',
                        'notes' => 'Penugasan dari import Excel.',
                    ]);
                });

                $this->successCount++;

            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = "Baris {$row['row']} ({$row['name']}): "
                    . $e->getMessage();
            }
        }

        $this->done = true;
        $this->importing = false;
    }

    public function reset_form(): void
    {
        $this->file = null;
        $this->previewing = false;
        $this->done = false;
        $this->rows = [];
        $this->errors = [];
        $this->successCount = 0;
        $this->errorCount = 0;
    }

    public function render()
    {
        return view('livewire.admin.employee-import');
    }
}