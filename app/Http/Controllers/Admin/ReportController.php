<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Applicant;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReportController extends Controller
{
    public function index()        { return view('admin.reports.index'); }
    public function recruitment()  { return $this->exportRecruitment(); }
    public function probation()    { return $this->exportProbation(); }
    public function leaves()       { return $this->exportLeaves(); }

    // ── Export Pegawai ─────────────────────────────────────────
    public function employees()
    {
        $employees = Employee::with(['school','activeAssignment.position','activeAssignment.department'])
            ->whereIn('status',['active','probation'])
            ->orderBy('name')->get();

        $ss    = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('Data Pegawai');

        $headers = ['No','NIK/NIPY','Nama','Jenis Kelamin','Jabatan','Departemen','Unit',
                    'Tipe','Guru/Staf','Status','Tanggal Masuk','Pendidikan'];

        $this->setHeaders($sheet, $headers);

        foreach ($employees as $i => $emp) {
            $row = $i + 2;
            $data = [
                $i+1,
                $emp->nipy ?? $emp->nik,
                $emp->name,
                $emp->gender_label,
                $emp->activeAssignment?->position->name ?? '—',
                $emp->activeAssignment?->department->name ?? '—',
                $emp->school->name,
                $emp->employee_type_label,
                $emp->role_label,
                $emp->status_label,
                $emp->join_date->format('d/m/Y'),
                $emp->last_education_label,
            ];
            $this->setRow($sheet, $row, $data, $i % 2 === 0);
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return $this->download($ss, 'laporan-pegawai-'.now()->format('Ymd').'.xlsx');
    }

    // ── Export Rekrutmen ───────────────────────────────────────
    private function exportRecruitment()
    {
        $applicants = Applicant::with(['jobVacancy.school','jobVacancy.position','convertedEmployee'])
            ->orderBy('created_at','desc')->get();

        $ss    = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('Rekrutmen');

        $headers = ['No','Nama Pelamar','Email','Pendidikan','Lowongan','Unit','Status Pipeline',
                    'Tanggal Daftar','Dikonversi?','NIK/NIPY Pegawai'];
        $this->setHeaders($sheet, $headers);

        foreach ($applicants as $i => $app) {
            $row  = $i + 2;
            $data = [
                $i+1,
                $app->name,
                $app->email,
                $app->last_education_label,
                $app->jobVacancy->title,
                $app->jobVacancy->school->name,
                $app->status_label,
                $app->created_at->format('d/m/Y'),
                $app->is_converted ? 'Ya' : 'Tidak',
                $app->convertedEmployee?->display_id ?? '—',
            ];
            $this->setRow($sheet, $row, $data, $i % 2 === 0);
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return $this->download($ss, 'laporan-rekrutmen-'.now()->format('Ymd').'.xlsx');
    }

    // ── Export Masa Percobaan ──────────────────────────────────
    private function exportProbation()
    {
        $employees = Employee::with(['school','activeAssignment.position'])
            ->where('status','probation')
            ->orderBy('probation_end_date')->get();

        $ss    = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('Masa Percobaan');

        $headers = ['No','NIK','Nama','Jabatan','Unit','Guru/Staf',
                    'Durasi Percobaan','Mulai','Berakhir','Sisa Hari','Status'];
        $this->setHeaders($sheet, $headers);

        foreach ($employees as $i => $emp) {
            $row  = $i + 2;
            $data = [
                $i+1,
                $emp->nik,
                $emp->name,
                $emp->activeAssignment?->position->name ?? '—',
                $emp->school->name,
                $emp->role_label,
                $emp->probation_duration_label,
                $emp->probation_start_date?->format('d/m/Y') ?? '—',
                $emp->probation_end_date?->format('d/m/Y') ?? '—',
                $emp->probation_days_left !== null ? $emp->probation_days_left.' hari' : '—',
                $emp->is_probation_overdue ? 'LEWAT - Perlu Evaluasi' : 'On Progress',
            ];
            $this->setRow($sheet, $row, $data, $i % 2 === 0);
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return $this->download($ss, 'laporan-masa-percobaan-'.now()->format('Ymd').'.xlsx');
    }

    // ── Export Cuti ────────────────────────────────────────────
    private function exportLeaves()
    {
        $year      = request('year', now()->year);
        $employees = Employee::with(['school'])
            ->whereIn('status',['active','probation'])
            ->orderBy('name')->get();

        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $balances   = LeaveBalance::where('year', $year)
            ->get()->groupBy('employee_id');

        $ss    = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle("Saldo Cuti {$year}");

        $headers = ['No','NIK/NIPY','Nama','Unit'];
        foreach ($leaveTypes as $lt) {
            $headers[] = $lt->name.' (Kuota)';
            $headers[] = $lt->name.' (Pakai)';
            $headers[] = $lt->name.' (Sisa)';
        }
        $this->setHeaders($sheet, $headers);

        foreach ($employees as $i => $emp) {
            $row     = $i + 2;
            $data    = [$i+1, $emp->nipy??$emp->nik, $emp->name, $emp->school->name];
            $empBals = $balances->get($emp->id, collect());

            foreach ($leaveTypes as $lt) {
                $bal    = $empBals->firstWhere('leave_type_id', $lt->id);
                $data[] = $bal?->quota ?? 0;
                $data[] = $bal?->used ?? 0;
                $data[] = $bal?->remaining ?? 0;
            }
            $this->setRow($sheet, $row, $data, $i % 2 === 0);
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return $this->download($ss, "laporan-cuti-{$year}.xlsx");
    }

    // ── Helpers ───────────────────────────────────────────────
    private function setHeaders($sheet, array $headers): void
    {
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1);
            $sheet->setCellValue("{$col}1", $h);
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
            'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'1a1040']],
            'alignment' => ['horizontal'=>Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(20);
    }

    private function setRow($sheet, int $row, array $data, bool $alt): void
    {
        foreach ($data as $i => $val) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1);
            $sheet->setCellValue("{$col}{$row}", $val);
        }
        if ($alt) {
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($data));
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill' => ['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'F5F3FF']],
            ]);
        }
    }

    private function download(Spreadsheet $ss, string $filename)
    {
        $writer = new Xlsx($ss);
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
