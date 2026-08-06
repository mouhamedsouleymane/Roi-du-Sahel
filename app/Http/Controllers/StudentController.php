<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display listing of students.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $classId = $request->query('class_id');
        $activeYear = AcademicYear::getActive();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $students = Student::with(['guardians', 'enrollments.schoolClass.level.cycle'])
            ->when($search, function ($q) use ($search) {
                $q->where('matricule', 'LIKE', "%{$search}%")
                  ->orWhere('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%");
            })
            ->when($classId, function ($q) use ($classId) {
                $q->whereHas('enrollments', function ($eq) use ($classId) {
                    $eq->where('class_id', $classId)->where('status', 'VALIDE');
                });
            })
            ->orderBy('last_name')
            ->paginate(15);

        return view('students.index', compact('students', 'classes', 'search', 'classId'));
    }

    /**
     * Display student record / profile.
     */
    public function show(Student $student): View
    {
        $student->load(['guardians', 'enrollments.schoolClass.level.cycle', 'enrollments.academicYear']);

        return view('students.show', compact('student'));
    }
}
