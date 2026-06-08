<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeStatusHistory extends Model {
    protected $fillable = ['employee_id','employee_type','status','effective_date','notes','recorded_by'];
    protected $casts = ['effective_date'=>'date'];
    public function employee()   { return $this->belongsTo(Employee::class); }
    public function recordedBy() { return $this->belongsTo(\App\Models\User::class, 'recorded_by'); }
}
