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

        $profMath = Teacher::where('speciality', 'Mathématiques')->first();
        $profFran = Teacher::where('speciality', 'Français')->first();
        $profPC = Teacher::where('speciality', 'Physique - Chimie')->first();

        $math = Subject::where('code', 'MATH')->first();
        $fran = Subject::where('code', 'FRAN')->first();
        $pc = Subject::where('code', 'PC')->first();

        $class6A = SchoolClass::where('name', '6ème A')->first();
        $classTleD = SchoolClass::where('name', 'Terminale D1')->first();

        if ($profMath && $math && $class6A) {
            TeacherAssignment::firstOrCreate([
                'academic_year_id' => $activeYear->id,
                'class_id' => $class6A->id,
                'subject_id' => $math->id,
            ], ['teacher_id' => $profMath->id]);
        }

        if ($profFran && $fran && $class6A) {
            TeacherAssignment::firstOrCreate([
                'academic_year_id' => $activeYear->id,
                'class_id' => $class6A->id,
                'subject_id' => $fran->id,
            ], ['teacher_id' => $profFran->id]);
        }

        if ($profPC && $pc && $classTleD) {
            TeacherAssignment::firstOrCreate([
                'academic_year_id' => $activeYear->id,
                'class_id' => $classTleD->id,
                'subject_id' => $pc->id,
            ], ['teacher_id' => $profPC->id]);
        }
    }
}
