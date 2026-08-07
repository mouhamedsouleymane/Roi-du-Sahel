<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\ReportCard;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    /**
     * Display the parent portal dashboard with children summaries.
     */
    public function index(Request $request): View
    {
        $user       = auth()->user();
        $activeYear = AcademicYear::getActive();

        // Find linked Guardian by user_id or email
        $guardian = Guardian::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        // Fallback for admin preview / testing if no linked guardian
        if (! $guardian && $request->has('guardian_id')) {
            $guardian = Guardian::find($request->query('guardian_id'));
        }

        $children = collect();
        if ($guardian) {
            $children = $guardian->students()
                ->with(['enrollments' => function ($q) use ($activeYear) {
                    $q->where('academic_year_id', $activeYear?->id ?? 0)
                      ->where('status', 'VALIDE')
                      ->with('schoolClass.level.cycle');
                }])
                ->get();
        } else {
            // For testing/preview when logged in as admin without guardian link
            $children = Student::with(['enrollments' => function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear?->id ?? 0)
                  ->where('status', 'VALIDE')
                  ->with('schoolClass.level.cycle');
            }])
            ->limit(3)
            ->get();
        }

        $selectedStudentId = $request->query('student_id', $children->first()?->id);
        $selectedStudent   = $children->firstWhere('id', $selectedStudentId) ?? $children->first();

        $reportCards = collect();
        $attendances = collect();
        $invoices    = collect();

        if ($selectedStudent) {
            // Published report cards
            $reportCards = ReportCard::with(['period', 'schoolClass'])
                ->where('student_id', $selectedStudent->id)
                ->where('is_published', true)
                ->orderByDesc('created_at')
                ->get();

            // Attendances
            $attendances = Attendance::with(['subject'])
                ->where('student_id', $selectedStudent->id)
                ->where('academic_year_id', $activeYear?->id ?? 0)
                ->orderByDesc('attendance_date')
                ->limit(20)
                ->get();

            // Invoices
            $invoices = Invoice::with(['feeType', 'payments'])
                ->where('student_id', $selectedStudent->id)
                ->where('academic_year_id', $activeYear?->id ?? 0)
                ->get();
        }

        return view('portal.parent', compact(
            'guardian',
            'children',
            'selectedStudent',
            'reportCards',
            'attendances',
            'invoices',
            'activeYear'
        ));
    }
}
