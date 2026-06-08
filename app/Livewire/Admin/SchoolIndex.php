<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\School;

class SchoolIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $code = '', $name = '', $address = '', $phone = '', $email = '', $principal_name = '';
    public bool $is_active = true;

    protected function rules(): array {
        return [
            'code'           => 'required|max:20|unique:schools,code,'.($this->editingId ?? 'NULL'),
            'name'           => 'required|max:255',
            'address'        => 'nullable|max:500',
            'phone'          => 'nullable|max:20',
            'email'          => 'nullable|email|max:255',
            'principal_name' => 'nullable|max:255',
            'is_active'      => 'boolean',
        ];
    }

    protected $messages = [
        'code.required' => 'Kode sekolah wajib diisi.',
        'code.unique'   => 'Kode sudah dipakai.',
        'name.required' => 'Nama sekolah wajib diisi.',
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function openCreate(): void { $this->reset(['editingId','code','name','address','phone','email','principal_name']); $this->is_active = true; $this->resetValidation(); $this->showModal = true; }

    public function openEdit(int $id): void {
        $s = School::findOrFail($id);
        $this->editingId = $s->id; $this->code = $s->code; $this->name = $s->name;
        $this->address = $s->address ?? ''; $this->phone = $s->phone ?? '';
        $this->email = $s->email ?? ''; $this->principal_name = $s->principal_name ?? '';
        $this->is_active = $s->is_active; $this->resetValidation(); $this->showModal = true;
    }

    public function save(): void {
        $this->validate();
        $data = ['code'=>strtoupper($this->code),'name'=>$this->name,'address'=>$this->address ?: null,'phone'=>$this->phone ?: null,'email'=>$this->email ?: null,'principal_name'=>$this->principal_name ?: null,'is_active'=>$this->is_active];
        $this->editingId ? School::findOrFail($this->editingId)->update($data) : School::create($data);
        session()->flash('success', $this->editingId ? 'Sekolah diperbarui.' : 'Sekolah ditambahkan.');
        $this->showModal = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }

    public function delete(): void {
        School::findOrFail($this->deletingId)->delete();
        session()->flash('success', 'Sekolah dihapus.');
        $this->showDeleteModal = false;
    }

    public function toggleStatus(int $id): void {
        $s = School::findOrFail($id); $s->update(['is_active' => !$s->is_active]);
        session()->flash('success', 'Status diubah.');
    }

    public function render() {
        $schools = School::query()
            ->when($this->search, fn($q) => $q->where('name','like',"%{$this->search}%")->orWhere('code','like',"%{$this->search}%"))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter))
            ->orderBy('name')->paginate(15);
        return view('livewire.admin.school-index', compact('schools'));
    }
}
