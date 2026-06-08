<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('applicant_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['sd','smp','sma','d3','s1','s2','s3']);
            $table->string('institution');
            $table->string('major')->nullable();
            $table->year('start_year');
            $table->year('end_year')->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->boolean('is_latest')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('applicant_educations'); }
};
