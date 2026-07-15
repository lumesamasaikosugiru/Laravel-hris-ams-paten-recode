<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Applicant;
use App\Models\JobVacancy;
use App\Models\Employee;
use App\Models\School;
use App\Models\PositionAssignment;
use App\Models\EmployeeStatusHistory;
use App\Services\NipyGenerator;
use Illuminate\Support\Facades\DB;

class ApplicantIndex extends Component
{
    use WithPagination;
    use \Livewire\WithFileUploads;

    public string $search = '';
    public string $jobFilter = '';
    public string $statusFilter = '';
    public string $sourceFilter = '';  // '' = semua, 'public_form' = portal, 'admin_input' = walk-in

    // ── Walk-in input modal ───────────────────────────────────
    public bool $showWalkInModal = false;
    public string $wi_name = '';
    public string $wi_email = '';
    public string $wi_phone = '';
    public string $wi_gender = 'male';
    public string $wi_applied_position = '';
    public string $wi_last_education = '';
    public string $wi_hr_notes = '';
    public $wi_cv_file = null;

    // Detail modal
    public bool $showDetailModal = false;
    public ?int $viewingId = null;
    public string $hrNotes = '';

    // Convert modal
    public bool $showConvertModal = false;
    public ?int $convertingId = null;
    public string $convertNik = '';
    public string $convertJoinDate = '';
    public string $convertType = 'contract';
    public bool $convertIsGuru = false;
    public string $convertNote = '';
    public int|string $convertSchoolId = '';  // hanya untuk walk-in
    public bool $convertIsWalkIn = false;     // flag untuk tampilkan dropdown sekolah

    public function mount(): void
    {
        abort_unless(auth()->user()->can('recruitment.view'), 403);
    }

    // ── Walk-in input ─────────────────────────────────────────
    public function openWalkInModal(): void
    {
        abort_unless(auth()->user()->can('recruitment.create'), 403);
        $this->reset([
            'wi_name',
            'wi_email',
            'wi_phone',
            'wi_gender',
            'wi_applied_position',
            'wi_last_education',
            'wi_hr_notes',
            'wi_cv_file',
        ]);
        $this->wi_gender = 'male';
        $this->resetValidation();
        $this->showWalkInModal = true;
    }

