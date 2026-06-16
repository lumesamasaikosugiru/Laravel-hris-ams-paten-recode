<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectByRole
{
    /** Setelah login, arahkan ke portal atau dashboard berdasarkan role */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) return $next($request);

        $user        = auth()->user();
        $portalRoles = ['kepala_bidang', 'staf_yayasan'];

        // Kalau role portal tapi akses dashboard → redirect ke portal
        if ($user->hasAnyRole($portalRoles) && $request->routeIs('dashboard')) {
            return redirect()->route('portal.home');
        }

        // Kalau role admin tapi akses portal → redirect ke dashboard
        if (!$user->hasAnyRole($portalRoles) && $request->routeIs('portal.*')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
