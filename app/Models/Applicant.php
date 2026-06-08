<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applicant extends Model {
    use SoftDeletes;
    protected $fillable = ['job_vacancy_id','name','email','phone','gender','place_of_birth','date_of_birth','address','last_education','last_education_major','last_education_institution','cv_file','status','hr_notes','source','converted_to_employee_id','converted_at','converted_by'];
    protected $casts = ['date_of_birth'=>'date','converted_at'=>'datetime'];

    public function jobVacancy()        { return $this->belongsTo(JobVacancy::class, 'job_vacancy_id'); }
    public function educations()        { return $this->hasMany(ApplicantEducation::class); }
    public function experiences()       { return $this->hasMany(ApplicantExperience::class); }
    public function convertedEmployee() { return $this->belongsTo(Employee::class, 'converted_to_employee_id'); }

    public function getIsConvertedAttribute(): bool { return !is_null($this->converted_to_employee_id); }
    public function getGenderLabelAttribute(): string { return $this->gender === 'male' ? 'Laki-laki' : 'Perempuan'; }
    public function getStatusLabelAttribute(): string { return match($this->status) { 'submitted'=>'Lamaran Masuk','tes_berkas'=>'Verifikasi Berkas','tes_potensi'=>'Tes Potensi','diterima'=>'Diterima','ditolak'=>'Ditolak',default=>'-' }; }
    public function getStatusColorAttribute(): string { return match($this->status) { 'submitted'=>'bg-blue-100 text-blue-700','tes_berkas'=>'bg-yellow-100 text-yellow-700','tes_potensi'=>'bg-purple-100 text-purple-700','diterima'=>'bg-green-100 text-green-700','ditolak'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-500' }; }
    public function getLastEducationLabelAttribute(): string { return match($this->last_education) { 'sd'=>'SD','smp'=>'SMP','sma'=>'SMA/SMK','d3'=>'D3','s1'=>'S1','s2'=>'S2','s3'=>'S3',default=>'-' }; }
}