    public function saveWalkIn(): void
    {
        abort_unless(auth()->user()->can('recruitment.create'), 403);
        $this->validate([
            'wi_name' => 'required|string|max:255',
            'wi_email' => 'required|email|max:255',
            'wi_phone' => 'nullable|string|max:20',
            'wi_gender' => 'required|in:male,female',
            'wi_applied_position' => 'required|string|max:255',
            'wi_last_education' => 'nullable|in:sd,smp,sma,d3,s1,s2,s3',
            'wi_hr_notes' => 'nullable|string|max:1000',
            'wi_cv_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'wi_name.required' => 'Nama pelamar wajib diisi.',
            'wi_email.required' => 'Email wajib diisi.',
            'wi_email.email' => 'Format email tidak valid.',
            'wi_applied_position.required' => 'Posisi yang dilamar wajib diisi.',
        ]);

        $cvPath = null;
        if ($this->wi_cv_file) {
            $cvPath = $this->wi_cv_file->store('applicants/cv', 'public');
        }

        Applicant::create([
            'job_vacancy_id' => null,           // walk-in: tidak terikat lowongan
            'applied_position' => $this->wi_applied_position,
            'name' => $this->wi_name,
            'email' => $this->wi_email,
            'phone' => $this->wi_phone ?: null,
            'gender' => $this->wi_gender,
            'last_education' => $this->wi_last_education ?: null,
            'cv_file' => $cvPath,
            'status' => 'submitted',
            'hr_notes' => $this->wi_hr_notes ?: null,
            'source' => 'admin_input',  // penanda walk-in
        ]);

        session()->flash('success', 'Pelamar walk-in berhasil ditambahkan.');
        $this->showWalkInModal = false;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingJobFilter(): void
    {
        $this->resetPage();
    }
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    // ── Pipeline update ───────────────────────────────────────
    public function updateStatus(int $id, string $status): void
    {
        abort_unless(auth()->user()->can('recruitment.edit'), 403);
        Applicant::findOrFail($id)->update(['status' => $status]);
        session()->flash('success', 'Status pelamar diperbarui.');
    }

    // ── Detail modal ──────────────────────────────────────────
    public function openDetail(int $id): void
    {
        $applicant = Applicant::findOrFail($id);
        $this->viewingId = $id;
        $this->hrNotes = $applicant->hr_notes ?? '';
        $this->showDetailModal = true;
    }

    public function saveNotes(): void
    {
        abort_unless(auth()->user()->can('recruitment.edit'), 403);
        Applicant::findOrFail($this->viewingId)->update(['hr_notes' => $this->hrNotes]);
        session()->flash('success', 'Catatan HR disimpan.');
        $this->showDetailModal = false;
    }

    // ── Convert modal ─────────────────────────────────────────
    public function openConvert(int $id): void
    {
        abort_unless(auth()->user()->can('recruitment.convert'), 403);
        $applicant = Applicant::with('jobVacancy')->findOrFail($id);
        $this->convertingId = $id;
        $this->convertNik = NipyGenerator::generateTemporaryNik();
        $this->convertJoinDate = now()->format('Y-m-d');
        $this->convertType = $applicant->jobVacancy?->employment_type ?? 'contract';
        $this->convertIsGuru = false;
        $this->convertNote = '';
        $this->convertIsWalkIn = $applicant->is_walk_in;
        $this->convertSchoolId = '';   // HR harus pilih untuk walk-in
        $this->resetValidation();
        $this->showConvertModal = true;
    }

    public function convertToEmployee(): void
    {
        abort_unless(auth()->user()->can('recruitment.convert'), 403);
        $this->validate([
            'convertNik' => 'required|string|max:30|unique:employees,nik',
            'convertJoinDate' => 'required|date',
            'convertType' => 'required|in:permanent,contract,intern',
            'convertSchoolId' => $this->convertIsWalkIn ? 'required|exists:schools,id' : 'nullable',
        ], [
            'convertNik.required' => 'NIK wajib diisi.',
            'convertNik.unique' => 'NIK sudah digunakan.',
            'convertJoinDate.required' => 'Tanggal masuk wajib diisi.',
            'convertSchoolId.required' => 'Sekolah/Unit kerja wajib dipilih untuk pelamar walk-in.',
        ]);

        $applicant = Applicant::with('jobVacancy')->findOrFail($this->convertingId);

        // Walk-in: sekolah dipilih HR di modal. Public form: dari jobVacancy.
        $schoolId = $applicant->is_walk_in
            ? $this->convertSchoolId
            : $applicant->jobVacancy?->school_id;
        $departmentId = $applicant->jobVacancy?->department_id ?? null;
        $positionId = $applicant->jobVacancy?->position_id ?? null;

        DB::transaction(function () use ($applicant, $schoolId, $departmentId, $positionId) {
            $joinDate = \Carbon\Carbon::parse($this->convertJoinDate);
            $probationEnd = NipyGenerator::calculateProbationEndDate($joinDate, $this->convertIsGuru);

            $employee = Employee::create([
                'school_id' => $schoolId,
                'applicant_id' => $applicant->id,
                'nik' => $this->convertNik,
                'nipy' => null,
                'name' => $applicant->name,
                'email' => $applicant->email,
                'phone' => $applicant->phone,
                'gender' => $applicant->gender,
                'place_of_birth' => $applicant->place_of_birth,
                'date_of_birth' => $applicant->date_of_birth,
                'address' => $applicant->address,
                'last_education' => $applicant->last_education,
                'last_education_major' => $applicant->last_education_major,
                'last_education_institution' => $applicant->last_education_institution,
                'is_guru' => $this->convertIsGuru,
                'join_date' => $this->convertJoinDate,
                'employee_type' => $this->convertType,
                'status' => 'probation',
                'probation_start_date' => $this->convertJoinDate,
                'probation_end_date' => $probationEnd->format('Y-m-d'),
                'probation_status' => 'on_probation',
            ]);

            // Hanya buat PositionAssignment kalau data jabatan tersedia
            // (pelamar dari lowongan). Walk-in: HR lengkapi lewat edit pegawai.
            if ($schoolId && $departmentId && $positionId) {
                PositionAssignment::create([
                    'employee_id' => $employee->id,
                    'school_id' => $schoolId,
                    'department_id' => $departmentId,
                    'position_id' => $positionId,
                    'start_date' => $this->convertJoinDate,
                    'is_active' => true,
                    'assignment_type' => 'primary',
                    'type' => 'assignment',
                    'notes' => 'Masa percobaan ' . ($this->convertIsGuru ? '6' : '3') . ' bulan.',
                ]);
            }

            EmployeeStatusHistory::create([
                'employee_id' => $employee->id,
                'employee_type' => $this->convertType,
                'status' => 'probation',
                'effective_date' => $this->convertJoinDate,
                'recorded_by' => auth()->id(),
                'notes' => 'Diterima dari rekrutmen. Masa percobaan s/d ' . $probationEnd->format('d M Y') . '.'
                    . ($applicant->is_walk_in ? ' (Walk-in, unit kerja perlu dilengkapi manual).' : ''),
            ]);

            $applicant->update([
                'status' => 'diterima',
                'converted_to_employee_id' => $employee->id,
                'converted_at' => now(),
                'converted_by' => auth()->id(),
            ]);
        });

        $msg = 'Pelamar berhasil dijadikan pegawai. Masa percobaan dimulai.';
        if ($applicant->is_walk_in) {
            $msg .= ' Lengkapi unit kerja & jabatan di halaman detail pegawai.';
        }
        session()->flash('success', $msg);
        $this->showConvertModal = false;
    }

    public function render()
    {
        $applicants = Applicant::with(['jobVacancy.school', 'jobVacancy.position'])
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->jobFilter, fn($q) => $q->where('job_vacancy_id', $this->jobFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->sourceFilter, fn($q) => $q->where('source', $this->sourceFilter))
            ->latest()
            ->paginate(15);

        $jobs = JobVacancy::orderBy('title')->get();
        $schools = School::active()->orderBy('name')->get();
        $viewing = $this->viewingId
            ? Applicant::with(['jobVacancy', 'educations', 'experiences'])->find($this->viewingId)
            : null;

        return view('livewire.admin.applicant-index', compact('applicants', 'jobs', 'schools', 'viewing'));
    }
}