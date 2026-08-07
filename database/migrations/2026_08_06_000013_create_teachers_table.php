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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('matricule', 50)->unique(); // ENS-2025-XXXX
            $table->string('speciality', 125);
            $table->string('qualification', 125)->nullable(); // Licence, Master, CAPES
            $table->enum('employment_type', ['PERMANENT', 'VACATAIRE', 'CONTRACTUEL'])->default('PERMANENT');
            $table->date('hire_date')->nullable();
            $table->string('status', 50)->default('ACTIF');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
