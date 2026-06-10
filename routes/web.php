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
use App\Http\Controllers\Public\CareerController;

Route::get('/', fn() => redirect()->route('careers.index'));

Route::prefix('karir')->name('careers.')->group(function () {
    Route::get('/',                    [CareerController::class, 'index'])->name('index');
    Route::get('/{jobVacancy}',        [CareerController::class, 'show'])->name('show');
    Route::get('/{jobVacancy}/daftar', [CareerController::class, 'apply'])->name('apply');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {

        // Master Data
        Route::resource('schools',     SchoolController::class)->only(['index']);
        Route::resource('departments', DepartmentController::class)->only(['index']);
        Route::resource('positions',   PositionController::class)->only(['index']);
        Route::resource('skills',      SkillController::class)->only(['index']);
        Route::resource('leave-types', LeaveTypeController::class)->only(['index']);

        // Rekrutmen
        Route::resource('jobs',       JobController::class)->only(['index']);
        Route::resource('applicants', ApplicantController::class)->only(['index']);

        // Kepegawaian
        Route::get('employees/import',   [EmployeeController::class, 'import'])
            ->name('employees.import');
        Route::get('employees/template', [EmployeeController::class, 'downloadTemplate'])
            ->name('employees.template');
        Route::resource('employees', EmployeeController::class)
            ->only(['index','create','edit','show']);

        // Absensi
        Route::get('attendance',         [AttendanceController::class, 'index'])
            ->name('attendance.index');
        Route::get('attendance/report',  [AttendanceController::class, 'report'])
            ->name('attendance.report');
        Route::get('attendance/export',  [AttendanceController::class, 'export'])
            ->name('attendance.export');
    });

});

require __DIR__.'/auth.php';
