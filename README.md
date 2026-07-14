# HRIS Yayasan Fatahillah

> **Human Resource Information System** — Sistem informasi SDM berbasis web untuk Yayasan Fatahillah. Mengelola seluruh siklus kepegawaian: rekrutmen, masa percobaan, NIPY, absensi (manual + GPS Geofencing), cuti, dan laporan dalam satu platform terpadu yang mendukung struktur multi-sekolah, dengan dua titik akses: Dashboard (admin) dan Portal Mobile (self-service pegawai).

**Versi:** 1.4+ (pasca RBAC granular, fitur is_active, rantai approval, defense-in-depth) · **Status:** Production Ready — Portal & GPS Geofencing aktif di server online

> 📘 **Dokumen referensi lengkap:** lihat _HRIS Yayasan Fatahillah — Dokumen Konteks Master_ (.docx) untuk detail menyeluruh skema database, semua relasi model, dan daftar temuan/diskrepansi yang sedang dipantau. README ini sengaja dibuat ringkas untuk kebutuhan harian — **README ini yang paling sering update**, dokumen Master di-update berkala/menyeluruh.

---

## Tech Stack

| Layer         | Teknologi                          | Versi                                         |
| ------------- | ---------------------------------- | --------------------------------------------- |
| Backend       | Laravel                            | 12.x                                          |
| Reactive UI   | Livewire                           | v4.x                                          |
| CSS Framework | Tailwind CSS                       | v3.x                                          |
| JS Alpine     | Alpine.js                          | v3.x (jangan panggil `Alpine.start()` manual) |
| Database      | MySQL                              | 8.0+                                          |
| Auth          | Laravel Breeze + Spatie Permission | latest                                        |
| Excel         | PhpSpreadsheet                     | latest                                        |
| Chart         | Chart.js                           | v4.4                                          |
| Font          | Plus Jakarta Sans                  | Google Fonts                                  |

---

## Instalasi

### Prasyarat

- PHP 8.2+ (BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML)
- Composer 2.x
- Node.js 18+ dan NPM
- MySQL 8.0+ atau MariaDB 10.6+
- XAMPP / LAMPP

### Langkah

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
DB_DATABASE=hrisv1
DB_USERNAME=root
DB_PASSWORD=
APP_TIMEZONE=Asia/Jakarta

# Geofencing GPS — default ada di config/geofence.php (saat ini 30m)
# Override di sini hanya jika butuh nilai berbeda per environment
GEOFENCE_RADIUS=30
GEOFENCE_STRICT=true
```

```bash
# 4. Buat database
/opt/lampp/bin/mysql -u root -e "CREATE DATABASE hrisv1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Migrasi dan seeder
php artisan migrate --seed

# 6. Build assets
npm run build

# 7. Storage symlink
php artisan storage:link

