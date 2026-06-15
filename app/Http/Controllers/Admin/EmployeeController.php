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
    public function import()
    {
        return view('admin.employees.import');
    }

    public function edit(Employee $employee)
    {
        return view('admin.employees.edit', compact('employee'));
    }

    public function show(Employee $employee)
    {
        return view('admin.employees.show', compact('employee'));
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Pegawai');

        // ── Header kolom (17 kolom) ───────────────────────────
        $headers = [
            'A1' => 'NIPY',
            'B1' => 'NIK KTP (opsional)',
            'C1' => 'Nama Lengkap',
            'D1' => 'Gender (male/female)',
            'E1' => 'Tempat Lahir',
            'F1' => 'Tanggal Lahir (YYYY-MM-DD)',
            'G1' => 'Pendidikan (sd/smp/sma/smk/d3/s1/s2/s3)',
            'H1' => 'No. HP',
            'I1' => 'Unit/Sekolah',
            'J1' => 'Tanggal Masuk (YYYY-MM-DD)',
            'K1' => 'Tipe Pegawai (permanent/contract/intern)',
            'L1' => 'Status (active/probation)',
            'M1' => 'Guru? (ya/tidak)',
            'N1' => 'Departemen',
            'O1' => 'Jabatan',
            'P1' => 'Email',
            'Q1' => 'Alamat',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style header
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a1040']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '7c3aed']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Data referensi ────────────────────────────────────
        $schools = School::active()->get();
        $departments = Department::active()->get();
        $positions = Position::active()->get();

        // ── Contoh data (2 baris) ─────────────────────────────
        $examples = [
            [
                '2607110001',                                        // A NIPY
                '3273021505900001',                                  // B NIK KTP
                'Ahmad Fauzi',                                       // C Nama
                'male',                                              // D Gender
                'Jakarta',                                           // E Tempat Lahir
                '1990-05-15',                                        // F Tanggal Lahir
                's1',                                                // G Pendidikan
                '081234567890',                                      // H No HP
                $schools->first()?->name ?? 'SMK YP. Fatahillah 1 CLG', // I Unit
                now()->format('Y-m-d'),                              // J Tanggal Masuk
                'permanent',                                         // K Tipe Pegawai
                'active',                                            // L Status
                'ya',                                                // M Guru
                $departments->first()?->name ?? 'Kurikulum & Pengajaran', // N Departemen
                $positions->first()?->name ?? 'Guru Tetap',         // O Jabatan
                'ahmad@email.com',                                   // P Email
                'Jl. Contoh No. 1 Jakarta',                         // Q Alamat
            ],
            [
                '2607210001',                                        // A NIPY
                '',                                                  // B NIK KTP (kosong = opsional)
                'Siti Rahma',                                        // C Nama
                'female',                                            // D Gender
                'Bandung',                                           // E Tempat Lahir
                '1992-08-20',                                        // F Tanggal Lahir
                's1',                                                // G Pendidikan
                '089876543210',                                      // H No HP
                $schools->first()?->name ?? 'SMK YP. Fatahillah 1 CLG', // I Unit
                now()->format('Y-m-d'),                              // J Tanggal Masuk
                'contract',                                          // K Tipe Pegawai
                'active',                                            // L Status
                'tidak',                                             // M Guru
                $departments->skip(1)->first()?->name ?? 'Tata Usaha', // N Departemen
                $positions->skip(1)->first()?->name ?? 'Staf Tata Usaha', // O Jabatan
                'siti@email.com',                                    // P Email
                'Jl. Contoh No. 2 Bandung',                         // Q Alamat
            ],
        ];

        foreach ($examples as $idx => $row) {
            $rowNum = $idx + 2;
            foreach (array_values($row) as $colIdx => $value) {
                $col = $colIdx < 26
                    ? chr(65 + $colIdx)
                    : 'A' . chr(65 + $colIdx - 26);
                $sheet->setCellValue("{$col}{$rowNum}", $value);
            }
            $sheet->getStyle("A{$rowNum}:Q{$rowNum}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            ]);
        }

        // ── Lebar kolom ───────────────────────────────────────
        $colWidths = [
            'A' => 20,
            'B' => 22,
            'C' => 28,
            'D' => 18,
            'E' => 18,
            'F' => 24,
            'G' => 35,
            'H' => 16,
            'I' => 32,
            'J' => 24,
            'K' => 30,
            'L' => 22,
            'M' => 14,
            'N' => 38,
            'O' => 38,
            'P' => 28,
            'Q' => 38,
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Sheet Referensi ───────────────────────────────────
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi');

        // Header referensi
        foreach (['A1' => 'Nama Sekolah', 'B1' => 'Nama Departemen', 'C1' => 'Nama Jabatan', 'D1' => 'Kode Status'] as $cell => $val) {
            $refSheet->setCellValue($cell, $val);
        }
        $refSheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7c3aed']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data referensi
        $row = 2;
        foreach ($schools as $s) {
            $refSheet->setCellValue("A{$row}", $s->name);
            $row++;
        }
        $row = 2;
        foreach ($departments as $d) {
            $refSheet->setCellValue("B{$row}", $d->name);
            $row++;
        }
        $row = 2;
        foreach ($positions as $p) {
            $refSheet->setCellValue("C{$row}", $p->name);
            $row++;
        }

        // Kode status
        $refSheet->setCellValue('D2', 'active');
        $refSheet->setCellValue('D3', 'probation');

        foreach (['A', 'B', 'C', 'D'] as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Kembali ke sheet utama & download ─────────────────
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $filename = 'template-import-pegawai-' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}