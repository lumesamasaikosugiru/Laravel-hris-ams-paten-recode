<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionAssignment extends Model {
    protected $fillable = ['employee_id','school_id','department_id','position_id','start_date','end_date','is_active','type','notes'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','is_active'=>'boolean'];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function school()     { return $this->belongsTo(School::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function position()   { return $this->belongsTo(Position::class); }
    public function getTypeLabelAttribute(): string { return match($this->type) { 'assignment'=>'Penugasan','mutation'=>'Mutasi','promotion'=>'Promosi','demotion'=>'Demosi',default=>'-' }; }
}
