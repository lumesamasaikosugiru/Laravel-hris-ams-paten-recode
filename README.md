# HRIS Yayasan Fatahillah

> **Human Resource Information System** — Sistem informasi SDM berbasis web untuk Yayasan Fatahillah. Mengelola seluruh siklus kepegawaian: rekrutmen, masa percobaan, NIPY, absensi (manual + GPS Geofencing), cuti, dan laporan dalam satu platform terpadu yang mendukung struktur multi-sekolah, dengan dua titik akses: Dashboard (admin) dan Portal Mobile (self-service pegawai).

**Versi:** 1.4+ (pasca RBAC granular & fitur is_active) · **Status:** Production Ready — Portal & GPS Geofencing dalam tahap testing online

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

# Geofencing GPS (opsional, default ada di config/geofence.php)
GEOFENCE_RADIUS=100
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
- **Approval flow:** Pending → Disetujui/Ditolak. Permission `leave.approve` dipegang `admin_sdm` & `ketua`, TAPI sejak 21 Juni 2026 ada **rantai approver per-role pengaju** (hard block) — lihat bagian **Rantai Approval Cuti** di bawah.
- **Approval 2 tahap untuk guru & non-guru sekolah** — Kepala Sekolah (tahap 1) → Admin SDM/Ketua (tahap 2). Lihat bagian **Approval Cuti 2 Tahap** di bawah.
- **Hari kerja: Senin–Sabtu** (bukan Senin-Jumat). Sumber kebenaran tunggal: `LeaveRequest::WORK_DAYS` + `LeaveRequest::isWorkDay()` — dipanggil oleh `countWorkDays()`, `LeaveService::calcMaxEndDate()`, pembuatan baris attendance `'leave'` saat cuti disetujui, dan tampilan "Hari Libur" di Portal absensi. **Jangan** pakai `Carbon::isWeekday()/isWeekend()` bawaan untuk keputusan terkait cuti/absensi — hardcode Senin-Jumat, tidak ikut WORK_DAYS.
- **Haji & Umroh: tanggal selesai otomatis penuh** sesuai sisa saldo, field dikunci (readonly) — tidak bisa dipilih manual. By nama spesifik (`LeaveService::AUTO_FULL_BALANCE_LEAVE_TYPES`), bukan berdasarkan `cycle='once'`.

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
- Radius saat ini: **100 meter** (`GEOFENCE_RADIUS` di `.env`, default di config). ⚠️ Riwayat angka ini sempat berubah beberapa kali (200m → 100m) — pastikan nilai di `.env` lokal/server sesuai keputusan terbaru sebelum dianggap final.
- Lintang/bujur, status valid, dan nama lokasi tersimpan terpisah untuk check-in dan check-out
- Sedang tahap testing di server online khusus GPS (mihow.my.id) — **bukan untuk pemakaian operasional harian dulu**

### ✅ Phase 11.2 — Kegiatan Luar Lokasi (Offsite) — Read-Only

- Pegawai yang absen di luar radius dapat mengajukan alasan kegiatan luar lokasi (6 pilihan alasan + catatan bebas)
- **Diubah 20 Juni 2026:** TIDAK ADA LAGI workflow approve/reject. Absensi offsite otomatis sah (`offsite_status` selalu `approved`), HR hanya butuh visibilitas. Halaman `/admin/offsite-approvals` sekarang read-only — daftar informasi (siapa, kapan, alasan, lokasi via link Maps), tanpa tombol aksi.

### ✅ RBAC Granular per Fitur

- 8 role yayasan + 3 role sekolah (`guru`, `non_guru`, `kepala_sekolah`) = 11 total, 35 permission, defense-in-depth (Blade `@can` + middleware route + `abort_unless` — sejak 21 Juni 2026 ada di **16 dari 17** komponen Livewire Admin, lihat Known Issues untuk sisanya)
- 4 role dual-access (`staf_sdm`, `sekretaris`, `bendahara`, `ketua`) + **`admin_sdm`** bisa akses Portal **dan** Dashboard sekaligus

### ✅ Fitur Nonaktifkan Akun (`is_active`)

- Kolom `is_active` di tabel `users`, toggle UI tersedia di Manajemen User (`UserManagement.php`)
- Saat login: akun nonaktif ditolak dengan pesan jelas (`LoginRequest.php`)
- Saat sesi sedang aktif: middleware `check.active` memaksa logout otomatis begitu akun dinonaktifkan, tidak perlu menunggu sesi browser berakhir sendiri

### ✅ Polish & Bug Fix Terbaru

- Redirect setelah login disatukan ke `User::isPortalRole()` / `User::PORTAL_ROLES` — sumber kebenaran tunggal, dipakai di `AuthenticatedSessionController` **dan** `bootstrap/app.php` (sebelumnya dua tempat ini sempat tidak sinkron, sudah diperbaiki)
- Middleware `RedirectByRole.php` (dead code, tidak pernah terdaftar) sudah dihapus
- Permission `admin_sdm` dilengkapi `attendance.view.own` + `leave.view.own` agar sejajar dengan role dual-access lain

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
└── seeders/                   # RolePermissionSeeder (8 role), UserSeeder, dst

