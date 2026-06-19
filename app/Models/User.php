<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'is_active'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    /**
     * Role yang tujuan utamanya setelah login adalah Portal Mobile
     * (absen & cuti harian), bukan Dashboard.
     *
     * SUMBER KEBENARAN TUNGGAL — dipakai di:
     * - AuthenticatedSessionController::store() (redirect setelah login)
     * - bootstrap/app.php RedirectIfAuthenticated::redirectUsing()
     *   (redirect saat user yang sudah login mencoba akses halaman guest)
     *
     * PENTING: kepala_bidang & staf_yayasan TIDAK punya dashboard.view
     * sama sekali (portal-only). sekretaris, bendahara, ketua, staf_sdm
     * bersifat dual-access — mereka JUGA punya dashboard.view, tapi
     * Portal tetap jadi tujuan utama setelah login karena dipakai untuk
     * absen/cuti harian; Dashboard diakses manual lewat menu saat
     * dibutuhkan untuk memantau. Jangan hapus salah satu role dari sini
     * tanpa mengecek ulang routes/web.php prefix('portal').
     */
    public const PORTAL_ROLES = [
        'kepala_bidang',
        'staf_yayasan',
        'sekretaris',
        'bendahara',
        'ketua',
        'staf_sdm',
        'admin_sdm',
    ];

    /**
     * Pegawai yang terhubung dengan akun ini.
     *
     * Foreign key ADA di tabel employees (employees.user_id),
     * BUKAN di tabel users. Jadi relasi dari sisi User harus
     * hasOne, bukan belongsTo.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    /**
     * Apakah tujuan utama user ini setelah login adalah Portal,
     * bukan Dashboard. Lihat komentar PORTAL_ROLES di atas.
     */
    public function isPortalRole(): bool
    {
        return $this->hasAnyRole(self::PORTAL_ROLES);
    }
}