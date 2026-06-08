<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model {
    use SoftDeletes;
    protected $fillable = ['code','name','address','phone','email','principal_name','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function departments() { return $this->hasMany(Department::class); }
    public function positions()   { return $this->hasMany(Position::class); }
    public function employees()   { return $this->hasMany(Employee::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
