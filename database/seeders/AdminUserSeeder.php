<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crée le compte Super Admin de l'application.
     */
    public function run(): void
    {
        // Créer ou mettre à jour l'utilisateur admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@roisdusahel.ne'],
            [
                'name' => 'Administrateur Principal',
                'password' => Hash::make('Admin@2025!'),
                'email_verified_at' => now(),
            ]
        );

        // Attribuer le rôle Super Admin
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if ($superAdminRole) {
            $admin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        $this->command->info('✅ Compte Super Admin créé avec succès !');
        $this->command->table(
            ['Champ', 'Valeur'],
            [
                ['Email', 'admin@roisdusahel.ne'],
                ['Mot de passe', 'Admin@2025!'],
                ['Rôle', 'Super Admin'],
            ]
        );
        $this->command->warn('⚠️  Pensez à changer le mot de passe après la première connexion !');
    }
}
