<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'school_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'late_minutes',
        'work_minutes',
        'notes',
        'recorded_by',

        // GPS (check-in/check-out lokasi terpisah). BUG TEMUAN 20 Juni
        // 2026: kolom-kolom ini sebelumnya TIDAK ADA di $fillable sama
        // sekali, padahal sudah ada migration-nya dan sudah dicoba
        // diisi PortalAttendance.php sejak fitur GPS dibuat. Eloquent
        // menolak mass-assignment ke kolom luar $fillable secara DIAM-
        // DIAM (tidak error) -- akibatnya semua data GPS & offsite
        // selalu tersimpan null/false walau UI bilang "berhasil".
        'checkin_latitude',
        'checkin_longitude',
        'checkin_location_valid',
        'checkin_location_name',
        'checkout_latitude',
        'checkout_longitude',
        'checkout_location_valid',
        'checkout_location_name',

        // Offsite (kegiatan luar lokasi)
        'is_offsite',
        'offsite_reason',
        'offsite_note',
        'offsite_status',
        'offsite_approved_by',
        'offsite_approved_at',
        'offsite_rejection_note',
    ];

    protected $casts = [
        'date' => 'date',
        'checkin_location_valid' => 'boolean',
        'checkout_location_valid' => 'boolean',
        'is_offsite' => 'boolean',
        'offsite_approved_at' => 'datetime',
    ];

    // Jam kerja standar Yayasan Fatahillah.
    // Diubah dari 07:30-16:00 menjadi 07:00-15:00 per 19 Juni 2026.
    // Hari kerja terkait (Senin-Sabtu) diatur terpisah di
    // LeaveRequest::WORK_DAYS, bukan di sini -- konstanta ini HANYA
    // untuk jam masuk/selesai dalam satu hari kerja.
    const WORK_START = '07:00';
    const WORK_END = '15:00';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * HR/Admin yang memproses (approve/reject) pengajuan kegiatan
     * luar lokasi (offsite) untuk baris absensi ini. Sebelumnya
     * relasi ini TIDAK ADA SAMA SEKALI, padahal OffsiteApproval.php
     * sudah memanggil ->with(['employee.school', 'approvedBy']) --
     * nama relasi yang dipanggil di sana juga salah (approvedBy,
     * bukan offsiteApprovedBy), sudah diperbaiki bersamaan.
     */
    public function offsiteApprovedBy()
    {
        return $this->belongsTo(User::class, 'offsite_approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'leave' => 'Cuti/Izin',
            default => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => 'badge-green',
            'late' => 'bg-yellow-100 text-yellow-700',
            'absent' => 'badge-red',
            'leave' => 'badge-blue',
            default => 'badge-gray',
        };
    }

    public function getWorkHoursAttribute(): string
    {
        if ($this->work_minutes <= 0)
            return '—';
        $h = intdiv($this->work_minutes, 60);
        $m = $this->work_minutes % 60;
        return $h > 0 ? "{$h}j {$m}m" : "{$m}m";
    }

    // Hitung status, keterlambatan, dan jam kerja
    public static function calculate(string $checkIn, ?string $checkOut): array
    {
        $inTime = Carbon::parse($checkIn);
        $workStart = Carbon::parse(self::WORK_START);
        $workEnd = Carbon::parse(self::WORK_END);

        $lateMinutes = 0;
        $status = 'present';

        if ($inTime->gt($workStart)) {
            $lateMinutes = (int) $inTime->diffInMinutes($workStart);
            $status = 'late';
        }

        $workMinutes = 0;
        if ($checkOut) {
            $outTime = Carbon::parse($checkOut);
            $workMinutes = max(0, (int) $inTime->diffInMinutes($outTime));
        }

        return compact('status', 'lateMinutes', 'workMinutes');
    }
}