<?php
namespace App\Livewire\Portal;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\Employee;

class PortalHome extends Component
{
    public function render()
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)
            ->with(['school','activeAssignment.position','additionalAssignment.school'])
            ->first();

        $today = now()->format('Y-m-d');

        // Absensi hari ini
        $todayAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->where('date', $today)->first()
            : null;

        // Cuti pending
        $pendingLeave = $employee
            ? LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'pending')->count()
            : 0;

        // Saldo cuti (cuti tahunan)
        $leaveBalance = $employee
            ? LeaveBalance::where('employee_id', $employee->id)
                ->where('year', now()->year)
                ->whereHas('leaveType', fn($q) => $q->where('name', 'Cuti Tahunan'))
                ->first()
            : null;

        // Absensi bulan ini
        $monthlyAttendance = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')->pluck('total', 'status')
            : collect();

        return view('livewire.portal.portal-home',
            compact('employee','todayAttendance','pendingLeave','leaveBalance','monthlyAttendance'));
    }
}
