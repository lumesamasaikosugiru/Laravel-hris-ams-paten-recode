<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantEducation extends Model
{
    protected $table = 'applicant_educations';
    protected $fillable = ['applicant_id', 'level', 'institution', 'major', 'start_year', 'end_year', 'gpa', 'is_latest'];
    protected $casts = ['is_latest' => 'boolean', 'gpa' => 'float'];
    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) { 'sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA/SMK', 'd3' => 'D3', 's1' => 'S1', 's2' => 'S2', 's3' => 'S3', default => '-'};
    }
}
