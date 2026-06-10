<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveBalance as LeaveBalanceModel;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\School;

class LeaveBalance extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $schoolFilter = '';
    public string $typeFilter   = '';
    public int    $yearFilter;

    public bool $showGenerateModal = false;
    public int  $generateYear;

    public function mount(): void
    {
        $this->yearFilter    = now()->year;
        $this->generateYear  = now()->year;
    }

    public function generateBalances(): void
    {
        LeaveBalanceModel::generateForYear($this->generateYear);
        session()->flash('success', "Saldo cuti tahun {$this->generateYear} berhasil di-generate untuk semua pegawai aktif.");
        $this->showGenerateModal = false;
    }

    public function render()
    {
        $employees = Employee::with(['school','activeAssignment.position'])
            ->whereIn('status',['active','probation'])
            ->when($this->search, fn($q) => $q->where('name','like',"%{$this->search}%"))
            ->when($this->schoolFilter, fn($q) => $q->where('school_id', $this->schoolFilter))
            ->orderBy('name')
            ->paginate(20);

        $employeeIds = $employees->pluck('id');
        $balances    = LeaveBalanceModel::whereIn('employee_id', $employeeIds)
            ->where('year', $this->yearFilter)
            ->when($this->typeFilter, fn($q) => $q->where('leave_type_id', $this->typeFilter))
            ->get()
            ->groupBy('employee_id');

        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $schools    = School::active()->orderBy('name')->get();

        return view('livewire.admin.leave-balance',
            compact('employees','balances','leaveTypes','schools'));
    }
}
