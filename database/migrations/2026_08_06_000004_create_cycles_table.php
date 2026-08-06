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
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // MATERNELLE, PRIMAIRE, COLLEGE, LYCEE
            $table->string('name', 125);
            $table->string('uniform_tshirt_color', 125)->nullable(); // Violet, Jaune
            $table->string('start_time', 20)->default('08:00');
            $table->string('end_time', 20)->default('14:30');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycles');
    }
};
