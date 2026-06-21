<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Department;
use App\Models\School;

class DepartmentIndex extends Component
{
    use WithPagination;

    public string $search = '', $schoolFilter = '', $statusFilter = '';
    public bool $showModal = false, $showDeleteModal = false;
    public ?int $editingId = null, $deletingId = null;
    public int|string $school_id = '';
    public string $code = '', $name = '', $description = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return ['school_id' => 'required|exists:schools,id', 'code' => 'required|max:20', 'name' => 'required|max:255', 'description' => 'nullable|max:500', 'is_active' => 'boolean'];
    }
    protected $messages = ['school_id.required' => 'Sekolah wajib dipilih.', 'code.required' => 'Kode wajib diisi.', 'name.required' => 'Nama wajib diisi.'];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('master.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'school_id', 'code', 'name', 'description']);
        $this->is_active = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $d = Department::findOrFail($id);
        $this->editingId = $d->id;
        $this->school_id = $d->school_id;
        $this->code = $d->code;
        $this->name = $d->name;
        $this->description = $d->description ?? '';
        $this->is_active = $d->is_active;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()->can($this->editingId ? 'master.edit' : 'master.create'),
            403
        );
        $this->validate();
        $exists = Department::where('school_id', $this->school_id)->where('code', strtoupper($this->code))->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))->exists();
        if ($exists) {
            $this->addError('code', 'Kode sudah dipakai di sekolah ini.');
            return;
        }
        $data = ['school_id' => $this->school_id, 'code' => strtoupper($this->code), 'name' => $this->name, 'description' => $this->description ?: null, 'is_active' => $this->is_active];
        $this->editingId ? Department::findOrFail($this->editingId)->update($data) : Department::create($data);
        session()->flash('success', $this->editingId ? 'Departemen diperbarui.' : 'Departemen ditambahkan.');
        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }
    public function delete(): void
    {
        abort_unless(auth()->user()->can('master.delete'), 403);
        Department::findOrFail($this->deletingId)->delete();
        session()->flash('success', 'Departemen dihapus.');
        $this->showDeleteModal = false;
    }
    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()->can('master.edit'), 403);
        $d = Department::findOrFail($id);
        $d->update(['is_active' => !$d->is_active]);
        session()->flash('success', 'Status diubah.');
    }

    public function render()
    {
        $departments = Department::with('school')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
            ->when($this->schoolFilter, fn($q) => $q->where('school_id', $this->schoolFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->orderBy('name')->paginate(15);
        $schools = School::active()->orderBy('name')->get();
        return view('livewire.admin.department-index', compact('departments', 'schools'));
    }
}