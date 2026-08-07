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
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();         // INSCRIPT, SCOLARITE, EXAMEN, CANTINE
            $table->string('name', 125);                  // Frais d'inscription
            $table->string('description', 250)->nullable();
            $table->boolean('is_recurring')->default(false); // Récurrent chaque trimestre
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
};
