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
            // Check-in GPS
            $table->decimal('checkin_latitude', 10, 8)->nullable()->after('check_in');
            $table->decimal('checkin_longitude', 11, 8)->nullable()->after('checkin_latitude');
            $table->boolean('checkin_location_valid')->nullable()->after('checkin_longitude');
            $table->string('checkin_location_name')->nullable()->after('checkin_location_valid');

            // Check-out GPS
            $table->decimal('checkout_latitude', 10, 8)->nullable()->after('check_out');
            $table->decimal('checkout_longitude', 11, 8)->nullable()->after('checkout_latitude');
            $table->boolean('checkout_location_valid')->nullable()->after('checkout_longitude');
            $table->string('checkout_location_name')->nullable()->after('checkout_location_valid');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'checkin_latitude',
                'checkin_longitude',
                'checkin_location_valid',
                'checkin_location_name',
                'checkout_latitude',
                'checkout_longitude',
                'checkout_location_valid',
                'checkout_location_name',
            ]);
        });
    }
};
