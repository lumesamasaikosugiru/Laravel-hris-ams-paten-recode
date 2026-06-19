<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days',
        'reason',
        'document_file',
        'status',
        'approver_notes',
        'approved_by',
        'approved_at',
        'requires_school_approval',
        'school_status',
        'school_approved_by',
        'school_approved_at',
        'school_rejection_note',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'school_approved_at' => 'datetime',
        'days' => 'integer',
        'requires_school_approval' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function schoolApprovedBy()
    {
        return $this->belongsTo(User::class, 'school_approved_by');
    }

    /**
     * Apakah pengajuan ini sudah lolos tahap Kepala Sekolah dan SIAP
     * diproses oleh Admin SDM / Ketua. Untuk pengajuan yang tidak butuh
     * approval sekolah sama sekali (requires_school_approval=false),
     * selalu true -- perilaku lama tidak berubah.
     */
    public function getReadyForSdmAttribute(): bool
    {
        if (!$this->requires_school_approval) {
            return true;
        }
        return $this->school_status === 'approved';
    }

    public function getSchoolStatusLabelAttribute(): string
    {
        return match ($this->school_status) {
            'pending' => 'Menunggu Kepala Sekolah',
            'approved' => 'Disetujui Kepala Sekolah',
            'rejected' => 'Ditolak Kepala Sekolah',
            default => '-',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'badge-green',
            'rejected' => 'badge-red',
            default => 'badge-gray',
        };
    }

    // Hitung hari kerja (Senin-Jumat) antara dua tanggal
    public static function countWorkDays(string $start, string $end): int
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);
        $days = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekday())
                $days++;
            $current->addDay();
        }
        return $days;
    }
}