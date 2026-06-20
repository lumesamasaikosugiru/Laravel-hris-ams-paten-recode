<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: unique constraint attendances sebelumnya hanya (employee_id, date),
 * TIDAK menyertakan school_id. Ini salah untuk pegawai dengan tugas
 * tambahan lintas unit -- PRD v1.4 Bab 3.2 menyatakan absensi dihitung
 * TERPISAH per unit (2 baris berbeda jika pegawai bertugas di 2 sekolah
 * pada hari yang sama), tapi constraint lama justru MENOLAK baris kedua
 * karena employee_id+date sudah terpakai oleh baris pertama (sekolah lain).
 *
 * Constraint baru (employee_id, date, school_id) tetap mencegah double
 * check-in DI SEKOLAH YANG SAMA pada hari yang sama, tapi mengizinkan
 * baris terpisah untuk sekolah berbeda. Aman terhadap data lama: semua
 * baris yang valid di constraint lama otomatis valid di constraint baru
 * (constraint baru lebih longgar, bukan lebih ketat).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'date']);
            $table->unique(['employee_id', 'date', 'school_id'], 'attendances_employee_date_school_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_employee_date_school_unique');
            $table->unique(['employee_id', 'date']);
        });
    }
};
