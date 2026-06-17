<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Apakah absensi ini kegiatan luar?
            $table->boolean('is_offsite')->default(false)->after('checkin_location_name');

            // Alasan kegiatan luar (enum pilihan)
            $table->string('offsite_reason')->nullable()->after('is_offsite');
            // Keterangan tambahan bebas (wajib jika reason = 'Lainnya')
            $table->text('offsite_note')->nullable()->after('offsite_reason');

            // Approval HR
            $table->enum('offsite_status', ['pending', 'approved', 'rejected'])
                ->nullable()
                ->after('offsite_note');
            $table->foreignId('offsite_approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('offsite_status');
            $table->timestamp('offsite_approved_at')->nullable()->after('offsite_approved_by');
            $table->text('offsite_rejection_note')->nullable()->after('offsite_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('offsite_approved_by');
            $table->dropColumn([
                'is_offsite',
                'offsite_reason',
                'offsite_note',
                'offsite_status',
                'offsite_approved_at',
                'offsite_rejection_note',
            ]);
        });
    }
};
