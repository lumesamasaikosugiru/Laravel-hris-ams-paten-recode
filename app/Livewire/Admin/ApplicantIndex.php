<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Applicant;
use App\Models\JobVacancy;
use App\Models\Employee;
use App\Models\PositionAssignment;
use App\Models\EmployeeStatusHistory;
use App\Services\NipyGenerator;
use Illuminate\Support\Facades\DB;

class ApplicantIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $jobFilter = '';
    public string $statusFilter = '';

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

    public function mount(): void
    {
        abort_unless(auth()->user()->can('recruitment.view'), 403);
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
        $this->convertType = $applicant->jobVacancy->employment_type;
        $this->convertIsGuru = false;
        $this->convertNote = '';
        $this->showConvertModal = true;
    }

    public function convertToEmployee(): void
    {
        abort_unless(auth()->user()->can('recruitment.convert'), 403);
        $this->validate([
            'convertNik' => 'required|string|max:30|unique:employees,nik',
            'convertJoinDate' => 'required|date',
            'convertType' => 'required|in:permanent,contract,intern',
        ], [
            'convertNik.required' => 'NIK wajib diisi.',
            'convertNik.unique' => 'NIK sudah digunakan.',
            'convertJoinDate.required' => 'Tanggal masuk wajib diisi.',
        ]);

        $applicant = Applicant::with('jobVacancy')->findOrFail($this->convertingId);

        DB::transaction(function () use ($applicant) {
            $joinDate = \Carbon\Carbon::parse($this->convertJoinDate);
            $probationEnd = NipyGenerator::calculateProbationEndDate($joinDate, $this->convertIsGuru);

            $employee = Employee::create([
                'school_id' => $applicant->jobVacancy->school_id,
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

            PositionAssignment::create([
                'employee_id' => $employee->id,
                'school_id' => $applicant->jobVacancy->school_id,
                'department_id' => $applicant->jobVacancy->department_id,
                'position_id' => $applicant->jobVacancy->position_id,
                'start_date' => $this->convertJoinDate,
                'is_active' => true,
                'type' => 'assignment',
                'notes' => 'Masa percobaan ' . ($this->convertIsGuru ? '6' : '3') . ' bulan.',
            ]);

            EmployeeStatusHistory::create([
                'employee_id' => $employee->id,
                'employee_type' => $this->convertType,
                'status' => 'probation',
                'effective_date' => $this->convertJoinDate,
                'recorded_by' => auth()->id(),
                'notes' => 'Diterima dari rekrutmen. Masa percobaan s/d ' . $probationEnd->format('d M Y') . '.',
            ]);

            $applicant->update([
                'status' => 'diterima',
                'converted_to_employee_id' => $employee->id,
                'converted_at' => now(),
                'converted_by' => auth()->id(),
            ]);
        });

        session()->flash('success', 'Pelamar berhasil dijadikan pegawai. Masa percobaan dimulai.');
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
            ->latest()
            ->paginate(15);

        $jobs = JobVacancy::orderBy('title')->get();
        $viewing = $this->viewingId
            ? Applicant::with(['jobVacancy', 'educations', 'experiences'])->find($this->viewingId)
            : null;

        return view('livewire.admin.applicant-index', compact('applicants', 'jobs', 'viewing'));
    }
}