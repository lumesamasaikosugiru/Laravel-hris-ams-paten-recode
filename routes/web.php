<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\ApplicantController;
use App\Http\Controllers\Public\CareerController;

// ── Public ──────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('careers.index'));

Route::prefix('karir')->name('careers.')->group(function () {
    Route::get('/',                       [CareerController::class, 'index'])->name('index');
    Route::get('/{jobVacancy}',           [CareerController::class, 'show'])->name('show');
    Route::get('/{jobVacancy}/daftar',    [CareerController::class, 'apply'])->name('apply');
});

// ── Admin ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('schools',     SchoolController::class)->only(['index']);
        Route::resource('departments', DepartmentController::class)->only(['index']);
        Route::resource('positions',   PositionController::class)->only(['index']);
        Route::resource('skills',      SkillController::class)->only(['index']);
        Route::resource('leave-types', LeaveTypeController::class)->only(['index']);
        Route::resource('jobs',        JobController::class)->only(['index']);
        Route::resource('applicants',  ApplicantController::class)->only(['index']);
    });

});

require __DIR__.'/auth.php';
