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
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('period_id')->constrained('periods')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->decimal('general_average', 5, 2)->nullable(); // Moyenne générale calculée
            $table->unsignedSmallInteger('rank')->nullable();      // Rang de l'élève dans la classe
            $table->unsignedSmallInteger('total_students')->nullable(); // Total élèves dans la classe
            $table->string('appreciation', 250)->nullable();          // Mention / Appréciation du CP
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'period_id'], 'report_student_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