resources/
├── css/app.css                # Design tokens + utility classes
├── js/app.js                  # Alpine.js (tanpa Alpine.start())
└── views/
    ├── components/layouts/    # admin.blade.php, public.blade.php
    ├── layouts/portal.blade.php # Layout khusus Portal Mobile
    ├── livewire/admin/         # Blade views per component Dashboard
    ├── livewire/portal/         # Blade views per component Portal
    ├── pages/                   # dashboard.blade.php
    └── public/careers/          # Halaman publik lowongan

routes/
├── web.php                    # Semua route (Public, Portal, Admin)
└── console.php                # Scheduler
```

---

## Database (30 Tabel)

| Domain            | Tabel                                                                                                                                                                                                                                  |
| ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Auth & Permission | `users` (+ `is_active`), `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` — managed by Spatie                                                                                                |
| Laravel bawaan    | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`                                                                                                                                      |
| Master Data       | `schools`, `departments`, `positions`, `skills`, `leave_types`                                                                                                                                                                         |
| Rekrutmen         | `job_vacancies`, `applicants`, `applicant_educations`, `applicant_experiences`, `applicant_skills`                                                                                                                                     |
| Kepegawaian       | `employees` (soft delete), `position_assignments` (+ `assignment_type`: primary/additional), `employee_status_histories`, `employee_skills`, `employee_school_histories`                                                               |
| Absensi           | `attendances` (+ kolom GPS: `checkin/checkout_latitude/longitude/location_valid/location_name`, + kolom offsite: `is_offsite`, `offsite_reason`, `offsite_note`, `offsite_status`, `offsite_approved_by/at`, `offsite_rejection_note`) |
| Cuti              | `leave_balances`, `leave_requests` (soft delete)                                                                                                                                                                                       |

---

## NIPY — Komposisi

Format: `YY` + `PP` + `KK` + `NNNN`

| Segmen | Arti                   | Contoh            |
| ------ | ---------------------- | ----------------- |
| YY     | 2 digit tahun masuk    | `26` = 2026       |
| PP     | Kode pendidikan        | `07` = S1         |
| KK     | Kode jenis kepegawaian | `11` = guru tetap |
| NNNN   | Nomor urut 4 digit     | `0001`            |

**Kode PP:** `01`=SD · `02`=SMP · `03`=SMA/SMK · `06`=D3 · `07`=S1 · `08`=S2 · `09`=S3

**Kode KK:** `11`=guru tetap · `12`=guru tidak tetap · `21`=non-guru tetap · `22`=non-guru tidak tetap

**Contoh:** `2607110001` = masuk 2026, S1, guru tetap, urut ke-1

---

## Aturan Bisnis Fatahillah

| Aturan                                        | Nilai                                                                                             | Lokasi Konfigurasi                            |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------- | --------------------------------------------- |
| Usia pensiun                                  | 60 tahun                                                                                          | `Employee::RETIREMENT_AGE`                    |
| Jam masuk standar                             | 07:00 WIB                                                                                         | `Attendance::WORK_START`                      |
| Jam selesai kerja                             | 15:00 WIB                                                                                         | `Attendance::WORK_END`                        |
| Hari kerja                                    | Senin–Sabtu                                                                                       | `LeaveRequest::WORK_DAYS`                     |
| Radius valid lokasi absensi (GPS)             | 100 meter (⚠️ cek `.env` lokal, lihat catatan Phase 11.1)                                         | `config/geofence.php`                         |
| Minimal pengajuan cuti                        | H-5                                                                                               | `LeaveService::MIN_DAYS_BEFORE`               |
| Cuti yang tidak boleh untuk guru              | Cuti Tahunan                                                                                      | `LeaveService::EXCLUDED_FOR_GURU`             |
| Tanggal selesai Haji/Umroh                    | Otomatis penuh sesuai sisa saldo, tidak bisa diubah manual                                        | `LeaveService::AUTO_FULL_BALANCE_LEAVE_TYPES` |
| Masa percobaan non-guru                       | 3 bulan                                                                                           | `NipyGenerator`                               |
| Masa percobaan guru                           | 6 bulan                                                                                           | `NipyGenerator`                               |
| Pegawai probation dapat cuti                  | ❌ Tidak                                                                                          | `LeaveService::validate()`                    |
| Maksimal tugas tambahan aktif                 | 1 per pegawai                                                                                     | `AdditionalAssignment.php`                    |
| Tugas tambahan lintas unit                    | Wajib beda dari unit induk                                                                        | `AdditionalAssignment::saveAdditional()`      |
| Cuti pegawai dengan tugas tambahan            | Hanya berlaku di sekolah INDUK, tidak mempengaruhi absensi di sekolah tugas tambahan              | `LeaveIndex::processLeave()`                  |
| Approver cuti tahap final harus sesuai rantai | Hard block — `admin_sdm`/`ketua` tidak bisa approve sembarang pengaju, lihat Rantai Approval Cuti | `LeaveService::LEAVE_APPROVER_CHAIN`          |
| Kegiatan luar lokasi (offsite)                | Otomatis sah, tanpa approval — HR hanya lihat informasi                                           | `OffsiteApproval.php` (read-only)             |
| Akun dinonaktifkan                            | Auto-logout langsung, tidak perlu tunggu sesi habis                                               | `CheckUserActive` middleware (`check.active`) |

