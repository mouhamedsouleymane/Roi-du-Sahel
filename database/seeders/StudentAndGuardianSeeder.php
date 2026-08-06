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
        $enrollmentService = new EnrollmentService();

        $class6A = SchoolClass::where('name', '6ème A')->first();
        $classTleD = SchoolClass::where('name', 'Terminale D1')->first();
        $classCM2 = SchoolClass::where('name', 'CM2 A')->first();

        $studentsData = [
            [
                'student' => [
                    'first_name' => 'Souleymane',
                    'last_name' => 'Moussa',
                    'gender' => 'M',
                    'birth_date' => '2012-05-14',
                    'birth_place' => 'Niamey',
                    'blood_group' => 'O+',
                ],
                'guardian' => [
                    'guardian_first_name' => 'Moussa',
                    'guardian_last_name' => 'Souley',
                    'relationship' => 'PERE',
                    'phone_primary' => '+227 90 11 22 33',
                    'address' => 'Koubia Plateau, Niamey',
                ],
                'class_id' => $class6A?->id,
            ],
            [
                'student' => [
                    'first_name' => 'Fati',
                    'last_name' => 'Amadou',
                    'gender' => 'F',
                    'birth_date' => '2008-11-20',
                    'birth_place' => 'Maradi',
                    'blood_group' => 'A+',
                ],
                'guardian' => [
                    'guardian_first_name' => 'Amadou',
                    'guardian_last_name' => 'Harouna',
                    'relationship' => 'PERE',
                    'phone_primary' => '+227 96 44 55 66',
                    'address' => 'Plateau, Niamey',
                ],
                'class_id' => $classTleD?->id,
            ],
            [
                'student' => [
                    'first_name' => 'Ibrahim',
                    'last_name' => 'Oumarou',
                    'gender' => 'M',
                    'birth_date' => '2015-02-10',
                    'birth_place' => 'Zinder',
                    'blood_group' => 'B+',
                ],
                'guardian' => [
                    'guardian_first_name' => 'Aïchatou',
                    'guardian_last_name' => 'Abdou',
                    'relationship' => 'MERE',
                    'phone_primary' => '+227 91 77 88 99',
                    'address' => 'Koubia, Niamey',
                ],
                'class_id' => $classCM2?->id,
            ],
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
