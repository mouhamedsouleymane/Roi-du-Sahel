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
            // ── Mathématiques ───────────────────────────────────────────
            ['name' => 'Prof. Ali Oumarou', 'email' => 'ali.oumarou@roisdusahel.ne', 'matricule' => 'ENS-2025-0001', 'speciality' => 'Mathématiques', 'qualification' => 'Master 2 Mathématiques Pure', 'employment_type' => 'PERMANENT'],
            ['name' => 'M. Issoufou Garba', 'email' => 'issoufou.garba@roisdusahel.ne', 'matricule' => 'ENS-2025-0004', 'speciality' => 'Mathématiques', 'qualification' => 'Licence Mathématiques', 'employment_type' => 'VACATAIRE'],

            // ── Français ────────────────────────────────────────────────
            ['name' => 'Mme Amina Hassane', 'email' => 'amina.hassane@roisdusahel.ne', 'matricule' => 'ENS-2025-0002', 'speciality' => 'Français', 'qualification' => 'CAPES Lettres Modernes', 'employment_type' => 'PERMANENT'],
            ['name' => 'M. Boubacar Sani', 'email' => 'boubacar.sani@roisdusahel.ne', 'matricule' => 'ENS-2025-0005', 'speciality' => 'Français', 'qualification' => 'Maîtrise Lettres Modernes', 'employment_type' => 'CONTRACTUEL'],

            // ── Physique - Chimie ───────────────────────────────────────
            ['name' => 'M. Boubacar Garba', 'email' => 'boubacar.garba@roisdusahel.ne', 'matricule' => 'ENS-2025-0003', 'speciality' => 'Physique - Chimie', 'qualification' => 'Licence Physique', 'employment_type' => 'VACATAIRE'],
            ['name' => 'Mme Hadiza Moussa', 'email' => 'hadiza.moussa@roisdusahel.ne', 'matricule' => 'ENS-2025-0006', 'speciality' => 'Physique - Chimie', 'qualification' => 'Master Physique-Chimie', 'employment_type' => 'PERMANENT'],

            // ── SVT ─────────────────────────────────────────────────────
            ['name' => 'M. Mahamadou Issa', 'email' => 'mahamadou.issa@roisdusahel.ne', 'matricule' => 'ENS-2025-0007', 'speciality' => 'SVT', 'qualification' => 'Master Biologie', 'employment_type' => 'PERMANENT'],

            // ── Histoire - Géographie ───────────────────────────────────
            ['name' => 'M. Seydou Amadou', 'email' => 'seydou.amadou@roisdusahel.ne', 'matricule' => 'ENS-2025-0008', 'speciality' => 'Histoire - Géographie', 'qualification' => 'Maîtrise Histoire-Géo', 'employment_type' => 'PERMANENT'],

            // ── Anglais ─────────────────────────────────────────────────
            ['name' => 'Mme Aïssatou Ibrahim', 'email' => 'aissatou.ibrahim@roisdusahel.ne', 'matricule' => 'ENS-2025-0009', 'speciality' => 'Anglais', 'qualification' => 'Licence Anglais', 'employment_type' => 'VACATAIRE'],

            // ── Philosophie ─────────────────────────────────────────────
            ['name' => 'M. Abdoulaye Mahamadou', 'email' => 'abdoulaye.mahamadou@roisdusahel.ne', 'matricule' => 'ENS-2025-0010', 'speciality' => 'Philosophie', 'qualification' => 'Master Philosophie', 'employment_type' => 'PERMANENT'],

            // ── EPS ─────────────────────────────────────────────────────
            ['name' => 'M. Sani Boubacar', 'email' => 'sani.boubacar@roisdusahel.ne', 'matricule' => 'ENS-2025-0011', 'speciality' => 'EPS', 'qualification' => 'Licence STAPS', 'employment_type' => 'VACATAIRE'],

            // ── Informatique ────────────────────────────────────────────
            ['name' => 'M. Oumarou Seydou', 'email' => 'oumarou.seydou@roisdusahel.ne', 'matricule' => 'ENS-2025-0012', 'speciality' => 'Informatique', 'qualification' => 'Master Informatique', 'employment_type' => 'CONTRACTUEL'],

            // ── Arabe ───────────────────────────────────────────────────
            ['name' => 'M. Ibrahim Sani', 'email' => 'ibrahim.sani@roisdusahel.ne', 'matricule' => 'ENS-2025-0013', 'speciality' => 'Arabe', 'qualification' => 'Licence Arabe', 'employment_type' => 'VACATAIRE'],

            // ── Primaire (instituteurs) ─────────────────────────────────
            ['name' => 'Mme Fati Amadou', 'email' => 'fati.amadou@roisdusahel.ne', 'matricule' => 'ENS-2025-0014', 'speciality' => 'Primaire', 'qualification' => 'CAP', 'employment_type' => 'PERMANENT'],
            ['name' => 'M. Moussa Ibrahim', 'email' => 'moussa.ibrahim@roisdusahel.ne', 'matricule' => 'ENS-2025-0015', 'speciality' => 'Primaire', 'qualification' => 'CAP', 'employment_type' => 'PERMANENT'],
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