---

## Services

### `NipyGenerator`

```php
NipyGenerator::generate($employee)                         // Generate NIPY resmi
NipyGenerator::generateTemporaryNik()                      // NIK sementara TMP-YYYYMMDD-XXXX
NipyGenerator::calculateProbationEndDate($start, $isGuru)  // +3 atau +6 bulan
NipyGenerator::getEducationCode($education)                // Kode PP
NipyGenerator::getEmploymentCode($isGuru, $type)           // Kode KK
```

### `LeaveService`

```php
// Konstanta
const MIN_DAYS_BEFORE   = 5;                   // Minimal H-5
const EXCLUDED_FOR_GURU = ['cuti tahunan'];    // Tidak boleh untuk guru

// Method
LeaveService::validate($employee, $leaveType, $start, $end, $balance) // array errors
LeaveService::isLeaveTypeAllowed($leaveType, $employee)                // bool
LeaveService::calcMaxEndDate($startDate, $quota)                       // string date
LeaveService::minStartDate()                                           // string date
LeaveService::rules()                                                  // array config UI
```

### `GeofenceService`

```php
GeofenceService::check($latitude, $longitude)   // ['valid' => bool, 'location_name' => string, 'distance' => float]
GeofenceService::haversine($lat1, $lon1, $lat2, $lon2) // jarak dalam meter
```

---

## Scheduler

```php
// routes/console.php
Schedule::command('hris:check-probation')->dailyAt('07:00');
```

```bash
# Crontab production
* * * * * php /opt/lampp/htdocs/hrisv1/artisan schedule:run >> /dev/null 2>&1

# Jalankan manual
php artisan hris:check-probation
```

---

## Roles (11 Role — Struktur Kepengurusan Yayasan + Lingkungan Sekolah)

> ⚠️ Role di bawah ini **menggantikan total** role generik versi lama (`admin_hr`, `kepala_sekolah` generik lama, `pegawai`, `guru` generik lama, dst yang sempat tercatat di README versi sebelumnya — sudah tidak berlaku dengan definisi yang sama).

| Role             | Siapa                               |          Dashboard           |                                      Portal                                       | Data Pegawai |
| ---------------- | ----------------------------------- | :--------------------------: | :-------------------------------------------------------------------------------: | :----------: |
| `super_admin`    | Staf IT                             |   Penuh (+ `user.manage`)    |                                        ✅                                         |    Penuh     |
| `admin_sdm`      | Kepala Bidang SDM                   |         Hampir penuh         |                                        ✅                                         |    Penuh     |
| `staf_sdm`       | Staf Bidang SDM                     | Sebagian, employee read-only |                                        ✅                                         |  Read-only   |
| `sekretaris`     | Sekretaris YPFC                     |           Sebagian           |                                        ✅                                         |  Read-only   |
| `bendahara`      | Bendahara YPFC                      |           Terbatas           |                                        ✅                                         |  Read-only   |
| `ketua`          | Ketua Yayasan                       |  Terbatas + `leave.approve`  |                                        ✅                                         |  Read-only   |
| `kepala_bidang`  | Kabid P2MP/Keuangan/Sarpras/Humas   |         ❌ Tidak ada         |                        ✅ (profil, absensi, cuti sendiri)                         |      ❌      |
| `staf_yayasan`   | Staf umum yayasan                   |         ❌ Tidak ada         |                        ✅ (profil, absensi, cuti sendiri)                         |      ❌      |
| `guru`           | Guru tetap/tidak tetap (sekolah)    |         ❌ Tidak ada         |            ✅ (profil, absensi, cuti — **wajib approval Kepsek dulu**)            |      ❌      |
| `non_guru`       | Staf non-pengajar sekolah (TU, dst) |         ❌ Tidak ada         |                              ✅ (sama seperti guru)                               |      ❌      |
| `kepala_sekolah` | Kepala Sekolah tiap unit **(baru)** |         ❌ Tidak ada         | ✅ (profil, absensi, cuti sendiri + **approve cuti guru/non-guru di sekolahnya**) |      ❌      |

Daftar role mana yang dianggap "tujuan utama Portal setelah login" ada di **satu tempat saja**: `App\Models\User::PORTAL_ROLES`. `routes/web.php` juga membaca konstanta ini langsung (`implode('|', User::PORTAL_ROLES)`) — jangan hardcode daftar role lagi di route manapun.

### Approval Cuti 2 Tahap (Guru & Non-Guru Sekolah)

