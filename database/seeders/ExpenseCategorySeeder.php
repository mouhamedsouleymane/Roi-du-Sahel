<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'SALAIRES',
                'name' => 'Salaires & Rémunérations',
                'description' => 'Salaires des enseignants, du personnel administratif et d\'entretien.',
                'color' => '#7c3aed',
            ],
            [
                'code' => 'FOURNITURES',
                'name' => 'Fournitures Scolaires',
                'description' => 'Cahiers, stylos, craies, cartouches, matériel pédagogique.',
                'color' => '#2563eb',
            ],
            [
                'code' => 'ENTRETIEN',
                'name' => 'Entretien & Réparations',
                'description' => 'Maintenance des bâtiments, mobilier, équipements.',
                'color' => '#d97706',
            ],
            [
                'code' => 'EAU_ELEC',
                'name' => 'Eau & Électricité',
                'description' => 'Factures eau, électricité, internet.',
                'color' => '#0891b2',
            ],
            [
                'code' => 'TRANSPORT',
                'name' => 'Transport & Déplacements',
                'description' => 'Carburant, transport scolaire, missions.',
                'color' => '#16a34a',
            ],
            [
                'code' => 'ALIMENTATION',
                'name' => 'Alimentation & Cantine',
                'description' => 'Denrées alimentaires, repas du personnel.',
                'color' => '#dc2626',
            ],
            [
                'code' => 'MATERIEL_INFO',
                'name' => 'Matériel Informatique',
                'description' => 'Ordinateurs, imprimantes, logiciels, accessoires.',
                'color' => '#6366f1',
            ],
            [
                'code' => 'COMM',
                'name' => 'Communication & Marketing',
                'description' => 'Publicité, impression affiches, frais téléphoniques.',
                'color' => '#ec4899',
            ],
            [
                'code' => 'CHARGES_ADMIN',
                'name' => 'Charges Administratives',
                'description' => 'Frais bancaires, timbres, cotisations, taxes diverses.',
                'color' => '#64748b',
            ],
            [
                'code' => 'DIVERS',
                'name' => 'Dépenses Diverses',
                'description' => 'Toute dépense non classifiée dans les autres catégories.',
                'color' => '#78716c',
            ],
        ];

        foreach ($categories as $data) {
            ExpenseCategory::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
