<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;

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
        $subjects = Subject::all()->keyBy('code');
        $classes = SchoolClass::all();

        // Affectations par matière pour toutes les classes
        $assignments = [];

        foreach ($classes as $class) {
            $levelCode = $class->level?->code;

            // Primaire : instituteurs pour toutes les matières de base
            if (in_array($levelCode, ['CI', 'CP', 'CE1', 'CE2', 'CM1', 'CM2'])) {
                $instituteur = $teachers['Primaire'] ?? null;
                if ($instituteur) {
                    $assignments[] = ['teacher' => $instituteur, 'class' => $class, 'subject' => $subjects['FRAN'] ?? null];
                    $assignments[] = ['teacher' => $instituteur, 'class' => $class, 'subject' => $subjects['MATH'] ?? null];
                }
            }

            // Collège
            if (in_array($levelCode, ['6EME', '5EME', '4EME', '3EME'])) {
                $assignments[] = ['teacher' => $teachers['Mathématiques'] ?? null, 'class' => $class, 'subject' => $subjects['MATH'] ?? null];
                $assignments[] = ['teacher' => $teachers['Français'] ?? null, 'class' => $class, 'subject' => $subjects['FRAN'] ?? null];
                $assignments[] = ['teacher' => $teachers['Physique - Chimie'] ?? null, 'class' => $class, 'subject' => $subjects['PC'] ?? null];
                $assignments[] = ['teacher' => $teachers['SVT'] ?? null, 'class' => $class, 'subject' => $subjects['SVT'] ?? null];
                $assignments[] = ['teacher' => $teachers['Histoire - Géographie'] ?? null, 'class' => $class, 'subject' => $subjects['HIST_GEO'] ?? null];
                $assignments[] = ['teacher' => $teachers['Anglais'] ?? null, 'class' => $class, 'subject' => $subjects['ANG'] ?? null];
                $assignments[] = ['teacher' => $teachers['EPS'] ?? null, 'class' => $class, 'subject' => $subjects['EPS'] ?? null];
            }

            // Lycée
            if (in_array($levelCode, ['2NDE_A', '2NDE_C', '1ERE_A', '1ERE_C', '1ERE_D', 'TLE_A', 'TLE_C', 'TLE_D'])) {
                $assignments[] = ['teacher' => $teachers['Mathématiques'] ?? null, 'class' => $class, 'subject' => $subjects['MATH'] ?? null];
                $assignments[] = ['teacher' => $teachers['Français'] ?? null, 'class' => $class, 'subject' => $subjects['FRAN'] ?? null];
                $assignments[] = ['teacher' => $teachers['Physique - Chimie'] ?? null, 'class' => $class, 'subject' => $subjects['PC'] ?? null];
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

            TeacherAssignment::firstOrCreate(
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
