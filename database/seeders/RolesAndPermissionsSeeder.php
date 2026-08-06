<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Roles
        $roles = [
            'Super Admin' => 'Administrateur Général du Système',
            'Fondatrice' => 'Promoteur / Fondatrice du Complexe',
            'Directeur' => 'Directeur Général / Proviseur',
            'Censeur' => 'Censeur / Responsable Pédagogique',
            'Surveillant Général' => 'Responsable de la Discipline et des Présences',
            'Secrétaire' => 'Secrétaire Administratif',
            'Comptable' => 'Responsable Financier et Comptable',
            'Caissier' => 'Caissier des Frais de Scolarité',
            'Enseignant' => 'Corps Professoral / Enseignant',
            'Parent' => 'Parent d\'élève ou Tuteur Légal',
            'Élève' => 'Élève inscrit au Complexe',
        ];

        foreach ($roles as $roleName => $description) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['display_name' => $roleName, 'description' => $description]
            );
        }

        // 2. Define Core Permissions
        $permissions = [
            // Settings & Years
            ['name' => 'manage-settings', 'group' => 'administration', 'display_name' => 'Gérer les Paramètres Établissement'],
            ['name' => 'manage-academic-years', 'group' => 'administration', 'display_name' => 'Gérer les Années Scolaires'],
            ['name' => 'manage-users', 'group' => 'iam', 'display_name' => 'Gérer les Utilisateurs et Rôles'],
            
            // Academic Structure
            ['name' => 'manage-cycles-levels', 'group' => 'academique', 'display_name' => 'Gérer les Cycles et Niveaux'],
            ['name' => 'manage-classes', 'group' => 'academique', 'display_name' => 'Gérer les Classes'],
            ['name' => 'manage-subjects', 'group' => 'academique', 'display_name' => 'Gérer les Matières et Coefficients'],
            
            // Students & Enrollment
            ['name' => 'view-students', 'group' => 'scolarite', 'display_name' => 'Consulter les Fiches Élèves'],
            ['name' => 'enroll-students', 'group' => 'scolarite', 'display_name' => 'Inscrire et Réinscrire les Élèves'],
            
            // Grades & Evaluation
            ['name' => 'enter-grades', 'group' => 'pedagogie', 'display_name' => 'Saisir les Notes d\'Évaluations'],
            ['name' => 'generate-report-cards', 'group' => 'pedagogie', 'display_name' => 'Générer les Bulletins Scolaires'],
            
            // Finance
            ['name' => 'manage-payments', 'group' => 'finance', 'display_name' => 'Enregistrer les Paiements'],
            ['name' => 'view-financial-reports', 'group' => 'finance', 'display_name' => 'Consulter les Rapports Financiers'],
            
            // Discipline & Attendance
            ['name' => 'record-attendance', 'group' => 'discipline', 'display_name' => 'Saisir l\'Appel et les Absences'],
            ['name' => 'manage-discipline', 'group' => 'discipline', 'display_name' => 'Gérer les Sanctions Disciplinaires'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                ['group' => $perm['group'], 'display_name' => $perm['display_name']]
            );
        }

        // Assign permissions to Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            foreach (Permission::all() as $permission) {
                $superAdminRole->givePermissionTo($permission);
            }
        }

        // Create Super Admin default user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@roisdusahel.ne'],
            [
                'name' => 'Administrateur Principal',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $adminUser->assignRole('Super Admin');
    }
}
