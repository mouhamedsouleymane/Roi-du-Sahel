<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return;
        }

        $activeYear->generatePeriods();
    }
}