# 8. Jalankan server
php artisan serve
```

### Konfigurasi `resources/js/app.js` (Penting!)

```js
import "./bootstrap";
import Alpine from "alpinejs";
window.Alpine = Alpine;
// JANGAN tambahkan Alpine.start() — Livewire v4 yang handle ini
```

### Akun Login Dev (Password: `password`)

Diisi via `UserSeeder.php`. Semua akun di bawah dibuat otomatis saat `php artisan migrate:fresh --seed`.

| Nama                                            | Email                                           | Role            |
| ----------------------------------------------- | ----------------------------------------------- | --------------- |
| Super Admin                                     | hris@superadmin.dev                             | `super_admin`   |
| Admin \| Kepala Bidang SDM                      | hris@adminsdm.dev                               | `admin_sdm`     |
| Staf SDM - Gatot                                | hris@stafsdm1.dev                               | `staf_sdm`      |
| Ketua Yayasan                                   | hris@ketua.dev                                  | `ketua`         |
| Sekretaris                                      | hris@sekretaris.dev                             | `sekretaris`    |
| Bendahara                                       | hris@bendahara.dev                              | `bendahara`     |
| Kepala Bidang P2MP / Keuangan / Sarpras / Humas | hris@kabid.[p2mp\|keuangan\|sarpras\|humas].dev | `kepala_bidang` |
| Staf Sarpras - Subhi                            | hris@staf.sarpras.dev                           | `staf_yayasan`  |
| Staf Bendahara - Via                            | hris@staf.bendahara.dev                         | `staf_yayasan`  |
| Staf P2MP - Dwiki                               | hris@staf.p2mp.dev                              | `staf_yayasan`  |
| Staf SDM - Deni                                 | hris@staf.sdm.dev                               | `staf_yayasan`  |

> ⚠️ Tidak ada lagi akun `admin@hris.test` — login default lama sudah tidak berlaku sejak struktur role disesuaikan ke struktur kepengurusan Yayasan (lihat bagian **Roles** di bawah).

---

## Fitur (Phase 1–6 + Polish + Portal + GPS + RBAC Granular)

### ✅ Phase 1 — Fondasi & Master Data

- Auth via Laravel Breeze + Spatie Permission (**11 roles**, lihat bagian Roles)
- CRUD: Sekolah, Departemen, Jabatan, Skill, Jenis Cuti
- Seeder struktur Yayasan Fatahillah

### ✅ Phase 2 — Rekrutmen

- Kelola lowongan kerja (Draft → Dibuka → Ditutup)
- Halaman publik `/karir` dengan form pendaftaran multi-tab
- CV wajib diupload saat pendaftaran
- Pipeline seleksi: Lamaran → Verifikasi Berkas → Tes Potensi → Diterima/Ditolak
- Klik baris tabel → lihat detail pelamar (modal)
- Konversi pelamar → pegawai (NIK sementara auto-generated)

### ✅ Phase 3 — Manajemen Pegawai

- Form pegawai multi-tab (Identitas, Kontak, Kepegawaian, Pendidikan, Jabatan)
- Import massal dari Excel (template downloadable v2, 17 kolom + sheet referensi)
- Halaman detail: profil lengkap, timeline riwayat jabatan
- Mutasi / Promosi / Demosi dengan riwayat tersimpan
- Evaluasi masa percobaan + auto-generate NIPY
- **Soft delete** — pegawai dengan riwayat tidak bisa dihapus permanen
- **Info pensiun** — usia saat ini, tanggal pensiun (60 tahun), sisa waktu
- **Tugas Tambahan (Additional Assignment)** — pegawai bisa punya jabatan tambahan lintas unit (maks. 1 aktif), terpisah dari jabatan utama. Lihat `AdditionalAssignment.php`.

### ✅ Phase 4 — Absensi

- Input manual via Dashboard (combobox Alpine.js searchable) — auto-kalkulasi Hadir/Terlambat/Tidak Hadir
- **Absensi mandiri via Portal Mobile** dengan validasi lokasi GPS (lihat Phase 11.1)
- Laporan absensi bulanan + export Excel

### ✅ Phase 5 — Cuti & Izin

- Generate saldo cuti otomatis per tahun — **hanya pegawai Aktif** (Probation tidak dapat saldo)
- Pengajuan cuti dengan validasi aturan bisnis terpusat via `LeaveService`
- Pengajuan cuti mandiri via Portal Mobile
- **Approval flow:** Pending → Disetujui/Ditolak. Permission `leave.approve` dipegang `admin_sdm` & `ketua`, tapi ada **rantai approver per-role pengaju** (hard block) — lihat bagian **Rantai Approval Cuti** di bawah.
- **Approval 2 tahap untuk guru & non-guru sekolah** — Kepala Sekolah (tahap 1) → Admin SDM (tahap 2). Lihat bagian **Approval Cuti 2 Tahap** di bawah.
- **Hari kerja: Senin–Sabtu** (bukan Senin-Jumat). Sumber kebenaran tunggal: `LeaveRequest::WORK_DAYS` + `LeaveRequest::isWorkDay()`. **Jangan** pakai `Carbon::isWeekday()/isWeekend()` bawaan untuk keputusan terkait cuti/absensi.
- **Haji & Umroh: tanggal selesai otomatis penuh** sesuai sisa saldo, field dikunci readonly. By nama spesifik (`LeaveService::AUTO_FULL_BALANCE_LEAVE_TYPES`).

### ✅ Phase 6 — Dashboard & Laporan

- Dashboard real-time: statistik pegawai, kehadiran, cuti, rekrutmen
- Grafik batang absensi 7 hari terakhir
- Alert pegawai mendekati pensiun, kontrak hampir habis, masa percobaan overdue
- Export Excel: Pegawai, Rekrutmen, Masa Percobaan, Saldo Cuti

### ✅ Phase 11 — Portal Mobile

- Route `/portal` — profil diri, absensi mandiri, riwayat absensi, saldo & ajukan cuti
- Navigasi dua arah: tombol "Buka Dashboard" di Portal (jika role punya `dashboard.view`), tombol "Buka Portal" di Dashboard (untuk role dual-access)
- Landing page (`/`) berupa 2 kartu pilihan: masuk Dashboard atau Portal

### ✅ Phase 11.1 — GPS Geofencing

- Validasi lokasi check-in/out via formula Haversine, terhadap 8 titik koordinat unit yayasan (`config/geofence.php`)
- Radius: **30 meter** (KEPUTUSAN FINAL setelah testing lapangan — diubah dari 100m). Default di `config/geofence.php`, bisa di-override via `GEOFENCE_RADIUS` di `.env`.
- Lintang/bujur, status valid, dan nama lokasi tersimpan terpisah untuk check-in dan check-out
- Server online: mihow.my.id (GPS aktif berjalan)

### ✅ Phase 11.2 — Kegiatan Luar Lokasi (Offsite) — Read-Only

- Pegawai yang absen di luar radius dapat mengajukan alasan kegiatan luar lokasi (6 pilihan alasan + catatan bebas)
- **Tidak ada workflow approve/reject** — absensi offsite otomatis sah, HR hanya lihat informasi di `/admin/offsite-approvals` (read-only, ada link Google Maps per baris).

### ✅ RBAC Granular per Fitur

- 8 role yayasan + 3 role sekolah (`guru`, `non_guru`, `kepala_sekolah`) = **11 role total**, 35 permission
- **Defense-in-depth 3 lapis:** Blade `@can` + middleware route + `abort_unless` — sejak 21 Juni 2026 ada di **16 dari 17** komponen Livewire Admin (lihat Known Issues)
- 4 role dual-access (`staf_sdm`, `sekretaris`, `bendahara`, `ketua`) + `admin_sdm` bisa akses Portal **dan** Dashboard sekaligus

### ✅ Fitur Nonaktifkan Akun (`is_active`)

- Kolom `is_active` di tabel `users`, toggle UI di Manajemen User
- Saat login: akun nonaktif ditolak dengan pesan jelas
- Saat sesi aktif: middleware `check.active` auto-logout langsung, tanpa tunggu sesi habis

### ✅ Polish & Bug Fix Terbaru

- Redirect setelah login: `User::isPortalRole()` / `User::PORTAL_ROLES` — satu sumber kebenaran, dipakai di `AuthenticatedSessionController` **dan** `bootstrap/app.php`
- `routes/web.php` membaca `User::PORTAL_ROLES` langsung via `implode('|', ...)` — tidak hardcode string role
- `Attendance::$fillable` dilengkapi semua kolom GPS & offsite (sebelumnya kosong, menyebabkan data tidak pernah tersimpan secara diam-diam)

---

## Struktur Direktori Penting

```
app/
├── Console/Commands/        # CheckProbationEndDate.php (scheduler harian)
├── Http/Controllers/
│   ├── Admin/                # Thin controllers (logic di Livewire)
│   ├── Portal/                # PortalController (thin, untuk Portal Mobile)
│   └── Public/                # CareerController
├── Http/Middleware/
│   ├── CheckPermission.php    # permission:xxx
│   └── CheckUserActive.php    # check.active — auto-logout jika is_active=false
├── Livewire/
│   ├── Admin/                 # 17 komponen — logic utama Dashboard
│   ├── Portal/                 # 4 komponen — logic Portal Mobile (GPS, cuti, profil)
│   └── Public/                 # ApplyForm
├── Models/                    # 15 Eloquent models
└── Services/
    ├── GeofenceService.php     # Haversine + validasi radius GPS
    ├── LeaveService.php        # Aturan bisnis cuti (terpusat)
    └── NipyGenerator.php       # Logic generate NIPY

