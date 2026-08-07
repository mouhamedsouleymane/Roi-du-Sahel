<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachersData = [
            [
                'name' => 'Prof. Ali Oumarou',
                'email' => 'ali.oumarou@roisdusahel.ne',
                'matricule' => 'ENS-2025-0001',
                'speciality' => 'Mathématiques',
                'qualification' => 'Master 2 Mathématiques Pure',
                'employment_type' => 'PERMANENT',
            ],
            [
                'name' => 'Mme Amina Hassane',
                'email' => 'amina.hassane@roisdusahel.ne',
                'matricule' => 'ENS-2025-0002',
                'speciality' => 'Français',
                'qualification' => 'CAPES Lettres Modernes',
                'employment_type' => 'PERMANENT',
            ],
            [
                'name' => 'M. Boubacar Garba',
                'email' => 'boubacar.garba@roisdusahel.ne',
                'matricule' => 'ENS-2025-0003',
                'speciality' => 'Physique - Chimie',
                'qualification' => 'Licence Physique',
                'employment_type' => 'VACATAIRE',
            ],
        ];

        foreach ($teachersData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole('Enseignant');

            Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'matricule' => $data['matricule'],
                    'speciality' => $data['speciality'],
                    'qualification' => $data['qualification'],
                    'employment_type' => $data['employment_type'],
                    'hire_date' => '2025-09-01',
                    'status' => 'ACTIF',
                ]
            );
        }
    }
}
