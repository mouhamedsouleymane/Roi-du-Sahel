<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
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

        $classList = [
            'PS' => ['Petite Section A'],
            'MS' => ['Moyenne Section A'],
            'GS' => ['Grande Section A'],
            'CI' => ['CI A', 'CI B'],
            'CP' => ['CP A', 'CP B'],
            'CE1' => ['CE1 A'],
            'CE2' => ['CE2 A'],
            'CM1' => ['CM1 A'],
            'CM2' => ['CM2 A'],
            '6EME' => ['6ème A', '6ème B'],
            '5EME' => ['5ème A', '5ème B'],
            '4EME' => ['4ème A', '4ème B'],
            '3EME' => ['3ème A', '3ème B'],
            '2NDE_C' => ['Seconde C1'],
            '2NDE_A' => ['Seconde A1'],
            '1ERE_D' => ['Première D1'],
            '1ERE_C' => ['Première C1'],
            'TLE_D' => ['Terminale D1'],
            'TLE_A' => ['Terminale A1'],
        ];

        foreach ($classList as $levelCode => $names) {
            $level = Level::where('code', $levelCode)->first();
            if ($level) {
                foreach ($names as $name) {
                    SchoolClass::firstOrCreate(
                        [
                            'academic_year_id' => $activeYear->id,
                            'level_id' => $level->id,
                            'name' => $name,
                        ],
                        [
                            'capacity' => 45,
                            'room_number' => 'Salle '.rand(1, 20),
                        ]
                    );
                }
            }
        }
    }
}