Sejak penambahan role `guru`, `non_guru`, dan `kepala_sekolah`, pengajuan cuti dari kedua role pertama **wajib** lewat 2 tahap:

1. **Tahap 1 — Kepala Sekolah.** Disetujui/ditolak lewat halaman `/portal/leave` (section khusus yang hanya tampil untuk role `kepala_sekolah`). Kepsek **hanya** bisa memproses pengajuan dari pegawai di sekolahnya sendiri (`employee.school_id` sama) — scoping dikerjakan lewat logic, bukan lewat role terpisah per sekolah (lihat `PortalLeave::getKepalaSekolahSchoolId()`).
2. **Tahap 2 — Admin SDM / Ketua.** Sama seperti pengajuan role lain, lewat `/admin/leaves`. **Tidak akan muncul sebagai actionable** sebelum tahap 1 disetujui (lihat `LeaveRequest::ready_for_sdm` dan guard di `LeaveIndex::processLeave()`).

Kalau Kepsek menolak di tahap 1, pengajuan langsung **final ditolak** — tidak diteruskan ke SDM untuk diproses ulang.

Halaman `/portal/leave` untuk role `kepala_sekolah` menampilkan 3 section: **Menunggu Persetujuan Anda** (pengajuan guru/non-guru di sekolahnya yang masih `school_status=pending`), **Riwayat Diproses** (collapsible — semua yang sudah dia/Kepsek sebelumnya approve/tolak di sekolah itu, beserta status lanjutan di SDM/Ketua), dan **Riwayat Pengajuan** pribadi (cuti milik Kepsek sendiri — section yang sama dipakai semua role, otomatis berlaku karena Kepsek juga punya record `Employee`).

Akun Kepala Sekolah **wajib** terhubung ke `Employee` dengan posisi "Kepala Sekolah" di sekolah yang sesuai (`employees.user_id` → `users.id`, lalu `employees.school_id` menentukan sekolah mana yang ia boleh approve). Role ini **satu role generik** dipakai semua Kepsek di semua unit — bukan role terpisah per sekolah (`kepsek_smk1`, dst).

Implementasi: kolom baru di `leave_requests` (`requires_school_approval`, `school_status`, `school_approved_by`, `school_approved_at`, `school_rejection_note`) — kolom `status` utama **tidak diubah artinya**, tetap berarti "keputusan akhir" untuk semua role seperti sebelumnya.

### Rantai Approval Cuti (Tahap Final, `leave.approve`)

`admin_sdm` dan `ketua` sama-sama punya permission `leave.approve`, tapi sejak 21 Juni 2026 ada validasi **siapa approver yang benar** untuk role pengaju tertentu — **hard block**, bukan sekadar peringatan. Approver yang salah akan ditolak dengan pesan jelas, bukan diam-diam berhasil.

| Role Pengaju                                                              | Approver yang Benar                                              |
| ------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| `staf_yayasan`, `kepala_bidang`, `staf_sdm`, `guru`, `non_guru`           | `admin_sdm`                                                      |
| `sekretaris`, `bendahara`, `admin_sdm` (mengajukan untuk dirinya sendiri) | `ketua`                                                          |
| `kepala_sekolah`                                                          | Tidak diatur — fallback, `admin_sdm` ATAU `ketua` boleh keduanya |

- `super_admin` **selalu** boleh approve siapa saja, di luar rantai (override darurat).
- Untuk `guru`/`non_guru`: rantai ini berlaku di **tahap 2** (setelah lolos approval Kepala Sekolah di tahap 1) — approver tahap 2 mereka sama dengan `staf_yayasan`/`kepala_bidang` (`admin_sdm`), bukan approver terpisah.
- Sumber kebenaran tunggal: `LeaveService::LEAVE_APPROVER_CHAIN` + `LeaveService::isCorrectApprover()`. Kalau role pengaju tidak terdaftar di rantai (seperti `kepala_sekolah`), validasi fallback ke "boleh diproses siapa saja yang punya `leave.approve`" — bukan blokir total.

---

## Routes Utama

### Publik

| URL                  | Keterangan                          |
| -------------------- | ----------------------------------- |
| `/karir`             | Daftar lowongan aktif               |
| `/karir/{id}`        | Detail lowongan                     |
| `/karir/{id}/daftar` | Form pendaftaran pelamar (CV wajib) |

### Portal Mobile (auth + `check.active` + role dual-access/portal-only)

| URL                  | Keterangan                      |
| -------------------- | ------------------------------- |
| `/portal`            | Home Portal                     |
| `/portal/attendance` | Absensi mandiri (GPS + offsite) |
| `/portal/leave`      | Saldo & pengajuan cuti          |
| `/portal/profile`    | Profil diri sendiri             |

### Admin (auth + `check.active`)

