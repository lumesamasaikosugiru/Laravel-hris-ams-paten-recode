<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Bersihkan intended URL lama dari sesi sebelumnya (mis. percobaan
        // akses halaman terproteksi oleh user/role yang berbeda) supaya
        // tidak "membajak" redirect berdasarkan role yang baru login.
        $request->session()->forget('url.intended');

        // Tujuan setelah login ditentukan oleh User::isPortalRole()
        // — SATU sumber kebenaran yang sama dipakai juga oleh
        // RedirectIfAuthenticated di bootstrap/app.php. Jangan
        // hardcode daftar role di sini lagi (riwayat: sebelumnya
        // pernah out-of-sync dan menyebabkan sekretaris/bendahara/
        // ketua/staf_sdm salah diarahkan ke dashboard saat baru login).
        $user = auth()->user();

        $target = $user->isPortalRole()
            ? route('portal.home')
            : route('dashboard');

        return redirect()->to($target);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}