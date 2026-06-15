<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name'              => 'Cuti Tahunan',
                'quota'             => 12,
                'gender'            => 'all',
                'cycle'             => 'annual',
                'requires_document' => false,
                'description'       => 'Cuti tahunan reguler. Tidak berlaku untuk Guru.',
            ],
            [
                'name'              => 'Cuti Sakit',
                'quota'             => 90,
                'gender'            => 'all',
                'cycle'             => 'annual',
                'requires_document' => true,
                'description'       => 'Wajib melampirkan surat keterangan dokter.',
            ],
            [
                'name'              => 'Cuti Melahirkan',
                'quota'             => 90,
                'gender'            => 'female',
                'cycle'             => 'once',
                'requires_document' => true,
                'description'       => 'Hanya untuk pegawai perempuan.',
            ],
            [
                'name'              => 'Cuti Menikah',
                'quota'             => 3,
                'gender'            => 'all',
                'cycle'             => 'once',
                'requires_document' => false,
                'description'       => 'Cuti pernikahan pertama.',
            ],
            [
                'name'              => 'Cuti Duka',
                'quota'             => 3,
                'gender'            => 'all',
                'cycle'             => 'annual',
                'requires_document' => false,
                'description'       => 'Keluarga inti meninggal dunia.',
            ],
            [
                'name'              => 'Izin Tidak Masuk',
                'quota'             => 5,
                'gender'            => 'all',
                'cycle'             => 'annual',
                'requires_document' => false,
                'description'       => 'Izin keperluan mendadak tanpa potong cuti tahunan.',
            ],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::firstOrCreate(
                ['name' => $lt['name']],
                array_merge($lt, ['is_active' => true])
            );
        }

        $this->command->info('✅ Leave Types selesai! ('.LeaveType::count().' jenis cuti)');
    }
}
