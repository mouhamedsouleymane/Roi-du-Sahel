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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('first_name', 125);
            $table->string('last_name', 125);
            $table->string('relationship', 50)->default('PERE'); // PERE, MERE, TUTEUR_LEGAL, ONCLE, TANTE
            $table->string('profession', 125)->nullable();
            $table->string('phone_primary', 50);
            $table->string('phone_secondary', 50)->nullable();
            $table->string('email', 125)->nullable();
            $table->string('address', 191)->nullable();
            $table->string('city', 125)->default('Niamey');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
