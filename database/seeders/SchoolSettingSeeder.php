<?php

namespace Database\Seeders;

use App\Models\SchoolSetting;
use Illuminate\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'school_name',
                'value' => 'Complexe Scolaire Privé Les Rois du Sahel',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nom officiel de l\'établissement scolaire',
            ],
            [
                'key' => 'school_motto',
                'value' => 'Discipline - Travail - Succès',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Devise de l\'établissement',
            ],
            [
                'key' => 'school_address',
                'value' => 'Koubia Plateau, Niamey, Niger',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Adresse physique de l\'établissement',
            ],
            [
                'key' => 'school_phones',
                'value' => '+227 90 42 06 80 / +227 90 41 93 00 / +227 96 89 76 64',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Numéros de téléphone officiels',
            ],
            [
                'key' => 'school_email',
                'value' => 'contact@roisdusahel.ne',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Adresse email officielle',
            ],
            [
                'key' => 'founder_message',
                'value' => 'Au CSP Les Rois du Sahel, nous croyons que chaque enfant est une richesse précieuse. Grâce à la discipline et au travail, nous les aidons à révéler tout leur potentiel.',
                'group' => 'general',
                'type' => 'text',
                'description' => 'Mot de la Fondatrice',
            ],
            [
                'key' => 'maternelle_primaire_hours',
                'value' => '08h00 à 14h30min',
                'group' => 'pedagogy',
                'type' => 'string',
                'description' => 'Horaires de descente Maternelle et Primaire',
            ],
            [
                'key' => 'college_lycee_hours',
                'value' => '08h00 à 13h30min',
                'group' => 'pedagogy',
                'type' => 'string',
                'description' => 'Horaires de descente Collège et Lycée',
            ],
            [
                'key' => 'maternelle_primaire_uniform',
                'value' => 'T-shirt couleur violette, Pantalon ou Jupe kaki',
                'group' => 'pedagogy',
                'type' => 'string',
                'description' => 'Tenue de l\'école Maternelle et Primaire',
            ],
            [
                'key' => 'college_lycee_uniform',
                'value' => 'T-shirt couleur jaune, Pantalon ou Jupe kaki',
                'group' => 'pedagogy',
                'type' => 'string',
                'description' => 'Tenue de l\'école Collège et Lycée',
            ],
            [
                'key' => 'school_currency',
                'value' => 'FCFA',
                'group' => 'finance',
                'type' => 'string',
                'description' => 'Devise monétaire utilisée',
            ],
        ];

        foreach ($settings as $setting) {
            SchoolSetting::set(
                key: $setting['key'],
                value: $setting['value'],
                group: $setting['group'],
                type: $setting['type'],
                description: $setting['description']
            );
        }
    }
}
