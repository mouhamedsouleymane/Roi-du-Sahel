<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SchoolSettingSeeder::class,
            AcademicYearSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Utilisateur Test',
            'email' => 'test@example.com',
        ]);
    }
}
