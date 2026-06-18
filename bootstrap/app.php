<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckUserActive;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.active' => CheckUserActive::class,
            'permission' => CheckPermission::class,
            'role' => RoleMiddleware::class,
        ]);

        // ─────────────────────────────────────────────────────────
        // Tujuan redirect untuk user yang SUDAH LOGIN tapi mencoba
        // mengakses halaman guest (/login, /register, dst).
        //
        // Role yang masuk $portalRoles akan diarahkan ke Portal
        // mobile (/portal). Catatan: sekretaris, bendahara, ketua,
        // dan staf_sdm SENGAJA dimasukkan di sini meski mereka JUGA
        // punya akses ke /dashboard (lihat routes/web.php) — karena
        // bagi mereka, Portal adalah tujuan utama setelah login
        // (untuk absen/cuti harian), sedangkan Dashboard diakses
        // manual lewat menu saat dibutuhkan untuk memantau.
        // ─────────────────────────────────────────────────────────
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if (!$user) {
                return route('welcome');
            }

            $portalRoles = [
                'kepala_bidang',
                'staf_yayasan',
                'sekretaris',
                'bendahara',
                'ketua',
                'staf_sdm',
            ];

            return $user->hasAnyRole($portalRoles)
                ? route('portal.home')
                : route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();