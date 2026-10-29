<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Student;
use Database\Seeders\ClassStudentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassStudentsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_classes_up_to_35_students(): void
    {
        $year = AcademicYear::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-15',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        $cycle = Cycle::create(['code' => 'COLLEGE', 'name' => 'Collège']);
        $level = Level::create(['cycle_id' => $cycle->id, 'code' => '6EME', 'name' => 'Sixième', 'order_index' => 1]);

        $classA = SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème A',
            'capacity' => 45,
        ]);

        $classB = SchoolClass::create([
            'academic_year_id' => $year->id,
            'level_id' => $level->id,
            'name' => '6ème B',
            'capacity' => 45,
        ]);

        // Pre-create 2 students in class A
        for ($i = 1; $i <= 2; $i++) {
            $student = Student::create([
                'matricule' => "RS-2026-000{$i}",
                'first_name' => "Eleve{$i}",
                'last_name' => 'Existant',
                'gender' => 'M',
                'birth_date' => '2014-01-01',
                'birth_place' => 'Niamey',
                'status' => 'ACTIF',
            ]);

            Enrollment::create([
                'student_id' => $student->id,
                'class_id' => $classA->id,
                'academic_year_id' => $year->id,
                'enrollment_number' => "INS-2026-000{$i}",
                'type' => 'NOUVEAU',
                'status' => 'VALIDE',
                'enrollment_date' => '2026-09-15',
            ]);
        }

        $this->assertEquals(2, $classA->enrollments()->where('status', 'VALIDE')->count());
        $this->assertEquals(0, $classB->enrollments()->where('status', 'VALIDE')->count());

        // Run the seeder
        $this->seed(ClassStudentsSeeder::class);

        // Class A and Class B must now have exactly 35 students each
        $this->assertEquals(35, $classA->enrollments()->where('status', 'VALIDE')->count());
        $this->assertEquals(35, $classB->enrollments()->where('status', 'VALIDE')->count());

        // Check that created students have guardians linked
        $sampleStudent = Student::where('first_name', '!=', 'Eleve1')->where('first_name', '!=', 'Eleve2')->first();
        $this->assertNotNull($sampleStudent);
        $this->assertCount(1, $sampleStudent->guardians);
        $this->assertNotNull($sampleStudent->guardians->first()->phone_primary);

        // Check idempotency: running seeder again should not add more students
        $this->seed(ClassStudentsSeeder::class);
        $this->assertEquals(35, $classA->enrollments()->where('status', 'VALIDE')->count());
        $this->assertEquals(35, $classB->enrollments()->where('status', 'VALIDE')->count());
    }
}
