<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Position;
use App\Models\School;
use App\Models\Department;

class PositionIndex extends Component
{
    use WithPagination;

    public string $search = '', $schoolFilter = '', $departmentFilter = '', $statusFilter = '';
    public bool $showModal = false, $showDeleteModal = false;
    public ?int $editingId = null, $deletingId = null;
    public int|string $school_id = '', $department_id = '', $level = 1;
    public string $name = '', $description = '';
    public bool $is_active = true;
    public $modalDepts = [];

    protected function rules(): array {
        return ['school_id'=>'required|exists:schools,id','department_id'=>'required|exists:departments,id','name'=>'required|max:255','level'=>'required|integer|min:1|max:5','description'=>'nullable|max:500','is_active'=>'boolean'];
    }
    protected $messages = ['school_id.required'=>'Sekolah wajib dipilih.','department_id.required'=>'Departemen wajib dipilih.','name.required'=>'Nama jabatan wajib diisi.'];

    public function updatedSchoolId($v): void { $this->department_id = ''; $this->modalDepts = Department::active()->where('school_id',$v)->orderBy('name')->get(); }

    public function openCreate(): void { $this->reset(['editingId','school_id','department_id','name','description','modalDepts']); $this->level = 1; $this->is_active = true; $this->resetValidation(); $this->showModal = true; }

    public function openEdit(int $id): void {
        $p = Position::findOrFail($id);
        $this->editingId = $p->id; $this->school_id = $p->school_id; $this->department_id = $p->department_id;
        $this->name = $p->name; $this->level = $p->level; $this->description = $p->description ?? ''; $this->is_active = $p->is_active;
        $this->modalDepts = Department::active()->where('school_id',$p->school_id)->orderBy('name')->get();
        $this->resetValidation(); $this->showModal = true;
    }

    public function save(): void {
        $this->validate();
        $data = ['school_id'=>$this->school_id,'department_id'=>$this->department_id,'name'=>$this->name,'level'=>$this->level,'description'=>$this->description ?: null,'is_active'=>$this->is_active];
        $this->editingId ? Position::findOrFail($this->editingId)->update($data) : Position::create($data);
        session()->flash('success', $this->editingId ? 'Jabatan diperbarui.' : 'Jabatan ditambahkan.');
        $this->showModal = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }
    public function delete(): void { Position::findOrFail($this->deletingId)->delete(); session()->flash('success','Jabatan dihapus.'); $this->showDeleteModal = false; }
    public function toggleStatus(int $id): void { $p = Position::findOrFail($id); $p->update(['is_active'=>!$p->is_active]); session()->flash('success','Status diubah.'); }

    public function render() {
        $positions = Position::with(['school','department'])
            ->when($this->search, fn($q) => $q->where('name','like',"%{$this->search}%"))
            ->when($this->schoolFilter, fn($q) => $q->where('school_id',$this->schoolFilter))
            ->when($this->departmentFilter, fn($q) => $q->where('department_id',$this->departmentFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active',$this->statusFilter))
            ->orderBy('name')->paginate(15);
        $schools     = School::active()->orderBy('name')->get();
        $departments = Department::active()->when($this->schoolFilter, fn($q)=>$q->where('school_id',$this->schoolFilter))->orderBy('name')->get();
        return view('livewire.admin.position-index', compact('positions','schools','departments'));
    }
}
