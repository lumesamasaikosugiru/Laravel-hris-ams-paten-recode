<?php
namespace App\Livewire\Portal;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Support\Facades\DB;

class PortalLeave extends Component
{
    use WithFileUploads;

    // Form
    public bool        $showForm      = false;
    public int|string  $leave_type_id = '';
    public string      $start_date    = '';
    public string      $end_date      = '';
    public string      $reason        = '';
    public $document_file             = null;
    public int         $calculatedDays = 0;
    public string      $maxEndDate    = '';
    public ?array      $selectedBalance = null;

    public function mount(): void
    {
        $this->start_date = now()->addDays(LeaveService::MIN_DAYS_BEFORE)->format('Y-m-d');
        $this->end_date   = $this->start_date;
    }

    private function getEmployee(): ?Employee
    {
        return Employee::where('user_id', auth()->id())->first();
    }

    public function updatedLeaveTypeId(): void
    {
        $employee = $this->getEmployee();
        if (!$employee || !$this->leave_type_id) return;

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('year', now()->year)->first();

        $this->selectedBalance = $balance
            ? ['quota' => $balance->quota, 'used' => $balance->used, 'remaining' => $balance->remaining]
            : null;

        $this->updateMaxEndDate();
    }

    public function updatedStartDate(): void { $this->updateMaxEndDate(); $this->recalcDays(); }
    public function updatedEndDate(): void   { $this->recalcDays(); }

    private function recalcDays(): void
    {
        if ($this->start_date && $this->end_date && $this->end_date >= $this->start_date) {
            $this->calculatedDays = LeaveRequest::countWorkDays($this->start_date, $this->end_date);
        } else {
            $this->calculatedDays = 0;
        }
    }

    private function updateMaxEndDate(): void
    {
        if (!$this->start_date || !$this->leave_type_id) { $this->maxEndDate = ''; return; }
        $lt    = LeaveType::find($this->leave_type_id);
        $quota = $this->selectedBalance ? $this->selectedBalance['remaining'] : ($lt?->quota ?? 1);
        $this->maxEndDate = LeaveService::calcMaxEndDate($this->start_date, $quota);
        if ($this->end_date && $this->end_date > $this->maxEndDate) {
            $this->end_date = $this->maxEndDate;
        }
        $this->recalcDays();
    }

    public function save(): void
    {
        $this->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|min:10|max:500',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'leave_type_id.required' => 'Pilih jenis cuti.',
            'start_date.required'    => 'Tanggal mulai wajib diisi.',
            'end_date.required'      => 'Tanggal selesai wajib diisi.',
            'reason.required'        => 'Alasan cuti wajib diisi.',
            'reason.min'             => 'Alasan minimal 10 karakter.',
        ]);

        $employee  = $this->getEmployee();
        $leaveType = LeaveType::find($this->leave_type_id);

        if (!$employee) { session()->flash('error', 'Data pegawai tidak ditemukan.'); return; }

        $errors = LeaveService::validate($employee, $leaveType, $this->start_date, $this->end_date, $this->selectedBalance);
        if (!empty($errors)) {
            foreach ($errors as $field => $msg) { $this->addError($field, $msg); }
            return;
        }

        $docPath = null;
        if ($this->document_file) {
            $docPath = $this->document_file->store('leaves/documents', 'public');
        }

        LeaveRequest::create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $this->leave_type_id,
            'start_date'    => $this->start_date,
            'end_date'      => $this->end_date,
            'days'          => LeaveRequest::countWorkDays($this->start_date, $this->end_date),
            'reason'        => $this->reason,
            'document_file' => $docPath,
            'status'        => 'pending',
        ]);

        session()->flash('success', 'Pengajuan cuti berhasil dikirim.');
        $this->reset(['showForm','leave_type_id','start_date','end_date','reason','document_file','calculatedDays','selectedBalance','maxEndDate']);
        $this->start_date = now()->addDays(LeaveService::MIN_DAYS_BEFORE)->format('Y-m-d');
        $this->end_date   = $this->start_date;
    }

    public function render()
    {
        $employee = $this->getEmployee();

        $requests = $employee
            ? LeaveRequest::where('employee_id', $employee->id)
                ->with('leaveType')->latest()->limit(10)->get()
            : collect();

        $balances = $employee
            ? LeaveBalance::where('employee_id', $employee->id)
                ->where('year', now()->year)
                ->with('leaveType')->get()
            : collect();

        $leaveTypes = $employee
            ? LeaveType::active()->orderBy('name')->get()
                ->filter(fn($lt) => LeaveService::isLeaveTypeAllowed($lt, $employee))
                ->values()
            : collect();

        return view('livewire.portal.portal-leave',
            compact('employee','requests','balances','leaveTypes'));
    }
}
