<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id','leave_type_id','start_date','end_date','days',
        'reason','document_file','status','approver_notes','approved_by','approved_at',
    ];
    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
        'days'        => 'integer',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function leaveType()  { return $this->belongsTo(LeaveType::class); }
    public function approvedBy() { return $this->belongsTo(User::class,'approved_by'); }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'approved' => 'badge-green',
            'rejected' => 'badge-red',
            default    => 'badge-gray',
        };
    }

    // Hitung hari kerja (Senin-Jumat) antara dua tanggal
    public static function countWorkDays(string $start, string $end): int
    {
        $start   = Carbon::parse($start);
        $end     = Carbon::parse($end);
        $days    = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekday()) $days++;
            $current->addDay();
        }
        return $days;
    }
}
