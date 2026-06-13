# HRIS Yayasan Fatahillah

> **Human Resource Information System** — Sistem informasi SDM berbasis web untuk Yayasan Fatahillah. Mengelola seluruh siklus kepegawaian: rekrutmen, masa percobaan, NIPY, absensi, cuti, dan laporan dalam satu platform terpadu yang mendukung struktur multi-sekolah.

**Versi:** 1.1 · **Status:** Production Ready (HRIS Phase 1–6 + Polish)

---

## Tech Stack

| Layer         | Teknologi                          | Versi        |
| ------------- | ---------------------------------- | ------------ |
| Backend       | Laravel                            | 12.x         |
| Reactive UI   | Livewire                           | v4.x         |
| CSS Framework | Tailwind CSS                       | v3.x         |
| JS Alpine     | Alpine.js                          | v3.x         |
| Database      | MySQL                              | 8.0+         |
| Auth          | Laravel Breeze + Spatie Permission | latest       |
| Excel         | PhpSpreadsheet                     | latest       |
| Chart         | Chart.js                           | v4.4         |
| Font          | Plus Jakarta Sans                  | Google Fonts |

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

### Default Login

| Field    | Value           |
| -------- | --------------- |
| Email    | admin@hris.test |
| Password | password        |
| Role     | Super Admin     |

---

## Fitur (Phase 1–6 + Polish v1.1)

### ✅ Phase 1 — Fondasi & Master Data

- Auth via Laravel Breeze + Spatie Permission (7 roles)
- CRUD: Sekolah, Departemen, Jabatan, Skill, Jenis Cuti
- Seeder struktur Yayasan Fatahillah (3 unit, jabatan lengkap)

### ✅ Phase 2 — Rekrutmen

- Kelola lowongan kerja (Draft → Dibuka → Ditutup)
- Halaman publik `/karir` dengan form pendaftaran multi-tab
- CV wajib diupload saat pendaftaran
- Validasi error otomatis mengarahkan ke tab yang bermasalah
- Pipeline seleksi: Lamaran → Verifikasi Berkas → Tes Potensi → Diterima/Ditolak
- Klik baris tabel → lihat detail pelamar (modal)
- Konversi pelamar → pegawai (NIK sementara auto-generated)

### ✅ Phase 3 — Manajemen Pegawai

- Form pegawai multi-tab (Identitas, Kontak, Kepegawaian, Pendidikan, Jabatan)
- Navigasi antar tab dengan tombol Selanjutnya/Sebelumnya
- Tombol Simpan hanya muncul di tab terakhir (Jabatan) saat tambah baru
- Validasi error otomatis mengarahkan ke tab yang bermasalah + error summary
- NIK KTP dibatasi 16 karakter, hanya angka
- Import massal dari Excel (template downloadable dengan sheet referensi)
- Klik baris tabel → halaman detail pegawai
- Halaman detail: profil lengkap, timeline riwayat jabatan
- Mutasi / Promosi / Demosi dengan riwayat tersimpan
- Evaluasi masa percobaan + auto-generate NIPY
- **Soft delete** — pegawai dengan riwayat tidak bisa dihapus permanen
- **Info pensiun** — usia saat ini, tanggal pensiun (60 tahun), sisa waktu
- Setelah tambah/edit → redirect ke halaman detail pegawai
- Status default pegawai manual = Aktif (bukan Masa Percobaan)

### ✅ Phase 4 — Absensi Harian

- Input check-in/check-out manual dengan combobox Alpine.js (searchable)
- Auto-kalkulasi: Hadir / Terlambat (setelah 07:30) / Tidak Hadir
- Laporan absensi bulanan + export Excel

### ✅ Phase 5 — Cuti & Izin

- Generate saldo cuti otomatis per tahun — **hanya pegawai Aktif** (Probation tidak dapat saldo)
- Pengajuan cuti dengan validasi aturan bisnis via `LeaveService`
- **Pegawai Probation tidak bisa mengajukan cuti**
- Guru tidak mendapat Cuti Tahunan
- Pengajuan minimal H-5 sebelum tanggal mulai
- Tanggal selesai dibatasi sesuai sisa saldo
- Approval flow: Pending → Disetujui/Ditolak
- Integrasi otomatis dengan absensi

### ✅ Phase 6 — Dashboard & Laporan

- Dashboard real-time: statistik pegawai, kehadiran, cuti, rekrutmen
- Grafik batang absensi 7 hari terakhir
- **Alert pegawai mendekati pensiun** (≤ 2 tahun)
- Alert kontrak hampir habis (30 hari) dan masa percobaan overdue
- Export Excel: Pegawai, Rekrutmen, Masa Percobaan, Saldo Cuti

### ✅ Polish & Bug Fix (v1.1)

