<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model {
    use SoftDeletes;
    protected $fillable = ['school_id','code','name','description','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function school()    { return $this->belongsTo(School::class); }
    public function positions() { return $this->hasMany(Position::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
