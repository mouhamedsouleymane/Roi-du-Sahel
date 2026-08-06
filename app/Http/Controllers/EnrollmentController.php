<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments.
     */
    public function index(): View
    {
        $activeYear = AcademicYear::getActive();

        $enrollments = Enrollment::with(['student.guardians', 'schoolClass.level.cycle', 'academicYear'])
            ->when($activeYear, function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('enrollments.index', compact('enrollments', 'activeYear'));
    }

    /**
     * Show registration form for new student.
     */
    public function create(): View
    {
        $activeYear = AcademicYear::getActive();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        return view('enrollments.create', compact('classes', 'activeYear'));
    }

    /**
     * Process new student enrollment.
     */
    public function store(Request $request, EnrollmentService $enrollmentService): RedirectResponse
    {
        $validated = $request->validate([
            // Student data
            'first_name' => 'required|string|max:125',
            'last_name' => 'required|string|max:125',
            'gender' => 'required|in:M,F',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:125',
            'blood_group' => 'nullable|string|max:10',
            'previous_school' => 'nullable|string|max:191',
            'medical_notes' => 'nullable|string',

            // Guardian data
            'guardian_first_name' => 'required|string|max:125',
            'guardian_last_name' => 'required|string|max:125',
            'relationship' => 'required|string|max:50',
            'phone_primary' => 'required|string|max:50',
            'phone_secondary' => 'nullable|string|max:50',
            'guardian_email' => 'nullable|email|max:125',
            'address' => 'nullable|string|max:191',

            // Enrollment data
            'class_id' => 'required|exists:classes,id',
            'type' => 'required|in:NOUVEAU,REINSCRIPTION,TRANSFERT',
            'is_repeater' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $studentData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'birth_place' => $validated['birth_place'],
            'blood_group' => $validated['blood_group'] ?? null,
            'previous_school' => $validated['previous_school'] ?? null,
            'medical_notes' => $validated['medical_notes'] ?? null,
        ];

        $guardianData = [
            'first_name' => $validated['guardian_first_name'],
            'last_name' => $validated['guardian_last_name'],
            'relationship' => $validated['relationship'],
            'phone_primary' => $validated['phone_primary'],
            'phone_secondary' => $validated['phone_secondary'] ?? null,
            'email' => $validated['guardian_email'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        $enrollment = $enrollmentService->enrollNewStudent(
            studentData: $studentData,
            guardianData: $guardianData,
            classId: $validated['class_id'],
            type: $validated['type'],
            isRepeater: (bool) ($validated['is_repeater'] ?? false),
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('students.show', $enrollment->student_id)
            ->with('status', "Inscription de l'élève effectuée avec succès (Matricule: {$enrollment->student->matricule}).");
    }
}
