<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\JobVacancy;
use App\Models\School;
use App\Models\Department;
use App\Models\Position;

class JobIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $schoolFilter = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public int|string $school_id = '';
    public int|string $department_id = '';
    public int|string $position_id = '';
    public string $title = '';
    public string $description = '';
    public string $requirements = '';
    public string $employment_type = 'contract';
    public int|string $quota = 1;
    public string $open_date = '';
    public string $close_date = '';
    public string $status = 'draft';

    // Dynamic dropdowns
    public $modalDepts = [];
    public $modalPositions = [];

    protected function rules(): array
    {
        return [
            'school_id' => 'required|exists:schools,id',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'employment_type' => 'required|in:permanent,contract,intern',
            'quota' => 'required|integer|min:1',
            'open_date' => 'required|date',
            'close_date' => 'nullable|date|after:open_date',
            'status' => 'required|in:draft,open,closed',
        ];
    }

    protected $messages = [
        'school_id.required' => 'Unit/Sekolah wajib dipilih.',
        'department_id.required' => 'Departemen wajib dipilih.',
        'position_id.required' => 'Jabatan wajib dipilih.',
        'title.required' => 'Judul lowongan wajib diisi.',
        'open_date.required' => 'Tanggal buka wajib diisi.',
        'close_date.after' => 'Tanggal tutup harus setelah tanggal buka.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingSchoolFilter(): void
    {
        $this->resetPage();
    }
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSchoolId($value): void
    {
        $this->department_id = '';
        $this->position_id = '';
        $this->modalPositions = [];
        $this->modalDepts = Department::active()
            ->where('school_id', $value)
            ->orderBy('name')->get();
    }

    public function updatedDepartmentId($value): void
    {
        $this->position_id = '';
        $this->modalPositions = Position::active()
            ->where('department_id', $value)
            ->orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->reset([
            'editingId',
            'school_id',
            'department_id',
            'position_id',
            'title',
            'description',
            'requirements',
            'close_date',
            'modalDepts',
            'modalPositions'
        ]);
        $this->employment_type = 'contract';
        $this->quota = 1;
        $this->status = 'draft';
        $this->open_date = now()->format('Y-m-d');
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $job = JobVacancy::findOrFail($id);
        $this->editingId = $job->id;
        $this->school_id = $job->school_id;
        $this->department_id = $job->department_id;
        $this->position_id = $job->position_id;
        $this->title = $job->title;
        $this->description = $job->description ?? '';
        $this->requirements = $job->requirements ?? '';
        $this->employment_type = $job->employment_type;
        $this->quota = $job->quota;
        $this->open_date = $job->open_date->format('Y-m-d');
        $this->close_date = $job->close_date?->format('Y-m-d') ?? '';
        $this->status = $job->status;
        $this->modalDepts = Department::active()
            ->where('school_id', $job->school_id)->orderBy('name')->get();
        $this->modalPositions = Position::active()
            ->where('department_id', $job->department_id)->orderBy('name')->get();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'school_id' => $this->school_id,
            'department_id' => $this->department_id,
            'position_id' => $this->position_id,
            'title' => $this->title,
            'description' => $this->description ?: null,
            'requirements' => $this->requirements ?: null,
            'employment_type' => $this->employment_type,
            'quota' => $this->quota,
            'open_date' => $this->open_date,
            'close_date' => $this->close_date ?: null,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            JobVacancy::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Lowongan berhasil diperbarui.');
        } else {
            JobVacancy::create($data);
            session()->flash('success', 'Lowongan berhasil dibuat.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'modalDepts', 'modalPositions']);
    }

    public function changeStatus(int $id, string $status): void
    {
        JobVacancy::findOrFail($id)->update(['status' => $status]);
        session()->flash('success', 'Status lowongan diperbarui.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        JobVacancy::findOrFail($this->deletingId)->delete();
        session()->flash('success', 'Lowongan dihapus.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function render()
    {
        $jobs = JobVacancy::with(['school', 'department', 'position'])
            ->withCount('applicants')
            ->when($this->search, fn($q) =>
                $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->schoolFilter, fn($q) =>
                $q->where('school_id', $this->schoolFilter))
            ->when($this->statusFilter, fn($q) =>
                $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        $schools = School::active()->orderBy('name')->get();

        return view('livewire.admin.job-index', compact('jobs', 'schools'));
    }
}
