<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Department;
use App\Models\Position;
use App\Models\Skill;
use App\Models\LeaveType;

class FatahillahStructureSeeder extends Seeder
{
    public function run(): void
    {
        // ── Sekolah ──────────────────────────────────────
        $yayasan = School::firstOrCreate(['code'=>'YF-PUSAT'],['name'=>'Yayasan Fatahillah (Pusat)','is_active'=>true]);
        $smk = School::firstOrCreate(['code'=>'SMK-FTH'],['name'=>'SMK Fatahillah','is_active'=>true]);
        $sma = School::firstOrCreate(['code'=>'SMA-FTH'],['name'=>'SMA Fatahillah','is_active'=>true]);

        // ── Yayasan Pusat ────────────────────────────────
        $dewan = Department::firstOrCreate(['school_id'=>$yayasan->id,'code'=>'DEWAN'],['name'=>'Dewan Yayasan','is_active'=>true]);
        foreach ([['Dewan Pembina',5],['Dewan Pengawas',5],['Ketua Yayasan',5]] as [$n,$l]) {
            Position::firstOrCreate(['school_id'=>$yayasan->id,'department_id'=>$dewan->id,'name'=>$n],['level'=>$l,'is_active'=>true]);
        }
        $sek = Department::firstOrCreate(['school_id'=>$yayasan->id,'code'=>'SEKRET'],['name'=>'Sekretariat','is_active'=>true]);
        Position::firstOrCreate(['school_id'=>$yayasan->id,'department_id'=>$sek->id,'name'=>'Sekretaris'],['level'=>4,'is_active'=>true]);
        $keu = Department::firstOrCreate(['school_id'=>$yayasan->id,'code'=>'KEU'],['name'=>'Keuangan','is_active'=>true]);
        Position::firstOrCreate(['school_id'=>$yayasan->id,'department_id'=>$keu->id,'name'=>'Bendahara'],['level'=>4,'is_active'=>true]);
        $bid = Department::firstOrCreate(['school_id'=>$yayasan->id,'code'=>'BIDANG'],['name'=>'Bidang','is_active'=>true]);
        Position::firstOrCreate(['school_id'=>$yayasan->id,'department_id'=>$bid->id,'name'=>'Kepala Bidang'],['level'=>3,'is_active'=>true]);

        // ── Per Sekolah ──────────────────────────────────
        foreach ([$smk, $sma] as $school) {
            $pim = Department::firstOrCreate(['school_id'=>$school->id,'code'=>'PIMPIN'],['name'=>'Pimpinan Sekolah','is_active'=>true]);
            Position::firstOrCreate(['school_id'=>$school->id,'department_id'=>$pim->id,'name'=>'Kepala Sekolah'],['level'=>5,'is_active'=>true]);

            $tu = Department::firstOrCreate(['school_id'=>$school->id,'code'=>'TU'],['name'=>'Tata Usaha','is_active'=>true]);
            foreach ([['Kepala Bagian Tata Usaha',3],['Bendahara Sekolah',2],['Staf Tata Usaha',1]] as [$n,$l]) {
                Position::firstOrCreate(['school_id'=>$school->id,'department_id'=>$tu->id,'name'=>$n],['level'=>$l,'is_active'=>true]);
            }

            $kur = Department::firstOrCreate(['school_id'=>$school->id,'code'=>'KUR'],['name'=>'Kurikulum & Pengajaran','is_active'=>true]);
            foreach ([['Wakil Kepala Sekolah Bidang Kurikulum',4],['Wakil Kepala Sekolah Bidang Kesiswaan',4],['Wakil Kepala Sekolah Bidang Sarana',4],['Wakil Kepala Sekolah Bidang Humas',4],['Guru',2],['Wali Kelas',2]] as [$n,$l]) {
                Position::firstOrCreate(['school_id'=>$school->id,'department_id'=>$kur->id,'name'=>$n],['level'=>$l,'is_active'=>true]);
            }

            $umum = Department::firstOrCreate(['school_id'=>$school->id,'code'=>'UMUM'],['name'=>'Staf Umum','is_active'=>true]);
            Position::firstOrCreate(['school_id'=>$school->id,'department_id'=>$umum->id,'name'=>'Staf'],['level'=>1,'is_active'=>true]);
        }

        // ── Skills ───────────────────────────────────────
        foreach ([
            ['Microsoft Word','IT'],['Microsoft Excel','IT'],['Microsoft PowerPoint','IT'],
            ['Bahasa Inggris','Bahasa'],['Bahasa Arab','Bahasa'],
            ['Manajemen Kelas','Mengajar'],['Kurikulum Merdeka','Mengajar'],
            ['Administrasi Perkantoran','Administrasi'],['Keuangan','Administrasi'],
        ] as [$n,$c]) {
            Skill::firstOrCreate(['name'=>$n],['category'=>$c,'is_active'=>true]);
        }

        // ── Leave Types ──────────────────────────────────
        foreach ([
            ['Cuti Tahunan',12,'all','annual',false,'Cuti tahunan reguler'],
            ['Cuti Sakit',90,'all','annual',true,'Wajib surat dokter'],
            ['Cuti Melahirkan',90,'female','once',true,'Untuk pegawai perempuan'],
            ['Cuti Menikah',3,'all','once',false,'Cuti pernikahan pertama'],
            ['Cuti Duka',3,'all','annual',false,'Keluarga inti meninggal'],
            ['Izin Tidak Masuk',5,'all','annual',false,'Izin tanpa potong cuti tahunan'],
        ] as [$n,$q,$g,$c,$d,$desc]) {
            LeaveType::firstOrCreate(['name'=>$n],['quota'=>$q,'gender'=>$g,'cycle'=>$c,'requires_document'=>$d,'description'=>$desc,'is_active'=>true]);
        }

        $this->command->info('✅ Fatahillah structure, skills, & leave types created');
    }
}
