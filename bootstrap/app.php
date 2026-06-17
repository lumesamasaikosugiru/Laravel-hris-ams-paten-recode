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
        // ─────────────────────────────────────────────────────────
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = $request->user();

            if (!$user) {
                return route('welcome');
            }

            $portalRoles = ['kepala_bidang', 'staf_yayasan'];

            return $user->hasAnyRole($portalRoles)
                ? route('portal.home')
                : route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();