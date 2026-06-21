<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\School;
use Carbon\Carbon;

class AttendanceReport extends Component
{
    public string $monthFilter = '';
    public string $schoolFilter = '';
    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('attendance.report'), 403);
        $this->monthFilter = now()->format('Y-m');
    }

    public function exportExcel(): void
    {
        abort_unless(auth()->user()->can('attendance.export'), 403);
        $this->dispatch('export-attendance', [
            'month' => $this->monthFilter,
            'school' => $this->schoolFilter,
        ]);
        session()->flash('success', 'Laporan sedang diproses untuk di-export.');
    }

    public function render()
    {
        [$year, $month] = explode('-', $this->monthFilter . '-' . now()->month);
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Ambil pegawai aktif
        $employees = Employee::with(['school', 'activeAssignment.position'])
            ->whereIn('status', ['active', 'probation'])
            ->when($this->schoolFilter, fn($q) => $q->where('school_id', $this->schoolFilter))
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('nik', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        // Ambil semua absensi bulan ini untuk pegawai di halaman ini
        $employeeIds = $employees->pluck('id');
        $attendances = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('employee_id');

        // Hitung rekap per pegawai
        $summary = [];
        foreach ($employees as $emp) {
            $empAtt = $attendances->get($emp->id, collect());
            $summary[$emp->id] = [
                'present' => $empAtt->whereIn('status', ['present'])->count(),
                'late' => $empAtt->where('status', 'late')->count(),
                'absent' => $empAtt->where('status', 'absent')->count(),
                'leave' => $empAtt->where('status', 'leave')->count(),
                'pct' => $daysInMonth > 0
                    ? round(($empAtt->whereIn('status', ['present', 'late'])->count() / $daysInMonth) * 100)
                    : 0,
            ];
        }

        $schools = School::active()->orderBy('name')->get();

        return view(
            'livewire.admin.attendance-report',
            compact('employees', 'summary', 'daysInMonth', 'startDate', 'schools')
        );
    }
}