database/
├── migrations/                # 26 file migration
└── seeders/                   # RolePermissionSeeder (11 role), UserSeeder, dst

resources/
├── css/app.css
├── js/app.js                  # Alpine.js (tanpa Alpine.start())
└── views/
    ├── components/layouts/    # admin.blade.php
    ├── layouts/portal.blade.php
    ├── livewire/admin/
    ├── livewire/portal/
    └── public/careers/

routes/
├── web.php                    # Semua route (Public, Portal, Admin)
└── console.php                # Scheduler
```

---

## Database (30 Tabel)

| Domain            | Tabel                                                                                                                                                                                                                      |
| ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Auth & Permission | `users` (+ `is_active`), `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` — Spatie                                                                                               |
| Laravel bawaan    | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`                                                                                                                          |
| Master Data       | `schools`, `departments`, `positions`, `skills`, `leave_types`                                                                                                                                                             |
| Rekrutmen         | `job_vacancies`, `applicants`, `applicant_educations`, `applicant_experiences`, `applicant_skills`                                                                                                                         |
| Kepegawaian       | `employees` (soft delete), `position_assignments` (+ `assignment_type`), `employee_status_histories`, `employee_skills`, `employee_school_histories`                                                                       |
| Absensi           | `attendances` (+ GPS: `checkin/checkout_latitude/longitude/location_valid/location_name`, + offsite: `is_offsite`, `offsite_reason`, `offsite_note`, `offsite_status`, `offsite_approved_by/at`, `offsite_rejection_note`) |
| Cuti              | `leave_balances`, `leave_requests` (soft delete, + `requires_school_approval`, `school_status`, `school_approved_by/at`, `school_rejection_note`)                                                                          |

