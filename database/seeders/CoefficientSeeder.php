<?php

namespace Database\Seeders;

use App\Models\ClassSubjectCoefficient;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class CoefficientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subject::all()->keyBy('code');

        // Collège default coefficients
        $collegeLevels = Level::whereIn('code', ['6EME', '5EME', '4EME', '3EME'])->get();
        foreach ($collegeLevels as $level) {
            $matrix = [
                'MATH' => ['coef' => 4, 'hours' => 5],
                'FRAN' => ['coef' => 4, 'hours' => 5],
                'PC' => ['coef' => 2, 'hours' => 3],
                'SVT' => ['coef' => 2, 'hours' => 2],
                'HIST_GEO' => ['coef' => 2, 'hours' => 3],
                'ANG' => ['coef' => 2, 'hours' => 3],
                'EPS' => ['coef' => 1, 'hours' => 2],
                'ECM' => ['coef' => 1, 'hours' => 1],
            ];

            foreach ($matrix as $code => $data) {
                if (isset($subjects[$code])) {
                    ClassSubjectCoefficient::firstOrCreate(
                        ['level_id' => $level->id, 'subject_id' => $subjects[$code]->id],
                        ['coefficient' => $data['coef'], 'weekly_hours' => $data['hours']]
                    );
                }
            }
        }

        // Lycée Terminale D coefficients
        $tleD = Level::where('code', 'TLE_D')->first();
        if ($tleD) {
            $matrix = [
                'MATH' => ['coef' => 4, 'hours' => 5],
                'PC' => ['coef' => 4, 'hours' => 5],
                'SVT' => ['coef' => 5, 'hours' => 6],
                'FRAN' => ['coef' => 2, 'hours' => 3],
                'PHIL' => ['coef' => 2, 'hours' => 3],
                'ANG' => ['coef' => 2, 'hours' => 3],
                'HIST_GEO' => ['coef' => 2, 'hours' => 2],
                'EPS' => ['coef' => 1, 'hours' => 2],
            ];

            foreach ($matrix as $code => $data) {
                if (isset($subjects[$code])) {
                    ClassSubjectCoefficient::firstOrCreate(
                        ['level_id' => $tleD->id, 'subject_id' => $subjects[$code]->id],
                        ['coefficient' => $data['coef'], 'weekly_hours' => $data['hours']]
                    );
                }
            }
        }
    }
}
