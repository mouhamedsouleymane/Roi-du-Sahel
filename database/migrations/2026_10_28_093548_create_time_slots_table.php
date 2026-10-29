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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('label');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('cycle_id')->nullable()->constrained('cycles')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_pause')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('color', 30)->nullable();
            $table->timestamps();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('time_slot_id')->nullable()->after('end_time')->constrained('time_slots')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['time_slot_id']);
            $table->dropColumn('time_slot_id');
        });

        Schema::dropIfExists('time_slots');
    }
};

