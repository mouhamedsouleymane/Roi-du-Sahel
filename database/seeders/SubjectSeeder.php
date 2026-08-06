<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['code' => 'MATH', 'name' => 'Mathématiques', 'category' => 'scientifique'],
            ['code' => 'FRAN', 'name' => 'Français', 'category' => 'littéraire'],
            ['code' => 'PC', 'name' => 'Physique - Chimie', 'category' => 'scientifique'],
            ['code' => 'SVT', 'name' => 'Sciences de la Vie et de la Terre (SVT)', 'category' => 'scientifique'],
            ['code' => 'HIST_GEO', 'name' => 'Histoire - Géographie', 'category' => 'littéraire'],
            ['code' => 'ANG', 'name' => 'Anglais', 'category' => 'langues'],
            ['code' => 'PHIL', 'name' => 'Philosophie', 'category' => 'littéraire'],
            ['code' => 'ECM', 'name' => 'Éducation Civique et Morale (ECM)', 'category' => 'littéraire'],
            ['code' => 'EPS', 'name' => 'Éducation Physique et Sportive (EPS)', 'category' => 'sport'],
            ['code' => 'INFO', 'name' => 'Informatique & Technologies', 'category' => 'scientifique'],
            ['code' => 'ARABE', 'name' => 'Langue Arabe', 'category' => 'langues'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['code' => $subject['code']],
                $subject
            );
        }
    }
}
