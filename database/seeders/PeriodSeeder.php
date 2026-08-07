<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Period;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return;
        }

        $periods = [
            [
                'name'       => 'Trimestre 1',
                'type'       => 'TRIMESTRE',
                'order'      => 1,
                'start_date' => '2025-09-15',
                'end_date'   => '2025-12-14',
                'is_closed'  => false,
            ],
            [
                'name'       => 'Trimestre 2',
                'type'       => 'TRIMESTRE',
                'order'      => 2,
                'start_date' => '2026-01-05',
                'end_date'   => '2026-03-29',
                'is_closed'  => false,
            ],
            [
                'name'       => 'Trimestre 3',
                'type'       => 'TRIMESTRE',
                'order'      => 3,
                'start_date' => '2026-04-06',
                'end_date'   => '2026-06-27',
                'is_closed'  => false,
            ],
        ];

        foreach ($periods as $data) {
            Period::firstOrCreate(
                [
                    'academic_year_id' => $activeYear->id,
                    'order'            => $data['order'],
                    'type'             => $data['type'],
                ],
                array_merge(['academic_year_id' => $activeYear->id], $data)
            );
        }
    }
}
