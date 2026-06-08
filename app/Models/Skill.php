<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model {
    protected $fillable = ['name','category','is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function scopeActive($q) { return $q->where('is_active', true); }
}
