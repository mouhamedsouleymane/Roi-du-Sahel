<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\FeeType;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClassStudentsSeeder extends Seeder
{
    /**
     * Target number of students per class.
     */
    protected int $targetPerClass = 35;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            $this->command?->error("Aucune année scolaire active n'a été trouvée.");

            return;
        }

        $yearPrefix = '2025';
        $parts = explode('-', $activeYear->name);
        if (! empty($parts[0])) {
            $yearPrefix = $parts[0];
        }

        // Récupérer le prochain numéro de matricule séquentiel
        $latestStudent = Student::withTrashed()
            ->where('matricule', 'LIKE', "RS-{$yearPrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextStudentNum = 1;
        if ($latestStudent) {
            $partsM = explode('-', $latestStudent->matricule);
            $nextStudentNum = ((int) ($partsM[2] ?? 0)) + 1;
        }

        // Récupérer le prochain numéro d'inscription séquentiel
        $latestEnrollment = Enrollment::where('enrollment_number', 'LIKE', "INS-{$yearPrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextEnrollmentNum = 1;
        if ($latestEnrollment) {
            $partsE = explode('-', $latestEnrollment->enrollment_number);
            $nextEnrollmentNum = ((int) ($partsE[2] ?? 0)) + 1;
        }

        // Compteurs de facturation et reçus
        $currentYearStr = date('Y');
        $invoiceCounter = Invoice::whereYear('created_at', $currentYearStr)->count() + 1;
        $paymentCounter = Payment::whereYear('created_at', $currentYearStr)->count() + 1;

        // Pré-hachage unique du mot de passe pour des performances optimales
        $defaultPasswordHash = Hash::make('password');

        // Pré-chargement des rôles et types de frais
        $parentRole = Role::firstOrCreate(['name' => 'Parent'], ['display_name' => 'Parent']);
        $paymentService = app(PaymentService::class);

        $feeTypeInscript = FeeType::firstOrCreate(
            ['code' => 'INSCRIPT'],
            ['name' => "Frais d'inscription", 'is_recurring' => false, 'is_active' => true]
        );
        $feeTypeScolarite = FeeType::firstOrCreate(
            ['code' => 'SCOLARITE'],
            ['name' => 'Frais de scolarité', 'is_recurring' => true, 'is_active' => true]
        );
        $feeTypeT1 = FeeType::firstOrCreate(
            ['code' => 'SCOLARITE_T1'],
            ['name' => 'Scolarité — 1ère Tranche (50%)', 'is_recurring' => true, 'is_active' => true]
        );
        $feeTypeT2 = FeeType::firstOrCreate(
            ['code' => 'SCOLARITE_T2'],
            ['name' => 'Scolarité — 2ème Tranche (35%)', 'is_recurring' => true, 'is_active' => true]
        );
        $feeTypeT3 = FeeType::firstOrCreate(
            ['code' => 'SCOLARITE_T3'],
            ['name' => 'Scolarité — 3ème Tranche (15%)', 'is_recurring' => true, 'is_active' => true]
        );

        // Banques de données nigériennes réalistes
        $maleFirstNames = [
            'Ibrahim', 'Moussa', 'Abdoulaye', 'Mahamadou', 'Ousmane', 'Souleymane', 'Hassane',
            'Sani', 'Boubacar', 'Seydou', 'Alzouma', 'Rabiou', 'Amadou', 'Harouna', 'Salifou',
            'Issoufou', 'Hamani', 'Ali', 'Mounkaila', 'Nouhou', 'Yacouba', 'Adamou', 'Mamane',
            'Bachir', 'Nassirou', 'Lawali', 'Dan Baba', 'Chaibou', 'Kabirou', 'Alio', 'Boureima',
            'Moustapha', 'Garba', 'Hamidou', 'Gado', 'Zakaria', 'Kader', 'Nafiou', 'Abdou',
            'Tahirou', 'Idrissa', 'Issa', 'Daouda', 'Djibo', 'Habibou', 'Ibro', 'Bello',
            'Anas', 'Farouk', 'Bilal', 'Ismaël', 'Kassim',
        ];

        $femaleFirstNames = [
            'Aïcha', 'Fatouma', 'Ramatou', 'Aminatou', 'Mariama', 'Hadiza', 'Zeinabou',
            'Rahinatou', 'Fati', 'Salamatou', 'Aïssatou', 'Hassana', 'Balkissa', 'Haoua',
            'Nafissatou', 'Samira', 'Habsatou', 'Halima', 'Maimouna', 'Jamila', 'Fadila',
            'Safia', 'Zara', 'Rachida', 'Binta', 'Nana', 'Zoulaha', 'Roukayatou', 'Bilkisu',
            'Latifa', 'Safiatou', 'Oumaïma', 'Faouzia', 'Noura', 'Khadidja', 'Chamsia',
            'Zalika', 'Djamila', 'Hassia', 'Inoussa', 'Assamaou', 'Rabia', 'Mariam',
            'Zeina', 'Nafissa', 'Khadija', 'Farida', 'Charifatou', 'Zouley', 'Safoura',
        ];

        $lastNames = [
            'Moussa', 'Ibrahim', 'Amadou', 'Garba', 'Mahamadou', 'Ousmane', 'Sani', 'Abdou',
            'Oumarou', 'Boubacar', 'Issoufou', 'Seydou', 'Hama', 'Alzouma', 'Abdoulaye',
            'Harouna', 'Salifou', 'Souley', 'Hamani', 'Maïga', 'Bello', 'Daouda', 'Mamane',
            'Kountché', 'Diallo', 'Cissé', 'Djibo', 'Sidikou', 'Bagna', 'Tondi', 'Tankoano',
            'Saley', 'Dan Mallam', 'Idé', 'Tindano', 'Hamidou', 'Gado', 'Zakou', 'Boureima',
            'Hassane', 'Adamou', 'Bawa', 'Dodo', 'Laminou', 'Mainassara', 'Yahaya', 'Yacouba',
            'Rabiou', 'Moumouni', 'Kassoum', 'Inoussa', 'Almoustapha', 'Zada', 'Barazé',
            'Tahirou', 'Wazir', 'Chékaraou', 'Barkiré', 'Dan Kane',
        ];

        $birthPlaces = [
            'Niamey', 'Niamey (Commune 1)', 'Niamey (Commune 2)', 'Niamey (Commune 3)',
            'Niamey (Commune 4)', 'Niamey (Commune 5)', 'Maradi', 'Zinder', 'Tahoua',
            'Dosso', 'Tillabéri', 'Agadez', 'Diffa',
        ];

        $bloodGroups = ['O+', 'A+', 'B+', 'AB+', 'O-', 'A-', 'B-'];

        $neighborhoods = [
            'Koubia Plateau, Niamey', 'Yantala Haut, Niamey', 'Yantala Bas, Niamey',
            'Banifandou 1, Niamey', 'Banifandou 2, Niamey', 'Karadjé, Niamey',
            'Gamkalley, Niamey', 'Koira Kano, Niamey', 'Koira Tégui, Niamey',
            'Plateau 1, Niamey', 'Plateau 2, Niamey', 'Talladjé, Niamey',
            'Wadata, Niamey', 'Boukoki, Niamey', 'Lazaret, Niamey',
            'Cité Député, Niamey', 'Dar-es-salam, Niamey', 'Francophonie, Niamey',
            'Bobiel, Niamey', 'Sonuci, Niamey', 'Pays-Bas, Niamey',
            'Harobanda, Niamey', 'Goudel, Niamey',
        ];

        $professions = [
            'Commerçant', 'Enseignant', 'Fonctionnaire', 'Ingénieur', 'Médecin',
            'Comptable', 'Magistrat', 'Juriste', 'Pharmacien', 'Infirmier',
            'Chauffeur', 'Entrepreneur', 'Militaire', 'Agent de Banque', 'Transitaire',
            'Professeur d\'Université', 'Technicien', 'Gestionnaire', 'Opérateur Économique',
        ];

        $phonePrefixes = [90, 91, 92, 93, 94, 96, 97, 98, 99, 80, 88, 89];
        $usedPhones = Guardian::pluck('phone_primary')->flip()->toArray();

        // Récupérer toutes les classes de l'année scolaire active
        $classes = SchoolClass::with(['level.cycle', 'enrollments' => function ($q) use ($activeYear) {
            $q->where('academic_year_id', $activeYear->id)->where('status', 'VALIDE');
        }])
            ->where('academic_year_id', $activeYear->id)
            ->orderBy('id')
            ->get();

        $this->command?->info("Début du seeding de 35 élèves par classe pour l'année scolaire {$activeYear->name}...");

        $totalAdded = 0;
        $totalClasses = $classes->count();

        foreach ($classes as $classIndex => $class) {
            $currentCount = $class->enrollments->count();
            $needed = max(0, $this->targetPerClass - $currentCount);

            if ($needed === 0) {
                $this->command?->line("• Classe [{$class->name}] : déjà complète avec {$currentCount} élèves.");

                continue;
            }

            $levelCode = $class->level?->code ?? '';
            $birthYear = $this->resolveBirthYearForLevel($levelCode, $activeYear);

            DB::transaction(function () use (
                $class,
                $activeYear,
                $needed,
                $birthYear,
                $maleFirstNames,
                $femaleFirstNames,
                $lastNames,
                $birthPlaces,
                $bloodGroups,
                $neighborhoods,
                $professions,
                $phonePrefixes,
                &$usedPhones,
                &$nextStudentNum,
                &$nextEnrollmentNum,
                &$invoiceCounter,
                &$paymentCounter,
                $yearPrefix,
                $currentYearStr,
                $defaultPasswordHash,
                $parentRole,
                $paymentService,
                $feeTypeInscript,
                $feeTypeScolarite,
                $feeTypeT1,
                $feeTypeT2,
                $feeTypeT3
            ) {
                for ($i = 0; $i < $needed; $i++) {
                    $gender = (rand(1, 100) <= 50) ? 'M' : 'F';
                    $firstName = ($gender === 'M')
                        ? $maleFirstNames[array_rand($maleFirstNames)]
                        : $femaleFirstNames[array_rand($femaleFirstNames)];
                    $lastName = $lastNames[array_rand($lastNames)];

                    // Date de naissance réaliste
                    $birthMonth = rand(1, 12);
                    $birthDay = rand(1, 28);
                    $birthDate = sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay);

                    // Matricule unique
                    $matricule = sprintf('RS-%s-%04d', $yearPrefix, $nextStudentNum++);

                    // 1. Création de l'élève
                    $student = Student::create([
                        'matricule' => $matricule,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'gender' => $gender,
                        'birth_date' => $birthDate,
                        'birth_place' => $birthPlaces[array_rand($birthPlaces)],
                        'blood_group' => $bloodGroups[array_rand($bloodGroups)],
                        'previous_school' => rand(1, 10) > 3 ? 'Complexe Scolaire Sahel' : null,
                        'medical_notes' => rand(1, 20) === 1 ? 'Allergie à la poussière' : null,
                        'status' => 'ACTIF',
                    ]);

                    // 2. Création du Tuteur / Parent
                    $guardianRel = rand(1, 100) <= 70 ? 'PERE' : (rand(1, 100) <= 85 ? 'MERE' : 'TUTEUR');
                    $guardianFirstName = ($guardianRel === 'MERE')
                        ? $femaleFirstNames[array_rand($femaleFirstNames)]
                        : $maleFirstNames[array_rand($maleFirstNames)];
                    $guardianLastName = ($guardianRel === 'PERE') ? $lastName : $lastNames[array_rand($lastNames)];

                    // Générer un numéro de téléphone nigérien unique
                    do {
                        $pfx = $phonePrefixes[array_rand($phonePrefixes)];
                        $phone = sprintf('+227 %02d %02d %02d %02d', $pfx, rand(10, 99), rand(10, 99), rand(10, 99));
                    } while (isset($usedPhones[$phone]));
                    $usedPhones[$phone] = true;

                    $digits = preg_replace('/\D/', '', $phone);
                    $cleanPhone = strlen($digits) > 8 ? substr($digits, -8) : $digits;
                    $parentEmail = "parent.{$cleanPhone}@roisdusahel.ne";

                    $parentUser = User::create([
                        'name' => "{$guardianFirstName} {$guardianLastName}",
                        'email' => $parentEmail,
                        'phone' => $cleanPhone,
                        'password' => $defaultPasswordHash,
                        'email_verified_at' => now(),
                    ]);
                    $parentUser->assignRole($parentRole);

                    $guardian = Guardian::create([
                        'user_id' => $parentUser->id,
                        'first_name' => $guardianFirstName,
                        'last_name' => $guardianLastName,
                        'relationship' => $guardianRel,
                        'profession' => $professions[array_rand($professions)],
                        'phone_primary' => $phone,
                        'address' => $neighborhoods[array_rand($neighborhoods)],
                        'city' => 'Niamey',
                        'email' => $parentEmail,
                    ]);

                    // Liaison Élève - Tuteur
                    $student->guardians()->attach($guardian->id, [
                        'is_primary_contact' => true,
                        'can_pick_up' => true,
                    ]);

                    // 3. Inscription officielle dans la classe
                    $enrollmentNumber = sprintf('INS-%s-%04d', $yearPrefix, $nextEnrollmentNum++);
                    $enrollmentDate = $activeYear->start_date ? $activeYear->start_date->toDateString() : '2026-09-15';

                    $enrollment = Enrollment::create([
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'academic_year_id' => $activeYear->id,
                        'enrollment_number' => $enrollmentNumber,
                        'type' => rand(1, 10) > 3 ? 'NOUVEAU' : 'REINSCRIPTION',
                        'status' => 'VALIDE',
                        'is_repeater' => rand(1, 20) === 1,
                        'enrollment_date' => $enrollmentDate,
                        'notes' => 'Inscription standard saisie via le seeder d\'initialisation.',
                    ]);

                    // 4. Facturation des frais d'inscription (INSCRIPT)
                    $inscriptAmount = $paymentService->resolveFeeAmount($feeTypeInscript, $enrollment) ?? 3000.0;
                    if ($inscriptAmount > 0) {
                        $isPaid = rand(1, 100) <= 85;
                        $amountPaid = $isPaid ? $inscriptAmount : 0.0;

                        $invInscript = Invoice::create([
                            'invoice_number' => sprintf('FACT-%s-%05d', $currentYearStr, $invoiceCounter++),
                            'student_id' => $student->id,
                            'enrollment_id' => $enrollment->id,
                            'academic_year_id' => $activeYear->id,
                            'fee_type_id' => $feeTypeInscript->id,
                            'amount_due' => $inscriptAmount,
                            'amount_paid' => $amountPaid,
                            'status' => $isPaid ? 'PAYEE' : 'IMPAYEE',
                            'due_date' => $enrollmentDate,
                            'notes' => "Frais d'inscription",
                        ]);

                        if ($isPaid) {
                            Payment::create([
                                'receipt_number' => sprintf('RECU-%s-%05d', $currentYearStr, $paymentCounter++),
                                'invoice_id' => $invInscript->id,
                                'student_id' => $student->id,
                                'amount' => $inscriptAmount,
                                'payment_method' => ['ESPECES', 'MOBILE_MONEY', 'VIREMENT'][rand(0, 2)],
                                'payment_date' => $enrollmentDate,
                                'reference' => 'RECU-INSC-'.strtoupper(substr(md5(uniqid()), 0, 6)),
                                'recorded_by' => 'Caissier Principal',
                                'notes' => "Règlement des frais d'inscription",
                            ]);
                        }
                    }

                    // 5. Facturation des 3 Tranches de Scolarité
                    $totalTuition = $paymentService->resolveFeeAmount($feeTypeScolarite, $enrollment) ?? 60000.0;
                    if ($totalTuition > 0) {
                        $t1 = round($totalTuition * 0.50);
                        $t2 = round($totalTuition * 0.35);
                        $t3 = $totalTuition - $t1 - $t2;

                        $targetEndYear = $activeYear->end_date
                            ? Carbon::parse($activeYear->end_date)->year
                            : ((now()->month >= 7) ? now()->year + 1 : now()->year);

                        $tranches = [
                            [
                                'type' => $feeTypeT1,
                                'amount' => $paymentService->resolveFeeAmount($feeTypeT1, $enrollment) ?? $t1,
                                'due_date' => $enrollmentDate,
                                'notes' => '1ère Tranche scolarité (50%)',
                                'paid_rate' => 75,
                                'partial_rate' => 15,
                            ],
                            [
                                'type' => $feeTypeT2,
                                'amount' => $paymentService->resolveFeeAmount($feeTypeT2, $enrollment) ?? $t2,
                                'due_date' => "{$targetEndYear}-01-05",
                                'notes' => '2ème Tranche scolarité (35%)',
                                'paid_rate' => 30,
                                'partial_rate' => 15,
                            ],
                            [
                                'type' => $feeTypeT3,
                                'amount' => $paymentService->resolveFeeAmount($feeTypeT3, $enrollment) ?? $t3,
                                'due_date' => "{$targetEndYear}-03-01",
                                'notes' => '3ème Tranche scolarité (15%)',
                                'paid_rate' => 5,
                                'partial_rate' => 5,
                            ],
                        ];

                        foreach ($tranches as $t) {
                            if (! $t['type']) {
                                continue;
                            }

                            $tAmount = (float) $t['amount'];
                            $roll = rand(1, 100);

                            if ($roll <= $t['paid_rate']) {
                                $paid = $tAmount;
                                $status = 'PAYEE';
                            } elseif ($roll <= ($t['paid_rate'] + $t['partial_rate'])) {
                                $paid = round($tAmount * 0.5);
                                $status = 'PARTIELLE';
                            } else {
                                $paid = 0.0;
                                $status = 'IMPAYEE';
                            }

                            $invTuition = Invoice::create([
                                'invoice_number' => sprintf('FACT-%s-%05d', $currentYearStr, $invoiceCounter++),
                                'student_id' => $student->id,
                                'enrollment_id' => $enrollment->id,
                                'academic_year_id' => $activeYear->id,
                                'fee_type_id' => $t['type']->id,
                                'amount_due' => $tAmount,
                                'amount_paid' => $paid,
                                'status' => $status,
                                'due_date' => $t['due_date'],
                                'notes' => $t['notes'],
                            ]);

                            if ($paid > 0) {
                                Payment::create([
                                    'receipt_number' => sprintf('RECU-%s-%05d', $currentYearStr, $paymentCounter++),
                                    'invoice_id' => $invTuition->id,
                                    'student_id' => $student->id,
                                    'amount' => $paid,
                                    'payment_method' => ['ESPECES', 'MOBILE_MONEY', 'VIREMENT'][rand(0, 2)],
                                    'payment_date' => $t['due_date'],
                                    'reference' => 'RECU-SCOL-'.strtoupper(substr(md5(uniqid()), 0, 6)),
                                    'recorded_by' => 'Caissier Scolarité',
                                    'notes' => "Règlement de {$t['notes']}",
                                ]);
                            }
                        }
                    }
                }
            });

            $totalAdded += $needed;
            $this->command?->info("✓ Classe [{$class->name}] : +{$needed} élève(s) ajouté(s) (Effectif total : 35).");
        }

        $this->command?->info("Seeding terminé avec succès ! Total de {$totalAdded} nouveaux élèves créés. Toutes les {$totalClasses} classes disposent désormais de 35 élèves.");
    }

    /**
     * Calcule l'année de naissance appropriée selon le niveau d'études.
     */
    protected function resolveBirthYearForLevel(string $levelCode, AcademicYear $activeYear): int
    {
        $baseYear = match ($levelCode) {
            'PS' => 2023,
            'MS' => 2022,
            'GS' => 2021,
            'CI' => 2020,
            'CP' => 2019,
            'CE1' => 2018,
            'CE2' => 2017,
            'CM1' => 2016,
            'CM2' => 2015,
            '6EME' => 2014,
            '5EME' => 2013,
            '4EME' => 2012,
            '3EME' => 2011,
            '2NDE_C', '2NDE_A' => 2010,
            '1ERE_D', '1ERE_C' => 2009,
            'TLE_D', 'TLE_A' => 2008,
            default => 2014,
        };

        $yearOffset = 0;
        if ($activeYear->start_date) {
            $startYear = Carbon::parse($activeYear->start_date)->year;
            $yearOffset = $startYear - 2026;
        }

        return $baseYear + $yearOffset;
    }
}
