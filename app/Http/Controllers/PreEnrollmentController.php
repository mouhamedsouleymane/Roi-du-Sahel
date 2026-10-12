<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\FeeStructure;
use App\Models\Level;
use App\Models\PreEnrollment;
use App\Models\SchoolClass;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreEnrollmentController extends Controller
{
    /**
     * Traite la soumission d'une demande de pré-inscription en ligne depuis le site web public.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Identité Élève
            'student_last_name' => 'required|string|max:125',
            'student_first_name' => 'required|string|max:125',
            'gender' => 'required|in:M,F',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:125',
            'blood_group' => 'nullable|string|max:10',
            'previous_school' => 'nullable|string|max:191',
            'medical_notes' => 'nullable|string',

            // Souhait d'admission
            'requested_cycle' => 'required|string|max:125',
            'requested_level' => 'nullable|string|max:125',
            'type' => 'nullable|string|in:NOUVEAU,TRANSFERT,REINSCRIPTION',

            // Tuteur Légal
            'guardian_last_name' => 'required|string|max:125',
            'guardian_first_name' => 'required|string|max:125',
            'relationship' => 'required|string|max:50',
            'guardian_profession' => 'nullable|string|max:125',
            'guardian_phone' => 'required|string|max:50',
            'guardian_phone_secondary' => 'nullable|string|max:50',
            'guardian_email' => 'nullable|email|max:125',
            'guardian_city' => 'nullable|string|max:125',
            'guardian_address' => 'nullable|string|max:191',

            'notes' => 'nullable|string|max:1000',
        ]);

        $activeYear = AcademicYear::getActive();
        $ref = 'PRE-'.date('Y').'-'.strtoupper(substr(md5(uniqid()), 0, 5));

        $preEnrollment = PreEnrollment::create([
            'reference' => $ref,
            'academic_year_id' => $activeYear?->id,
            'student_first_name' => $validated['student_first_name'],
            'student_last_name' => $validated['student_last_name'],
            'birth_date' => $validated['birth_date'],
            'birth_place' => $validated['birth_place'],
            'gender' => $validated['gender'],
            'blood_group' => $validated['blood_group'] ?? null,
            'previous_school' => $validated['previous_school'] ?? null,
            'medical_notes' => $validated['medical_notes'] ?? null,
            'requested_cycle' => $validated['requested_cycle'],
            'requested_level' => $validated['requested_level'] ?? null,
            'type' => $validated['type'] ?? 'NOUVEAU',
            'guardian_first_name' => $validated['guardian_first_name'],
            'guardian_last_name' => $validated['guardian_last_name'],
            'relationship' => $validated['relationship'],
            'guardian_profession' => $validated['guardian_profession'] ?? null,
            'guardian_phone' => $validated['guardian_phone'],
            'guardian_phone_secondary' => $validated['guardian_phone_secondary'] ?? null,
            'guardian_email' => $validated['guardian_email'] ?? null,
            'guardian_city' => $validated['guardian_city'] ?? 'Niamey',
            'guardian_address' => $validated['guardian_address'] ?? null,
            'status' => 'EN_ATTENTE',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect(url('/#preinscription'))->with('preinscription_success', [
            'ref' => $preEnrollment->reference,
            'name' => $preEnrollment->student_first_name.' '.$preEnrollment->student_last_name,
            'cycle' => $preEnrollment->requested_cycle,
        ]);
    }

    /**
     * Liste des demandes de pré-inscriptions (Back-Office).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $totalPreEnrollments = PreEnrollment::count();
        $pendingCount = PreEnrollment::where('status', 'EN_ATTENTE')->count();
        $validatedCount = PreEnrollment::where('status', 'VALIDEE')->count();
        $rejectedCount = PreEnrollment::where('status', 'REJETEE')->count();

        $preEnrollments = PreEnrollment::with(['academicYear', 'convertedEnrollment.schoolClass'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('student_first_name', 'LIKE', "%{$search}%")
                        ->orWhere('student_last_name', 'LIKE', "%{$search}%")
                        ->orWhere('reference', 'LIKE', "%{$search}%")
                        ->orWhere('guardian_phone', 'LIKE', "%{$search}%")
                        ->orWhere('guardian_first_name', 'LIKE', "%{$search}%")
                        ->orWhere('guardian_last_name', 'LIKE', "%{$search}%");
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderByRaw("CASE WHEN status = 'EN_ATTENTE' THEN 1 WHEN status = 'VALIDEE' THEN 2 ELSE 3 END")
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('pre_enrollments.index', compact(
            'preEnrollments',
            'search',
            'status',
            'totalPreEnrollments',
            'pendingCount',
            'validatedCount',
            'rejectedCount'
        ));
    }

    /**
     * Fiche détaillée d'une pré-inscription.
     */
    public function show(PreEnrollment $preEnrollment): View
    {
        $preEnrollment->load(['academicYear', 'processor', 'convertedEnrollment.schoolClass.level.cycle']);

        $activeYear = AcademicYear::getActive();

        $cycles = Cycle::with(['levels.classes' => fn ($q) => $q->where('academic_year_id', $activeYear?->id ?? 0)])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $levels = Level::with(['cycle', 'classes' => fn ($q) => $q->where('academic_year_id', $activeYear?->id ?? 0)])
            ->orderBy('order_index')
            ->get();

        $classes = SchoolClass::with('level.cycle')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        $feeStructures = FeeStructure::with('feeType')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->get();

        // Build fee map per level
        $feeMap = [];
        foreach ($levels as $lvl) {
            $inscriptFee = $feeStructures
                ->where('feeType.code', 'INSCRIPTION')
                ->first(fn ($f) => $f->level_id == $lvl->id || ($f->cycle_id == $lvl->cycle_id && ! $f->level_id));

            $tuitionFee = $feeStructures
                ->where('feeType.code', 'SCOLARITE')
                ->first(fn ($f) => $f->level_id == $lvl->id || ($f->cycle_id == $lvl->cycle_id && ! $f->level_id));

            $isPrimaryOrMaternelle = in_array(strtolower(trim($lvl->cycle?->name ?? '')), ['maternelle', 'primaire'], true);
            $defaultInscript = $isPrimaryOrMaternelle ? 1000 : 3000;

            $feeMap[$lvl->id] = [
                'level_id' => $lvl->id,
                'level_name' => $lvl->name,
                'cycle_id' => $lvl->cycle_id,
                'cycle_name' => $lvl->cycle?->name,
                'inscription_amount' => $inscriptFee ? (float) $inscriptFee->amount : $defaultInscript,
                'tuition_amount' => $tuitionFee ? (float) $tuitionFee->amount : 0,
            ];
        }

        // Try to match requested cycle and level
        $matchedCycle = null;
        $matchedLevel = null;
        if ($preEnrollment->requested_cycle) {
            $matchedCycle = $cycles->first(fn ($c) => strcasecmp(trim($c->name), trim($preEnrollment->requested_cycle)) === 0 || strcasecmp(trim($c->code), trim($preEnrollment->requested_cycle)) === 0);
        }
        if ($preEnrollment->requested_level && $matchedCycle) {
            $matchedLevel = $levels->where('cycle_id', $matchedCycle->id)->first(fn ($l) => strcasecmp(trim($l->name), trim($preEnrollment->requested_level)) === 0 || strcasecmp(trim($l->code), trim($preEnrollment->requested_level)) === 0);
        }

        $initialCycleId = $matchedCycle?->id ?? ($cycles->first()?->id ?? null);
        $initialLevelId = $matchedLevel?->id ?? null;

        return view('pre_enrollments.show', compact(
            'preEnrollment',
            'cycles',
            'levels',
            'classes',
            'feeMap',
            'activeYear',
            'initialCycleId',
            'initialLevelId'
        ));
    }

    /**
     * Convertir une pré-inscription en inscription officielle.
     */
    public function convert(Request $request, PreEnrollment $preEnrollment, EnrollmentService $enrollmentService): RedirectResponse
    {
        if ($preEnrollment->status === 'VALIDEE' && $preEnrollment->converted_enrollment_id) {
            return redirect()->route('enrollments.show', $preEnrollment->converted_enrollment_id)
                ->with('status', 'Cette candidature a déjà été convertie en inscription.');
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'payment_method' => 'required|string|in:ESPECES,MOBILE_MONEY,VIREMENT,CHEQUE',
            'amount_paid' => 'nullable|numeric|min:0',
            'birth_place' => 'nullable|string|max:125',
        ]);

        $studentData = [
            'first_name' => $preEnrollment->student_first_name,
            'last_name' => $preEnrollment->student_last_name,
            'gender' => $preEnrollment->gender,
            'birth_date' => $preEnrollment->birth_date->toDateString(),
            'birth_place' => $validated['birth_place'] ?? $preEnrollment->birth_place ?? 'Niamey',
            'blood_group' => $preEnrollment->blood_group,
            'previous_school' => $preEnrollment->previous_school,
            'medical_notes' => $preEnrollment->medical_notes,
        ];

        $guardianData = [
            'first_name' => $preEnrollment->guardian_first_name,
            'last_name' => $preEnrollment->guardian_last_name,
            'relationship' => $preEnrollment->relationship,
            'profession' => $preEnrollment->guardian_profession,
            'phone_primary' => $preEnrollment->guardian_phone,
            'phone_secondary' => $preEnrollment->guardian_phone_secondary,
            'email' => $preEnrollment->guardian_email,
            'city' => $preEnrollment->guardian_city ?? 'Niamey',
            'address' => $preEnrollment->guardian_address,
        ];

        $enrollment = $enrollmentService->enrollNewStudent(
            studentData: $studentData,
            guardianData: $guardianData,
            classId: (int) $validated['class_id'],
            type: $preEnrollment->type ?? 'NOUVEAU',
            isRepeater: false,
            notes: "Issu de la pré-inscription en ligne (Réf: {$preEnrollment->reference})".($preEnrollment->notes ? " — Note parent : {$preEnrollment->notes}" : ''),
            paymentMethod: $validated['payment_method'],
            customAmountPaid: isset($validated['amount_paid']) ? (float) $validated['amount_paid'] : null
        );

        // Mettre à jour le statut de la pré-inscription
        $preEnrollment->update([
            'status' => 'VALIDEE',
            'converted_enrollment_id' => $enrollment->id,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        return redirect()->route('enrollments.show', $enrollment)
            ->with('status', "La pré-inscription {$preEnrollment->reference} a été convertie avec succès en inscription officielle.");
    }

    /**
     * Rejeter une demande de pré-inscription.
     */
    public function reject(PreEnrollment $preEnrollment): RedirectResponse
    {
        $preEnrollment->update([
            'status' => 'REJETEE',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        return back()->with('status', "La candidature {$preEnrollment->reference} a été marquée comme rejetée.");
    }

    /**
     * Supprimer une demande de pré-inscription.
     */
    public function destroy(PreEnrollment $preEnrollment): RedirectResponse
    {
        $preEnrollment->delete();

        return redirect()->route('pre-enrollments.index')
            ->with('status', 'La demande de pré-inscription a été supprimée.');
    }
}
