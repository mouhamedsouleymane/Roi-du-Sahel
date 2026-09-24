<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserPhoneSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereNull('phone')->get();

        if ($users->isEmpty()) {
            $this->command->info('ℹ️  Tous les utilisateurs ont déjà un numéro de téléphone.');
            return;
        }

        $used = [];
        $rows = [];

        foreach ($users as $user) {
            do {
                $phone = str_pad(random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            } while (in_array($phone, $used) || User::where('phone', $phone)->exists());

            $used[] = $phone;
            $user->update(['phone' => $phone]);

            $rows[] = [$user->name, $user->email, $phone];
        }

        $this->command->info("✅ {$users->count()} numéro(s) attribué(s) :");
        $this->command->table(['Nom', 'Email', 'Téléphone'], $rows);
        $this->command->warn('⚠️  Ces numéros sont générés aléatoirement. Mettez-les à jour avec les vrais numéros.');
    }
}