---

## NIPY — Komposisi

Format: `YY` + `PP` + `KK` + `NNNN`

**Kode PP:** `01`=SD · `02`=SMP · `03`=SMA/SMK · `06`=D3 · `07`=S1 · `08`=S2 · `09`=S3

**Kode KK:** `11`=guru tetap · `12`=guru tidak tetap · `21`=non-guru tetap · `22`=non-guru tidak tetap

**Contoh:** `2607110001` = masuk 2026, S1, guru tetap, urut ke-1

---

## Aturan Bisnis Fatahillah

| Aturan                             | Nilai                                                                                    | Lokasi Konfigurasi                            |
| ---------------------------------- | ---------------------------------------------------------------------------------------- | --------------------------------------------- |
| Usia pensiun                       | 60 tahun                                                                                 | `Employee::RETIREMENT_AGE`                    |
| Jam masuk standar                  | 07:00 WIB                                                                                | `Attendance::WORK_START`                      |
| Jam selesai kerja                  | 15:00 WIB                                                                                | `Attendance::WORK_END`                        |
| Hari kerja                         | Senin–Sabtu                                                                              | `LeaveRequest::WORK_DAYS`                     |
| Radius valid lokasi absensi (GPS)  | **30 meter** (FINAL setelah testing lapangan — override via `GEOFENCE_RADIUS` di `.env`) | `config/geofence.php`                         |
| Minimal pengajuan cuti             | H-5                                                                                      | `LeaveService::MIN_DAYS_BEFORE`               |
| Cuti yang tidak boleh untuk guru   | Cuti Tahunan                                                                             | `LeaveService::EXCLUDED_FOR_GURU`             |
| Tanggal selesai Haji/Umroh         | Otomatis penuh sesuai sisa saldo, tidak bisa diubah manual                               | `LeaveService::AUTO_FULL_BALANCE_LEAVE_TYPES` |
| Masa percobaan non-guru            | 3 bulan                                                                                  | `NipyGenerator`                               |
| Masa percobaan guru                | 6 bulan                                                                                  | `NipyGenerator`                               |
| Pegawai probation dapat cuti       | ❌ Tidak                                                                                 | `LeaveService::validate()`                    |
| Maksimal tugas tambahan aktif      | 1 per pegawai                                                                            | `AdditionalAssignment.php`                    |
| Tugas tambahan lintas unit         | Wajib beda dari unit induk                                                               | `AdditionalAssignment::saveAdditional()`      |
| Cuti pegawai dengan tugas tambahan | Hanya berlaku di sekolah INDUK, tidak mempengaruhi absensi sekolah tugas tambahan        | `LeaveIndex::processLeave()`                  |
| Approver cuti harus sesuai rantai  | Hard block — lihat Rantai Approval Cuti                                                  | `LeaveService::LEAVE_APPROVER_CHAIN`          |
| Kegiatan luar lokasi (offsite)     | Otomatis sah, tanpa approval — HR hanya lihat informasi                                  | `OffsiteApproval.php` (read-only)             |
| Akun dinonaktifkan                 | Auto-logout langsung, tidak perlu tunggu sesi habis                                      | `CheckUserActive` middleware (`check.active`) |

