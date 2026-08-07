<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    /**
     * Display the student portal with personal grades, timetable and attendance.
     */
    public function index(Request $request): View
    {
        $user       = auth()->user();
        $activeYear = AcademicYear::getActive();

        // Find linked Student by user_id
        $student = Student::where('user_id', $user->id)->first();

        // Fallback for admin preview / testing
        if (! $student && $request->has('student_id')) {
            $student = Student::find($request->query('student_id'));
        }

        if (! $student) {
            $student = Student::first();
        }

        $enrollment = $student?->enrollments()
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->where('status', 'VALIDE')
            ->with('schoolClass.level.cycle')
            ->first();

        $classId = $enrollment?->class_id;

        // Timetable
        $schedules = collect();
        if ($classId) {
            $schedules = Schedule::with(['subject', 'teacher'])
                ->where('class_id', $classId)
                ->get()
                ->groupBy('day_of_week');
        }

        // Grades
        $grades = collect();
        if ($student) {
            $grades = Grade::with(['evaluation.subject', 'evaluation.period'])
                ->where('student_id', $student->id)
                ->latest()
                ->limit(15)
                ->get();
        }

        // Published Report Cards
        $reportCards = collect();
        if ($student) {
            $reportCards = ReportCard::with(['period', 'schoolClass'])
                ->where('student_id', $student->id)
                ->where('is_published', true)
                ->get();
        }

        // Attendance stats
        $attendances = collect();
        if ($student) {
            $attendances = Attendance::with('subject')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $activeYear?->id ?? 0)
                ->orderByDesc('attendance_date')
                ->get();
        }

        return view('portal.student', compact(
            'student',
            'enrollment',
            'schedules',
            'grades',
            'reportCards',
            'attendances',
            'activeYear'
        ));
    }
}
