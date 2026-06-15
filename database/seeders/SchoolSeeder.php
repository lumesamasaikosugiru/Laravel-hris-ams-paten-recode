<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            ['code' => 'YF-PUSAT', 'name' => 'Yayasan Fatahillah (Pusat)',  'is_active' => true],
            ['code' => 'SMK1-FTH', 'name' => 'SMK YP. Fatahillah 1 CLG',   'is_active' => true],
            ['code' => 'SMK2-FTH', 'name' => 'SMK YP. Fatahillah 2 CLG',   'is_active' => true],
        ];

        foreach ($schools as $school) {
            School::firstOrCreate(
                ['code' => $school['code']],
                $school
            );
        }

        $this->command->info('✅ Schools selesai! ('.School::count().' unit)');
    }
}
