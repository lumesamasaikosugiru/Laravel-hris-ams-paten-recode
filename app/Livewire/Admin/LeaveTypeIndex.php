<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveType;

class LeaveTypeIndex extends Component
{
    use WithPagination;

    public string $search = '', $statusFilter = '';
    public bool $showModal = false, $showDeleteModal = false;
    public ?int $editingId = null, $deletingId = null;
    public string $name = '', $gender = 'all', $cycle = 'annual', $description = '';
    public int|string $quota = 12;
    public bool $requires_document = false, $is_active = true;

    protected function rules(): array
    {
        return ['name' => 'required|max:255', 'quota' => 'required|integer|min:1|max:365', 'gender' => 'required|in:all,male,female', 'cycle' => 'required|in:annual,once', 'requires_document' => 'boolean', 'description' => 'nullable|max:500', 'is_active' => 'boolean'];
    }
    protected $messages = ['name.required' => 'Nama jenis cuti wajib diisi.', 'quota.required' => 'Kuota hari wajib diisi.'];

    public function mount(): void
    {
        abort_unless(auth()->user()->can('master.view'), 403);
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->quota = 12;
        $this->gender = 'all';
        $this->cycle = 'annual';
        $this->requires_document = false;
        $this->is_active = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $lt = LeaveType::findOrFail($id);
        $this->editingId = $lt->id;
        $this->name = $lt->name;
        $this->quota = $lt->quota;
        $this->gender = $lt->gender;
        $this->cycle = $lt->cycle;
        $this->requires_document = $lt->requires_document;
        $this->description = $lt->description ?? '';
        $this->is_active = $lt->is_active;
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
        $data = ['name' => $this->name, 'quota' => $this->quota, 'gender' => $this->gender, 'cycle' => $this->cycle, 'requires_document' => $this->requires_document, 'description' => $this->description ?: null, 'is_active' => $this->is_active];
        $this->editingId ? LeaveType::findOrFail($this->editingId)->update($data) : LeaveType::create($data);
        session()->flash('success', $this->editingId ? 'Jenis cuti diperbarui.' : 'Jenis cuti ditambahkan.');
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
        LeaveType::findOrFail($this->deletingId)->delete();
        session()->flash('success', 'Jenis cuti dihapus.');
        $this->showDeleteModal = false;
    }
    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()->can('master.edit'), 403);
        $lt = LeaveType::findOrFail($id);
        $lt->update(['is_active' => !$lt->is_active]);
        session()->flash('success', 'Status diubah.');
    }

    public function render()
    {
        $leaveTypes = LeaveType::when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->orderBy('name')->paginate(15);
        return view('livewire.admin.leave-type-index', compact('leaveTypes'));
    }
}