<?php

namespace Database\Seeders;

use App\Models\Cycle;
use App\Models\Level;
use Illuminate\Database\Seeder;

class CycleAndLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cyclesData = [
            [
                'code' => 'MATERNELLE',
                'name' => 'Maternelle',
                'uniform_tshirt_color' => 'Violet',
                'start_time' => '08:00',
                'end_time' => '14:30',
                'description' => 'Cycle d\'éveil et de pré-scolarisation (T-shirt violet)',
                'levels' => [
                    ['code' => 'PS', 'name' => 'Petite Section', 'order_index' => 1],
                    ['code' => 'MS', 'name' => 'Moyenne Section', 'order_index' => 2],
                    ['code' => 'GS', 'name' => 'Grande Section', 'order_index' => 3],
                ],
            ],
            [
                'code' => 'PRIMAIRE',
                'name' => 'Primaire',
                'uniform_tshirt_color' => 'Violet',
                'start_time' => '08:00',
                'end_time' => '14:30',
                'description' => 'Enseignement fondamental primaire (T-shirt violet)',
                'levels' => [
                    ['code' => 'CI', 'name' => 'Cours d\'Initiation (CI)', 'order_index' => 4],
                    ['code' => 'CP', 'name' => 'Cours Préparatoire (CP)', 'order_index' => 5],
                    ['code' => 'CE1', 'name' => 'Cours Élémentaire 1ère année (CE1)', 'order_index' => 6],
                    ['code' => 'CE2', 'name' => 'Cours Élémentaire 2ème année (CE2)', 'order_index' => 7],
                    ['code' => 'CM1', 'name' => 'Cours Moyen 1ère année (CM1)', 'order_index' => 8],
                    ['code' => 'CM2', 'name' => 'Cours Moyen 2ème année (CM2)', 'order_index' => 9],
                ],
            ],
            [
                'code' => 'COLLEGE',
                'name' => 'Collège',
                'uniform_tshirt_color' => 'Jaune',
                'start_time' => '08:00',
                'end_time' => '13:30',
                'description' => 'Enseignement secondaire premier cycle (T-shirt jaune)',
                'levels' => [
                    ['code' => '6EME', 'name' => 'Sixième (6ème)', 'order_index' => 10],
                    ['code' => '5EME', 'name' => 'Cinquième (5ème)', 'order_index' => 11],
                    ['code' => '4EME', 'name' => 'Quatrième (4ème)', 'order_index' => 12],
                    ['code' => '3EME', 'name' => 'Troisième (3ème)', 'order_index' => 13],
                ],
            ],
            [
                'code' => 'LYCEE',
                'name' => 'Lycée',
                'uniform_tshirt_color' => 'Jaune',
                'start_time' => '08:00',
                'end_time' => '13:30',
                'description' => 'Enseignement secondaire second cycle / Séries A, C, D (T-shirt jaune)',
                'levels' => [
                    ['code' => '2NDE_A', 'name' => 'Seconde A (Littéraire)', 'order_index' => 14],
                    ['code' => '2NDE_C', 'name' => 'Seconde C (Scientifique)', 'order_index' => 15],
                    ['code' => '1ERE_A', 'name' => 'Première A (Littéraire)', 'order_index' => 16],
                    ['code' => '1ERE_C', 'name' => 'Première C (Maths/Physique)', 'order_index' => 17],
                    ['code' => '1ERE_D', 'name' => 'Première D (Sciences Naturelles)', 'order_index' => 18],
                    ['code' => 'TLE_A', 'name' => 'Terminale A (Littéraire)', 'order_index' => 19],
                    ['code' => 'TLE_C', 'name' => 'Terminale C (Maths/Physique)', 'order_index' => 20],
                    ['code' => 'TLE_D', 'name' => 'Terminale D (Sciences Naturelles)', 'order_index' => 21],
                ],
            ],
        ];

        foreach ($cyclesData as $cData) {
            $levels = $cData['levels'];
            unset($cData['levels']);

            $cycle = Cycle::firstOrCreate(['code' => $cData['code']], $cData);

            foreach ($levels as $lData) {
                Level::firstOrCreate(
                    ['cycle_id' => $cycle->id, 'code' => $lData['code']],
                    $lData
                );
            }
        }
    }
}
