<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'school_id',
        'applicant_id',
        'nik',
        'nipy',
        'name',
        'national_id',
        'gender',
        'place_of_birth',
        'date_of_birth',
        'religion',
        'marital_status',
        'nationality',
        'email',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'address',
        'is_guru',
        'join_date',
        'employee_type',
        'contract_start',
        'contract_end',
        'status',
        'probation_start_date',
        'probation_end_date',
        'probation_status',
        'probation_evaluated_at',
        'probation_evaluated_by',
        'probation_notes',
        'last_education',
        'last_education_major',
        'last_education_institution',
        'photo',
    ];
    protected $casts = [
        'is_guru' => 'boolean',
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'contract_start' => 'date',
        'contract_end' => 'date',
        'probation_start_date' => 'date',
        'probation_end_date' => 'date',
        'probation_evaluated_at' => 'date',
    ];

    // Usia pensiun
    const RETIREMENT_AGE = 60;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function positionAssignments()
    {
        return $this->hasMany(PositionAssignment::class);
    }
    public function activeAssignment()
    {
        return $this->hasOne(PositionAssignment::class)->where('is_active', true)->latest();
    }
    public function statusHistories()
    {
        return $this->hasMany(EmployeeStatusHistory::class);
    }
    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(\App\Models\LeaveRequest::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
    public function scopeProbation($q)
    {
        return $q->where('status', 'probation');
    }
    public function scopeProbationEnding($q)
    {
        return $q->where('status', 'probation')->where('probation_status', 'on_probation')->where('probation_end_date', '<=', now());
    }
    public function scopeProbationEndingSoon($q, int $days = 7)
    {
        return $q->where('status', 'probation')->where('probation_status', 'on_probation')->whereBetween('probation_end_date', [now(), now()->addDays($days)]);
    }

    public function getDisplayIdAttribute(): string
    {
        return $this->nipy ?? $this->nik;
    }
    public function getIsOnProbationAttribute(): bool
    {
        return $this->status === 'probation' && $this->probation_status === 'on_probation';
    }
    public function getIsProbationOverdueAttribute(): bool
    {
        return $this->is_on_probation && $this->probation_end_date?->isPast();
    }
    public function getProbationDaysLeftAttribute(): ?int
    {
        if (!$this->is_on_probation || !$this->probation_end_date)
            return null;
        return max(0, (int) now()->diffInDays($this->probation_end_date, false));
    }
    public function getEmployeeTypeLabelAttribute(): string
    {
        return match ($this->employee_type) { 'permanent' => 'Tetap', 'contract' => 'Kontrak', 'intern' => 'Magang', default => '-'};
    }
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) { 'probation' => 'Masa Percobaan', 'active' => 'Aktif', 'inactive' => 'Nonaktif', 'resigned' => 'Mengundurkan Diri', 'terminated' => 'Diberhentikan', default => '-'};
    }
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) { 'probation' => 'bg-amber-100 text-amber-700', 'active' => 'bg-green-100 text-green-700', 'inactive' => 'bg-gray-100 text-gray-500', 'resigned' => 'bg-orange-100 text-orange-700', 'terminated' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-500'};
    }
    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'male' ? 'Laki-laki' : 'Perempuan';
    }
    public function getRoleLabelAttribute(): string
    {
        return $this->is_guru ? 'Guru' : 'Non-Guru';
    }
    public function getProbationDurationLabelAttribute(): string
    {
        return $this->is_guru ? '6 bulan' : '3 bulan';
    }
    public function getLastEducationLabelAttribute(): string
    {
        return match ($this->last_education) { 'sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA/SMK', 'd3' => 'D3', 's1' => 'S1', 's2' => 'S2', 's3' => 'S3', default => '-'};
    }

    //pensiun
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth
            ? $this->date_of_birth->age
            : null;
    }

    public function getRetirementDateAttribute(): ?\Carbon\Carbon
    {
        return $this->date_of_birth
            ? $this->date_of_birth->copy()->addYears(self::RETIREMENT_AGE)
            : null;
    }

    public function getYearsToRetirementAttribute(): ?int
    {
        return $this->retirement_date
            ? (int) now()->diffInYears($this->retirement_date, false)
            : null;
    }

    public function getIsRetiredAttribute(): bool
    {
        return $this->age !== null && $this->age >= self::RETIREMENT_AGE;
    }

    public function additionalAssignment()
    {
        return $this->hasOne(PositionAssignment::class)
            ->where('is_active', true)
            ->where('assignment_type', 'additional');
    }

    // Semua jabatan aktif (primary + additional)
    public function activeAssignments()
    {
        return $this->hasMany(PositionAssignment::class)
            ->where('is_active', true)
            ->orderBy('assignment_type'); // primary dulu
    }

    // Cek apakah punya tugas tambahan
    public function getHasAdditionalAssignmentAttribute(): bool
    {
        return $this->additionalAssignment()->exists();
    }

    // Semua unit yang terkait (induk + tambahan)
    public function getAllSchoolsAttribute(): array
    {
        $schools = [$this->school_id];
        $additional = $this->additionalAssignment;
        if ($additional) {
            $schools[] = $additional->school_id;
        }
        return array_unique($schools);
    }
}