- Tombol ESC menutup semua modal di seluruh sistem
- Klik baris tabel navigasi yang konsisten di semua halaman
- Alpine.js duplicate instance dihilangkan (tidak ada `Alpine.start()` ganda)
- Model `ApplicantEducation` — nama tabel eksplisit (`applicant_educations`)
- `app/Models/Employee` — tambah relasi `attendances()` dan `leaveRequests()`
- Soft delete pegawai dengan proteksi data historis

---

## Struktur Direktori Penting

```
app/
├── Console/Commands/        # CheckProbationEndDate.php (scheduler harian)
├── Http/Controllers/Admin/  # Thin controllers (logic di Livewire)
├── Http/Controllers/Public/ # CareerController
├── Livewire/Admin/          # 17 Livewire components
├── Livewire/Public/         # ApplyForm
├── Models/                  # 15 Eloquent models
└── Services/
    ├── LeaveService.php     # Aturan bisnis cuti (terpusat)
    └── NipyGenerator.php    # Logic generate NIPY

database/
├── migrations/              # 20 migration files (termasuk soft delete employees)
└── seeders/                 # RolePermission + FatahillahStructure

resources/
├── css/app.css              # Design tokens + utility classes
├── js/app.js                # Alpine.js (tanpa Alpine.start())
└── views/
    ├── components/layouts/  # admin.blade.php, public.blade.php
    ├── livewire/admin/      # Blade views per component
    ├── pages/               # dashboard.blade.php
    └── public/careers/      # Halaman publik lowongan

routes/
├── web.php                  # Semua route
└── console.php              # Scheduler
```

---

## Database (20 Tabel)

| Domain      | Tabel                                                                                                                          |
| ----------- | ------------------------------------------------------------------------------------------------------------------------------ |
| Auth        | `users`, `roles`, `permissions`                                                                                                |
| Master Data | `schools`, `departments`, `positions`, `skills`, `leave_types`                                                                 |
| Rekrutmen   | `job_vacancies`, `applicants`, `applicant_educations`, `applicant_experiences`, `applicant_skills`                             |
| Kepegawaian | `employees` (soft delete), `position_assignments`, `employee_status_histories`, `employee_skills`, `employee_school_histories` |
| Absensi     | `attendances`                                                                                                                  |
| Cuti        | `leave_balances`, `leave_requests`                                                                                             |

---

## NIPY — Komposisi

Format: `YY` + `PP` + `KK` + `NNNN`

| Segmen | Arti                   | Contoh            |
| ------ | ---------------------- | ----------------- |
| YY     | 2 digit tahun masuk    | `25` = 2025       |
| PP     | Kode pendidikan        | `07` = S1         |
| KK     | Kode jenis kepegawaian | `11` = guru tetap |
| NNNN   | Nomor urut 4 digit     | `0001`            |

**Kode PP:** `01`=SD · `02`=SMP · `03`=SMA/SMK · `06`=D3 · `07`=S1 · `08`=S2 · `09`=S3

**Kode KK:** `11`=guru tetap · `12`=guru tidak tetap · `21`=non-guru tetap · `22`=non-guru tidak tetap

**Contoh:** `2507110001` = masuk 2025, S1, guru tetap, urut ke-1

---

## Aturan Bisnis Fatahillah

| Aturan                           | Nilai        | Lokasi Konfigurasi                |
| -------------------------------- | ------------ | --------------------------------- |
| Usia pensiun                     | 60 tahun     | `Employee::RETIREMENT_AGE`        |
| Jam masuk standar                | 07:30 WIB    | `Attendance::WORK_START`          |
| Jam selesai kerja                | 16:00 WIB    | `Attendance::WORK_END`            |
| Minimal pengajuan cuti           | H-5          | `LeaveService::MIN_DAYS_BEFORE`   |
| Cuti yang tidak boleh untuk guru | Cuti Tahunan | `LeaveService::EXCLUDED_FOR_GURU` |
| Masa percobaan non-guru          | 3 bulan      | `NipyGenerator`                   |
| Masa percobaan guru              | 6 bulan      | `NipyGenerator`                   |
| Pegawai probation dapat cuti     | ❌ Tidak     | `LeaveService::validate()`        |

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

## Roles

| Role                    | Akses                                 |
| ----------------------- | ------------------------------------- |
| `super_admin`           | Akses penuh ke semua fitur            |
| `admin_hr`              | Kepegawaian, rekrutmen, absensi, cuti |
| `kepala_sekolah`        | View unit sendiri, approval cuti      |
| `pegawai`               | Submit cuti, lihat data sendiri       |
| `guru`                  | Sama seperti pegawai                  |
| `koordinator_kurikulum` | Reserved untuk AMS Phase 7+           |
| `siswa`                 | Reserved untuk AMS Phase 11           |

---

## Routes Utama

### Publik

