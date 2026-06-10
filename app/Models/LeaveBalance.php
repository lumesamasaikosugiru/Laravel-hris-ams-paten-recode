<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = ['employee_id','leave_type_id','year','quota','used'];
    protected $casts    = ['quota'=>'integer','used'=>'integer','remaining'=>'integer'];

    public function employee()  { return $this->belongsTo(Employee::class); }
    public function leaveType() { return $this->belongsTo(LeaveType::class); }

    // Generate saldo untuk semua pegawai aktif
    public static function generateForYear(int $year): void
    {
        $employees  = Employee::whereIn('status',['active','probation'])->get();
        $leaveTypes = LeaveType::active()->get();

        foreach ($employees as $emp) {
            foreach ($leaveTypes as $lt) {
                // Skip jika gender tidak sesuai
                if ($lt->gender !== 'all' && $lt->gender !== $emp->gender) continue;
                // Skip jika siklus sekali dan sudah punya
                if ($lt->cycle === 'once' && self::where('employee_id',$emp->id)->where('leave_type_id',$lt->id)->exists()) continue;

                self::firstOrCreate(
                    ['employee_id'=>$emp->id,'leave_type_id'=>$lt->id,'year'=>$year],
                    ['quota'=>$lt->quota,'used'=>0]
                );
            }
        }
    }
}
