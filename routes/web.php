<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\LeaveTypeController;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('schools',     SchoolController::class)->only(['index']);
        Route::resource('departments', DepartmentController::class)->only(['index']);
        Route::resource('positions',   PositionController::class)->only(['index']);
        Route::resource('skills',      SkillController::class)->only(['index']);
        Route::resource('leave-types', LeaveTypeController::class)->only(['index']);
    });

});

require __DIR__.'/auth.php';
