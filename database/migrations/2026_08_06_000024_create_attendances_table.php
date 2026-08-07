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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('session', ['MATIN', 'APRES_MIDI', 'JOURNEE'])->default('JOURNEE');
            $table->enum('status', ['PRESENT', 'ABSENT', 'RETARD', 'EXCUSE'])->default('PRESENT');
            $table->string('reason', 250)->nullable();    // Motif d'absence / retard
            $table->string('recorded_by', 125)->nullable(); // Enseignant / Admin
            $table->timestamps();

            $table->unique(
                ['student_id', 'class_id', 'subject_id', 'attendance_date', 'session'],
                'attendance_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
