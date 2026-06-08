<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Skill;

class SkillIndex extends Component
{
    use WithPagination;

    public string $search = '', $categoryFilter = '';
    public bool $showModal = false, $showDeleteModal = false;
    public ?int $editingId = null, $deletingId = null;
    public string $name = '', $category = '';
    public bool $is_active = true;

    protected function rules(): array { return ['name'=>'required|max:255','category'=>'nullable|max:100','is_active'=>'boolean']; }
    protected $messages = ['name.required'=>'Nama skill wajib diisi.'];

    public function openCreate(): void { $this->reset(['editingId','name','category']); $this->is_active = true; $this->resetValidation(); $this->showModal = true; }

    public function openEdit(int $id): void {
        $s = Skill::findOrFail($id); $this->editingId = $s->id; $this->name = $s->name; $this->category = $s->category ?? ''; $this->is_active = $s->is_active;
        $this->resetValidation(); $this->showModal = true;
    }

    public function save(): void {
        $this->validate();
        $data = ['name'=>$this->name,'category'=>$this->category ?: null,'is_active'=>$this->is_active];
        $this->editingId ? Skill::findOrFail($this->editingId)->update($data) : Skill::create($data);
        session()->flash('success', $this->editingId ? 'Skill diperbarui.' : 'Skill ditambahkan.');
        $this->showModal = false;
    }

    public function confirmDelete(int $id): void { $this->deletingId = $id; $this->showDeleteModal = true; }
    public function delete(): void { Skill::findOrFail($this->deletingId)->delete(); session()->flash('success','Skill dihapus.'); $this->showDeleteModal = false; }
    public function toggleStatus(int $id): void { $s = Skill::findOrFail($id); $s->update(['is_active'=>!$s->is_active]); session()->flash('success','Status diubah.'); }

    public function render() {
        $categories = Skill::whereNotNull('category')->distinct()->pluck('category')->sort();
        $skills = Skill::when($this->search, fn($q)=>$q->where('name','like',"%{$this->search}%"))
            ->when($this->categoryFilter, fn($q)=>$q->where('category',$this->categoryFilter))
            ->orderBy('category')->orderBy('name')->paginate(15);
        return view('livewire.admin.skill-index', compact('skills','categories'));
    }
}
