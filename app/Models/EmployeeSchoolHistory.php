<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSchoolHistory extends Model {
    protected $fillable = ['employee_id','from_school_id','to_school_id','effective_date','reason','recorded_by'];
    protected $casts = ['effective_date'=>'date'];
    public function employee()   { return $this->belongsTo(Employee::class); }
    public function fromSchool() { return $this->belongsTo(School::class, 'from_school_id'); }
    public function toSchool()   { return $this->belongsTo(School::class, 'to_school_id'); }
    public function recordedBy() { return $this->belongsTo(\App\Models\User::class, 'recorded_by'); }
}
