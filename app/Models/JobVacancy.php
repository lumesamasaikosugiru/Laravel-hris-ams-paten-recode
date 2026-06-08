<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobVacancy extends Model {
    use SoftDeletes;
    protected $table = 'job_vacancies';
    protected $fillable = ['school_id','department_id','position_id','title','description','requirements','employment_type','quota','open_date','close_date','status'];
    protected $casts = ['open_date'=>'date','close_date'=>'date','quota'=>'integer'];

    public function school()     { return $this->belongsTo(School::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function position()   { return $this->belongsTo(Position::class); }
    public function applicants() { return $this->hasMany(Applicant::class, 'job_vacancy_id'); }

    public function scopeOpen($q) { return $q->where('status','open')->where(fn($q2)=>$q2->whereNull('close_date')->orWhere('close_date','>=',now())); }
    public function getStatusLabelAttribute(): string { return match($this->status) { 'draft'=>'Draft','open'=>'Dibuka','closed'=>'Ditutup',default=>'-' }; }
    public function getEmploymentTypeLabelAttribute(): string { return match($this->employment_type) { 'permanent'=>'Tetap','contract'=>'Kontrak','intern'=>'Magang',default=>'-' }; }
    public function getIsExpiredAttribute(): bool { return $this->close_date && $this->close_date->isPast(); }
}
