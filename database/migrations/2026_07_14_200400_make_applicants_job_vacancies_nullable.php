<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mendukung input pelamar walk-in oleh HR tanpa lowongan aktif.
 *
 * Sebelumnya job_vacancy_id NOT NULL -- semua pelamar WAJIB terikat
 * ke lowongan. Sekarang nullable supaya HR bisa input pelamar yang
 * datang langsung/walk-in tanpa lowongan publik. Kolom applied_position
 * ditambahkan sebagai text bebas untuk mencatat posisi yang dilamar
 * oleh pelamar walk-in (pengganti job_vacancy->position->name yang
 * tidak tersedia untuk walk-in).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Lepas FK constraint dulu sebelum ubah kolom
            $table->dropForeign(['job_vacancy_id']);

            // Jadikan nullable
            $table->foreignId('job_vacancy_id')
                ->nullable()
                ->change()
                ->constrained('job_vacancies')
                ->nullOnDelete();

            // Posisi yang dilamar (text bebas, diisi HR untuk walk-in)
            $table->string('applied_position')->nullable()->after('job_vacancy_id');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('applied_position');
            $table->dropForeign(['job_vacancy_id']);
            $table->foreignId('job_vacancy_id')
                ->change()
                ->constrained('job_vacancies')
                ->cascadeOnDelete();
        });
    }
};