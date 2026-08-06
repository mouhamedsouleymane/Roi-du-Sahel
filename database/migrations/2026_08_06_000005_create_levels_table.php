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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('cycles')->onDelete('cascade');
            $table->string('code', 50); // PS, MS, GS, CI, CP, 6EME, TLE_D, etc.
            $table->string('name', 125);
            $table->integer('order_index')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['cycle_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
