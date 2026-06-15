<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Roles & Permissions (harus pertama)
            RolePermissionSeeder::class,

            // 2. Master Data
            SchoolSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            SkillSeeder::class,
            LeaveTypeSeeder::class,

            // 3. Users (harus setelah roles)
            UserSeeder::class,
        ]);
    }
}
