<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Services\EnrollmentService;
use Illuminate\Database\Seeder;

class StudentAndGuardianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enrollmentService = new EnrollmentService;

        // Récupérer les classes par nom
        $classes = SchoolClass::all()->keyBy('name');

        // Données réalistes du Niger : noms haoussa, zarma, peul, touareg, kanouri
        $studentsData = [
            // ── Maternelle ──────────────────────────────────────────────
            ['student' => ['first_name' => 'Aïcha', 'last_name' => 'Souley', 'gender' => 'F', 'birth_date' => '2021-03-12', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Souley', 'guardian_last_name' => 'Oumarou', 'relationship' => 'PERE', 'phone_primary' => '+227 90 11 22 33', 'address' => 'Koubia Plateau, Niamey'],
                'class_id' => $classes['Petite Section A']?->id],
            ['student' => ['first_name' => 'Moussa', 'last_name' => 'Ibrahim', 'gender' => 'M', 'birth_date' => '2020-07-25', 'birth_place' => 'Niamey', 'blood_group' => 'A+'],
                'guardian' => ['guardian_first_name' => 'Ibrahim', 'guardian_last_name' => 'Moussa', 'relationship' => 'PERE', 'phone_primary' => '+227 96 33 44 55', 'address' => 'Yantala, Niamey'],
                'class_id' => $classes['Petite Section A']?->id],
            ['student' => ['first_name' => 'Fatouma', 'last_name' => 'Amadou', 'gender' => 'F', 'birth_date' => '2020-11-08', 'birth_place' => 'Niamey', 'blood_group' => 'B+'],
                'guardian' => ['guardian_first_name' => 'Amadou', 'guardian_last_name' => 'Harouna', 'relationship' => 'PERE', 'phone_primary' => '+227 91 55 66 77', 'address' => 'Banifandou, Niamey'],
                'class_id' => $classes['Moyenne Section A']?->id],
            ['student' => ['first_name' => 'Ramatou', 'last_name' => 'Garba', 'gender' => 'F', 'birth_date' => '2019-05-30', 'birth_place' => 'Niamey', 'blood_group' => 'O-'],
                'guardian' => ['guardian_first_name' => 'Garba', 'guardian_last_name' => 'Sani', 'relationship' => 'PERE', 'phone_primary' => '+227 97 88 99 00', 'address' => 'Karadjé, Niamey'],
                'class_id' => $classes['Grande Section A']?->id],
            ['student' => ['first_name' => 'Abdoulaye', 'last_name' => 'Mahamadou', 'gender' => 'M', 'birth_date' => '2019-01-15', 'birth_place' => 'Niamey', 'blood_group' => 'AB+'],
                'guardian' => ['guardian_first_name' => 'Mahamadou', 'guardian_last_name' => 'Abdou', 'relationship' => 'PERE', 'phone_primary' => '+227 90 44 55 66', 'address' => 'Gamkalley, Niamey'],
                'class_id' => $classes['Grande Section A']?->id],

            // ── Primaire ────────────────────────────────────────────────
            ['student' => ['first_name' => 'Hassane', 'last_name' => 'Moussa', 'gender' => 'M', 'birth_date' => '2018-02-20', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Moussa', 'guardian_last_name' => 'Hassane', 'relationship' => 'PERE', 'phone_primary' => '+227 96 12 34 56', 'address' => 'Koubia Plateau, Niamey'],
                'class_id' => $classes['CI A']?->id],
            ['student' => ['first_name' => 'Aminatou', 'last_name' => 'Ousmane', 'gender' => 'F', 'birth_date' => '2018-06-10', 'birth_place' => 'Niamey', 'blood_group' => 'A+'],
                'guardian' => ['guardian_first_name' => 'Ousmane', 'guardian_last_name' => 'Souleymane', 'relationship' => 'PERE', 'phone_primary' => '+227 91 23 45 67', 'address' => 'Yantala, Niamey'],
                'class_id' => $classes['CI A']?->id],
            ['student' => ['first_name' => 'Ibrahim', 'last_name' => 'Sani', 'gender' => 'M', 'birth_date' => '2017-09-05', 'birth_place' => 'Niamey', 'blood_group' => 'B+'],
                'guardian' => ['guardian_first_name' => 'Sani', 'guardian_last_name' => 'Ibrahim', 'relationship' => 'PERE', 'phone_primary' => '+227 97 34 56 78', 'address' => 'Banifandou, Niamey'],
                'class_id' => $classes['CP A']?->id],
            ['student' => ['first_name' => 'Mariama', 'last_name' => 'Abdou', 'gender' => 'F', 'birth_date' => '2017-03-18', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Abdou', 'guardian_last_name' => 'Rabiou', 'relationship' => 'PERE', 'phone_primary' => '+227 90 45 67 89', 'address' => 'Karadjé, Niamey'],
                'class_id' => $classes['CP A']?->id],
            ['student' => ['first_name' => 'Souleymane', 'last_name' => 'Oumarou', 'gender' => 'M', 'birth_date' => '2016-11-22', 'birth_place' => 'Niamey', 'blood_group' => 'A-'],
                'guardian' => ['guardian_first_name' => 'Oumarou', 'guardian_last_name' => 'Souley', 'relationship' => 'PERE', 'phone_primary' => '+227 96 56 78 90', 'address' => 'Gamkalley, Niamey'],
                'class_id' => $classes['CE1 A']?->id],
            ['student' => ['first_name' => 'Hadiza', 'last_name' => 'Boubacar', 'gender' => 'F', 'birth_date' => '2016-04-14', 'birth_place' => 'Niamey', 'blood_group' => 'B-'],
                'guardian' => ['guardian_first_name' => 'Boubacar', 'guardian_last_name' => 'Mahamadou', 'relationship' => 'PERE', 'phone_primary' => '+227 91 67 89 01', 'address' => 'Koubia Plateau, Niamey'],
                'class_id' => $classes['CE1 A']?->id],
            ['student' => ['first_name' => 'Mahamadou', 'last_name' => 'Issoufou', 'gender' => 'M', 'birth_date' => '2015-08-01', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Issoufou', 'guardian_last_name' => 'Garba', 'relationship' => 'PERE', 'phone_primary' => '+227 97 78 90 12', 'address' => 'Yantala, Niamey'],
                'class_id' => $classes['CE2 A']?->id],
            ['student' => ['first_name' => 'Zeinabou', 'last_name' => 'Seydou', 'gender' => 'F', 'birth_date' => '2015-01-25', 'birth_place' => 'Niamey', 'blood_group' => 'A+'],
                'guardian' => ['guardian_first_name' => 'Seydou', 'guardian_last_name' => 'Amadou', 'relationship' => 'PERE', 'phone_primary' => '+227 90 89 01 23', 'address' => 'Banifandou, Niamey'],
                'class_id' => $classes['CE2 A']?->id],
            ['student' => ['first_name' => 'Boubacar', 'last_name' => 'Hama', 'gender' => 'M', 'birth_date' => '2014-06-12', 'birth_place' => 'Niamey', 'blood_group' => 'B+'],
                'guardian' => ['guardian_first_name' => 'Hama', 'guardian_last_name' => 'Moussa', 'relationship' => 'PERE', 'phone_primary' => '+227 96 90 12 34', 'address' => 'Karadjé, Niamey'],
                'class_id' => $classes['CM1 A']?->id],
            ['student' => ['first_name' => 'Rahinatou', 'last_name' => 'Alzouma', 'gender' => 'F', 'birth_date' => '2014-02-28', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Alzouma', 'guardian_last_name' => 'Souley', 'relationship' => 'PERE', 'phone_primary' => '+227 91 01 23 45', 'address' => 'Gamkalley, Niamey'],
                'class_id' => $classes['CM1 A']?->id],
            ['student' => ['first_name' => 'Moussa', 'last_name' => 'Abdoulaye', 'gender' => 'M', 'birth_date' => '2013-10-15', 'birth_place' => 'Niamey', 'blood_group' => 'A+'],
                'guardian' => ['guardian_first_name' => 'Abdoulaye', 'guardian_last_name' => 'Ibrahim', 'relationship' => 'PERE', 'phone_primary' => '+227 97 12 34 56', 'address' => 'Koubia Plateau, Niamey'],
                'class_id' => $classes['CM2 A']?->id],
            ['student' => ['first_name' => 'Fati', 'last_name' => 'Amadou', 'gender' => 'F', 'birth_date' => '2013-05-20', 'birth_place' => 'Niamey', 'blood_group' => 'B+'],
                'guardian' => ['guardian_first_name' => 'Amadou', 'guardian_last_name' => 'Harouna', 'relationship' => 'PERE', 'phone_primary' => '+227 90 23 45 67', 'address' => 'Yantala, Niamey'],
                'class_id' => $classes['CM2 A']?->id],

            // ── Collège ─────────────────────────────────────────────────
            ['student' => ['first_name' => 'Ibrahim', 'last_name' => 'Oumarou', 'gender' => 'M', 'birth_date' => '2012-05-14', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Oumarou', 'guardian_last_name' => 'Souley', 'relationship' => 'PERE', 'phone_primary' => '+227 96 34 56 78', 'address' => 'Koubia Plateau, Niamey'],
                'class_id' => $classes['6ème A']?->id],
            ['student' => ['first_name' => 'Salamatou', 'last_name' => 'Moussa', 'gender' => 'F', 'birth_date' => '2012-09-03', 'birth_place' => 'Niamey', 'blood_group' => 'A+'],
                'guardian' => ['guardian_first_name' => 'Moussa', 'guardian_last_name' => 'Souley', 'relationship' => 'PERE', 'phone_primary' => '+227 91 45 67 89', 'address' => 'Banifandou, Niamey'],
                'class_id' => $classes['6ème A']?->id],
            ['student' => ['first_name' => 'Abdou', 'last_name' => 'Rabiou', 'gender' => 'M', 'birth_date' => '2012-01-30', 'birth_place' => 'Niamey', 'blood_group' => 'B+'],
                'guardian' => ['guardian_first_name' => 'Rabiou', 'guardian_last_name' => 'Abdou', 'relationship' => 'PERE', 'phone_primary' => '+227 97 56 78 90', 'address' => 'Karadjé, Niamey'],
                'class_id' => $classes['6ème B']?->id],
            ['student' => ['first_name' => 'Aïssatou', 'last_name' => 'Garba', 'gender' => 'F', 'birth_date' => '2011-07-22', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Garba', 'guardian_last_name' => 'Sani', 'relationship' => 'PERE', 'phone_primary' => '+227 90 67 89 01', 'address' => 'Gamkalley, Niamey'],
                'class_id' => $classes['5ème A']?->id],
            ['student' => ['first_name' => 'Sani', 'last_name' => 'Boubacar', 'gender' => 'M', 'birth_date' => '2011-03-11', 'birth_place' => 'Niamey', 'blood_group' => 'A-'],
                'guardian' => ['guardian_first_name' => 'Boubacar', 'guardian_last_name' => 'Mahamadou', 'relationship' => 'PERE', 'phone_primary' => '+227 96 78 90 12', 'address' => 'Koubia Plateau, Niamey'],
                'class_id' => $classes['5ème A']?->id],
            ['student' => ['first_name' => 'Hassana', 'last_name' => 'Issoufou', 'gender' => 'F', 'birth_date' => '2010-10-05', 'birth_place' => 'Niamey', 'blood_group' => 'B+'],
                'guardian' => ['guardian_first_name' => 'Issoufou', 'guardian_last_name' => 'Garba', 'relationship' => 'PERE', 'phone_primary' => '+227 91 89 01 23', 'address' => 'Yantala, Niamey'],
                'class_id' => $classes['4ème A']?->id],
            ['student' => ['first_name' => 'Oumarou', 'last_name' => 'Seydou', 'gender' => 'M', 'birth_date' => '2010-04-18', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Seydou', 'guardian_last_name' => 'Amadou', 'relationship' => 'PERE', 'phone_primary' => '+227 97 90 12 34', 'address' => 'Banifandou, Niamey'],
                'class_id' => $classes['4ème A']?->id],
            ['student' => ['first_name' => 'Ramatou', 'last_name' => 'Hama', 'gender' => 'F', 'birth_date' => '2009-08-14', 'birth_place' => 'Niamey', 'blood_group' => 'A+'],
                'guardian' => ['guardian_first_name' => 'Hama', 'guardian_last_name' => 'Moussa', 'relationship' => 'PERE', 'phone_primary' => '+227 90 01 23 45', 'address' => 'Karadjé, Niamey'],
                'class_id' => $classes['3ème A']?->id],
            ['student' => ['first_name' => 'Mahamane', 'last_name' => 'Alzouma', 'gender' => 'M', 'birth_date' => '2009-02-25', 'birth_place' => 'Niamey', 'blood_group' => 'B-'],
                'guardian' => ['guardian_first_name' => 'Alzouma', 'guardian_last_name' => 'Souley', 'relationship' => 'PERE', 'phone_primary' => '+227 96 12 34 56', 'address' => 'Gamkalley, Niamey'],
                'class_id' => $classes['3ème A']?->id],

            // ── Lycée ───────────────────────────────────────────────────
            ['student' => ['first_name' => 'Fati', 'last_name' => 'Amadou', 'gender' => 'F', 'birth_date' => '2008-11-20', 'birth_place' => 'Maradi', 'blood_group' => 'A+'],
                'guardian' => ['guardian_first_name' => 'Amadou', 'guardian_last_name' => 'Harouna', 'relationship' => 'PERE', 'phone_primary' => '+227 96 44 55 66', 'address' => 'Plateau, Niamey'],
                'class_id' => $classes['Seconde C1']?->id],
            ['student' => ['first_name' => 'Moussa', 'last_name' => 'Ibrahim', 'gender' => 'M', 'birth_date' => '2008-06-15', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Ibrahim', 'guardian_last_name' => 'Moussa', 'relationship' => 'PERE', 'phone_primary' => '+227 91 33 44 55', 'address' => 'Koubia Plateau, Niamey'],
                'class_id' => $classes['Seconde C1']?->id],
            ['student' => ['first_name' => 'Aïcha', 'last_name' => 'Souley', 'gender' => 'F', 'birth_date' => '2007-09-10', 'birth_place' => 'Niamey', 'blood_group' => 'B+'],
                'guardian' => ['guardian_first_name' => 'Souley', 'guardian_last_name' => 'Oumarou', 'relationship' => 'PERE', 'phone_primary' => '+227 97 55 66 77', 'address' => 'Yantala, Niamey'],
                'class_id' => $classes['Première D1']?->id],
            ['student' => ['first_name' => 'Abdoulaye', 'last_name' => 'Mahamadou', 'gender' => 'M', 'birth_date' => '2007-03-28', 'birth_place' => 'Niamey', 'blood_group' => 'A+'],
                'guardian' => ['guardian_first_name' => 'Mahamadou', 'guardian_last_name' => 'Abdou', 'relationship' => 'PERE', 'phone_primary' => '+227 90 66 77 88', 'address' => 'Banifandou, Niamey'],
                'class_id' => $classes['Première D1']?->id],
            ['student' => ['first_name' => 'Hadiza', 'last_name' => 'Boubacar', 'gender' => 'F', 'birth_date' => '2006-12-05', 'birth_place' => 'Niamey', 'blood_group' => 'O+'],
                'guardian' => ['guardian_first_name' => 'Boubacar', 'guardian_last_name' => 'Mahamadou', 'relationship' => 'PERE', 'phone_primary' => '+227 96 77 88 99', 'address' => 'Karadjé, Niamey'],
                'class_id' => $classes['Terminale D1']?->id],
            ['student' => ['first_name' => 'Ibrahim', 'last_name' => 'Oumarou', 'gender' => 'M', 'birth_date' => '2006-05-19', 'birth_place' => 'Zinder', 'blood_group' => 'B+'],
                'guardian' => ['guardian_first_name' => 'Aïchatou', 'guardian_last_name' => 'Abdou', 'relationship' => 'MERE', 'phone_primary' => '+227 91 77 88 99', 'address' => 'Koubia, Niamey'],
                'class_id' => $classes['Terminale D1']?->id],
        ];

        foreach ($studentsData as $data) {
            if ($data['class_id']) {
                try {
                    $enrollmentService->enrollNewStudent(
                        studentData: $data['student'],
                        guardianData: $data['guardian'],
                        classId: $data['class_id'],
                        type: 'NOUVEAU'
                    );
                } catch (\Exception $e) {
                    // Ignore if already seeded
                }
            }
        }
    }
}
