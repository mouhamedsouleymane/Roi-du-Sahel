<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments grouped by levels with statistics and filters.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $levelId = $request->query('level_id');
        $classId = $request->query('class_id');
        $cycleId = $request->query('cycle_id');
        $type = $request->query('type');
        $status = $request->query('status');
        $activeYear = AcademicYear::getActive();

        $cycles = Cycle::with(['levels' => function ($q) {
            $q->orderBy('order_index');
        }])->where('is_active', true)->get();

        $levels = Level::with(['cycle', 'classes' => function ($q) use ($activeYear) {
            $q->where('academic_year_id', $activeYear?->id ?? 0);
        }])
            ->when($cycleId, fn ($q) => $q->where('cycle_id', $cycleId))
            ->orderBy('order_index')
            ->get();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->when($levelId, fn ($q) => $q->where('level_id', $levelId))
            ->get();

        // Statistiques globales pour l'année active
        $totalEnrollments = Enrollment::when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))->count();
        $newEnrollmentsCount = Enrollment::when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->where('type', 'NOUVEAU')->count();
        $reEnrollmentsCount = Enrollment::when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->whereIn('type', ['REINSCRIPTION', 'TRANSFERT'])->count();
        $validEnrollmentsCount = Enrollment::when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->where('status', 'VALIDE')->count();

        // Récupération des inscriptions
        $enrollmentsQuery = Enrollment::with(['student.guardians', 'schoolClass.level.cycle', 'academicYear'])
            ->when($activeYear, function ($q) use ($activeYear) {
                $q->where('academic_year_id', $activeYear->id);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('enrollment_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('student', function ($stq) use ($search) {
                            $stq->where('matricule', 'LIKE', "%{$search}%")
                                ->orWhere('first_name', 'LIKE', "%{$search}%")
                                ->orWhere('last_name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($levelId, function ($q) use ($levelId) {
                $q->whereHas('schoolClass', fn ($cq) => $cq->where('level_id', $levelId));
            })
            ->when($classId, function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->when($cycleId, function ($q) use ($cycleId) {
                $q->whereHas('schoolClass.level', fn ($lq) => $lq->where('cycle_id', $cycleId));
            })
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('id', 'desc');

        $enrollments = $enrollmentsQuery->paginate(30)->withQueryString();

        // Grouper les inscriptions de la collection courante par Niveau
        $enrollmentsByLevel = $enrollments->getCollection()->groupBy(function ($enrollment) {
            $level = $enrollment->schoolClass?->level;
            $cycle = $level?->cycle;

            return ($cycle?->name ?? 'Autre').' — '.($level?->name ?? 'Sans niveau');
        });

        return view('enrollments.index', compact(
            'enrollments',
            'enrollmentsByLevel',
            'cycles',
            'levels',
            'classes',
            'activeYear',
            'search',
            'levelId',
            'classId',
            'cycleId',
            'type',
            'status',
            'totalEnrollments',
            'newEnrollmentsCount',
            'reEnrollmentsCount',
            'validEnrollmentsCount'
        ));
    }

    /**
     * Show registration form for new student.
     */
    public function create(Request $request): View
    {
        $activeYear = AcademicYear::getActive();
        $preselectedClassId = $request->query('class_id');

        $cycles = Cycle::with(['levels' => function ($q) {
            $q->orderBy('order_index');
        }])->where('is_active', true)->get();

        $levels = Level::with(['cycle', 'classes' => function ($q) use ($activeYear) {
            $q->where('academic_year_id', $activeYear?->id ?? 0);
        }])
            ->orderBy('order_index')
            ->get();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $inscriptFeeType = FeeType::where('code', 'INSCRIPT')->first();
        $scolFeeType = FeeType::where('code', 'SCOLARITE')->first();
        $scolT1FeeType = FeeType::where('code', 'SCOLARITE_T1')->first();

        $feeStructures = $activeYear ? FeeStructure::where('academic_year_id', $activeYear->id)
            ->whereIn('fee_type_id', array_filter([
                $inscriptFeeType?->id,
                $scolFeeType?->id,
                $scolT1FeeType?->id,
            ]))
            ->get() : collect();

        $feeMap = [
            'by_level' => [],
            'by_cycle' => [],
            'global' => [
                'inscript' => 0,
                'scol' => 0,
                't1' => 0,
            ],
        ];

        foreach ($feeStructures as $struct) {
            $typeKey = match ($struct->fee_type_id) {
                $inscriptFeeType?->id => 'inscript',
                $scolFeeType?->id => 'scol',
                $scolT1FeeType?->id => 't1',
                default => null,
            };

            if (! $typeKey) {
                continue;
            }

            if ($struct->level_id) {
                $feeMap['by_level'][$struct->level_id][$typeKey] = (float) $struct->amount;
            } elseif ($struct->cycle_id) {
                $feeMap['by_cycle'][$struct->cycle_id][$typeKey] = (float) $struct->amount;
            } else {
                $feeMap['global'][$typeKey] = (float) $struct->amount;
            }
        }

        return view('enrollments.create', compact(
            'classes',
            'cycles',
            'levels',
            'activeYear',
            'preselectedClassId',
            'feeMap'
        ));
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
            'guardian_profession' => 'nullable|string|max:125',
            'guardian_city' => 'nullable|string|max:125',
            'address' => 'nullable|string|max:191',

            // Enrollment data
            'class_id' => 'required|exists:classes,id',
            'type' => 'required|in:NOUVEAU,REINSCRIPTION,TRANSFERT',
            'is_repeater' => 'nullable|boolean',
            'notes' => 'nullable|string',

            // Payment data
            'payment_method' => 'required|string|in:ESPECES,MOBILE_MONEY,VIREMENT,CHEQUE',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_reference' => 'nullable|string|max:100',
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
            'profession' => $validated['guardian_profession'] ?? null,
            'phone_primary' => $validated['phone_primary'],
            'phone_secondary' => $validated['phone_secondary'] ?? null,
            'email' => $validated['guardian_email'] ?? null,
            'city' => $validated['guardian_city'] ?? 'Niamey',
            'address' => $validated['address'] ?? null,
        ];

        $enrollment = $enrollmentService->enrollNewStudent(
            studentData: $studentData,
            guardianData: $guardianData,
            classId: (int) $validated['class_id'],
            type: $validated['type'],
            isRepeater: (bool) ($validated['is_repeater'] ?? false),
            notes: $validated['notes'] ?? null,
            paymentMethod: $validated['payment_method'],
            customAmountPaid: isset($validated['amount_paid']) ? (float) $validated['amount_paid'] : null,
            paymentReference: $validated['payment_reference'] ?? null
        );

        return redirect()->route('enrollments.receipt', $enrollment->id)
            ->with('status', "Inscription de l'élève effectuée avec succès (Matricule: {$enrollment->student->matricule}). Reçu d'inscription généré.");
    }

    /**
     * Display official enrollment receipt.
     */
    public function receipt(Enrollment $enrollment): View
    {
        $enrollment->load(['student.guardians', 'schoolClass.level.cycle', 'academicYear']);

        $invoices = Invoice::with(['feeType', 'payments'])
            ->where('enrollment_id', $enrollment->id)
            ->get();

        return view('enrollments.receipt', compact('enrollment', 'invoices'));
    }

    /**
     * Display the specified enrollment.
     */
    public function show(Enrollment $enrollment): View
    {
        $enrollment->load([
            'student.guardians',
            'schoolClass.level.cycle',
            'academicYear',
            'invoices.feeType',
            'invoices.payments',
        ]);

        return view('enrollments.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified enrollment.
     */
    public function edit(Enrollment $enrollment): View
    {
        $cycles = Cycle::with(['levels' => function ($q) {
            $q->orderBy('order_index');
        }])->where('is_active', true)->get();

        $levels = Level::with(['cycle', 'classes' => function ($q) use ($enrollment) {
            $q->where('academic_year_id', $enrollment->academic_year_id);
        }])
            ->orderBy('order_index')
            ->get();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $enrollment->academic_year_id)
            ->get();

        return view('enrollments.edit', compact('enrollment', 'classes', 'cycles', 'levels'));
    }

    /**
     * Update the specified enrollment in storage.
     */
    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'type' => 'required|in:NOUVEAU,REINSCRIPTION,TRANSFERT',
            'status' => 'required|in:EN_ATTENTE,VALIDE,ANNULE',
            'enrollment_date' => 'required|date',
            'is_repeater' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_repeater'] = (bool) ($request->has('is_repeater'));

        $enrollment->update($validated);

        return redirect()->route('enrollments.show', $enrollment)
            ->with('status', 'L\'inscription a été mise à jour avec succès.');
    }
}
