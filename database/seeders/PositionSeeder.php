<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Department;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $yayasan = School::where('code', 'YPFC')->first();
        $smk1 = School::where('code', 'SMK1-KWT')->first();
        $smk2 = School::where('code', 'SMK1-CLG')->first();
        $smk3 = School::where('code', 'SMK2-CLG')->first();
        $smp = School::where('code', 'SMP-CLG')->first();

        // ── Jabatan Yayasan Pusat ─────────────────────────────

        // Dewan Yayasan
        $dewan = Department::where('school_id', $yayasan->id)->where('code', 'DEWAN')->first();
        foreach ([
            ['Dewan Pembina', 5],
            ['Dewan Pengawas', 5],
        ] as [$name, $level]) {
            Position::firstOrCreate(
                ['school_id' => $yayasan->id, 'department_id' => $dewan->id, 'name' => $name],
                ['level' => $level, 'is_active' => true]
            );
        }

        // Pengurus YPFC
        $sekret = Department::where('school_id', $yayasan->id)->where('code', 'SEKRET')->first();
        foreach ([
            ['Ketua Yayasan', 4],
            ['Sekretaris', 4],
            ['Bendahara', 4],
            ['Staf Bendahara', 1],
        ] as [$name, $level]) {
            Position::firstOrCreate(
                ['school_id' => $yayasan->id, 'department_id' => $sekret->id, 'name' => $name],
                ['level' => $level, 'is_active' => true]
            );
        }

        // Bidang I P2MP
        $p2mp = Department::where('school_id', $yayasan->id)->where('code', 'P2MP')->first();
        foreach ([
            ['Kepala Bidang P2MP', 3],
            ['Staf Bidang P2MP', 1],
        ] as [$name, $level]) {
            Position::firstOrCreate(
                ['school_id' => $yayasan->id, 'department_id' => $p2mp->id, 'name' => $name],
                ['level' => $level, 'is_active' => true]
            );
        }

        // Bidang II SDM
        $sdm = Department::where('school_id', $yayasan->id)->where('code', 'SDM')->first();
        foreach ([
            ['Kepala Bidang SDM & Umum', 3],
            ['Staf Bidang SDM', 1],
        ] as [$name, $level]) {
            Position::firstOrCreate(
                ['school_id' => $yayasan->id, 'department_id' => $sdm->id, 'name' => $name],
                ['level' => $level, 'is_active' => true]
            );
        }

        // Bidang III Keuangan
        $keu = Department::where('school_id', $yayasan->id)->where('code', 'KEU')->first();
        foreach ([
            ['Kepala Bidang Keuangan', 3],
            ['Staf Bidang Keuangan', 1],
        ] as [$name, $level]) {
            Position::firstOrCreate(
                ['school_id' => $yayasan->id, 'department_id' => $keu->id, 'name' => $name],
                ['level' => $level, 'is_active' => true]
            );
        }

        // Bidang IV Sarpras
        $sarpras = Department::where('school_id', $yayasan->id)->where('code', 'SARPRAS')->first();
        foreach ([
            ['Kepala Bidang Sarana & Prasarana', 3],
            ['Staf Bidang Sarpras', 1],
        ] as [$name, $level]) {
            Position::firstOrCreate(
                ['school_id' => $yayasan->id, 'department_id' => $sarpras->id, 'name' => $name],
                ['level' => $level, 'is_active' => true]
            );
        }

        // Bidang V Humas
        $humas = Department::where('school_id', $yayasan->id)->where('code', 'HUMAS')->first();
        foreach ([
            ['Kepala Bidang Sosial & Humas', 3],
            ['Staf Bidang Humas', 1],
        ] as [$name, $level]) {
            Position::firstOrCreate(
                ['school_id' => $yayasan->id, 'department_id' => $humas->id, 'name' => $name],
                ['level' => $level, 'is_active' => true]
            );
        }

        // ── Jabatan Per Sekolah ───────────────────────────────
        foreach ([$smk1, $smk2, $smk3, $smp] as $school) {
            $pimpin = Department::where('school_id', $school->id)->where('code', 'KEPSEK')->first();
            Position::firstOrCreate(
                ['school_id' => $school->id, 'department_id' => $pimpin->id, 'name' => 'Kepala Sekolah'],
                ['level' => 5, 'is_active' => true]
            );

            $tu = Department::where('school_id', $school->id)->where('code', 'TU')->first();
            foreach ([
                ['Kepala Bagian Tata Usaha', 3],
                ['Bendahara Sekolah', 2],
                ['Staf Tata Usaha', 1],
            ] as [$name, $level]) {
                Position::firstOrCreate(
                    ['school_id' => $school->id, 'department_id' => $tu->id, 'name' => $name],
                    ['level' => $level, 'is_active' => true]
                );
            }

            $tendik = Department::where('school_id', $school->id)->where('code', 'TENDIK')->first();
            foreach ([
                ['Guru Tidak Tetap', 1],
                ['Guru Tetap', 1],
            ] as [$name, $level]) {
                Position::firstOrCreate(
                    ['school_id' => $school->id, 'department_id' => $tendik->id, 'name' => $name],
                    ['level' => $level, 'is_active' => true]
                );
            }

            $kur = Department::where('school_id', $school->id)->where('code', 'KUR')->first();
            foreach ([
                ['Wakil Kepala Sekolah Bidang Kurikulum', 4],
                ['Staf Kurikulum', 1],
            ] as [$name, $level]) {
                Position::firstOrCreate(
                    ['school_id' => $school->id, 'department_id' => $kur->id, 'name' => $name],
                    ['level' => $level, 'is_active' => true]
                );
            }
            $sarpras = Department::where('school_id', $school->id)->where('code', 'SARPRAS')->first();
            foreach ([
                ['Wakil Kepala Sekolah Bidang Sarana & Prasarana', 4],
                ['Staf Sarana', 1],
            ] as [$name, $level]) {
                Position::firstOrCreate(
                    ['school_id' => $school->id, 'department_id' => $sarpras->id, 'name' => $name],
                    ['level' => $level, 'is_active' => true]
                );
            }
            $hub = Department::where('school_id', $school->id)->where('code', 'HUBIN')->first();
            foreach ([
                ['Wakil Kepala Sekolah Bidang Hubungan Industri', 4],
                ['Staf Hubungan Industri', 1],
            ] as [$name, $level]) {
                Position::firstOrCreate(
                    ['school_id' => $school->id, 'department_id' => $hub->id, 'name' => $name],
                    ['level' => $level, 'is_active' => true]
                );
            }
            $sis = Department::where('school_id', $school->id)->where('code', 'SISWA')->first();
            foreach ([
                ['Wakil Kepala Sekolah Bidang Kesiswaan', 4],
                ['Staf Kesiswaan', 1],
            ] as [$name, $level]) {
                Position::firstOrCreate(
                    ['school_id' => $school->id, 'department_id' => $sis->id, 'name' => $name],
                    ['level' => $level, 'is_active' => true]
                );
            }
        }

        $this->command->info('✅ Positions selesai! (' . Position::count() . ' jabatan)');
    }
}