| URL                          | Keterangan                                        |
| ---------------------------- | ------------------------------------------------- |
| `/dashboard`                 | Dashboard + alert pensiun & kontrak               |
| `/admin/schools`             | CRUD Sekolah                                      |
| `/admin/departments`         | CRUD Departemen                                   |
| `/admin/positions`           | CRUD Jabatan                                      |
| `/admin/skills`              | CRUD Skill                                        |
| `/admin/leave-types`         | CRUD Jenis Cuti                                   |
| `/admin/jobs`                | Kelola Lowongan                                   |
| `/admin/applicants`          | Pipeline Pelamar                                  |
| `/admin/employees`           | Daftar Pegawai (soft delete)                      |
| `/admin/employees/create`    | Tambah Pegawai Manual                             |
| `/admin/employees/import`    | Import dari Excel                                 |
| `/admin/employees/template`  | Download Template Excel                           |
| `/admin/employees/{id}`      | Detail + Info Pensiun + Tugas Tambahan            |
| `/admin/employees/{id}/edit` | Edit Pegawai                                      |
| `/admin/attendance`          | Absensi Harian (input manual)                     |
| `/admin/attendance/report`   | Laporan Absensi                                   |
| `/admin/attendance/export`   | Export Excel Absensi                              |
| `/admin/offsite-approvals`   | Approval kegiatan luar lokasi                     |
| `/admin/leaves`              | Pengajuan Cuti                                    |
| `/admin/leaves/balance`      | Saldo Cuti                                        |
| `/admin/reports`             | Hub Laporan SDM                                   |
| `/admin/reports/employees`   | Export Laporan Pegawai                            |
| `/admin/reports/recruitment` | Export Laporan Rekrutmen                          |
| `/admin/reports/probation`   | Export Laporan Masa Percobaan                     |
| `/admin/reports/leaves`      | Export Laporan Cuti                               |
| `/admin/users`               | Manajemen User (link akun, toggle aktif/nonaktif) |

---

## Known Issues / Sedang Dipantau

Daftar lebih detail ada di Bab 12 Dokumen Master. Ringkasan yang paling relevan untuk kerja harian:

- ⚠️ Dua lokasi di `config/geofence.php` (`SMK YP. Fatahillah 1 Cilegon Kampus 1` dan `SMK YP. Fatahillah 2 Cilegon`) punya koordinat identik — **dikonfirmasi disengaja** (satu gedung, dua unit administratif).
- ⚠️ Belum ada test Pest untuk skenario `is_active`, approval 2 tahap, rantai approval per-role, atau constraint attendance.
- ⚠️ `kepala_sekolah` tidak punya approver spesifik di rantai approval cuti (fallback: `admin_sdm`/`ketua` mana pun boleh) — keputusan sadar, bukan kelupaan.
- ⚠️ Enum `last_education` di tabel `applicants` belum punya opsi `'smk'` (beda dengan `employees` yang punya) — belum diverifikasi bagaimana ini ditangani saat konversi pelamar→pegawai.

---

## Troubleshooting

| Error                                                        | Solusi                                                                         |
| ------------------------------------------------------------ | ------------------------------------------------------------------------------ |
| Class not found setelah install                              | `composer dump-autoload`                                                       |
| View tidak update                                            | `php artisan view:clear`                                                       |
| Route 404                                                    | `php artisan route:clear && php artisan cache:clear`                           |
| Migration error table exists                                 | `php artisan migrate:fresh --seed` ⚠️ data hilang                              |
| Storage file tidak bisa diakses                              | `php artisan storage:link`                                                     |
| Alpine / Livewire tidak bekerja                              | `npm run build` + hard refresh `Ctrl+Shift+R`                                  |
| PhpSpreadsheet not found                                     | `composer require phpoffice/phpspreadsheet`                                    |
| Alpine duplicate instances                                   | Pastikan `app.js` tidak ada `Alpine.start()`                                   |
| Modal tidak bisa ditutup ESC                                 | Cek script ESC di `layouts/admin.blade.php`                                    |
| Table not found (model)                                      | Tambahkan `protected $table = 'nama_tabel'` di model                           |
| Setelah edit `bootstrap/app.php` tidak ada efek              | `php artisan optimize:clear`                                                   |
| Role baru bisa masuk Portal tapi salah landing setelah login | Cek `User::PORTAL_ROLES` SAMA dengan middleware `role:...` di `routes/web.php` |

---

## Perintah Development

```bash
php artisan serve                              # Dev server
npm run dev                                    # Compile + watch assets
php artisan migrate:fresh --seed               # Reset database + seeder
php artisan db:seed --class=RolePermissionSeeder # Re-seed permission saja (tanpa fresh)
php artisan view:clear                         # Clear blade cache
php artisan cache:clear                        # Clear app cache
php artisan config:clear                       # Clear config cache
php artisan optimize:clear                     # Clear semua cache (route/config/view/event)
php artisan route:list                         # Lihat semua route
php artisan hris:check-probation               # Manual cek masa percobaan
```

---

## Roadmap

