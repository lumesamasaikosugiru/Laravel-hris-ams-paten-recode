<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (['super_admin','admin_hr','kepala_sekolah','pegawai','guru','koordinator_kurikulum','siswa'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@hris.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $admin->assignRole('super_admin');

        $this->command->info('✅ Roles & Super Admin created (admin@hris.test / password)');
    }
}
