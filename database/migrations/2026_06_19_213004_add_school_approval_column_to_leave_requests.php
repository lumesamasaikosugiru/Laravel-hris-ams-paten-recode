<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mendukung approval cuti 2 tahap untuk role guru & non_guru:
 * Kepala Sekolah (tahap 1) -> Admin SDM / Ketua (tahap 2, pakai
 * kolom approved_by/approved_at/status yang SUDAH ADA, tidak diubah).
 *
 * PENTING: kolom `status` di tabel ini TETAP berarti "keputusan akhir"
 * untuk SEMUA role, tidak berubah maknanya. Untuk pengajuan dengan
 * requires_school_approval=true, status TETAP 'pending' sampai
 * school_status juga 'approved' -- baru dianggap actionable oleh SDM.
 * Lihat LeaveService::requiresSchoolApproval() untuk logic penentunya.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->boolean('requires_school_approval')->default(false)->after('status');
            $table->enum('school_status', ['pending', 'approved', 'rejected'])
                ->nullable()->after('requires_school_approval');
            $table->foreignId('school_approved_by')->nullable()
                ->after('school_status')->constrained('users')->nullOnDelete();
            $table->timestamp('school_approved_at')->nullable()->after('school_approved_by');
            $table->text('school_rejection_note')->nullable()->after('school_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_approved_by');
            $table->dropColumn([
                'requires_school_approval',
                'school_status',
                'school_approved_at',
                'school_rejection_note',
            ]);
        });
    }
};