| Phase    | Nama                                      | Status                          |
| -------- | ----------------------------------------- | ------------------------------- |
| 1        | Fondasi & Master Data                     | ✅ Selesai                      |
| 2        | Rekrutmen                                 | ✅ Selesai                      |
| 3        | Manajemen Pegawai + Tugas Tambahan        | ✅ Selesai                      |
| 4        | Absensi Harian (Manual)                   | ✅ Selesai                      |
| 5        | Cuti & Izin                               | ✅ Selesai                      |
| 6        | Dashboard & Laporan                       | ✅ Selesai                      |
| Polish   | Bug Fix & UX Improvements                 | ✅ Selesai                      |
| 11       | Portal Mobile                             | ✅ Selesai                      |
| 11.1     | GPS Geofencing                            | ✅ Selesai — **testing online** |
| 11.2     | Offsite Approval                          | ✅ Selesai                      |
| RBAC     | Permission Granular (8 role, dual-access) | ✅ Selesai                      |
| —        | Fitur Nonaktifkan Akun (`is_active`)      | ✅ Selesai                      |
| 7        | Master Akademik & Jadwal (AMS)            | 🔲 Belum                        |
| 8        | RPP & Review Workflow (AMS)               | 🔲 Belum                        |
| 9        | Jurnal Mengajar (AMS)                     | 🔲 Belum                        |
| 10       | Absensi Siswa & Notifikasi (AMS)          | 🔲 Belum                        |
| 11 (AMS) | Portal Siswa                              | 🔲 Belum                        |
| 12       | Dashboard & Laporan Terintegrasi (AMS)    | 🔲 Belum                        |

### Backlog (Belum Dijadwalkan)

- **Event Attendance** — daftar hadir kegiatan yayasan per sekolah + rekap
- Notifikasi in-app — approval cuti, approval offsite, masa percobaan, kontrak hampir habis
- Rantai approval cuti per-role sesuai jabatan pengaju (lihat Known Issues)
- Defense-in-depth `abort_unless` diperluas ke 14 komponen Livewire yang belum punya (lihat Known Issues)
- Opsi konfigurasi radius geofencing per unit (saat ini seragam untuk semua unit)
- Optimasi query N+1 di beberapa halaman dengan data besar
- Rate limiting untuk form pendaftaran publik

---

## Changelog

### 11 Juli 2026

- **Fix bug tampilan saldo cuti untuk guru di Portal Beranda** — card "Sisa Cuti Tahunan" di `PortalHome` sebelumnya hardcode mencari saldo `Cuti Tahunan` tanpa mempedulikan apakah pegawai berhak. Untuk guru (`is_guru = true`), card ini sekarang menampilkan saldo **Izin Tidak Masuk** sebagai gantinya. Label card juga dibuat dinamis (`$leaveBalance?->leaveType->name`) sehingga otomatis menyesuaikan jenis cuti yang ditampilkan. File: `PortalHome.php` + `portal-home.blade.php`.
- **Fix bug saldo cuti untuk guru di halaman Cuti Portal** — `PortalLeave.php` sudah memfilter dropdown jenis cuti via `LeaveService::isLeaveTypeAllowed()`, tapi query `$balances` (daftar progress bar saldo) tidak difilter dengan cara yang sama. Akibatnya Cuti Tahunan tetap muncul di daftar saldo meski guru tidak berhak. Diperbaiki dengan menambahkan `->filter(fn($bal) => LeaveService::isLeaveTypeAllowed($bal->leaveType, $employee))->values()` ke query balances. File: `PortalLeave.php`.

### 21 Juni 2026

- **Defense-in-depth (`abort_unless`) diperluas ke 13 komponen Livewire Admin** yang sebelumnya hanya dilindungi Blade `@can` + middleware route: `SchoolIndex`, `DepartmentIndex`, `PositionIndex`, `SkillIndex`, `LeaveTypeIndex` (`master.view/create/edit/delete`), `UserManagement` (`user.manage`, di SETIAP method — paling ketat), `AttendanceIndex` (`attendance.view/create/edit`), `AttendanceReport` (`attendance.report`/`export`), `LeaveBalance` (`leave.balance`), `ApplicantIndex` (`recruitment.view/edit/convert`), `JobIndex` (`recruitment.view/create/edit/delete`), `EmployeeIndex` (`employee.view`), `AdditionalAssignment` (`employee.view/edit`). Total sekarang 16 dari 17 komponen Admin punya lapis 3 (`OffsiteApproval` sengaja tidak, karena sudah read-only tanpa aksi apa pun).
- **Fix bug serupa di `AttendanceIndex::saveManual()`** — key pencarian `updateOrCreate()` tidak menyertakan `school_id`, sama seperti bug yang diperbaiki di `LeaveIndex::processLeave()` (19 Juni). Diperbaiki dengan pola yang sama: key selalu sertakan `school_id` (sekolah induk pegawai), form input manual SDM sengaja tidak ditambah pilihan sekolah.
- **Rantai approval cuti per-role pengaju** (hard block) — lihat bagian **Rantai Approval Cuti** di atas. `LeaveService::LEAVE_APPROVER_CHAIN` + `isCorrectApprover()`/`getCorrectApproverRole()` sebagai sumber kebenaran tunggal. `LeaveIndex::processLeave()` menolak approver yang salah dengan pesan jelas, bukan diam-diam berhasil.

