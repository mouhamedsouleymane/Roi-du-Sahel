<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return;
        }

        $teachers = Teacher::all()->keyBy('speciality');
        $teachersByMatricule = Teacher::all()->keyBy('matricule');
        $subjects = Subject::all()->keyBy('code');
        $classes = SchoolClass::all();

        // Correspondance instituteurs / éducatrices par classe (1 enseignant polyvalent titulaire par classe)
        $titulairesMap = [
            'Petite Section A' => 'ENS-2025-0016',
            'Moyenne Section A' => 'ENS-2025-0017',
            'Grande Section A' => 'ENS-2025-0018',
            'CI A' => 'ENS-2025-0014',
            'CI B' => 'ENS-2025-0015',
            'CP A' => 'ENS-2025-0019',
            'CP B' => 'ENS-2025-0020',
            'CE1 A' => 'ENS-2025-0021',
            'CE2 A' => 'ENS-2025-0022',
            'CM1 A' => 'ENS-2025-0023',
            'CM2 A' => 'ENS-2025-0024',
        ];

        // Affectations par matière pour toutes les classes
        $assignments = [];

        foreach ($classes as $class) {
            $levelCode = $class->level?->code;
            $className = trim($class->name);

            // Maternelle : éducatrice dédiée par classe (FRAN, MATH, ECM)
            if (in_array($levelCode, ['PS', 'MS', 'GS'])) {
                $matricule = $titulairesMap[$className] ?? null;
                $educatrice = $matricule ? ($teachersByMatricule[$matricule] ?? null) : null;
                if (! $educatrice) {
                    $educatrice = Teacher::where('cycle_id', 1)->first() ?? $teachers['Primaire'] ?? null;
                }

                if ($educatrice) {
                    // Définir l'éducatrice comme professeur principal / titulaire si non défini
                    if (! $class->main_teacher_id) {
                        $class->update(['main_teacher_id' => $educatrice->user_id]);
                    }

                    $assignments[] = ['teacher' => $educatrice, 'class' => $class, 'subject' => $subjects['FRAN'] ?? null];
                    $assignments[] = ['teacher' => $educatrice, 'class' => $class, 'subject' => $subjects['MATH'] ?? null];
                    $assignments[] = ['teacher' => $educatrice, 'class' => $class, 'subject' => $subjects['ECM'] ?? null];
                }
            }

            // Primaire : 1 instituteur titulaire unique par classe pour TOUTES les 6 matières officielles
            if (in_array($levelCode, ['CI', 'CP', 'CE1', 'CE2', 'CM1', 'CM2'])) {
                $matricule = $titulairesMap[$className] ?? null;
                $instituteur = $matricule ? ($teachersByMatricule[$matricule] ?? null) : null;
                if (! $instituteur) {
                    $instituteur = Teacher::where('cycle_id', 2)->first() ?? $teachers['Primaire'] ?? null;
                }

                if ($instituteur) {
                    // Définir l'instituteur comme professeur principal / titulaire
                    if (! $class->main_teacher_id) {
                        $class->update(['main_teacher_id' => $instituteur->user_id]);
                    }

                    $primarySubjects = ['FRAN', 'MATH', 'HIST_GEO', 'ANG', 'ECM', 'EPS'];
                    foreach ($primarySubjects as $sCode) {
                        if (isset($subjects[$sCode])) {
                            $assignments[] = ['teacher' => $instituteur, 'class' => $class, 'subject' => $subjects[$sCode]];
                        }
                    }
                }
            }

            // Collège : 8 matières officielles (dont ECM attribué au professeur d'Histoire-Géographie)
            if (in_array($levelCode, ['6EME', '5EME', '4EME', '3EME'])) {
                $mTeacher = in_array($levelCode, ['6EME', '4EME']) ? ($teachersByMatricule['ENS-2025-0001'] ?? $teachers['Mathématiques'] ?? null) : ($teachersByMatricule['ENS-2025-0004'] ?? $teachers['Mathématiques'] ?? null);
                $frTeacher = in_array($levelCode, ['6EME', '5EME']) ? ($teachersByMatricule['ENS-2025-0005'] ?? $teachers['Français'] ?? null) : ($teachersByMatricule['ENS-2025-0002'] ?? $teachers['Français'] ?? null);
                $pcTeacher = $teachersByMatricule['ENS-2025-0006'] ?? $teachers['Physique - Chimie'] ?? null;

                $assignments[] = ['teacher' => $mTeacher, 'class' => $class, 'subject' => $subjects['MATH'] ?? null];
                $assignments[] = ['teacher' => $frTeacher, 'class' => $class, 'subject' => $subjects['FRAN'] ?? null];
                $assignments[] = ['teacher' => $pcTeacher, 'class' => $class, 'subject' => $subjects['PC'] ?? null];
                $assignments[] = ['teacher' => $teachers['SVT'] ?? null, 'class' => $class, 'subject' => $subjects['SVT'] ?? null];
                $assignments[] = ['teacher' => $teachers['Histoire - Géographie'] ?? null, 'class' => $class, 'subject' => $subjects['HIST_GEO'] ?? null];
                $assignments[] = ['teacher' => $teachers['Histoire - Géographie'] ?? null, 'class' => $class, 'subject' => $subjects['ECM'] ?? null];
                $assignments[] = ['teacher' => $teachers['Anglais'] ?? null, 'class' => $class, 'subject' => $subjects['ANG'] ?? null];
                $assignments[] = ['teacher' => $teachers['EPS'] ?? null, 'class' => $class, 'subject' => $subjects['EPS'] ?? null];
            }

            // Lycée
            if (in_array($levelCode, ['2NDE_A', '2NDE_C', '1ERE_A', '1ERE_C', '1ERE_D', 'TLE_A', 'TLE_C', 'TLE_D'])) {
                $mTeacher = in_array($levelCode, ['1ERE_A', '1ERE_C', '1ERE_D']) ? ($teachersByMatricule['ENS-2025-0001'] ?? $teachers['Mathématiques'] ?? null) : ($teachersByMatricule['ENS-2025-0004'] ?? $teachers['Mathématiques'] ?? null);
                $frTeacher = $teachersByMatricule['ENS-2025-0002'] ?? $teachers['Français'] ?? null;
                $pcTeacher = $teachersByMatricule['ENS-2025-0003'] ?? $teachers['Physique - Chimie'] ?? null;

                $assignments[] = ['teacher' => $mTeacher, 'class' => $class, 'subject' => $subjects['MATH'] ?? null];
                $assignments[] = ['teacher' => $frTeacher, 'class' => $class, 'subject' => $subjects['FRAN'] ?? null];
                $assignments[] = ['teacher' => $pcTeacher, 'class' => $class, 'subject' => $subjects['PC'] ?? null];
                $assignments[] = ['teacher' => $teachers['SVT'] ?? null, 'class' => $class, 'subject' => $subjects['SVT'] ?? null];
                $assignments[] = ['teacher' => $teachers['Histoire - Géographie'] ?? null, 'class' => $class, 'subject' => $subjects['HIST_GEO'] ?? null];
                $assignments[] = ['teacher' => $teachers['Anglais'] ?? null, 'class' => $class, 'subject' => $subjects['ANG'] ?? null];
                $assignments[] = ['teacher' => $teachers['Philosophie'] ?? null, 'class' => $class, 'subject' => $subjects['PHIL'] ?? null];
                $assignments[] = ['teacher' => $teachers['Informatique'] ?? null, 'class' => $class, 'subject' => $subjects['INFO'] ?? null];
                $assignments[] = ['teacher' => $teachers['Arabe'] ?? null, 'class' => $class, 'subject' => $subjects['ARABE'] ?? null];
                $assignments[] = ['teacher' => $teachers['EPS'] ?? null, 'class' => $class, 'subject' => $subjects['EPS'] ?? null];
            }
        }

        foreach ($assignments as $asn) {
            if (! $asn['teacher'] || ! $asn['subject']) {
                continue;
            }

            // Vérifier que la matière appartient bien au niveau de la classe (si configuré)
            $levelId = $asn['class']->level_id;
            $hasLevelCoeffs = DB::table('class_subject_coefficients')
                ->where('level_id', $levelId)
                ->exists();

            if ($hasLevelCoeffs) {
                $isAllowed = DB::table('class_subject_coefficients')
                    ->where('level_id', $levelId)
                    ->where('subject_id', $asn['subject']->id)
                    ->exists();

                if (! $isAllowed) {
                    continue;
                }
            }

            TeacherAssignment::updateOrCreate(
                [
                    'academic_year_id' => $activeYear->id,
                    'class_id' => $asn['class']->id,
                    'subject_id' => $asn['subject']->id,
                ],
                ['teacher_id' => $asn['teacher']->id]
            );
        }
    }
}
