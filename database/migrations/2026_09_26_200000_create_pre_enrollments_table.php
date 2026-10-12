<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pre_enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->string('student_last_name', 125);
            $table->string('student_first_name', 125);
            $table->date('birth_date');
            $table->string('birth_place', 125)->nullable();
            $table->enum('gender', ['M', 'F']);
            $table->string('requested_cycle', 125);
            $table->string('requested_level', 125)->nullable();
            $table->string('guardian_first_name', 125);
            $table->string('guardian_last_name', 125);
            $table->string('relationship', 50)->default('PERE');
            $table->string('guardian_phone', 50);
            $table->string('guardian_email', 125)->nullable();
            $table->string('guardian_city', 125)->nullable()->default('Niamey');
            $table->string('guardian_profession', 125)->nullable();
            $table->enum('status', ['EN_ATTENTE', 'VALIDEE', 'REJETEE'])->default('EN_ATTENTE');
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_enrollments');
    }
};