### 20 Juni 2026

**Bug ditemukan lewat testing manual (guru dengan tugas tambahan):**

- **Fix kritis:** `Attendance::$fillable` TIDAK PUNYA kolom GPS & offsite sama sekali (`checkin_latitude`, `is_offsite`, `offsite_status`, dst) sejak fitur-fitur itu pertama dibuat. Eloquent menolak mass-assignment ke kolom luar `$fillable` secara DIAM-DIAM (tidak error) — akibatnya data GPS/offsite SELALU tersimpan null/false walau UI Portal bilang "berhasil". Ini sebabnya kegiatan luar lokasi tidak pernah muncul di Dashboard admin. Semua kolom relevan sudah ditambahkan ke `$fillable` + `$casts`.
- **Perubahan kebijakan:** Workflow approve/reject kegiatan luar lokasi (offsite) **dihapus sepenuhnya**. Absensi offsite sekarang otomatis sah (`offsite_status` selalu `'approved'`), HR hanya butuh visibilitas bukan keputusan. `OffsiteApproval.php` & `offsite-approval.blade.php` ditulis ulang jadi read-only (tanpa tombol aksi, tanpa filter status, tanpa modal tolak).
- **Fix bug relasi:** `Attendance` model tidak punya relasi `offsiteApprovedBy()` sama sekali, padahal `OffsiteApproval.php` memanggil nama relasi yang salah (`approvedBy`, harusnya beda). Sudah diperbaiki (meski sekarang tidak lagi krusial karena workflow approval dihapus).
- **Fix bug:** Ajukan cuti kedua saat masih ada yang pending — tombol "Kirim" berubah "Mengirim" lalu balik normal TANPA pesan apa pun, terlihat seperti tidak terjadi apa-apa. Akar masalah ganda: (1) `LeaveService::validate()` mengirim error dengan key `selectedEmployeeId` (field yang hanya ada di Dashboard/`LeaveIndex`), sehingga di Portal error itu "ada" tapi tidak pernah ter-render; (2) `portal-leave.blade.php` TIDAK PUNYA blok flash message sama sekali sejak awal dibuat. Key error general sekarang dipetakan ke `general` lalu ditampilkan sebagai flash message di kedua komponen (Dashboard tetap dipetakan ke `selectedEmployeeId` karena field itu memang ada di sana).
- **Fitur baru:** Tanggal selesai cuti Haji & Umroh sekarang otomatis terisi penuh sesuai sisa saldo, field dikunci (readonly) — tidak bisa dipilih manual. Dikunci di backend juga (bukan cuma HTML `readonly`) supaya tidak bisa dimanipulasi lewat date-picker native browser. By nama spesifik (`LeaveService::AUTO_FULL_BALANCE_LEAVE_TYPES`), TIDAK berdasarkan `cycle='once'` agar jenis cuti sekali-pakai lain di masa depan tidak otomatis ikut aturan ini.
- **Fix teks:** Label "33 hari kerja (Senin–Jumat)" di form pengajuan cuti (Dashboard & Portal) diperbaiki jadi "Senin–Sabtu" — sebelumnya cuma teks statis yang lupa diupdate saat hari kerja diubah.

### 19 Juni 2026 (lanjutan #3 — jam & hari kerja, fix migration)

