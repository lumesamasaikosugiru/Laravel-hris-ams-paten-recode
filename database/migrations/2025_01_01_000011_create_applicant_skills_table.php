<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('applicant_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->enum('proficiency', ['beginner','intermediate','advanced','expert'])->default('intermediate');
            $table->timestamps();
            $table->unique(['applicant_id', 'skill_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('applicant_skills'); }
};
