<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // saisi par
            $table->string('label', 200);           // intitulé de la dépense
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('payment_method', 30)->default('ESPECES'); // ESPECES, VIREMENT, CHEQUE, MOBILE_MONEY
            $table->string('supplier', 150)->nullable();  // fournisseur / bénéficiaire
            $table->text('notes')->nullable();
            $table->string('receipt_path', 255)->nullable(); // justificatif scanné
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