- **Jam kerja diubah:** 07:30–16:00 → **07:00–15:00 WIB**. Satu tempat: `Attendance::WORK_START`/`WORK_END`, sudah otomatis konsisten di Dashboard & Portal (Portal memanggil konstanta yang sama, tidak ada nilai terpisah).
- **Hari kerja diubah:** Senin-Jumat → **Senin-Sabtu**. Sebelumnya logic ini DITULIS ULANG MANUAL di 4 tempat terpisah (`LeaveRequest::countWorkDays()`, `LeaveService::calcMaxEndDate()`, `LeaveIndex::processLeave()`, `PortalAttendance` tampilan "hari libur") pakai `Carbon::isWeekday()/isWeekend()` bawaan yang hardcode Senin-Jumat. Dikonsolidasi jadi SATU sumber kebenaran: `LeaveRequest::WORK_DAYS` + `LeaveRequest::isWorkDay()` — 3 tempat lain sekarang memanggil method itu.
- **Fix migration gagal (error MySQL 1553):** Migration fix unique constraint attendance (lihat 19 Juni #2) sempat gagal dengan "Cannot drop index ... needed in a foreign key constraint" — MySQL InnoDB mewajibkan foreign key `employee_id` selalu punya index pendukung. Diperbaiki dengan membalik urutan: buat unique BARU dulu, baru drop yang LAMA (sebelumnya kebalik).
- **Fix potensi konflik unique constraint baru:** `LeaveIndex::processLeave()` (pembuatan baris attendance `'leave'` saat cuti disetujui) sebelumnya tidak menyertakan `school_id` di key pencarian `updateOrCreate()` — berisiko salah update baris attendance milik sekolah lain untuk pegawai dengan tugas tambahan. Diperbaiki: key pencarian sekarang selalu sertakan `school_id` (sekolah INDUK), sesuai keputusan bahwa cuti hanya berlaku di unit induk, tidak mempengaruhi sekolah tugas tambahan.

### 19 Juni 2026 (lanjutan #2 — fix permission approve & riwayat Kepsek)

- **Fix bug:** tombol "Setujui/Tolak" di `/admin/leaves` muncul untuk role yang tidak punya permission `leave.approve` (mis. `sekretaris`) — Blade tidak pernah dibungkus `@can('leave.approve')`, hanya mengandalkan status pengajuan. Ditambahkan `@can('leave.approve')` di view, plus `abort_unless(...->can('leave.approve'))` di `LeaveIndex::openApproveModal()` dan `processLeave()` (defense-in-depth, sebelumnya komponen ini tidak punya lapis 3 sama sekali).
- **Section baru di Portal untuk `kepala_sekolah`:** "Riwayat Diproses" (collapsible) di `/portal/leave` — menampilkan semua pengajuan guru/non-guru di sekolahnya yang sudah pernah ia approve/tolak, lengkap dengan status lanjutan di SDM/Ketua. Ditampilkan terlepas dari siapa yang sedang login (riwayat sekolah, bukan riwayat per-akun Kepsek), supaya tetap utuh kalau ada pergantian Kepala Sekolah.
- Riwayat cuti pribadi Kepsek (cuti miliknya sendiri) sudah otomatis tampil tanpa perubahan tambahan, karena memakai section "Riwayat Pengajuan" yang sama dengan semua role lain.

### 19 Juni 2026 (lanjutan — role sekolah & approval 2 tahap)

- **Role baru:** `guru`, `non_guru`, `kepala_sekolah` — portal-only, ditambahkan ke `User::PORTAL_ROLES` dan `routes/web.php` (sekarang baca langsung dari konstanta, tidak hardcode lagi).
- **Approval cuti 2 tahap** untuk `guru`/`non_guru`: Kepala Sekolah (scoped ke sekolahnya sendiri, by data bukan by role per sekolah) → Admin SDM/Ketua. Lihat bagian **Approval Cuti 2 Tahap** di atas.
- Migration baru `add_school_approval_to_leave_requests` — kolom `requires_school_approval`, `school_status`, `school_approved_by`, `school_approved_at`, `school_rejection_note` di `leave_requests`. Kolom `status` lama TIDAK diubah maknanya.
- Permission baru `leave.approve.school`, khusus role `kepala_sekolah`.
- UI approval Kepsek digabung ke halaman Portal cuti yang sudah ada (`/portal/leave`), bukan halaman terpisah.
- **Belum dikerjakan:** akun dev contoh untuk role baru ini belum ditambahkan ke `UserSeeder.php` — perlu dibuat manual via Manajemen User atau menyusul di sesi berikutnya.

### 19 Juni 2026

- **Fix bug redirect setelah login** — `AuthenticatedSessionController::store()` sebelumnya masih pakai daftar 2 role lama (`kepala_bidang`, `staf_yayasan`) untuk menentukan tujuan redirect, padahal `bootstrap/app.php` sudah pakai 6 role. Akibatnya `sekretaris`/`bendahara`/`ketua`/`staf_sdm` yang baru login salah diarahkan ke `/dashboard` bukan `/portal`. Diperbaiki dengan menyatukan logic ke `User::isPortalRole()` / `User::PORTAL_ROLES` sebagai satu sumber kebenaran.
- **Hapus `app/Http/Middleware/RedirectByRole.php`** — dead code, tidak pernah terdaftar sebagai alias middleware maupun dipasang di route manapun.
- **Tambah role `admin_sdm` ke akses Portal** — sekarang dual-access (Dashboard + Portal), disamakan dengan `staf_sdm`/`sekretaris`/`bendahara`/`ketua`. Permission `attendance.view.own` dan `leave.view.own` ditambahkan ke `admin_sdm` di `RolePermissionSeeder.php` agar konsisten dengan pola role dual-access lain.
- **Fix data seeder** — email duplikat di `UserSeeder.php` (dua user berbeda memakai email yang sama, menyebabkan salah satunya ter-skip otomatis saat seeding) sudah diperbaiki.
- Database direset penuh via `php artisan migrate:fresh --seed` setelah semua perubahan di atas.

### Sebelumnya (belum tercatat tanggal pasti — sedang berjalan/uncommitted)

- Fitur nonaktifkan akun (`is_active`): migration kolom, pengecekan di `LoginRequest`, middleware `check.active` di route Portal & Admin, toggle UI di `UserManagement.php`.
- Radius geofencing diubah dari 200m ke 100m di `config/geofence.php` (env default).

---

> Dibangun khusus untuk **Yayasan Fatahillah** · Laravel 12 + Livewire v4 · 2025–2026