---

## Services

### `LeaveService` — Konstanta Penting

```php
const MIN_DAYS_BEFORE   = 5;
const EXCLUDED_FOR_GURU = ['cuti tahunan'];
const AUTO_FULL_BALANCE_LEAVE_TYPES = ['haji', 'umroh'];
const ROLES_REQUIRE_SCHOOL_APPROVAL = ['guru', 'non_guru'];
const LEAVE_APPROVER_CHAIN = [
    'staf_yayasan'  => 'admin_sdm',
    'kepala_bidang' => 'admin_sdm',
    'staf_sdm'      => 'admin_sdm',
    'guru'          => 'admin_sdm',
    'non_guru'      => 'admin_sdm',
    'sekretaris'    => 'ketua',
    'bendahara'     => 'ketua',
    'admin_sdm'     => 'ketua',   // jika admin_sdm ajukan cuti sendiri
];

LeaveService::validate($employee, $leaveType, $start, $end, $balance) // array errors
LeaveService::isCorrectApprover($employee, $approverUser)              // bool
LeaveService::getCorrectApproverRole($employee)                        // ?string
LeaveService::calcMaxEndDate($startDate, $quota)                       // string date
LeaveService::requiresSchoolApproval($employee)                        // bool
```

### `NipyGenerator`

```php
NipyGenerator::generate($employee)                         // NIPY resmi
NipyGenerator::generateTemporaryNik()                      // TMP-YYYYMMDD-XXXX
NipyGenerator::calculateProbationEndDate($start, $isGuru)  // +3 atau +6 bulan
```

### `GeofenceService`

```php
GeofenceService::check($latitude, $longitude)   // ['valid' => bool, 'location_name' => string, 'distance' => float]
```

---

## Scheduler

```bash
# Crontab production
* * * * * php /opt/lampp/htdocs/hrisv1/artisan schedule:run >> /dev/null 2>&1

# Jalankan manual
php artisan hris:check-probation
```

---

## Roles (11 Role)

| Role             | Siapa                               |         Dashboard          |                                      Portal                                       | Data Pegawai |
| ---------------- | ----------------------------------- | :------------------------: | :-------------------------------------------------------------------------------: | :----------: |
| `super_admin`    | Staf IT                             |  Penuh (+ `user.manage`)   |                                        ✅                                         |    Penuh     |
| `admin_sdm`      | Kepala Bidang SDM                   |        Hampir penuh        |                                        ✅                                         |    Penuh     |
| `staf_sdm`       | Staf Bidang SDM                     |    Sebagian, read-only     |                                        ✅                                         |  Read-only   |
| `sekretaris`     | Sekretaris YPFC                     |          Sebagian          |                                        ✅                                         |  Read-only   |
| `bendahara`      | Bendahara YPFC                      |          Terbatas          |                                        ✅                                         |  Read-only   |
| `ketua`          | Ketua Yayasan                       | Terbatas + `leave.approve` |                                        ✅                                         |  Read-only   |
| `kepala_bidang`  | Kabid P2MP/Keuangan/Sarpras/Humas   |             ❌             |                        ✅ (profil, absensi, cuti sendiri)                         |      ❌      |
| `staf_yayasan`   | Staf umum yayasan                   |             ❌             |                        ✅ (profil, absensi, cuti sendiri)                         |      ❌      |
| `guru`           | Guru tetap/tidak tetap (sekolah)    |             ❌             |            ✅ (profil, absensi, cuti — **wajib approval Kepsek dulu**)            |      ❌      |
| `non_guru`       | Staf non-pengajar sekolah (TU, dst) |             ❌             |                              ✅ (sama seperti guru)                               |      ❌      |
| `kepala_sekolah` | Kepala Sekolah tiap unit            |             ❌             | ✅ (profil, absensi, cuti sendiri + **approve cuti guru/non-guru di sekolahnya**) |      ❌      |

