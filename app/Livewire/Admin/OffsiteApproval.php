<?php

namespace App\Livewire\Admin;

use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class OffsiteApproval extends Component
{
    use WithPagination;

    // Filter
    public string $filterStatus = 'pending'; // pending | approved | rejected | all
    public string $filterDate = '';
    public string $search = '';

    // Modal reject
    public bool $showRejectModal = false;
    public ?int $rejectingId = null;
    public string $rejectionNote = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    // ── Approve ───────────────────────────────────────────────────────────────

    public function approve(int $attendanceId): void
    {
        $attendance = Attendance::findOrFail($attendanceId);

        $attendance->update([
            'offsite_status' => 'approved',
            'offsite_approved_by' => auth()->id(),
            'offsite_approved_at' => now(),
            'offsite_rejection_note' => null,
        ]);

        session()->flash('success', "Absensi kegiatan luar {$attendance->employee->name} disetujui.");
    }

    // ── Reject flow ───────────────────────────────────────────────────────────

    public function openReject(int $attendanceId): void
    {
        $this->rejectingId = $attendanceId;
        $this->rejectionNote = '';
        $this->showRejectModal = true;
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectionNote' => 'required|min:5',
        ], [
            'rejectionNote.required' => 'Isi catatan penolakan.',
            'rejectionNote.min' => 'Catatan minimal 5 karakter.',
        ]);

        $attendance = Attendance::findOrFail($this->rejectingId);

        $attendance->update([
            'offsite_status' => 'rejected',
            'offsite_approved_by' => auth()->id(),
            'offsite_approved_at' => now(),
            'offsite_rejection_note' => $this->rejectionNote,
        ]);

        session()->flash('success', "Absensi kegiatan luar {$attendance->employee->name} ditolak.");
        $this->showRejectModal = false;
        $this->rejectingId = null;
        $this->rejectionNote = '';
    }

    public function cancelReject(): void
    {
        $this->showRejectModal = false;
        $this->rejectingId = null;
        $this->rejectionNote = '';
    }

    // ── Counter untuk badge sidebar ───────────────────────────────────────────

    public static function pendingCount(): int
    {
        return Attendance::where('is_offsite', true)
            ->where('offsite_status', 'pending')
            ->count();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $query = Attendance::query()
            ->where('is_offsite', true)
            ->with(['employee.school', 'approvedBy'])
            ->when($this->filterStatus !== 'all', fn($q) => $q->where('offsite_status', $this->filterStatus))
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->when($this->search, fn($q) => $q->whereHas(
                'employee',
                fn($eq) =>
                $eq->where('name', 'like', "%{$this->search}%")
            ))
            ->orderByRaw("FIELD(offsite_status,'pending','approved','rejected')")
            ->orderByDesc('date');

        $pendingCount = self::pendingCount();

        return view('livewire.admin.offsite-approval', [
            'records' => $query->paginate(15),
            'pendingCount' => $pendingCount,
        ])->title('Approval Kegiatan Luar');
    }
}