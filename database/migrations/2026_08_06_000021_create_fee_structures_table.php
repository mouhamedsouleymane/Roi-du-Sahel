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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained('fee_types')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('cycle_id')->nullable()->constrained('cycles')->onDelete('cascade'); // Optionnel : tarif par cycle
            $table->foreignId('level_id')->nullable()->constrained('levels')->onDelete('cascade'); // Optionnel : tarif par niveau
            $table->decimal('amount', 10, 2);              // Montant en FCFA
            $table->timestamps();

            $table->unique(
                ['fee_type_id', 'academic_year_id', 'cycle_id', 'level_id'],
                'fee_struct_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
