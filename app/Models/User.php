<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

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
}