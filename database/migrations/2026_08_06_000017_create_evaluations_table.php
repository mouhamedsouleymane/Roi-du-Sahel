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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('periods')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('title', 125);              // ex: DS1 Maths, Compo Français
            $table->enum('type', ['DEVOIR', 'COMPOSITION', 'CONTROLE', 'EXAMEN'])->default('DEVOIR');
            $table->decimal('max_score', 5, 2)->default(20.00); // Note max (20 par défaut)
            $table->decimal('coefficient', 4, 2)->default(1.00); // Coefficient de l'éval
            $table->date('evaluation_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
