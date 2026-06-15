<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            ['code' => 'YPFC', 'name' => 'YP. Fatahillah (Pusat)', 'is_active' => true],
            ['code' => 'SMK1-KWT', 'name' => 'SMK YP. Fatahillah 1 Kramatwatu', 'is_active' => true],
            ['code' => 'SMK1-CLG', 'name' => 'SMK YP. Fatahillah 1 Cilegon', 'is_active' => true],
            ['code' => 'SMK2-CLG', 'name' => 'SMK YP. Fatahillah 2 Cilegon', 'is_active' => true],
            ['code' => 'SMP-CLG', 'name' => 'SMP YP. Fatahillah Cilegon', 'is_active' => true],
        ];

        foreach ($schools as $school) {
            School::firstOrCreate(
                ['code' => $school['code']],
                $school
            );
        }

        $this->command->info('✅ Schools selesai! (' . School::count() . ' unit)');
    }
}
