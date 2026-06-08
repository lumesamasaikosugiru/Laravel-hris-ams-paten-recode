<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model {
    use SoftDeletes;
    protected $fillable = ['name','quota','gender','cycle','requires_document','description','is_active'];
    protected $casts = ['is_active'=>'boolean','requires_document'=>'boolean','quota'=>'integer'];

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function getGenderLabelAttribute(): string { return match($this->gender) { 'male'=>'Laki-laki','female'=>'Perempuan',default=>'Semua' }; }
    public function getCycleLabelAttribute(): string { return $this->cycle === 'annual' ? 'Tahunan' : 'Sekali Seumur Hidup'; }
}
