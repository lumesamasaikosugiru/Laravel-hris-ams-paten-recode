<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantExperience extends Model {
    protected $fillable = ['applicant_id','company_name','position','start_date','end_date','is_current','description'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','is_current'=>'boolean'];
    public function applicant() { return $this->belongsTo(Applicant::class); }
    public function getDurationAttribute(): string { return $this->start_date->format('M Y').' — '.($this->is_current ? 'Sekarang' : $this->end_date?->format('M Y')); }
}
