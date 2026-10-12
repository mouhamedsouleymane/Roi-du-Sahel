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
        Schema::table('pre_enrollments', function (Blueprint $table) {
            $table->string('blood_group', 10)->nullable()->after('gender');
            $table->string('previous_school', 191)->nullable()->after('blood_group');
            $table->text('medical_notes')->nullable()->after('previous_school');
            $table->string('guardian_phone_secondary', 50)->nullable()->after('guardian_phone');
            $table->string('guardian_address', 191)->nullable()->after('guardian_city');
            $table->string('type', 50)->default('NOUVEAU')->after('requested_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'blood_group',
                'previous_school',
                'medical_notes',
                'guardian_phone_secondary',
                'guardian_address',
                'type',
            ]);
        });
    }
};
