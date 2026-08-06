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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('matricule', 50)->unique(); // RS-2025-XXXX
            $table->string('first_name', 125);
            $table->string('last_name', 125);
            $table->enum('gender', ['M', 'F']);
            $table->date('birth_date');
            $table->string('birth_place', 125);
            $table->string('blood_group', 10)->nullable();
            $table->string('previous_school', 191)->nullable();
            $table->text('medical_notes')->nullable();
            $table->string('photo_path', 191)->nullable();
            $table->string('status', 50)->default('ACTIF');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
