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

    // Form pengajuan cuti pribadi
    public bool $showForm = false;
    public int|string $leave_type_id = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';
    public $document_file = null;
    public int $calculatedDays = 0;
    public string $maxEndDate = '';
    public ?array $selectedBalance = null;

    // Approval Kepala Sekolah (tahap 1, untuk guru/non_guru di
    // sekolahnya sendiri). Section ini hanya tampil jika user login
    // punya role kepala_sekolah, lihat isKepalaSekolah di render().
    public bool $showSchoolApproveModal = false;
    public ?int $schoolProcessingId = null;
    public string $schoolApproveAction = 'approved';
    public string $schoolRejectionNote = '';

    public function mount(): void
    {
        $this->start_date = now()->addDays(LeaveService::MIN_DAYS_BEFORE)->format('Y-m-d');
        $this->end_date = $this->start_date;
    }

    private function getEmployee(): ?Employee
    {
        return Employee::where('user_id', auth()->id())->first();
    }

    public function updatedLeaveTypeId(): void
    {
        $employee = $this->getEmployee();
        if (!$employee || !$this->leave_type_id)
            return;

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('year', now()->year)->first();

        $this->selectedBalance = $balance
            ? ['quota' => $balance->quota, 'used' => $balance->used, 'remaining' => $balance->remaining]
            : null;

        $this->updateMaxEndDate();
    }

    public function updatedStartDate(): void
    {
        $this->updateMaxEndDate();
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

    private function updateMaxEndDate(): void
    {
        if (!$this->start_date || !$this->leave_type_id) {
            $this->maxEndDate = '';
            return;
        }
        $lt = LeaveType::find($this->leave_type_id);
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'leave_type_id.required' => 'Pilih jenis cuti.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'reason.required' => 'Alasan cuti wajib diisi.',
            'reason.min' => 'Alasan minimal 10 karakter.',
        ]);

        $employee = $this->getEmployee();
        $leaveType = LeaveType::find($this->leave_type_id);

        if (!$employee) {
            session()->flash('error', 'Data pegawai tidak ditemukan.');
            return;
        }

        $errors = LeaveService::validate($employee, $leaveType, $this->start_date, $this->end_date, $this->selectedBalance);
        if (!empty($errors)) {
            foreach ($errors as $field => $msg) {
                $this->addError($field, $msg);
            }
            return;
        }

        $docPath = null;
        if ($this->document_file) {
            $docPath = $this->document_file->store('leaves/documents', 'public');
        }

        $requiresSchoolApproval = LeaveService::requiresSchoolApproval($employee);

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days' => LeaveRequest::countWorkDays($this->start_date, $this->end_date),
            'reason' => $this->reason,
            'document_file' => $docPath,
            'status' => 'pending',
            'requires_school_approval' => $requiresSchoolApproval,
            'school_status' => $requiresSchoolApproval ? 'pending' : null,
        ]);

        session()->flash('success', 'Pengajuan cuti berhasil dikirim.');
        $this->reset(['showForm', 'leave_type_id', 'start_date', 'end_date', 'reason', 'document_file', 'calculatedDays', 'selectedBalance', 'maxEndDate']);
        $this->start_date = now()->addDays(LeaveService::MIN_DAYS_BEFORE)->format('Y-m-d');
        $this->end_date = $this->start_date;
    }

    // ── Approval Kepala Sekolah (tahap 1) ──────────────────────

    /**
     * Employee milik Kepsek yang sedang login. Dipakai untuk
     * menentukan school_id mana yang ia boleh approve -- BUKAN
     * lewat permission/role per sekolah, melainkan dibandingkan
     * langsung di setiap query/aksi di bawah ini.
     */
    private function getKepalaSekolahSchoolId(): ?int
    {
        if (!auth()->user()?->hasRole('kepala_sekolah')) {
            return null;
        }
        return $this->getEmployee()?->school_id;
    }

    public function openSchoolApproveModal(int $leaveRequestId, string $action): void
    {
        $schoolId = $this->getKepalaSekolahSchoolId();
        if (!$schoolId) {
            abort(403, 'Akun ini tidak terdaftar sebagai Kepala Sekolah.');
        }

        // Guard scoping: pengajuan yang mau diproses HARUS dari
        // pegawai di sekolah yang sama dengan sekolah Kepsek ini.
        $belongsToThisSchool = LeaveRequest::where('id', $leaveRequestId)
            ->whereHas('employee', fn($q) => $q->where('school_id', $schoolId))
            ->exists();

        if (!$belongsToThisSchool) {
            abort(403, 'Pengajuan ini bukan dari pegawai di sekolah Anda.');
        }

        $this->schoolProcessingId = $leaveRequestId;
        $this->schoolApproveAction = $action;
        $this->schoolRejectionNote = '';
        $this->resetValidation();
        $this->showSchoolApproveModal = true;
    }

    public function processSchoolApproval(): void
    {
        $schoolId = $this->getKepalaSekolahSchoolId();
        if (!$schoolId) {
            abort(403, 'Akun ini tidak terdaftar sebagai Kepala Sekolah.');
        }

        $request = LeaveRequest::with('employee')->findOrFail($this->schoolProcessingId);

        // Guard scoping diulang di sini (bukan hanya saat buka modal),
        // mengikuti pola defense-in-depth: cek lagi tepat sebelum aksi
        // benar-benar dieksekusi, jangan percaya state dari buka modal.
        if ($request->employee->school_id !== $schoolId) {
            abort(403, 'Pengajuan ini bukan dari pegawai di sekolah Anda.');
        }

        if (!$request->requires_school_approval) {
            session()->flash('error', 'Pengajuan ini tidak memerlukan approval Kepala Sekolah.');
            $this->showSchoolApproveModal = false;
            return;
        }

        if ($request->school_status !== 'pending') {
            session()->flash('error', 'Pengajuan ini sudah diproses sebelumnya.');
            $this->showSchoolApproveModal = false;
            return;
        }

        $this->validate([
            'schoolRejectionNote' => $this->schoolApproveAction === 'rejected' ? 'required|string|min:5' : 'nullable|string',
        ], [
            'schoolRejectionNote.required' => 'Alasan penolakan wajib diisi.',
            'schoolRejectionNote.min' => 'Alasan minimal 5 karakter.',
        ]);

        $request->update([
            'school_status' => $this->schoolApproveAction,
            'school_approved_by' => auth()->id(),
            'school_approved_at' => now(),
            'school_rejection_note' => $this->schoolApproveAction === 'rejected' ? $this->schoolRejectionNote : null,
            // Jika Kepsek menolak, pengajuan langsung final ditolak --
            // tidak perlu diteruskan ke SDM untuk diproses dua kali.
            'status' => $this->schoolApproveAction === 'rejected' ? 'rejected' : 'pending',
        ]);

        session()->flash('success', $this->schoolApproveAction === 'approved'
            ? 'Pengajuan disetujui, diteruskan ke Admin SDM untuk persetujuan akhir.'
            : 'Pengajuan ditolak.');
        $this->showSchoolApproveModal = false;
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

        // Data approval untuk Kepala Sekolah (kosong/diabaikan di view
        // jika role user login bukan kepala_sekolah).
        $kepsekSchoolId = $this->getKepalaSekolahSchoolId();
        $schoolApprovals = $kepsekSchoolId
            ? LeaveRequest::where('requires_school_approval', true)
                ->where('school_status', 'pending')
                ->whereHas('employee', fn($q) => $q->where('school_id', $kepsekSchoolId))
                ->with('employee', 'leaveType')
                ->latest()
                ->get()
            : collect();

        // Riwayat pengajuan guru/non_guru di sekolah ini yang SUDAH
        // diproses (approved/rejected) oleh Kepala Sekolah mana pun
        // yang pernah bertugas di sekolah ini -- bukan hanya yang
        // diproses Kepsek yang sedang login, supaya riwayat tetap utuh
        // walau terjadi pergantian Kepala Sekolah.
        $schoolHistory = $kepsekSchoolId
            ? LeaveRequest::where('requires_school_approval', true)
                ->whereIn('school_status', ['approved', 'rejected'])
                ->whereHas('employee', fn($q) => $q->where('school_id', $kepsekSchoolId))
                ->with('employee', 'leaveType', 'schoolApprovedBy')
                ->latest('school_approved_at')
                ->limit(15)
                ->get()
            : collect();

        return view(
            'livewire.portal.portal-leave',
            compact('employee', 'requests', 'balances', 'leaveTypes', 'schoolApprovals', 'schoolHistory')
        );
    }
}