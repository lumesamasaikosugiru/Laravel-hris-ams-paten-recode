<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->enum('gender', ['male','female']);
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->enum('last_education', ['sd','smp','sma','d3','s1','s2','s3'])->nullable();
            $table->string('last_education_major')->nullable();
            $table->string('last_education_institution')->nullable();
            $table->string('cv_file')->nullable();
            $table->enum('status', ['submitted','tes_berkas','tes_potensi','diterima','ditolak'])->default('submitted');
            $table->text('hr_notes')->nullable();
            $table->foreignId('converted_to_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('source', ['public_form','admin_input'])->default('public_form');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('applicants'); }
};