Daftar role Portal ada di **satu tempat saja**: `App\Models\User::PORTAL_ROLES`. `routes/web.php` membaca langsung via `implode('|', User::PORTAL_ROLES)`.

### Approval Cuti 2 Tahap (Guru & Non-Guru Sekolah)

1. **Tahap 1 — Kepala Sekolah** — lewat `/portal/leave`, hanya bisa proses pengajuan dari `school_id` yang sama. Kalau ditolak: langsung final, tidak diteruskan SDM.
2. **Tahap 2 — Admin SDM** — lewat `/admin/leaves`, hanya muncul actionable setelah tahap 1 disetujui.

Implementasi: kolom tambahan di `leave_requests` (`requires_school_approval`, `school_status`, `school_approved_by`, `school_approved_at`, `school_rejection_note`). Kolom `status` utama tidak diubah artinya.

### Rantai Approval Cuti (Tahap Final, `leave.approve`)

Hard block — approver yang salah ditolak dengan pesan jelas.

| Role Pengaju                                                              | Approver yang Benar                                |
| ------------------------------------------------------------------------- | -------------------------------------------------- |
| `staf_yayasan`, `kepala_bidang`, `staf_sdm`, `guru`, `non_guru`           | `admin_sdm`                                        |
| `sekretaris`, `bendahara`, `admin_sdm` (mengajukan untuk dirinya sendiri) | `ketua`                                            |
| `kepala_sekolah`                                                          | Fallback — `admin_sdm` ATAU `ketua` boleh keduanya |

- `super_admin` selalu boleh approve siapa saja (override darurat).
- Sumber kebenaran: `LeaveService::LEAVE_APPROVER_CHAIN` + `LeaveService::isCorrectApprover()`.

---

## Routes Utama

### Publik

| URL                  | Keterangan               |
| -------------------- | ------------------------ |
| `/karir`             | Daftar lowongan aktif    |
| `/karir/{id}/daftar` | Form pendaftaran pelamar |

### Portal Mobile (auth + `check.active` + role portal)

| URL                  | Keterangan                      |
| -------------------- | ------------------------------- |
| `/portal`            | Home Portal                     |
| `/portal/attendance` | Absensi mandiri (GPS + offsite) |
| `/portal/leave`      | Saldo & pengajuan cuti          |
| `/portal/profile`    | Profil diri sendiri             |

### Admin (auth + `check.active`)

| URL                          | Permission          | Keterangan                |
| ---------------------------- | ------------------- | ------------------------- |
| `/dashboard`                 | `dashboard.view`    | Dashboard utama           |
| `/admin/schools`             | `master.view`       | CRUD Sekolah              |
| `/admin/departments`         | `master.view`       | CRUD Departemen           |
| `/admin/positions`           | `master.view`       | CRUD Jabatan              |
| `/admin/skills`              | `master.view`       | CRUD Skill                |
| `/admin/leave-types`         | `master.view`       | CRUD Jenis Cuti           |
| `/admin/jobs`                | `recruitment.view`  | Kelola Lowongan           |
| `/admin/applicants`          | `recruitment.view`  | Pipeline Pelamar          |
| `/admin/employees`           | `employee.view`     | Daftar Pegawai            |
| `/admin/employees/create`    | `employee.create`   | Tambah Manual             |
| `/admin/employees/import`    | `employee.create`   | Import Excel              |
| `/admin/employees/{id}`      | `employee.view`     | Detail + Tugas Tambahan   |
| `/admin/employees/{id}/edit` | `employee.edit`     | Edit Pegawai              |
| `/admin/attendance`          | `attendance.view`   | Absensi Harian            |
| `/admin/attendance/report`   | `attendance.report` | Laporan Absensi           |
| `/admin/offsite-approvals`   | `attendance.view`   | Kegiatan Luar (read-only) |
| `/admin/leaves`              | `leave.view`        | Pengajuan Cuti            |
| `/admin/leaves/balance`      | `leave.balance`     | Saldo Cuti                |
| `/admin/reports`             | `report.view`       | Hub Laporan SDM           |
| `/admin/users`               | `user.manage`       | Manajemen User            |

---

## Known Issues / Sedang Dipantau

