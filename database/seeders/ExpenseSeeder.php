<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            $this->command->warn('Aucune année scolaire active — ExpenseSeeder ignoré.');

            return;
        }

        $adminUser = User::first();
        if (! $adminUser) {
            $this->command->warn('Aucun utilisateur trouvé — ExpenseSeeder ignoré.');

            return;
        }

        // Récupération des catégories par code
        $cats = ExpenseCategory::pluck('id', 'code');

        /**
         * Dépenses réalistes pour un complexe scolaire privé au Niger.
         *
         * Format : [category_code, label, amount, date (Y-m), payment_method, supplier]
         */
        $expenses = [

            // ── Salaires ─────────────────────────────────────────────────────
            ['SALAIRES', 'Salaires enseignants — Octobre',        1850000, '10-05', 'VIREMENT',    'Banque BOA Niger'],
            ['SALAIRES', 'Salaires enseignants — Novembre',       1850000, '11-05', 'VIREMENT',    'Banque BOA Niger'],
            ['SALAIRES', 'Salaires enseignants — Décembre',       1850000, '12-05', 'VIREMENT',    'Banque BOA Niger'],
            ['SALAIRES', 'Salaires personnel administratif — Oct',  320000, '10-05', 'ESPECES',     null],
            ['SALAIRES', 'Salaires personnel administratif — Nov',  320000, '11-05', 'ESPECES',     null],
            ['SALAIRES', 'Primes de fin d\'année (tous staffs)',   250000, '12-20', 'ESPECES',     null],
            ['SALAIRES', 'Salaires agents d\'entretien — Oct',      75000, '10-30', 'ESPECES',     null],
            ['SALAIRES', 'Salaires agents d\'entretien — Nov',      75000, '11-30', 'ESPECES',     null],

            // ── Fournitures Scolaires ─────────────────────────────────────────
            ['FOURNITURES', 'Achat cahiers et stylos — rentrée',       185000, '09-15', 'ESPECES',     'Librairie Al Baraka'],
            ['FOURNITURES', 'Craies et feutres pour tableaux',           18500, '09-20', 'ESPECES',     'Papeterie Centrale'],
            ['FOURNITURES', 'Rames de papier A4 (20 rames)',             35000, '10-10', 'ESPECES',     'Librairie Al Baraka'],
            ['FOURNITURES', 'Matériel de dessin — cours arts',          22000, '10-25', 'ESPECES',     'Papeterie Centrale'],
            ['FOURNITURES', 'Livres de bibliothèque scolaire',         120000, '11-05', 'CHEQUE',      'Librairie Hamani'],
            ['FOURNITURES', 'Cartouches d\'encre imprimante',           27500, '11-15', 'ESPECES',     'Tech Store Niamey'],
            ['FOURNITURES', 'Registres et cahiers d\'appel (25)',        15000, '09-05', 'ESPECES',     'Papeterie Centrale'],

            // ── Entretien & Réparations ───────────────────────────────────────
            ['ENTRETIEN', 'Réparation toiture salle B',              145000, '10-12', 'ESPECES',     'Maçon Ousseini'],
            ['ENTRETIEN', 'Peinture façade principale',              220000, '11-08', 'ESPECES',     'Peintre Ali Maïga'],
            ['ENTRETIEN', 'Remplacement fenêtres cassées (4)',        62000, '10-20', 'ESPECES',     'Menuisier Boubacar'],
            ['ENTRETIEN', 'Entretien groupe électrogène',             38500, '12-02', 'ESPECES',     'Mécanique Express'],
            ['ENTRETIEN', 'Achat de chaises — salle informatique',   95000, '09-28', 'ESPECES',     'Menuiserie Sahel'],

            // ── Eau & Électricité ─────────────────────────────────────────────
            ['EAU_ELEC', 'Facture NIGELEC — Octobre',               42000, '10-15', 'ESPECES',     'NIGELEC'],
            ['EAU_ELEC', 'Facture NIGELEC — Novembre',              38500, '11-15', 'ESPECES',     'NIGELEC'],
            ['EAU_ELEC', 'Facture NIGELEC — Décembre',              45000, '12-15', 'ESPECES',     'NIGELEC'],
            ['EAU_ELEC', 'Facture eau SEEN — Octobre',              12500, '10-20', 'ESPECES',     'SEEN'],
            ['EAU_ELEC', 'Facture eau SEEN — Novembre',             11000, '11-20', 'ESPECES',     'SEEN'],
            ['EAU_ELEC', 'Abonnement internet fibre — Oct',          35000, '10-01', 'MOBILE_MONEY', 'Orange Niger'],
            ['EAU_ELEC', 'Abonnement internet fibre — Nov',          35000, '11-01', 'MOBILE_MONEY', 'Orange Niger'],
            ['EAU_ELEC', 'Gasoil pour groupe électrogène',           55000, '10-25', 'ESPECES',     'Station Total'],

            // ── Transport & Déplacements ──────────────────────────────────────
            ['TRANSPORT', 'Carburant véhicule école — Oct',          28000, '10-31', 'ESPECES',     'Station Shell'],
            ['TRANSPORT', 'Carburant véhicule école — Nov',          31000, '11-30', 'ESPECES',     'Station Shell'],
            ['TRANSPORT', 'Transport élèves sortie pédagogique',     45000, '11-22', 'ESPECES',     'Transport Sahel'],
            ['TRANSPORT', 'Frais mission directeur à Dosso',         18000, '10-14', 'ESPECES',     null],

            // ── Alimentation & Cantine ────────────────────────────────────────
            ['ALIMENTATION', 'Achat vivres cantine — Semaine 1 Oct',  85000, '10-07', 'ESPECES',     'Marché Katako'],
            ['ALIMENTATION', 'Achat vivres cantine — Semaine 2 Oct',  82000, '10-14', 'ESPECES',     'Marché Katako'],
            ['ALIMENTATION', 'Achat vivres cantine — Semaine 3 Oct',  88000, '10-21', 'ESPECES',     'Marché Katako'],
            ['ALIMENTATION', 'Achat vivres cantine — Semaine 4 Oct',  79000, '10-28', 'ESPECES',     'Marché Katako'],
            ['ALIMENTATION', 'Achat vivres cantine — Semaine 1 Nov',  84000, '11-07', 'ESPECES',     'Marché Katako'],
            ['ALIMENTATION', 'Achat vivres cantine — Semaine 2 Nov',  86000, '11-14', 'ESPECES',     'Marché Katako'],
            ['ALIMENTATION', 'Achat gaz cuisine (3 bouteilles)',       22500, '10-10', 'ESPECES',     'Sodaz Niger'],

            // ── Matériel Informatique ─────────────────────────────────────────
            ['MATERIEL_INFO', 'Achat ordinateur portable (direction)',  285000, '09-10', 'VIREMENT',    'CFAO Technologies'],
            ['MATERIEL_INFO', 'Imprimante multifonction',               95000, '09-12', 'CHEQUE',      'Informatique Plus'],
            ['MATERIEL_INFO', 'Clés USB et câbles divers',              12500, '10-05', 'ESPECES',     'Tech Store Niamey'],
            ['MATERIEL_INFO', 'Réparation 2 ordinateurs de classe',     45000, '11-18', 'ESPECES',     'Informatique Plus'],

            // ── Communication & Marketing ─────────────────────────────────────
            ['COMM', 'Impression banderoles rentrée scolaire',       35000, '09-01', 'ESPECES',     'Imprimerie Sahel'],
            ['COMM', 'Confection cartes de visite établissement',    12000, '09-05', 'ESPECES',     'Imprimerie Sahel'],
            ['COMM', 'Frais publicité radio — rentrée',              45000, '09-01', 'MOBILE_MONEY', 'Radio Nationale'],
            ['COMM', 'Impression bulletins scolaires (500)',          28000, '11-20', 'ESPECES',     'Imprimerie Sahel'],

            // ── Charges Administratives ───────────────────────────────────────
            ['CHARGES_ADMIN', 'Frais bancaires — Octobre',               3500, '10-31', 'ESPECES',     'BOA Niger'],
            ['CHARGES_ADMIN', 'Frais bancaires — Novembre',              3500, '11-30', 'ESPECES',     'BOA Niger'],
            ['CHARGES_ADMIN', 'Timbres et frais postaux',                5000, '10-08', 'ESPECES',     'Poste Niamey'],
            ['CHARGES_ADMIN', 'Cotisation association des écoles privées', 15000, '09-20', 'ESPECES',     'FENEP Niger'],
            ['CHARGES_ADMIN', 'Assurance établissement — annuelle',     180000, '09-01', 'CHEQUE',      'SUNU Assurances'],

            // ── Divers ────────────────────────────────────────────────────────
            ['DIVERS', 'Décoration fête de fin d\'année',             28000, '12-15', 'ESPECES',     null],
            ['DIVERS', 'Trophées et médailles compétition sportive',  18500, '11-28', 'ESPECES',     'Sport Plus Niger'],
            ['DIVERS', 'Nettoyage et désinfection locaux',            22000, '09-08', 'ESPECES',     'Propre Niger'],
        ];

        $year = $activeYear->start_date->format('Y');

        foreach ($expenses as [$catCode, $label, $amount, $monthDay, $method, $supplier]) {
            if (! isset($cats[$catCode])) {
                continue;
            }

            // Construire la date avec l'année scolaire
            [$month, $day] = explode('-', $monthDay);
            $expenseYear = ((int) $month >= 9) ? $year : ((int) $year + 1);
            $expenseDate = "{$expenseYear}-{$month}-{$day}";

            Expense::firstOrCreate(
                [
                    'label' => $label,
                    'academic_year_id' => $activeYear->id,
                    'expense_date' => $expenseDate,
                ],
                [
                    'reference' => Expense::generateReference(),
                    'expense_category_id' => $cats[$catCode],
                    'user_id' => $adminUser->id,
                    'amount' => $amount,
                    'payment_method' => $method,
                    'supplier' => $supplier,
                ]
            );
        }

        $this->command->info('✅ '.count($expenses).' dépenses seedées avec succès.');
    }
}
