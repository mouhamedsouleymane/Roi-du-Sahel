<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds two columns to teachers:
     *  - cycle_id       : the cycle this teacher belongs to (required)
     *  - is_polyvalent  : true for Maternelle/Primaire teachers who teach all
     *                     subjects in their assigned class
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('cycle_id')
                ->nullable()
                ->after('user_id')
                ->constrained('cycles')
                ->nullOnDelete();

            $table->boolean('is_polyvalent')
                ->default(false)
                ->after('status')
                ->comment('True for Maternelle/Primaire: one teacher, all subjects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['cycle_id']);
            $table->dropColumn(['cycle_id', 'is_polyvalent']);
        });
    }
};
