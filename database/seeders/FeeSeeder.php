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
            ['code' => 'INSCRIPT',      'name' => "Frais d'inscription",              'is_recurring' => false],
            ['code' => 'SCOLARITE',     'name' => 'Frais de scolarité (Annuel)',      'is_recurring' => true],
            ['code' => 'SCOLARITE_T1',  'name' => 'Scolarité — 1ère Tranche (50%)',   'is_recurring' => true],
            ['code' => 'SCOLARITE_T2',  'name' => 'Scolarité — 2ème Tranche (35%)',   'is_recurring' => true],
            ['code' => 'SCOLARITE_T3',  'name' => 'Scolarité — 3ème Tranche (15%)',   'is_recurring' => true],
            ['code' => 'EXAMEN',        'name' => "Frais d'examen",                    'is_recurring' => false],
            ['code' => 'CANTINE',       'name' => 'Frais de cantine',                  'is_recurring' => true],
            ['code' => 'TRANSPORT',     'name' => 'Frais de transport',                'is_recurring' => true],
        ];

        foreach ($feeTypes as $data) {
            FeeType::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );
        }

        // ── Cycles ────────────────────────────────────────────────────────
        $maternelle = Cycle::where('code', 'MATERNELLE')->first();
        $primaire = Cycle::where('code', 'PRIMAIRE')->first();
        $college = Cycle::where('code', 'COLLEGE')->first();
        $lycee = Cycle::where('code', 'LYCEE')->first();

        // ── Types de frais ────────────────────────────────────────────────
        $inscript = FeeType::where('code', 'INSCRIPT')->first();
        $scol = FeeType::where('code', 'SCOLARITE')->first();
        $scolT1 = FeeType::where('code', 'SCOLARITE_T1')->first();
        $scolT2 = FeeType::where('code', 'SCOLARITE_T2')->first();
        $scolT3 = FeeType::where('code', 'SCOLARITE_T3')->first();
        $examen = FeeType::where('code', 'EXAMEN')->first();

        /**
         * Grille tarifaire officielle (FCFA)
         * ─────────────────────────────────────────────────────────────────
         * INSCRIPTION
         *   Maternelle / Primaire  :   1 000 F
         *   Collège / Lycée        :   3 000 F
         *
         * SCOLARITÉ ANNUELLE
         *   Maternelle             :  68 000 F
         *   Primaire               :  68 000 F
         *   Collège 6ème-5ème      : 128 000 F
         *   Collège 4ème-3ème      : 144 000 F
         *   Lycée 2nde-1ère        : 168 000 F
         *   Lycée Terminale        : 192 000 F
         *
         * TRANCHES (automatique)
         *   T1 = 50% | T2 = 35% | T3 = 15%
         * ─────────────────────────────────────────────────────────────────
         */

        // ── Frais d'inscription par cycle ────────────────────────────────
        foreach ([
            [$maternelle,  1000],
            [$primaire,    1000],
            [$college,     3000],
            [$lycee,       3000],
        ] as [$cycle, $amount]) {
            if (! $inscript || ! $cycle) {
                continue;
            }

            FeeStructure::firstOrCreate(
                [
                    'fee_type_id' => $inscript->id,
                    'academic_year_id' => $activeYear->id,
                    'cycle_id' => $cycle->id,
                    'level_id' => null,
                ],
                ['amount' => $amount]
            );
        }

        // ── Scolarité annuelle + tranches T1/T2/T3 par cycle/niveau ───────
        // Format : [cycle, level_code_or_null, montant_annuel]
        // Maternelle/Primaire : tarif unique par cycle (level = null)
        // Collège/Lycée : tarif par niveau (niveaux différenciés)
        $scolariteTarifs = [
            [$maternelle, null,      68000],
            [$primaire,   null,      68000],
            [$college,    '6EME',   128000],
            [$college,    '5EME',   128000],
            [$college,    '4EME',   144000],
            [$college,    '3EME',   144000],
            [$lycee,      '2NDE_A', 168000],
            [$lycee,      '2NDE_C', 168000],
            [$lycee,      '1ERE_A', 168000],
            [$lycee,      '1ERE_C', 168000],
            [$lycee,      '1ERE_D', 168000],
            [$lycee,      'TLE_A',  192000],
            [$lycee,      'TLE_C',  192000],
            [$lycee,      'TLE_D',  192000],
        ];

        foreach ($scolariteTarifs as [$cycle, $levelCode, $annual]) {
            if (! $cycle || ! $scol) {
                continue;
            }

            $level = $levelCode ? Level::where('code', $levelCode)->first() : null;
            $cycleId = $cycle->id;
            $levelId = $level?->id;

            // Annuel brut
            FeeStructure::firstOrCreate(
                ['fee_type_id' => $scol->id,   'academic_year_id' => $activeYear->id, 'cycle_id' => $cycleId, 'level_id' => $levelId],
                ['amount' => $annual]
            );

            // Tranche 1 — 50%
            if ($scolT1) {
                FeeStructure::firstOrCreate(
                    ['fee_type_id' => $scolT1->id, 'academic_year_id' => $activeYear->id, 'cycle_id' => $cycleId, 'level_id' => $levelId],
                    ['amount' => (int) round($annual * 0.50)]
                );
            }

            // Tranche 2 — 35%
            if ($scolT2) {
                FeeStructure::firstOrCreate(
                    ['fee_type_id' => $scolT2->id, 'academic_year_id' => $activeYear->id, 'cycle_id' => $cycleId, 'level_id' => $levelId],
                    ['amount' => (int) round($annual * 0.35)]
                );
            }

            // Tranche 3 — 15%
            if ($scolT3) {
                FeeStructure::firstOrCreate(
                    ['fee_type_id' => $scolT3->id, 'academic_year_id' => $activeYear->id, 'cycle_id' => $cycleId, 'level_id' => $levelId],
                    ['amount' => (int) round($annual * 0.15)]
                );
            }
        }

        // ── Frais d'examen (global — tous cycles) ────────────────────────
        if ($examen) {
            FeeStructure::firstOrCreate(
                [
                    'fee_type_id' => $examen->id,
                    'academic_year_id' => $activeYear->id,
                    'cycle_id' => null,
                    'level_id' => null,
                ],
                ['amount' => 10000]
            );
        }
    }
}
