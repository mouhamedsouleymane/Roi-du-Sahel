<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicYear::firstOrCreate(
            ['name' => '2025-2026'],
            [
                'start_date' => '2025-09-15',
                'end_date' => '2026-06-30',
                'is_active' => true,
                'is_closed' => false,
                'description' => 'Année scolaire en cours 2025-2026',
            ]
        );
    }
}
