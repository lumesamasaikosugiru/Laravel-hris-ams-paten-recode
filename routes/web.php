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

// ── Public ──────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('careers.index'));

Route::prefix('karir')->name('careers.')->group(function () {
    Route::get('/',                    [CareerController::class, 'index'])->name('index');
    Route::get('/{jobVacancy}',        [CareerController::class, 'show'])->name('show');
    Route::get('/{jobVacancy}/daftar', [CareerController::class, 'apply'])->name('apply');
});

// ── Admin ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('pages.dashboard'))
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {

        // ── Master Data ──────────────────────────────────────
        Route::middleware('permission:master.view')->group(function () {
            Route::resource('schools',     SchoolController::class)->only(['index']);
            Route::resource('departments', DepartmentController::class)->only(['index']);
            Route::resource('positions',   PositionController::class)->only(['index']);
            Route::resource('skills',      SkillController::class)->only(['index']);
            Route::resource('leave-types', LeaveTypeController::class)->only(['index']);
        });

        // ── Rekrutmen ────────────────────────────────────────
        Route::middleware('permission:recruitment.view')->group(function () {
            Route::resource('jobs',       JobController::class)->only(['index']);
            Route::resource('applicants', ApplicantController::class)->only(['index']);
        });

        // ── Kepegawaian ──────────────────────────────────────
        Route::middleware('permission:employee.view')->group(function () {
            Route::get('employees/import',   [EmployeeController::class, 'import'])
                ->name('employees.import');
            Route::get('employees/template', [EmployeeController::class, 'downloadTemplate'])
                ->name('employees.template');
            Route::resource('employees', EmployeeController::class)
                ->only(['index','create','edit','show']);
        });

        // ── Absensi ──────────────────────────────────────────
        Route::get('attendance',
            [AttendanceController::class, 'index'])
            ->middleware('permission:attendance.view')
            ->name('attendance.index');

        Route::get('attendance/report',
            [AttendanceController::class, 'report'])
            ->middleware('permission:attendance.report')
            ->name('attendance.report');

        Route::get('attendance/export',
            [AttendanceController::class, 'export'])
            ->middleware('permission:attendance.export')
            ->name('attendance.export');

        // ── Cuti & Izin ──────────────────────────────────────
        Route::get('leaves',
            [LeaveController::class, 'index'])
            ->middleware('permission:leave.view')
            ->name('leaves.index');

        Route::get('leaves/balance',
            [LeaveController::class, 'balance'])
            ->middleware('permission:leave.balance')
            ->name('leaves.balance');

        // ── Laporan ──────────────────────────────────────────
        Route::middleware('permission:report.view')->group(function () {
            Route::get('reports',             [ReportController::class, 'index'])
                ->name('reports.index');
            Route::get('reports/employees',   [ReportController::class, 'employees'])
                ->name('reports.employees');
            Route::get('reports/recruitment', [ReportController::class, 'recruitment'])
                ->name('reports.recruitment');
            Route::get('reports/probation',   [ReportController::class, 'probation'])
                ->name('reports.probation');
            Route::get('reports/leaves',      [ReportController::class, 'leaves'])
                ->name('reports.leaves');
        });
    });

});

require __DIR__.'/auth.php';
