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
        Schema::table('report_cards', function (Blueprint $table) {
            $table->decimal('class_average', 5, 2)->nullable()->after('total_students');
            $table->decimal('max_average', 5, 2)->nullable()->after('class_average');
            $table->decimal('min_average', 5, 2)->nullable()->after('max_average');
            $table->decimal('literary_average', 5, 2)->nullable()->after('min_average');
            $table->decimal('scientific_average', 5, 2)->nullable()->after('literary_average');
            $table->decimal('conduct_grade', 5, 2)->nullable()->after('scientific_average');
            $table->json('details')->nullable()->after('conduct_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropColumn([
                'class_average',
                'max_average',
                'min_average',
                'literary_average',
                'scientific_average',
                'conduct_grade',
                'details',
            ]);
        });
    }
};
