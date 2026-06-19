<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Public\CareerController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Admin\UserController;
use App\Models\User;

// ── Public ──────────────────────────────────────────────────

// Landing page company profile Yayasan Fatahillah
Route::get('/', fn() => view('welcome'))->name('welcome');

Route::prefix('karir')->name('careers.')->group(function () {
    Route::get('/', [CareerController::class, 'index'])->name('index');
    Route::get('/{jobVacancy}', [CareerController::class, 'show'])->name('show');
    Route::get('/{jobVacancy}/daftar', [CareerController::class, 'apply'])->name('apply');
});

// Portal — semua role di App\Models\User::PORTAL_ROLES (SATU sumber
// kebenaran, dipakai juga oleh AuthenticatedSessionController dan
// bootstrap/app.php untuk redirect setelah login). JANGAN hardcode
// daftar role di sini lagi -- riwayat: sebelumnya ada 3 tempat dengan
// daftar role yang berbeda-beda dan sempat out-of-sync, menyebabkan
// bug redirect (lihat README.md Changelog 19 Juni 2026).
//
// 'check.active' ditambahkan di sini: tanpa ini, akun yang dinonaktifkan
// lewat Manajemen User tetap bisa terus mengakses Portal selama sesi
// browser mereka belum berakhir, karena Laravel tidak otomatis mengecek
// ulang status is_active pada request setelah login awal.
Route::middleware(['auth', 'check.active', 'role:' . implode('|', User::PORTAL_ROLES)])
    ->prefix('portal')->name('portal.')->group(function () {
        Route::get('/', [PortalController::class, 'home'])->name('home');
        Route::get('/attendance', [PortalController::class, 'attendance'])->name('attendance');
        Route::get('/leave', [PortalController::class, 'leave'])->name('leave');
        Route::get('/profile', [PortalController::class, 'profile'])->name('profile');
    });

// ── Admin ────────────────────────────────────────────────────
// 'check.active' dipasang di level group paling atas supaya berlaku
// untuk SEMUA route admin & dashboard di bawahnya tanpa perlu diulang
// satu-satu di setiap sub-route.
Route::middleware(['auth', 'check.active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('pages.dashboard'))
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {

        // ── Master Data ──────────────────────────────────────
        Route::middleware('permission:master.view')->group(function () {
            Route::resource('schools', SchoolController::class)->only(['index']);
            Route::resource('departments', DepartmentController::class)->only(['index']);
            Route::resource('positions', PositionController::class)->only(['index']);
            Route::resource('skills', SkillController::class)->only(['index']);
            Route::resource('leave-types', LeaveTypeController::class)->only(['index']);
        });

        // ── Rekrutmen ────────────────────────────────────────
        Route::middleware('permission:recruitment.view')->group(function () {
            Route::resource('jobs', JobController::class)->only(['index']);
            Route::resource('applicants', ApplicantController::class)->only(['index']);
        });

        // ── Kepegawaian ──────────────────────────────────────
        // index & show hanya butuh employee.view (lihat saja).
        // create & edit butuh permission terpisah yang lebih ketat,
        // supaya role read-only (bendahara, ketua) tidak bisa akses
        // halaman create/edit lewat URL manual sekalipun tombolnya
        // sudah disembunyikan di tampilan.
        Route::middleware('permission:employee.view')->group(function () {
            Route::get('employees/import', [EmployeeController::class, 'import'])
                ->middleware('permission:employee.create')
                ->name('employees.import');
            Route::get('employees/template', [EmployeeController::class, 'downloadTemplate'])
                ->middleware('permission:employee.create')
                ->name('employees.template');

            Route::get('employees', [EmployeeController::class, 'index'])
                ->name('employees.index');
            Route::get('employees/create', [EmployeeController::class, 'create'])
                ->middleware('permission:employee.create')
                ->name('employees.create');
            Route::get('employees/{employee}', [EmployeeController::class, 'show'])
                ->name('employees.show');
            Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])
                ->middleware('permission:employee.edit')
                ->name('employees.edit');
        });

        // ── Absensi ──────────────────────────────────────────
        Route::get('attendance', [AttendanceController::class, 'index'])
            ->middleware('permission:attendance.view')
            ->name('attendance.index');

        Route::get('attendance/report', [AttendanceController::class, 'report'])
            ->middleware('permission:attendance.report')
            ->name('attendance.report');

        Route::get('attendance/export', [AttendanceController::class, 'export'])
            ->middleware('permission:attendance.export')
            ->name('attendance.export');

        // Approval kegiatan luar (bagian dari modul Absensi)
        Route::get('offsite-approvals', function () {
            return view('admin.offsite-approvals');
        })->middleware('permission:attendance.view')
            ->name('offsite-approvals');

        // ── Cuti & Izin ──────────────────────────────────────
        Route::get('leaves', [LeaveController::class, 'index'])
            ->middleware('permission:leave.view')
            ->name('leaves.index');

        Route::get('leaves/balance', [LeaveController::class, 'balance'])
            ->middleware('permission:leave.balance')
            ->name('leaves.balance');

        // ── Laporan ──────────────────────────────────────────
        Route::middleware('permission:report.view')->group(function () {
            Route::get('reports', [ReportController::class, 'index'])
                ->name('reports.index');
            Route::get('reports/employees', [ReportController::class, 'employees'])
                ->name('reports.employees');
            Route::get('reports/recruitment', [ReportController::class, 'recruitment'])
                ->name('reports.recruitment');
            Route::get('reports/probation', [ReportController::class, 'probation'])
                ->name('reports.probation');
            Route::get('reports/leaves', [ReportController::class, 'leaves'])
                ->name('reports.leaves');
        });

        // ── Managemen User ─────────────────────────────────────
        Route::get('users', [UserController::class, 'index'])
            ->middleware('permission:user.manage')
            ->name('users.index');

    });

});

require __DIR__ . '/auth.php';