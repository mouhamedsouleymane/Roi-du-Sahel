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

        // ── Primaire (CI à CM2) ─────────────────────────────────────────
        $primaireLevels = Level::whereIn('code', ['CI', 'CP', 'CE1', 'CE2', 'CM1', 'CM2'])->get();
        foreach ($primaireLevels as $level) {
            $matrix = [
                'FRAN' => ['coef' => 5, 'hours' => 8],
                'MATH' => ['coef' => 5, 'hours' => 6],
                'HIST_GEO' => ['coef' => 2, 'hours' => 3],
                'ECM' => ['coef' => 1, 'hours' => 1],
                'EPS' => ['coef' => 1, 'hours' => 2],
                'ANG' => ['coef' => 1, 'hours' => 2],
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

        // ── Collège (6ème à 3ème) ───────────────────────────────────────
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

        // ── Lycée Seconde C ─────────────────────────────────────────────
        $secondeC = Level::where('code', '2NDE_C')->first();
        if ($secondeC) {
            $matrix = [
                'MATH' => ['coef' => 4, 'hours' => 5],
                'PC' => ['coef' => 3, 'hours' => 4],
                'SVT' => ['coef' => 2, 'hours' => 3],
                'FRAN' => ['coef' => 3, 'hours' => 4],
                'ANG' => ['coef' => 2, 'hours' => 3],
                'HIST_GEO' => ['coef' => 2, 'hours' => 3],
                'INFO' => ['coef' => 1, 'hours' => 2],
                'EPS' => ['coef' => 1, 'hours' => 2],
            ];

            foreach ($matrix as $code => $data) {
                if (isset($subjects[$code])) {
                    ClassSubjectCoefficient::firstOrCreate(
                        ['level_id' => $secondeC->id, 'subject_id' => $subjects[$code]->id],
                        ['coefficient' => $data['coef'], 'weekly_hours' => $data['hours']]
                    );
                }
            }
        }

        // ── Lycée Seconde A ─────────────────────────────────────────────
        $secondeA = Level::where('code', '2NDE_A')->first();
        if ($secondeA) {
            $matrix = [
                'FRAN' => ['coef' => 4, 'hours' => 5],
                'MATH' => ['coef' => 3, 'hours' => 4],
                'HIST_GEO' => ['coef' => 3, 'hours' => 4],
                'ANG' => ['coef' => 2, 'hours' => 3],
                'PHIL' => ['coef' => 2, 'hours' => 3],
                'ARABE' => ['coef' => 2, 'hours' => 3],
                'INFO' => ['coef' => 1, 'hours' => 2],
                'EPS' => ['coef' => 1, 'hours' => 2],
            ];

            foreach ($matrix as $code => $data) {
                if (isset($subjects[$code])) {
                    ClassSubjectCoefficient::firstOrCreate(
                        ['level_id' => $secondeA->id, 'subject_id' => $subjects[$code]->id],
                        ['coefficient' => $data['coef'], 'weekly_hours' => $data['hours']]
                    );
                }
            }
        }

        // ── Lycée Première D ────────────────────────────────────────────
        $premiereD = Level::where('code', '1ERE_D')->first();
        if ($premiereD) {
            $matrix = [
                'MATH' => ['coef' => 4, 'hours' => 5],
                'PC' => ['coef' => 3, 'hours' => 4],
                'SVT' => ['coef' => 4, 'hours' => 5],
                'FRAN' => ['coef' => 3, 'hours' => 4],
                'ANG' => ['coef' => 2, 'hours' => 3],
                'HIST_GEO' => ['coef' => 2, 'hours' => 3],
                'INFO' => ['coef' => 1, 'hours' => 2],
                'EPS' => ['coef' => 1, 'hours' => 2],
            ];

            foreach ($matrix as $code => $data) {
                if (isset($subjects[$code])) {
                    ClassSubjectCoefficient::firstOrCreate(
                        ['level_id' => $premiereD->id, 'subject_id' => $subjects[$code]->id],
                        ['coefficient' => $data['coef'], 'weekly_hours' => $data['hours']]
                    );
                }
            }
        }

        // ── Lycée Première C ────────────────────────────────────────────
        $premiereC = Level::where('code', '1ERE_C')->first();
        if ($premiereC) {
            $matrix = [
                'MATH' => ['coef' => 5, 'hours' => 6],
                'PC' => ['coef' => 4, 'hours' => 5],
                'SVT' => ['coef' => 2, 'hours' => 3],
                'FRAN' => ['coef' => 3, 'hours' => 4],
                'ANG' => ['coef' => 2, 'hours' => 3],
                'HIST_GEO' => ['coef' => 2, 'hours' => 3],
                'INFO' => ['coef' => 1, 'hours' => 2],
                'EPS' => ['coef' => 1, 'hours' => 2],
            ];

            foreach ($matrix as $code => $data) {
                if (isset($subjects[$code])) {
                    ClassSubjectCoefficient::firstOrCreate(
                        ['level_id' => $premiereC->id, 'subject_id' => $subjects[$code]->id],
                        ['coefficient' => $data['coef'], 'weekly_hours' => $data['hours']]
                    );
                }
            }
        }

        // ── Lycée Terminale D ───────────────────────────────────────────
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

        // ── Lycée Terminale A ───────────────────────────────────────────
        $tleA = Level::where('code', 'TLE_A')->first();
        if ($tleA) {
            $matrix = [
                'FRAN' => ['coef' => 4, 'hours' => 5],
                'PHIL' => ['coef' => 4, 'hours' => 5],
                'MATH' => ['coef' => 2, 'hours' => 3],
                'HIST_GEO' => ['coef' => 3, 'hours' => 4],
                'ANG' => ['coef' => 2, 'hours' => 3],
                'ARABE' => ['coef' => 2, 'hours' => 3],
                'INFO' => ['coef' => 1, 'hours' => 2],
                'EPS' => ['coef' => 1, 'hours' => 2],
            ];

            foreach ($matrix as $code => $data) {
                if (isset($subjects[$code])) {
                    ClassSubjectCoefficient::firstOrCreate(
                        ['level_id' => $tleA->id, 'subject_id' => $subjects[$code]->id],
                        ['coefficient' => $data['coef'], 'weekly_hours' => $data['hours']]
                    );
                }
            }
        }
    }
}
