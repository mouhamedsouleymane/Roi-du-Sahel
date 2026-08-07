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

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:125',
            'last_name' => 'required|string|max:125',
            'gender' => 'required|in:M,F',
            'birth_date' => 'required|date',
            'birth_place' => 'nullable|string|max:125',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'medical_conditions' => 'nullable|string',
        ]);

        $student->update($validated);

        return redirect()->route('students.show', $student)
            ->with('status', 'Les informations de l\'élève ont été mises à jour avec succès.');
    }
}