| URL                  | Keterangan                          |
| -------------------- | ----------------------------------- |
| `/karir`             | Daftar lowongan aktif               |
| `/karir/{id}`        | Detail lowongan                     |
| `/karir/{id}/daftar` | Form pendaftaran pelamar (CV wajib) |

### Admin (auth required)

| URL                          | Keterangan                           |
| ---------------------------- | ------------------------------------ |
| `/dashboard`                 | Dashboard + alert pensiun & kontrak  |
| `/admin/schools`             | CRUD Sekolah                         |
| `/admin/departments`         | CRUD Departemen                      |
| `/admin/positions`           | CRUD Jabatan                         |
| `/admin/skills`              | CRUD Skill                           |
| `/admin/leave-types`         | CRUD Jenis Cuti                      |
| `/admin/jobs`                | Kelola Lowongan                      |
| `/admin/applicants`          | Pipeline Pelamar                     |
| `/admin/employees`           | Daftar Pegawai (soft delete)         |
| `/admin/employees/create`    | Tambah Pegawai Manual                |
| `/admin/employees/import`    | Import dari Excel                    |
| `/admin/employees/template`  | Download Template Excel              |
| `/admin/employees/{id}`      | Detail + Info Pensiun                |
| `/admin/employees/{id}/edit` | Edit Pegawai                         |
| `/admin/attendance`          | Absensi Harian                       |
| `/admin/attendance/report`   | Laporan Absensi                      |
| `/admin/attendance/export`   | Export Excel Absensi                 |
| `/admin/leaves`              | Pengajuan Cuti (hanya pegawai Aktif) |
| `/admin/leaves/balance`      | Saldo Cuti                           |
| `/admin/reports`             | Hub Laporan SDM                      |
| `/admin/reports/employees`   | Export Laporan Pegawai               |
| `/admin/reports/recruitment` | Export Laporan Rekrutmen             |
| `/admin/reports/probation`   | Export Laporan Masa Percobaan        |
| `/admin/reports/leaves`      | Export Laporan Cuti                  |

---

## Troubleshooting

| Error                           | Solusi                                               |
| ------------------------------- | ---------------------------------------------------- |
| Class not found setelah install | `composer dump-autoload`                             |
| View tidak update               | `php artisan view:clear`                             |
| Route 404                       | `php artisan route:clear && php artisan cache:clear` |
| Migration error table exists    | `php artisan migrate:fresh --seed` ⚠️ data hilang    |
| Storage file tidak bisa diakses | `php artisan storage:link`                           |
| Alpine / Livewire tidak bekerja | `npm run build` + hard refresh `Ctrl+Shift+R`        |
| PhpSpreadsheet not found        | `composer require phpoffice/phpspreadsheet`          |
| Alpine duplicate instances      | Pastikan `app.js` tidak ada `Alpine.start()`         |
| Modal tidak bisa ditutup ESC    | Cek script ESC di `layouts/admin.blade.php`          |
| Table not found (model)         | Tambahkan `protected $table = 'nama_tabel'` di model |

---

## Perintah Development

```bash
php artisan serve                     # Dev server
npm run dev                           # Compile + watch assets
php artisan migrate:fresh --seed      # Reset database + seeder
php artisan view:clear                # Clear blade cache
php artisan cache:clear               # Clear app cache
php artisan config:clear              # Clear config cache
php artisan route:list                # Lihat semua route
php artisan hris:check-probation      # Manual cek masa percobaan
```

---

## Roadmap

| Phase  | Nama                             | Status     |
| ------ | -------------------------------- | ---------- |
| 1      | Fondasi & Master Data            | ✅ Selesai |
| 2      | Rekrutmen                        | ✅ Selesai |
| 3      | Manajemen Pegawai                | ✅ Selesai |
| 4      | Absensi Harian                   | ✅ Selesai |
| 5      | Cuti & Izin                      | ✅ Selesai |
| 6      | Dashboard & Laporan              | ✅ Selesai |
| Polish | Bug Fix & UX Improvements        | ✅ Selesai |
| 7      | Master Akademik & Jadwal         | 🔲 Belum   |
| 8      | RPP & Review Workflow            | 🔲 Belum   |
| 9      | Jurnal Mengajar                  | 🔲 Belum   |
| 10     | Absensi Siswa & Notifikasi       | 🔲 Belum   |
| 11     | Portal Siswa                     | 🔲 Belum   |
| 12     | Dashboard & Laporan Terintegrasi | 🔲 Belum   |

### Backlog (Belum Dijadwalkan)

- **Event Attendance** — daftar hadir kegiatan yayasan per sekolah + rekap
- GPS Check-in — validasi lokasi saat absensi
- Notifikasi in-app — approval cuti, masa percobaan
- Role-based access control (middleware per halaman)
- Optimasi query N+1

---

> Dibangun khusus untuk **Yayasan Fatahillah** · Laravel 12 + Livewire v4 · 2025–2026