- ⚠️ **Belum ada test Pest** untuk skenario `is_active`, approval 2 tahap, rantai approval per-role, atau constraint attendance.
- ⚠️ `kepala_sekolah` tidak punya approver spesifik di rantai approval — fallback ke siapa saja yang punya `leave.approve`. Keputusan sadar, bukan kelupaan.
- ⚠️ Enum `last_education` di tabel `applicants` tidak punya opsi `'smk'` (beda dengan `employees` yang punya) — belum diverifikasi handling saat konversi pelamar→pegawai.

---

## Troubleshooting

| Error                                           | Solusi                                                                              |
| ----------------------------------------------- | ----------------------------------------------------------------------------------- |
| Class not found setelah install                 | `composer dump-autoload`                                                            |
| View tidak update                               | `php artisan view:clear`                                                            |
| Route 404                                       | `php artisan route:clear && php artisan cache:clear`                                |
| Migration error table exists                    | `php artisan migrate:fresh --seed` ⚠️ data hilang                                   |
| Storage file tidak bisa diakses                 | `php artisan storage:link`                                                          |
| Alpine / Livewire tidak bekerja                 | `npm run build` + hard refresh `Ctrl+Shift+R`                                       |
| Alpine duplicate instances                      | Pastikan `app.js` tidak ada `Alpine.start()`                                        |
| Setelah edit `bootstrap/app.php` tidak ada efek | `php artisan optimize:clear`                                                        |
| Migration gagal error MySQL 1553                | Cek urutan operasi di migration — buat index BARU dulu sebelum DROP index lama      |
| Role baru bisa masuk Portal tapi salah landing  | Cek `User::PORTAL_ROLES` konsisten dengan middleware `role:...` di `routes/web.php` |
| GPS check-in selalu offsite padahal sudah dekat | Turunkan `GEOFENCE_RADIUS` di `.env` atau cek koordinat di `config/geofence.php`    |

---

## Perintah Development

```bash
php artisan serve                                    # Dev server
npm run dev                                          # Compile + watch assets
php artisan migrate:fresh --seed                     # Reset database + seeder
php artisan db:seed --class=RolePermissionSeeder     # Re-seed permission saja
php artisan optimize:clear                           # Clear semua cache
php artisan route:list                               # Lihat semua route
php artisan hris:check-probation                     # Manual cek masa percobaan
```

---

## Roadmap

| Phase    | Nama                                   | Status                    |
| -------- | -------------------------------------- | ------------------------- |
| 1        | Fondasi & Master Data                  | ✅ Selesai                |
| 2        | Rekrutmen                              | ✅ Selesai                |
| 3        | Manajemen Pegawai + Tugas Tambahan     | ✅ Selesai                |
| 4        | Absensi Harian (Manual)                | ✅ Selesai                |
| 5        | Cuti & Izin                            | ✅ Selesai                |
| 6        | Dashboard & Laporan                    | ✅ Selesai                |
| Polish   | Bug Fix & UX Improvements              | ✅ Selesai                |
| 11       | Portal Mobile                          | ✅ Selesai                |
| 11.1     | GPS Geofencing                         | ✅ Selesai — aktif online |
| 11.2     | Kegiatan Luar Lokasi (Offsite)         | ✅ Selesai (read-only)    |
| RBAC     | Permission Granular (11 role)          | ✅ Selesai                |
| —        | Fitur Nonaktifkan Akun (`is_active`)   | ✅ Selesai                |
| —        | Defense-in-depth (16/17 komponen)      | ✅ Selesai                |
| —        | Rantai Approval Cuti per-role          | ✅ Selesai                |
| 7        | Master Akademik & Jadwal (AMS)         | 🔲 Belum                  |
| 8        | RPP & Review Workflow (AMS)            | 🔲 Belum                  |
| 9        | Jurnal Mengajar (AMS)                  | 🔲 Belum                  |
| 10       | Absensi Siswa & Notifikasi (AMS)       | 🔲 Belum                  |
| 11 (AMS) | Portal Siswa                           | 🔲 Belum                  |
| 12       | Dashboard & Laporan Terintegrasi (AMS) | 🔲 Belum                  |

### Backlog (Belum Dijadwalkan)

