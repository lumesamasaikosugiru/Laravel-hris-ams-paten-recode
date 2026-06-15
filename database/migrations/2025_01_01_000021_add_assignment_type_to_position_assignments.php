<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('position_assignments', function (Blueprint $table) {
            $table->enum('assignment_type', ['primary','additional'])
                  ->default('primary')
                  ->after('type')
                  ->comment('primary = jabatan utama/induk, additional = tugas tambahan');
        });

        // Set semua existing record sebagai primary
        DB::table('position_assignments')->update(['assignment_type' => 'primary']);
    }

    public function down(): void {
        Schema::table('position_assignments', function (Blueprint $table) {
            $table->dropColumn('assignment_type');
        });
    }
};
