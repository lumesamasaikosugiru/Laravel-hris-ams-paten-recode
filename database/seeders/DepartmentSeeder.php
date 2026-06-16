<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $yayasan = School::where('code', 'YPFC')->first();
        $smk1 = School::where('code', 'SMK1-KWT')->first();
        $smk2 = School::where('code', 'SMK1-CLG')->first();
        $smk3 = School::where('code', 'SMK2-CLG')->first();
        $smp = School::where('code', 'SMP-CLG')->first();

        // ── Departemen Yayasan Pusat ──────────────────────────
        $yayasanDepts = [
            ['code' => 'DEWAN', 'name' => 'Dewan Yayasan'],
            ['code' => 'SEKRET', 'name' => 'Pengurus YPFC'],
            ['code' => 'P2MP', 'name' => 'Bidang I Peningkatan & Penjamin Mutu Pendidikan'],
            ['code' => 'SDM', 'name' => 'Bidang II SDM & Umum'],
            ['code' => 'KEU', 'name' => 'Bidang III Keuangan'],
            ['code' => 'SARPRAS', 'name' => 'Bidang IV Sarana & Prasarana'],
            ['code' => 'HUMAS', 'name' => 'Bidang V Sosial & Humas'],
        ];

        foreach ($yayasanDepts as $dept) {
            Department::firstOrCreate(
                ['school_id' => $yayasan->id, 'code' => $dept['code']],
                ['name' => $dept['name'], 'is_active' => true]
            );
        }

        // ── Departemen Per Sekolah ────────────────────────────
        $schoolDepts = [
            ['code' => 'KEPSEK', 'name' => 'Kepala Sekolah'],
            ['code' => 'KUR', 'name' => 'Kurikulum'],
            ['code' => 'SARPRAS', 'name' => 'Sarana & Prasarana'],
            ['code' => 'HUBIN', 'name' => 'Hubungan Industri'],
            ['code' => 'SISWA', 'name' => 'Kesiswaan'],
            ['code' => 'TU', 'name' => 'Tata Usaha'],
            ['code' => 'TENDIK', 'name' => 'Tenaga Pendidik'],
        ];

        foreach ([$smk1, $smk2, $smk3, $smp] as $school) {
            foreach ($schoolDepts as $dept) {
                Department::firstOrCreate(
                    ['school_id' => $school->id, 'code' => $dept['code']],
                    ['name' => $dept['name'], 'is_active' => true]
                );
            }
        }

        $this->command->info('✅ Departments selesai! (' . Department::count() . ' departemen)');
    }
}
