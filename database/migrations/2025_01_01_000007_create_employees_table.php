<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('applicant_id')->nullable();

            // Identitas
            $table->string('nik', 30)->unique()->comment('NIK sementara: TMP-YYYYMMDD-XXXX');
            $table->string('nipy', 20)->unique()->nullable()->comment('NIPY resmi setelah lulus percobaan');
            $table->string('name');
            $table->string('national_id', 20)->nullable()->comment('NIK KTP');
            $table->enum('gender', ['male','female']);
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('religion', ['islam','kristen','katolik','hindu','buddha','konghucu'])->nullable();
            $table->enum('marital_status', ['single','married','divorced','widowed'])->nullable();
            $table->string('nationality')->default('Indonesia');

            // Kontak
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->text('address')->nullable();

            // Kepegawaian
            $table->boolean('is_guru')->default(false);
            $table->date('join_date');
            $table->enum('employee_type', ['permanent','contract','intern'])->default('contract');
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->enum('status', ['probation','active','inactive','resigned','terminated'])->default('probation');

            // Masa percobaan
            $table->date('probation_start_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->enum('probation_status', ['on_probation','passed','failed','not_applicable'])->default('on_probation');
            $table->date('probation_evaluated_at')->nullable();
            $table->foreignId('probation_evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('probation_notes')->nullable();

            // Pendidikan
            $table->enum('last_education', ['sd','smp','sma','d3','s1','s2','s3'])->nullable();
            $table->string('last_education_major')->nullable();
            $table->string('last_education_institution')->nullable();

            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};
