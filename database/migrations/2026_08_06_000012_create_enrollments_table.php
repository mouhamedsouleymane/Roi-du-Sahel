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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('enrollment_number', 50)->unique(); // INS-2025-XXXX
            $table->enum('type', ['NOUVEAU', 'REINSCRIPTION', 'TRANSFERT'])->default('NOUVEAU');
            $table->enum('status', ['EN_ATTENTE', 'VALIDE', 'ANNULE'])->default('VALIDE');
            $table->boolean('is_repeater')->default(false);
            $table->date('enrollment_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
