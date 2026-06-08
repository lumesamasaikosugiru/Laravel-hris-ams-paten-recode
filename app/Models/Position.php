<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model {
    use SoftDeletes;
    protected $fillable = ['school_id','department_id','name','level','description','is_active'];
    protected $casts = ['is_active' => 'boolean', 'level' => 'integer'];

    public function school()     { return $this->belongsTo(School::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
