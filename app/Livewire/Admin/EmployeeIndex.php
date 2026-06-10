<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\School;

class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $schoolFilter  = '';
    public string $typeFilter    = '';
    public string $statusFilter  = 'active';

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingSchoolFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function render()
    {
        $employees = Employee::with([
                'school',
                'activeAssignment.position',
                'activeAssignment.department',
            ])
            ->when($this->search, fn($q) => $q
                ->where('name',  'like', "%{$this->search}%")
                ->orWhere('nik', 'like', "%{$this->search}%")
                ->orWhere('nipy','like', "%{$this->search}%")
                ->orWhere('email','like',"%{$this->search}%"))
            ->when($this->schoolFilter, fn($q) => $q->where('school_id', $this->schoolFilter))
            ->when($this->typeFilter,   fn($q) => $q->where('employee_type', $this->typeFilter))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('name')
            ->paginate(15);

        $schools = School::active()->orderBy('name')->get();

        $counts = [
            'active'    => Employee::where('status','active')->count(),
            'probation' => Employee::where('status','probation')->count(),
            'inactive'  => Employee::whereIn('status',['inactive','resigned','terminated'])->count(),
        ];

        return view('livewire.admin.employee-index',
            compact('employees','schools','counts'));
    }
}