- **Event Attendance** — daftar hadir kegiatan yayasan per sekolah + rekap
- Notifikasi in-app — approval cuti, approval offsite, masa percobaan, kontrak hampir habis
- Opsi konfigurasi radius geofencing per unit (saat ini seragam 30m untuk semua unit)
- Optimasi query N+1 di beberapa halaman dengan data besar
- Rate limiting untuk form pendaftaran publik
- Test Pest otomatis (is_active, approval 2 tahap, rantai approval, constraint attendance)
- Restore pegawai soft-deleted via UI admin (saat ini via tinker)

---

## Changelog

### 11 Juli 2026

- **Fix bug tampilan saldo cuti untuk guru di Portal Beranda** — card "Sisa Cuti Tahunan" di `PortalHome` sebelumnya hardcode mencari saldo `Cuti Tahunan` tanpa mempedulikan apakah pegawai berhak. Untuk guru (`is_guru = true`), card ini sekarang menampilkan saldo **Izin Tidak Masuk** sebagai gantinya. Label card dibuat dinamis. File: `PortalHome.php` + `portal-home.blade.php`.
- **Fix bug saldo cuti untuk guru di halaman Cuti Portal** — query `$balances` (daftar progress bar saldo) tidak difilter sama seperti dropdown jenis cuti, akibatnya Cuti Tahunan tetap muncul di progress bar meski guru tidak berhak. Diperbaiki dengan filter `LeaveService::isLeaveTypeAllowed()`. File: `PortalLeave.php`.

### 21 Juni 2026

- **Defense-in-depth diperluas ke 13 komponen Livewire Admin** — total 16 dari 17 komponen Admin kini punya `abort_unless` di `mount()` dan setiap method aksi. Lihat bagian RBAC.
- **Fix bug `AttendanceIndex::saveManual()`** — key pencarian `updateOrCreate()` tidak menyertakan `school_id`, berisiko salah update baris attendance milik sekolah lain untuk pegawai tugas tambahan.
- **Rantai approval cuti per-role** — hard block, lihat bagian Rantai Approval Cuti. `LeaveService::LEAVE_APPROVER_CHAIN` sebagai sumber kebenaran tunggal.

### 20 Juni 2026

- **Fix kritis `Attendance::$fillable`** — kolom GPS & offsite tidak pernah ada di `$fillable`, menyebabkan data GPS/offsite selalu tersimpan null/false secara diam-diam.
- **Offsite workflow dihapus** — absensi offsite otomatis sah, `OffsiteApproval.php` jadi read-only.
- **Fix bug cuti pending kedua** tidak ada pesan error — `LeaveService::validate()` kirim error ke key yang tidak ada di Portal, ditambah `portal-leave.blade.php` tidak punya blok flash message sama sekali.
- **Haji/Umroh** — tanggal selesai otomatis penuh, field readonly, dikunci di backend juga.
- **Label hari kerja** — "Senin–Jumat" diganti "Senin–Sabtu" di semua form.

### 19 Juni 2026 (lanjutan — jam & hari kerja)

- Jam kerja diubah 07:30–16:00 → **07:00–15:00 WIB**.
- Hari kerja Senin-Jumat → **Senin-Sabtu**, dikonsolidasi ke `LeaveRequest::WORK_DAYS`.
- Fix migration gagal MySQL 1553 (urutan DROP/CREATE index dibalik).
- Fix `school_id` di key `updateOrCreate()` di `LeaveIndex::processLeave()`.

### 19 Juni 2026 (lanjutan — role sekolah & approval 2 tahap)

- Role baru: `guru`, `non_guru`, `kepala_sekolah`.
- Approval cuti 2 tahap untuk `guru`/`non_guru`.
- Permission baru `leave.approve.school`.
- Fix tombol approve muncul untuk `sekretaris` yang tidak punya `leave.approve`.
- Riwayat approval Kepsek di Portal — section collapsible di `/portal/leave`.

### 19 Juni 2026

- Fix bug redirect setelah login — `User::PORTAL_ROLES` sebagai satu sumber kebenaran.
- Hapus `RedirectByRole.php` (dead code).
- `admin_sdm` ditambahkan ke Portal (dual-access) + permission `attendance.view.own`/`leave.view.own`.
- Fix email duplikat `UserSeeder.php`.

---

> Dibangun khusus untuk **Yayasan Fatahillah** · Laravel 12 + Livewire v4 · 2025–2026
