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
            AdminUserSeeder::class,
            CycleAndLevelSeeder::class,
            SubjectSeeder::class,
            ClassSeeder::class,
            CoefficientSeeder::class,
            TeacherSeeder::class,
            StudentAndGuardianSeeder::class,
            TeacherAssignmentSeeder::class,
            ScheduleSeeder::class,
            PeriodSeeder::class,
            FeeSeeder::class,
            EvaluationSeeder::class,
            GradeSeeder::class,
            ReportCardSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
            AttendanceSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Utilisateur Test',
            'email' => 'test@example.com',
        ]);
    }
}
