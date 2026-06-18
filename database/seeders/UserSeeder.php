<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Super Admin
            [
                'name' => 'Super Admin',
                'email' => 'hris@superadmin.dev',
                'role' => 'super_admin',
            ],
            // Admin atau kepala bidang SDM
            [
                'name' => 'Admin | Kepala Bidang SDM',// bisa pak deni yang akses
                'email' => 'hris@adminsdm.dev',
                'role' => 'admin_sdm',
            ],
            // Staf SDM (punya akses dashboard)
            [
                'name' => 'Staf SDM - Gatot',
                'email' => 'hris@stafsdm1.dev',
                'role' => 'staf_sdm',
            ],
            // Ketua
            [
                'name' => 'Ketua Yayasan',
                'email' => 'hris@ketua.dev',
                'role' => 'ketua',
            ],
            // Sekretaris
            [
                'name' => 'Sekretaris',
                'email' => 'hris@sekretaris.dev',
                'role' => 'sekretaris',
            ],
            // Bendahara
            [
                'name' => 'Bendahara',
                'email' => 'hris@bendahara.dev',
                'role' => 'bendahara',
            ],
            // Kepala Bidang (4 orang)
            [
                'name' => 'Kepala Bidang P2MP',
                'email' => 'hris@kabid.p2mp.dev',
                'role' => 'kepala_bidang',
            ],
            [
                'name' => 'Kepala Bidang Keuangan',
                'email' => 'hris@kabid.keuangan.dev',
                'role' => 'kepala_bidang',
            ],
            [
                'name' => 'Kepala Bidang Sarpras',
                'email' => 'hris@kabid.sarpras.dev',
                'role' => 'kepala_bidang',
            ],
            [
                'name' => 'Kepala Bidang Humas',
                'email' => 'hris@kabid.humas.dev',
                'role' => 'kepala_bidang',
            ],
            // Staf Yayasan
            [
                'name' => 'Staf Sarpras - Subhi',
                'email' => 'hris@staf.sarpras.dev',
                'role' => 'staf_yayasan',
            ],
            [
                'name' => 'Staf Bendahara - Via',
                'email' => 'hris@staf.bendahara.dev',
                'role' => 'staf_yayasan',
            ],
            [
                'name' => 'Staf P2MP - Dwiki',
                'email' => 'hris@staf.p2mp.dev',
                'role' => 'staf_yayasan',
            ],
            [
                'name' => 'Staf SDM - Deni',
                'email' => 'hris@staf.sdm.dev',
                'role' => 'staf_yayasan',
            ],
            [
                'name' => 'Staf SDM - Muah',
                'email' => 'hris@staf.sdm.dev',
                'role' => 'staf_yayasan',
            ],
        ];

        foreach ($users as $data) {
            if (User::where('email', $data['email'])->exists())
                continue;

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $user->assignRole($data['role']);
        }

        $this->command->info('✅ User dev selesai!');
        $this->command->table(
            ['Nama', 'Email', 'Role'],
            collect($users)->map(fn($u) => [
                $u['name'],
                $u['email'],
                $u['role'],
            ])->toArray()
        );
    }
}
