<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Hapus semua permission & role lama
        Permission::query()->delete();
        Role::query()->delete();

        // ── Definisi semua permission ─────────────────────────
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Master Data
            'master.view',
            'master.create',
            'master.edit',
            'master.delete',

            // Rekrutmen
            'recruitment.view',
            'recruitment.create',
            'recruitment.edit',
            'recruitment.delete',
            'recruitment.pipeline',
            'recruitment.convert',

            // Pegawai
            'employee.view',
            'employee.view.own',
            'employee.create',
            'employee.edit',
            'employee.delete',
            'employee.import',
            'employee.probation',

            // Absensi
            'attendance.view',
            'attendance.view.own',
            'attendance.create',
            'attendance.edit',
            'attendance.report',
            'attendance.export',

            // Cuti
            'leave.view',
            'leave.view.own',
            'leave.view.subordinate',
            'leave.create',
            'leave.approve',
            'leave.approve.school',
            'leave.balance',

            // Laporan
            'report.view',
            'report.export',

            // User Management
            'user.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── 1. SUPER ADMIN — akses penuh ─────────────────────
        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // ── 2. ADMIN SDM — hampir penuh kecuali user.manage ──
        // Dual-access sejak penambahan admin_sdm ke User::PORTAL_ROLES:
        // bisa absen & ajukan cuti sendiri lewat Portal (attendance.view.own,
        // leave.view.own), SELAIN tetap punya akses penuh Dashboard di
        // bawah. Pola permission .view.own ini disamakan dengan role
        // dual-access lain (staf_sdm, sekretaris, bendahara, ketua).
        $adminSdm = Role::create(['name' => 'admin_sdm', 'guard_name' => 'web']);
        $adminSdm->syncPermissions([
            'dashboard.view',
            'master.view',
            'master.create',
            'master.edit',
            'master.delete',
            'recruitment.view',
            'recruitment.create',
            'recruitment.edit',
            'recruitment.delete',
            'recruitment.pipeline',
            'recruitment.convert',
            'employee.view',
            'employee.create',
            'employee.edit',
            'employee.delete',
            'employee.import',
            'employee.probation',
            'attendance.view',
            'attendance.view.own',
            'attendance.create',
            'attendance.edit',
            'attendance.report',
            'attendance.export',
            'leave.view',
            'leave.view.own',
            'leave.view.subordinate',
            'leave.create',
            'leave.approve',
            'leave.balance',
            'report.view',
            'report.export',
        ]);

        // ── 3. STAF SDM ───────────────────────────────────────
        // Akses portal (absen+cuti) DAN dashboard, absensi di
        // dashboard READ-ONLY. Data pegawai JUGA read-only (tidak
        // boleh create/edit/delete) — hanya bisa LIHAT.
        $stafSdm = Role::create(['name' => 'staf_sdm', 'guard_name' => 'web']);
        $stafSdm->syncPermissions([
            'dashboard.view',
            'master.view',
            'master.create',
            'master.edit',
            'master.delete',
            'recruitment.view',
            'recruitment.create',
            'recruitment.edit',
            'recruitment.delete',
            'recruitment.pipeline',
            'recruitment.convert',
            'employee.view', // hanya lihat, tanpa create/edit/delete
            'attendance.view', // read-only — TANPA attendance.create / attendance.edit
            'attendance.report',
            'attendance.export',
            'attendance.view.own',
            'leave.view.own',
            'leave.create', // akses portal
            'leave.view',
            'leave.view.subordinate',
            'leave.balance',
            'report.view',
            'report.export',
        ]);

        // ── 4. SEKRETARIS ───────────────────────────────────────
        // Akses portal (absen+cuti) DAN dashboard, absensi read-only.
        // Data pegawai JUGA read-only (tidak boleh create/edit/delete).
        $sekretaris = Role::create(['name' => 'sekretaris', 'guard_name' => 'web']);
        $sekretaris->syncPermissions([
            'dashboard.view',
            'master.view',
            'master.create',
            'master.edit',
            'employee.view', // hanya lihat, tanpa create/edit/delete
            'attendance.view', // read-only — TANPA attendance.create / attendance.edit
            'attendance.view.own',
            'leave.view.own',
            'leave.create', // akses portal
            'leave.view',
            'leave.create',
            'report.view',
            'report.export',
        ]);

        // ── 5. KETUA ──────────────────────────────────────────
        // Akses portal (absen+cuti) DAN dashboard, absensi read-only,
        // data pegawai HANYA LIHAT (tanpa create/edit/delete).
        $ketua = Role::create(['name' => 'ketua', 'guard_name' => 'web']);
        $ketua->syncPermissions([
            'dashboard.view',
            'employee.view', // hanya lihat, tanpa create/edit/delete
            'attendance.view', // read-only — TANPA attendance.create / attendance.edit
            'attendance.view.own',
            'leave.view.own',
            'leave.create', // akses portal
            'leave.view',
            'leave.view.subordinate',
            'leave.approve',
            'report.view',
            'report.export',
        ]);

        // ── 6. BENDAHARA ──────────────────────────────────────
        // Akses portal (absen+cuti) DAN dashboard, absensi read-only,
        // data pegawai HANYA LIHAT (tanpa create/edit/delete).
        $bendahara = Role::create(['name' => 'bendahara', 'guard_name' => 'web']);
        $bendahara->syncPermissions([
            'dashboard.view',
            'employee.view', // hanya lihat, tanpa create/edit/delete
            'attendance.view', // read-only — TANPA attendance.create / attendance.edit
            'attendance.view.own',
            'leave.view.own',
            'leave.create', // akses portal
            'leave.view.subordinate',
            'report.view',
            'report.export',
        ]);

        // ── 7. KEPALA BIDANG — diri sendiri + lihat cuti staf ─
        $kepalaBidang = Role::create(['name' => 'kepala_bidang', 'guard_name' => 'web']);
        $kepalaBidang->syncPermissions([
            'dashboard.view',
            'employee.view.own',
            'attendance.view.own',
            'leave.view.own',
            'leave.view.subordinate',
            'leave.create',
        ]);

        // ── 8. STAF YAYASAN — hanya data diri sendiri ────────
        $stafYayasan = Role::create(['name' => 'staf_yayasan', 'guard_name' => 'web']);
        $stafYayasan->syncPermissions([
            'employee.view.own',
            'attendance.view.own',
            'leave.view.own',
            'leave.create',
        ]);

        // ── 9. GURU — portal-only, sama dengan staf_yayasan ──
        // Pengajuan cutinya WAJIB lewat approval Kepala Sekolah dulu
        // (lihat LeaveService::ROLES_REQUIRE_SCHOOL_APPROVAL), baru
        // diproses Admin SDM/Ketua. Tidak ada akses Dashboard sama
        // sekali, persis seperti staf_yayasan/kepala_bidang.
        $guru = Role::create(['name' => 'guru', 'guard_name' => 'web']);
        $guru->syncPermissions([
            'employee.view.own',
            'attendance.view.own',
            'leave.view.own',
            'leave.create',
        ]);

        // ── 10. NON GURU (sekolah) — portal-only, sama dgn guru ──
        // Untuk staf non-pengajar di lingkungan sekolah (TU, dst),
        // BUKAN staf yayasan pusat. Aturan approval cuti sama
        // dengan guru: wajib lewat Kepala Sekolah dulu.
        $nonGuru = Role::create(['name' => 'non_guru', 'guard_name' => 'web']);
        $nonGuru->syncPermissions([
            'employee.view.own',
            'attendance.view.own',
            'leave.view.own',
            'leave.create',
        ]);

        // ── 11. KEPALA SEKOLAH — portal-only + approval tahap 1 ──
        // Satu role generik dipakai oleh SEMUA Kepala Sekolah di
        // semua unit. TIDAK ada role terpisah per sekolah (mis.
        // kepsek_smk1, kepsek_smk2) -- scoping "Kepsek ini cuma boleh
        // approve cuti guru di SEKOLAHNYA SENDIRI" dikerjakan lewat
        // LOGIC (bandingkan employee->school_id milik akun Kepsek
        // yang login dengan school_id pengaju cuti), BUKAN lewat
        // permission/role terpisah per sekolah. Lihat
        // LeaveSchoolApproval.php (Livewire Portal) untuk
        // implementasi scoping-nya. Akun Kepsek WAJIB terhubung ke
        // Employee dengan posisi 'Kepala Sekolah' di sekolah terkait
        // (employees.user_id -> users.id, lalu employees.school_id
        // menentukan sekolah mana yang ia boleh approve).
        $kepalaSekolah = Role::create(['name' => 'kepala_sekolah', 'guard_name' => 'web']);
        $kepalaSekolah->syncPermissions([
            'employee.view.own',
            'attendance.view.own',
            'leave.view.own',
            'leave.create',
            'leave.approve.school',
        ]);

        $this->command->info('✅ Roles & Permissions selesai!');
        $this->command->table(
            ['Role', 'Permission'],
            Role::all()->map(fn($r) => [
                $r->name,
                $r->permissions->count() . ' permission',
            ])->toArray()
        );
    }
}