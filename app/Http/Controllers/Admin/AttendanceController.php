<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\School;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('admin.attendance.index');
    }

    public function report()
    {
        return view('admin.attendance.report');
    }

    public function export()
    {
        $month      = request('month', now()->format('Y-m'));
        $schoolId   = request('school');
        [$year, $m] = explode('-', $month);

        $startDate   = Carbon::createFromDate($year, $m, 1)->startOfMonth();
        $endDate     = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $employees = Employee::with(['school','activeAssignment.position'])
            ->whereIn('status',['active','probation'])
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')->get();

        $employeeIds = $employees->pluck('id');
        $attendances = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()->groupBy('employee_id');

        // Build Excel
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Absensi');

        // Header
        $headers = ['No','NIK/NIPY','Nama','Jabatan','Unit'];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $headers[] = $d;
        }
        $headers = array_merge($headers, ['Hadir','Terlambat','Tidak Hadir','Cuti','% Hadir']);

        foreach ($headers as $idx => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->setCellValue("{$col}1", $h);
        }

        $sheet->getStyle('A1:'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)).'1')
            ->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a1040']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

        foreach ($employees as $rowIdx => $emp) {
            $row     = $rowIdx + 2;
            $empAtt  = $attendances->get($emp->id, collect())->keyBy(fn($a) => $a->date->format('j'));

            $rowData = [
                $rowIdx + 1,
                $emp->nipy ?? $emp->nik,
                $emp->name,
                $emp->activeAssignment?->position->name ?? '—',
                $emp->school->name,
            ];

            $present = 0; $late = 0; $absent = 0; $leave = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $att = $empAtt->get($d);
                if ($att) {
                    $symbol = match($att->status) {
                        'present' => 'H', 'late' => 'T',
                        'absent'  => 'A', 'leave' => 'C', default => '-',
                    };
                    match($att->status) {
                        'present' => $present++, 'late' => $late++,
                        'absent'  => $absent++, 'leave' => $leave++,
                        default   => null,
                    };
                } else {
                    $symbol = '-';
                }
                $rowData[] = $symbol;
            }

            $total = $present + $late;
            $pct   = $daysInMonth > 0 ? round(($total / $daysInMonth) * 100) : 0;
            $rowData = array_merge($rowData, [$present, $late, $absent, $leave, $pct.'%']);

            foreach ($rowData as $colIdx => $val) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue("{$col}{$row}", $val);
            }

            if ($rowIdx % 2 === 0) {
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']],
                ]);
            }
        }

        // Legend
        $legendRow = $employees->count() + 3;
        $sheet->setCellValue("A{$legendRow}", 'Keterangan: H=Hadir, T=Terlambat, A=Tidak Hadir, C=Cuti/Izin');

        foreach (range(1, 5) as $i) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'laporan-absensi-'.$month.'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
