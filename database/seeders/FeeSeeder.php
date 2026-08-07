<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Level;
use Illuminate\Database\Seeder;

class FeeSeeder extends Seeder
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

        // ── Types de Frais ────────────────────────────────────────────────
        $feeTypes = [
            ['code' => 'INSCRIPT',   'name' => "Frais d'inscription",    'is_recurring' => false],
            ['code' => 'SCOLARITE',  'name' => 'Frais de scolarité',      'is_recurring' => true],
            ['code' => 'EXAMEN',     'name' => "Frais d'examen",          'is_recurring' => false],
            ['code' => 'CANTINE',    'name' => 'Frais de cantine',        'is_recurring' => true],
            ['code' => 'TRANSPORT',  'name' => 'Frais de transport',      'is_recurring' => true],
        ];

        foreach ($feeTypes as $data) {
            FeeType::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );
        }

        // ── Cycles ────────────────────────────────────────────────────────
        $maternelle = Cycle::where('code', 'MATERNELLE')->first();
        $primaire   = Cycle::where('code', 'PRIMAIRE')->first();
        $college    = Cycle::where('code', 'COLLEGE')->first();
        $lycee      = Cycle::where('code', 'LYCEE')->first();

        $inscript  = FeeType::where('code', 'INSCRIPT')->first();
        $scol      = FeeType::where('code', 'SCOLARITE')->first();
        $examen    = FeeType::where('code', 'EXAMEN')->first();

        // ── Grille Tarifaire par Cycle (FCFA) ─────────────────────────────
        $tarifs = [
            // [fee_type, cycle, level, amount]
            // Frais d'inscription
            [$inscript, $maternelle, null, 25000],
            [$inscript, $primaire,   null, 30000],
            [$inscript, $college,    null, 35000],
            [$inscript, $lycee,      null, 40000],

            // Frais de scolarité
            [$scol, $maternelle, null, 15000],
            [$scol, $primaire,   null, 20000],
            [$scol, $college,    null, 25000],
            [$scol, $lycee,      null, 30000],

            // Frais d'examen (global — tous cycles)
            [$examen, null, null, 10000],
        ];

        foreach ($tarifs as [$feeType, $cycle, $level, $amount]) {
            if (! $feeType) {
                continue;
            }

            FeeStructure::firstOrCreate(
                [
                    'fee_type_id'      => $feeType->id,
                    'academic_year_id' => $activeYear->id,
                    'cycle_id'         => $cycle?->id,
                    'level_id'         => $level?->id,
                ],
                ['amount' => $amount]
            );
        }
    }
}
