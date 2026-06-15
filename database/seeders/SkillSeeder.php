<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Skill;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            // IT
            ['name' => 'Microsoft Word',        'category' => 'IT'],
            ['name' => 'Microsoft Excel',        'category' => 'IT'],
            ['name' => 'Microsoft PowerPoint',   'category' => 'IT'],
            ['name' => 'Google Workspace',       'category' => 'IT'],
            ['name' => 'Sistem Informasi',       'category' => 'IT'],

            // Bahasa
            ['name' => 'Bahasa Inggris',         'category' => 'Bahasa'],
            ['name' => 'Bahasa Arab',             'category' => 'Bahasa'],

            // Mengajar
            ['name' => 'Manajemen Kelas',        'category' => 'Mengajar'],
            ['name' => 'Kurikulum Merdeka',      'category' => 'Mengajar'],
            ['name' => 'Penyusunan RPP',         'category' => 'Mengajar'],

            // Administrasi
            ['name' => 'Administrasi Perkantoran', 'category' => 'Administrasi'],
            ['name' => 'Keuangan & Akuntansi',   'category' => 'Administrasi'],
            ['name' => 'Kearsipan',              'category' => 'Administrasi'],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['name' => $skill['name']],
                ['category' => $skill['category'], 'is_active' => true]
            );
        }

        $this->command->info('✅ Skills selesai! ('.Skill::count().' skill)');
    }
}
