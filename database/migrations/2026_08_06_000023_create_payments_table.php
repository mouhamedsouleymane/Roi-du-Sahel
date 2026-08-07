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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique(); // RECU-2025-00001
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['ESPECES', 'VIREMENT', 'CHEQUE', 'MOBILE_MONEY'])->default('ESPECES');
            $table->date('payment_date');
            $table->string('reference', 125)->nullable(); // Référence du virement/chèque/MoMo
            $table->string('recorded_by', 125)->nullable(); // Nom du caissier
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
