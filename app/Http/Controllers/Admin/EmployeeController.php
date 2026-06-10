<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\School;
use App\Models\Department;
use App\Models\Position;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.employees.index');
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function edit(Employee $employee)
    {
        return view('admin.employees.edit', compact('employee'));
    }

    public function show(Employee $employee)
    {
        return view('admin.employees.show', compact('employee'));
    }

    public function import()
    {
        return view('admin.employees.import');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Pegawai');

        $headers = [
            'A1' => 'NIK',
            'B1' => 'Nama Lengkap',
            'C1' => 'Gender (male/female)',
            'D1' => 'Tempat Lahir',
            'E1' => 'Tanggal Lahir (YYYY-MM-DD)',
            'F1' => 'Pendidikan (sd/smp/sma/d3/s1/s2/s3)',
            'G1' => 'No. HP',
            'H1' => 'Unit/Sekolah',
            'I1' => 'Tanggal Masuk (YYYY-MM-DD)',
            'J1' => 'Tipe Pegawai (permanent/contract/intern)',
            'K1' => 'Guru? (ya/tidak)',
            'L1' => 'Departemen',
            'M1' => 'Jabatan',
            'N1' => 'Email',
            'O1' => 'Alamat',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a1040']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ]);

        foreach (range('A','O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getRowDimension(1)->setRowHeight(20);

        $schools     = School::active()->get();
        $departments = Department::active()->get();
        $positions   = Position::active()->get();

        $examples = [
            ['NIPY-001','Ahmad Fauzi','male','Jakarta','1990-05-15','s1',
             '081234567890', $schools->first()?->name ?? 'SMK Fatahillah',
             now()->format('Y-m-d'),'permanent','tidak',
             $departments->first()?->name ?? 'Kurikulum & Pengajaran',
             $positions->first()?->name ?? 'Guru',
             'ahmad@email.com','Jl. Contoh No. 1 Jakarta'],
            ['NIPY-002','Siti Rahma','female','Bandung','1992-08-20','s1',
             '089876543210', $schools->first()?->name ?? 'SMK Fatahillah',
             now()->format('Y-m-d'),'contract','ya',
             $departments->first()?->name ?? 'Tata Usaha',
             $positions->skip(1)->first()?->name ?? 'Staf Tata Usaha',
             'siti@email.com','Jl. Contoh No. 2 Bandung'],
        ];

        foreach ($examples as $idx => $row) {
            $rowNum = $idx + 2;
            foreach (array_values($row) as $colIdx => $value) {
                $col = chr(65 + $colIdx);
                $sheet->setCellValue("{$col}{$rowNum}", $value);
            }
            $sheet->getStyle("A{$rowNum}:O{$rowNum}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            ]);
        }

        // Sheet referensi
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi');
        $refSheet->setCellValue('A1', 'Nama Sekolah');
        $refSheet->setCellValue('B1', 'Nama Departemen');
        $refSheet->setCellValue('C1', 'Nama Jabatan');
        $refSheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
        ]);
        $row = 2;
        foreach ($schools as $s) { $refSheet->setCellValue("A{$row}", $s->name); $row++; }
        $row = 2;
        foreach ($departments as $d) { $refSheet->setCellValue("B{$row}", $d->name); $row++; }
        $row = 2;
        foreach ($positions as $p) { $refSheet->setCellValue("C{$row}", $p->name); $row++; }
        foreach (['A','B','C'] as $col) { $refSheet->getColumnDimension($col)->setAutoSize(true); }

        $spreadsheet->setActiveSheetIndex(0);
        $writer   = new Xlsx($spreadsheet);
        $filename = 'template-import-pegawai-'.now()->format('Ymd').'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
