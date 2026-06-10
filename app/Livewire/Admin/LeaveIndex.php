<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\School;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class LeaveIndex extends Component
{
    use WithPagination, WithFileUploads;

    // Filters
    public string $search = '';
    public string $statusFilter = '';
    public string $schoolFilter = '';
    public string $monthFilter = '';

    // Modal pengajuan
    public bool $showRequestModal = false;
    public bool $showApproveModal = false;
    public bool $showDetailModal = false;
    public array $allEmployees = [];
    public ?int $selectedEmployeeId = null;
    public int|string $leave_type_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';
    public $document_file = null;
    public int $calculatedDays = 0;
    public ?array $selectedBalance = null;
    public string $selectedEmployeeGender = '';
    public string $selectedGender = '';

    // Modal approve/reject
    public ?int $processingId = null;
    public string $approverNotes = '';
    public string $approveAction = 'approved';

    // Detail
    public ?int $viewingId = null;

    // Generate saldo
    public bool $showGenerateModal = false;
    public int $generateYear;

    public function mount(): void
    {
        $this->monthFilter = now()->format('Y-m');
        $this->generateYear = now()->year;

        // Load employees sekali saat mount
        $this->allEmployees = Employee::whereIn('status', ['active', 'probation'])
            ->orderBy('name')
            ->get()
            ->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'code' => $e->nipy ?? $e->nik])
            ->toArray();
    }

    // ── Employee search ───────────────────────────────────────
    public function updatedLeaveTypeId(): void
    {
        if (!$this->selectedEmployeeId || !$this->leave_type_id)
            return;
        $balance = LeaveBalance::where('employee_id', $this->selectedEmployeeId)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('year', now()->year)
            ->first();
        $this->selectedBalance = $balance
            ? ['quota' => $balance->quota, 'used' => $balance->used, 'remaining' => $balance->remaining]
            : null;
    }

    public function updatedStartDate(): void
    {
        $this->recalcDays();
    }
    public function updatedEndDate(): void
    {
        $this->recalcDays();
    }

    private function recalcDays(): void
    {
        if ($this->start_date && $this->end_date && $this->end_date >= $this->start_date) {
            $this->calculatedDays = LeaveRequest::countWorkDays($this->start_date, $this->end_date);
        } else {
            $this->calculatedDays = 0;
        }
    }

    // ── Buka modal pengajuan ──────────────────────────────────
    public function openRequestModal(): void
    {
        $this->reset([
            'selectedEmployeeId',
            'leave_type_id',
            'start_date',
            'end_date',
            'reason',
            'document_file',
            'calculatedDays',
            'selectedBalance'
        ]);
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
        $this->resetValidation();
        $this->showRequestModal = true;
    }

    // ── Simpan pengajuan ──────────────────────────────────────
    public function saveRequest(): void
    {
        $this->validate([
            'selectedEmployeeId' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'selectedEmployeeId.required' => 'Pilih pegawai.',
            'leave_type_id.required' => 'Pilih jenis cuti.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'reason.required' => 'Alasan cuti wajib diisi.',
            'reason.min' => 'Alasan minimal 10 karakter.',
        ]);

        $days = LeaveRequest::countWorkDays($this->start_date, $this->end_date);

        // Cek saldo
        if ($this->selectedBalance && $days > $this->selectedBalance['remaining']) {
            $this->addError('days', "Sisa saldo tidak cukup. Sisa: {$this->selectedBalance['remaining']} hari, diajukan: {$days} hari.");
            return;
        }

        $docPath = null;
        if ($this->document_file) {
            $docPath = $this->document_file->store('leaves/documents', 'public');
        }

        LeaveRequest::create([
            'employee_id' => $this->selectedEmployeeId,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days' => $days,
            'reason' => $this->reason,
            'document_file' => $docPath,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Pengajuan cuti berhasil disimpan.');
        $this->showRequestModal = false;
    }

    // ── Approve / Reject ──────────────────────────────────────
    public function openApproveModal(int $id, string $action): void
    {
        $this->processingId = $id;
        $this->approveAction = $action;
        $this->approverNotes = '';
        $this->resetValidation();
        $this->showApproveModal = true;
    }

    public function processLeave(): void
    {
        $this->validate([
            'approverNotes' => $this->approveAction === 'rejected' ? 'required|string|min:5' : 'nullable|string',
        ], [
            'approverNotes.required' => 'Alasan penolakan wajib diisi.',
            'approverNotes.min' => 'Alasan minimal 5 karakter.',
        ]);

        $request = LeaveRequest::with('employee')->findOrFail($this->processingId);

        DB::transaction(function () use ($request) {
            $request->update([
                'status' => $this->approveAction,
                'approver_notes' => $this->approverNotes ?: null,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Jika disetujui — potong saldo
            if ($this->approveAction === 'approved') {
                $balance = LeaveBalance::where('employee_id', $request->employee_id)
                    ->where('leave_type_id', $request->leave_type_id)
                    ->where('year', $request->start_date->year)
                    ->first();

                if ($balance) {
                    $balance->increment('used', $request->days);
                }

                // Update attendance records
                $start = $request->start_date->copy();
                $end = $request->end_date->copy();
                $current = $start->copy();
                while ($current->lte($end)) {
                    if ($current->isWeekday()) {
                        Attendance::updateOrCreate(
                            ['employee_id' => $request->employee_id, 'date' => $current->format('Y-m-d')],
                            [
                                'school_id' => $request->employee->school_id,
                                'status' => 'leave',
                                'check_in' => null,
                                'check_out' => null,
                                'late_minutes' => 0,
                                'work_minutes' => 0,
                                'notes' => 'Cuti: ' . $request->leaveType->name ?? '',
                                'recorded_by' => auth()->id(),
                            ]
                        );
                    }
                    $current->addDay();
                }
            }
        });

        $msg = $this->approveAction === 'approved' ? 'Cuti disetujui.' : 'Cuti ditolak.';
        session()->flash('success', $msg);
        $this->showApproveModal = false;
    }

    // ── Detail ────────────────────────────────────────────────
    public function openDetail(int $id): void
    {
        $this->viewingId = $id;
        $this->showDetailModal = true;
    }

    // ── Generate saldo ────────────────────────────────────────
    public function generateBalances(): void
    {
        LeaveBalance::generateForYear($this->generateYear);
        session()->flash('success', "Saldo cuti tahun {$this->generateYear} berhasil di-generate.");
        $this->showGenerateModal = false;
    }

    public function render()
    {
        $requests = LeaveRequest::with(['employee.school', 'leaveType', 'approvedBy'])
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($eq) =>
                $eq->where('name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->schoolFilter, fn($q) => $q->whereHas('employee', fn($eq) =>
                $eq->where('school_id', $this->schoolFilter)))
            ->when($this->monthFilter, function ($q) {
                [$y, $m] = explode('-', $this->monthFilter);
                $q->whereYear('start_date', $y)->whereMonth('start_date', $m);
            })
            ->latest()
            ->paginate(15);

        $summary = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->whereYear('start_date', now()->year)->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->whereYear('start_date', now()->year)->count(),
        ];

        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $schools = School::active()->orderBy('name')->get();
        $viewing = $this->viewingId
            ? LeaveRequest::with(['employee.school', 'leaveType', 'approvedBy'])->find($this->viewingId)
            : null;

        $pendingEmployeeIds = LeaveRequest::where('status', 'pending')
            ->pluck('employee_id')
            ->toArray();

        // Sama persis dengan cara di attendance yang berhasil
        $employees = Employee::whereIn('status', ['active', 'probation'])
            ->whereNotIn('id', $pendingEmployeeIds)
            ->orderBy('name')->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'code' => $e->nipy ?? $e->nik,
                'gender' => $e->gender,
            ])->values()->toArray();

        return view(
            'livewire.admin.leave-index',
            compact('requests', 'summary', 'leaveTypes', 'schools', 'viewing', 'employees')
        );
    }
}
