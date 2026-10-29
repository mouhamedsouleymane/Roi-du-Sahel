<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClassSubjectCoefficient;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Period;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;

class ReportCardService
{
    /**
     * Calculate subject scores (MC, NC, MS) for a student in a given subject and period.
     *
     * Pour les cycles Collège et Lycée (Semestre) :
     * - MC/20 (Moyenne de classe) : moyenne des évaluations de type DEVOIR et CONTROLE.
     * - NC/20 (Note de composition) : moyenne des évaluations de type COMPOSITION et EXAMEN.
     * - MS/20 (Moyenne semestrielle) : (MC + NC) / 2 (ou NC seul si matière à compo unique, ex: Conduite).
     *
     * Pour Maternelle et Primaire (Trimestre) :
     * - Moyenne arithmétique simple de toutes les évaluations sur 20.
     *
     * @return array{mc: float|null, nc: float|null, ms: float|null}
     */
    public function calculateSubjectScores(Student $student, int $subjectId, Period $period, bool $isSemesterBased = true): array
    {
        $evaluations = Evaluation::query()->where('period_id', $period->id)
            ->where('subject_id', $subjectId)
            ->with(['grades' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->get();

        if ($evaluations->isEmpty()) {
            return ['mc' => null, 'nc' => null, 'ms' => null];
        }

        $mcSum = 0.0;
        $mcCount = 0;

        $ncSum = 0.0;
        $ncCount = 0;

        $allSum = 0.0;
        $allCount = 0;

        $targetScale = $isSemesterBased ? 20.0 : 10.0;

        foreach ($evaluations as $eval) {
            /** @var Grade|null $grade */
            $grade = $eval->grades->first();

            if (! $grade || $grade->is_absent || $grade->score === null) {
                continue;
            }

            $maxScore = (float) ($eval->max_score > 0 ? $eval->max_score : $targetScale);
            $normalized = ((float) $grade->score / $maxScore) * $targetScale;

            $allSum += $normalized;
            $allCount++;

            $type = strtoupper((string) $eval->type);
            if (in_array($type, ['DEVOIR', 'CONTROLE'], true)) {
                $mcSum += $normalized;
                $mcCount++;
            } elseif (in_array($type, ['COMPOSITION', 'EXAMEN'], true)) {
                $ncSum += $normalized;
                $ncCount++;
            } else {
                // Par défaut, toute autre évaluation continue va dans le bloc MC
                $mcSum += $normalized;
                $mcCount++;
            }
        }

        if ($allCount === 0) {
            return ['mc' => null, 'nc' => null, 'ms' => null];
        }

        $mc = $mcCount > 0 ? round($mcSum / $mcCount, 2) : null;
        $nc = $ncCount > 0 ? round($ncSum / $ncCount, 2) : null;

        if ($isSemesterBased) {
            if ($mc !== null && $nc !== null) {
                $ms = round(($mc + $nc) / 2.0, 2);
            } elseif ($nc !== null) {
                // Cas spécifique (ex: Conduite, ou matière avec composition uniquement)
                $ms = round($nc, 2);
            } elseif ($mc !== null) {
                // Cas où seule la note de classe a été saisie
                $ms = round($mc, 2);
            } else {
                $ms = null;
            }
        } else {
            // Trimestriel (Maternelle & Primaire) :
            // Note de composition uniquement, barème officiel sur 10 points
            if ($nc !== null) {
                $ms = $nc;
            } elseif ($allCount > 0) {
                // Fallback si l'évaluation n'a pas été typée explicitement COMPOSITION
                $ms = round($allSum / $allCount, 2);
                $nc = $ms;
            } else {
                $ms = null;
            }
            $mc = null;
        }

        return [
            'mc' => $mc,
            'nc' => $nc,
            'ms' => $ms,
        ];
    }

    /**
     * Calculate subject average for a student (backwards-compatible).
     */
    public function calculateSubjectAverage(Student $student, int $subjectId, Period $period): ?float
    {
        return $this->calculateSubjectScores($student, $subjectId, $period)['ms'];
    }

    /**
     * Calculate general average and detailed report card payload for a student in a period.
     *
     * @param  array<int, array<int, int>>  $subjectRanks  Pre-calculated ranks per subject: [subject_id => [student_id => rank]]
     * @param  array<string, float|int>  $classStats  Pre-calculated class statistics
     * @return array{
     *     average: float,
     *     details: array<int, array<string, mixed>>,
     *     totals: array<string, float|int>,
     *     class_stats: array<string, float|int>,
     *     attendance: array<string, int>,
     *     annual_progression: array<string, float|null>,
     *     discipline: array<string, mixed>
     * }
     */
    public function calculateGeneralAverage(
        Student $student,
        Period $period,
        int $classId,
        array $subjectRanks = [],
        array $classStats = []
    ): array {
        $schoolClass = SchoolClass::with('level.cycle')->find($classId);
        $levelId = $schoolClass?->level_id ?? 0;
        $isSemesterBased = $schoolClass?->isSemesterBased() ?? true;

        $subjectCoefficients = ClassSubjectCoefficient::query()->where('level_id', $levelId)
            ->with('subject')
            ->get()
            ->keyBy('subject_id');

        // Fallback si aucune matière n'a été rattachée au niveau
        if ($subjectCoefficients->isEmpty()) {
            $evalSubjectIds = Evaluation::query()->where('period_id', $period->id)
                ->where('class_id', $classId)
                ->pluck('subject_id')
                ->unique();

            $fallbackSubjects = Subject::query()->whereIn('id', $evalSubjectIds)->get();
            foreach ($fallbackSubjects as $sbj) {
                $coeffModel = new ClassSubjectCoefficient([
                    'level_id' => $levelId,
                    'subject_id' => $sbj->id,
                    'coefficient' => 1.0,
                ]);
                $coeffModel->setRelation('subject', $sbj);
                $subjectCoefficients->put($sbj->id, $coeffModel);
            }
        }

        $weightedSum = 0.0;
        $totalWeight = 0.0;
        $subjectDetail = [];

        $litWeightedSum = 0.0;
        $litWeight = 0.0;

        $sciWeightedSum = 0.0;
        $sciWeight = 0.0;

        $conductGrade = null;

        foreach ($subjectCoefficients as $subjectId => $coeff) {
            $scores = $this->calculateSubjectScores($student, $subjectId, $period, $isSemesterBased);
            $ms = $scores['ms'];

            $subject = $coeff->subject;
            $subjectName = $subject?->name ?? 'Matière';
            $category = $subject?->resolvedCategory() ?? 'AUTRE';
            $subjectWeight = (float) ($coeff->coefficient ?? 1.0);

            $rankInSubject = $subjectRanks[$subjectId][$student->id] ?? null;

            if ($ms !== null) {
                $weighted = round($ms * $subjectWeight, 2);
                $weightedSum += $weighted;
                $totalWeight += $subjectWeight;

                if ($category === 'LITTERAIRE') {
                    $litWeightedSum += $weighted;
                    $litWeight += $subjectWeight;
                } elseif ($category === 'SCIENTIFIQUE') {
                    $sciWeightedSum += $weighted;
                    $sciWeight += $subjectWeight;
                }

                if (strtoupper(trim($subjectName)) === 'CONDUITE') {
                    $conductGrade = $ms;
                }
            } else {
                $weighted = null;
            }

            $appreciation = $ms !== null ? $this->resolveSubjectAppreciation($ms, ! $isSemesterBased) : '—';

            $subjectDetail[] = [
                'subject_id' => $subjectId,
                'subject' => $subjectName,
                'category' => $category,
                'mc' => $scores['mc'],
                'nc' => $scores['nc'],
                'ms' => $ms,
                'average' => $ms, // Alias pour rétro-compatibilité
                'coefficient' => $subjectWeight,
                'weighted' => $weighted,
                'rank' => $rankInSubject,
                'appreciation' => $appreciation,
            ];
        }

        $generalAvg = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.0;
        $literaryAvg = $litWeight > 0 ? round($litWeightedSum / $litWeight, 2) : null;
        $scientificAvg = $sciWeight > 0 ? round($sciWeightedSum / $sciWeight, 2) : null;

        // Assiduité pour cet élève sur la période
        $attendanceStats = $this->calculateStudentAttendanceStats($student, $period);

        // Progression annuelle (ex: Semestre 1 vs Semestre 2)
        $annualProgression = $this->resolveAnnualProgression($student, $period, $generalAvg);

        // Discipline & Mentions
        $discipline = $this->resolveDisciplineAndHonors($generalAvg, $conductGrade, $attendanceStats, ! $isSemesterBased);

        $totals = [
            'total_coefficients' => $totalWeight,
            'total_points' => round($weightedSum, 2),
            'general_average' => $generalAvg,
            'literary_average' => $literaryAvg,
            'scientific_average' => $scientificAvg,
            'conduct_grade' => $conductGrade,
        ];

        return [
            'average' => $generalAvg,
            'details' => $subjectDetail,
            'totals' => $totals,
            'class_stats' => $classStats,
            'attendance' => $attendanceStats,
            'annual_progression' => $annualProgression,
            'discipline' => $discipline,
        ];
    }

    /**
     * Generate and persist report cards for every enrolled student in a class and period.
     */
    public function generateForClass(int $classId, Period $period): int
    {
        $schoolClass = SchoolClass::with('level.cycle')->findOrFail($classId);
        $levelId = $schoolClass->level_id ?? 0;
        $isSemesterBased = $schoolClass->isSemesterBased();

        $enrollments = Enrollment::query()->where('class_id', $classId)
            ->where('academic_year_id', $period->academic_year_id)
            ->where('status', 'VALIDE')
            ->with('student')
            ->get();

        $totalStudents = $enrollments->count();
        if ($totalStudents === 0) {
            return 0;
        }

        // 1. Matières concernées pour ce niveau
        $subjectCoefficients = ClassSubjectCoefficient::query()->where('level_id', $levelId)
            ->with('subject')
            ->get()
            ->keyBy('subject_id');

        if ($subjectCoefficients->isEmpty()) {
            $evalSubjectIds = Evaluation::query()->where('period_id', $period->id)
                ->where('class_id', $classId)
                ->pluck('subject_id')
                ->unique();

            $fallbackSubjects = Subject::query()->whereIn('id', $evalSubjectIds)->get();
            foreach ($fallbackSubjects as $sbj) {
                $coeffModel = new ClassSubjectCoefficient([
                    'level_id' => $levelId,
                    'subject_id' => $sbj->id,
                    'coefficient' => 1.0,
                ]);
                $coeffModel->setRelation('subject', $sbj);
                $subjectCoefficients->put($sbj->id, $coeffModel);
            }
        }

        // 2. Pré-calcul des notes MS par matière pour tous les élèves, afin de déterminer les rangs par matière
        $subjectRanks = [];
        foreach ($subjectCoefficients as $subjectId => $coeff) {
            $subjectScoresByStudent = [];

            foreach ($enrollments as $enrollment) {
                $scores = $this->calculateSubjectScores($enrollment->student, $subjectId, $period, $isSemesterBased);
                if ($scores['ms'] !== null) {
                    $subjectScoresByStudent[$enrollment->student_id] = $scores['ms'];
                }
            }

            // Calcul du rang par matière (avec gestion ex-aequo)
            arsort($subjectScoresByStudent);
            $pos = 0;
            $prevScore = null;
            $prevRank = 1;

            foreach ($subjectScoresByStudent as $sId => $score) {
                $pos++;
                $assignedRank = ($score === $prevScore) ? $prevRank : $pos;
                $prevScore = $score;
                $prevRank = $assignedRank;
                $subjectRanks[$subjectId][$sId] = $assignedRank;
            }
        }

        // 3. Calcul temporaire des moyennes générales pour obtenir les statistiques de classe
        $averages = [];
        $payloads = [];

        foreach ($enrollments as $enrollment) {
            $payload = $this->calculateGeneralAverage(
                $enrollment->student,
                $period,
                $classId,
                $subjectRanks,
                []
            );

            $averages[$enrollment->student_id] = $payload['average'];
            $payloads[$enrollment->student_id] = $payload;
        }

        // 4. Statistiques globales de la classe
        $nonZeroAverages = array_filter($averages, fn ($avg) => $avg > 0);
        $maxAvg = ! empty($averages) ? max($averages) : 0.0;
        $minAvg = ! empty($nonZeroAverages) ? min($nonZeroAverages) : (! empty($averages) ? min($averages) : 0.0);
        $classAvg = ! empty($averages) ? round(array_sum($averages) / count($averages), 2) : 0.0;

        $classStats = [
            'total_students' => $totalStudents,
            'max_average' => $maxAvg,
            'min_average' => $minAvg,
            'class_average' => $classAvg,
        ];

        // 5. Rangs généraux de la classe (tri décroissant avec ex-aequo)
        arsort($averages);
        $pos = 0;
        $prevAvg = null;
        $prevRank = 1;

        foreach ($averages as $studentId => $avg) {
            $pos++;
            $assignedRank = ($avg === $prevAvg) ? $prevRank : $pos;
            $prevAvg = $avg;
            $prevRank = $assignedRank;

            $payload = $payloads[$studentId];
            $payload['class_stats'] = $classStats;

            ReportCard::updateOrCreate(
                ['student_id' => $studentId, 'period_id' => $period->id],
                [
                    'class_id' => $classId,
                    'general_average' => $avg,
                    'rank' => $assignedRank,
                    'total_students' => $totalStudents,
                    'class_average' => $classAvg,
                    'max_average' => $maxAvg,
                    'min_average' => $minAvg,
                    'literary_average' => $payload['totals']['literary_average'] ?? null,
                    'scientific_average' => $payload['totals']['scientific_average'] ?? null,
                    'conduct_grade' => $payload['totals']['conduct_grade'] ?? null,
                    'details' => $payload,
                    'appreciation' => $this->resolveGlobalAppreciation($avg, ! $isSemesterBased),
                    'is_published' => false,
                ]
            );
        }

        return $totalStudents;
    }

    /**
     * Publish all report cards for a given period and class.
     */
    public function publishForClass(int $classId, Period $period): int
    {
        return ReportCard::query()->where('class_id', $classId)
            ->where('period_id', $period->id)
            ->update(['is_published' => true]);
    }

    /**
     * Resolve appreciation text based on subject score.
     */
    private function resolveSubjectAppreciation(float $note, bool $isOutOfTen = false): string
    {
        if ($isOutOfTen) {
            return match (true) {
                $note >= 9 => 'Excellent',
                $note >= 8 => 'Très bien',
                $note >= 7 => 'Bien',
                $note >= 6 => 'Assez bien',
                $note >= 5 => 'Passable',
                $note >= 4 => 'Insuffisant',
                default => 'Faible',
            };
        }

        return match (true) {
            $note >= 18 => 'Excellent',
            $note >= 16 => 'Très bien',
            $note >= 14 => 'Bien',
            $note >= 12 => 'Assez bien',
            $note >= 10 => 'Passable',
            $note >= 8 => 'Insuffisant',
            default => 'Faible',
        };
    }

    /**
     * Resolve appreciation text for the principal / report card summary.
     */
    private function resolveGlobalAppreciation(float $avg, bool $isOutOfTen = false): string
    {
        if ($isOutOfTen) {
            return match (true) {
                $avg >= 8 => 'Félicitations du Conseil des Maîtres. Excellent travail.',
                $avg >= 7 => 'Encouragements. Bon travail.',
                $avg >= 6 => 'Tableau d\'Honneur. Travail satisfaisant.',
                $avg >= 5 => 'Passable. Peut mieux faire avec plus d\'efforts.',
                $avg >= 4 => 'Insuffisant. Doit fournir des efforts soutenus.',
                default => 'Faible. Redoublement d\'efforts impératif.',
            };
        }

        return match (true) {
            $avg >= 16 => 'Félicitations du Conseil de Classe. Excellent travail.',
            $avg >= 14 => 'Encouragements du Conseil de Classe. Bon travail.',
            $avg >= 12 => 'Tableau d\'Honneur. Travail satisfaisant.',
            $avg >= 10 => 'Passable. Peut mieux faire avec plus d\'assiduité.',
            $avg >= 8 => 'Insuffisant. Doit fournir des efforts soutenus.',
            default => 'Faible. Redoublement d\'efforts impératif.',
        };
    }

    /**
     * Calculate student attendance summary for the period.
     *
     * @return array{
     *     justified_absences: int,
     *     unjustified_absences: int,
     *     late_arrivals: int,
     *     expulsions: int
     * }
     */
    private function calculateStudentAttendanceStats(Student $student, Period $period): array
    {
        $query = Attendance::query()->where('student_id', $student->id);

        if ($period->start_date && $period->end_date) {
            $query->whereBetween('attendance_date', [$period->start_date, $period->end_date]);
        } elseif ($period->academic_year_id) {
            $query->where('academic_year_id', $period->academic_year_id);
        }

        $records = $query->get();

        $justified = 0;
        $unjustified = 0;
        $late = 0;
        $expulsions = 0;

        foreach ($records as $record) {
            $status = strtoupper((string) $record->status);
            $reason = trim((string) $record->reason);

            if ($status === 'ABSENT') {
                if ($reason !== '') {
                    $justified++;
                } else {
                    $unjustified++;
                }
            } elseif ($status === 'RETARD') {
                $late++;
            }

            if (str_contains(strtoupper($reason), 'EXPUL')) {
                $expulsions++;
            }
        }

        return [
            'justified_absences' => $justified,
            'unjustified_absences' => $unjustified,
            'late_arrivals' => $late,
            'expulsions' => $expulsions,
        ];
    }

    /**
     * Resolve annual progression (Semestre 1, Semestre 2, Annuelle).
     *
     * @return array{
     *     type: string,
     *     semester_1: float|null,
     *     semester_2: float|null,
     *     trimester_1: float|null,
     *     trimester_2: float|null,
     *     trimester_3: float|null,
     *     annual_average: float|null
     * }
     */
    private function resolveAnnualProgression(Student $student, Period $currentPeriod, float $currentAverage): array
    {
        $periodsInYear = Period::query()->where('academic_year_id', $currentPeriod->academic_year_id)
            ->where('type', $currentPeriod->type)
            ->orderBy('order')
            ->get();

        $otherCards = ReportCard::query()->where('student_id', $student->id)
            ->whereIn('period_id', $periodsInYear->pluck('id'))
            ->get()
            ->keyBy('period_id');

        $isTrimestre = $currentPeriod->type === 'TRIMESTRE';

        if ($isTrimestre) {
            $p1 = $periodsInYear->where('order', 1)->first();
            $p2 = $periodsInYear->where('order', 2)->first();
            $p3 = $periodsInYear->where('order', 3)->first();

            $t1 = $this->extractAverageForPeriod($p1, $currentPeriod, $currentAverage, $otherCards);
            $t2 = $this->extractAverageForPeriod($p2, $currentPeriod, $currentAverage, $otherCards);
            $t3 = $this->extractAverageForPeriod($p3, $currentPeriod, $currentAverage, $otherCards);

            $available = array_filter([$t1, $t2, $t3], fn ($v) => $v !== null);
            $annualAvg = ! empty($available) ? round(array_sum($available) / count($available), 2) : null;

            return [
                'type' => 'TRIMESTRE',
                'semester_1' => null,
                'semester_2' => null,
                'trimester_1' => $t1,
                'trimester_2' => $t2,
                'trimester_3' => $t3,
                'annual_average' => $annualAvg,
            ];
        }

        $firstPeriod = $periodsInYear->where('order', 1)->first();
        $secondPeriod = $periodsInYear->where('order', 2)->first();

        $sem1 = $this->extractAverageForPeriod($firstPeriod, $currentPeriod, $currentAverage, $otherCards);
        $sem2 = $this->extractAverageForPeriod($secondPeriod, $currentPeriod, $currentAverage, $otherCards);

        $available = array_filter([$sem1, $sem2], fn ($v) => $v !== null);
        $annualAvg = ! empty($available) ? round(array_sum($available) / count($available), 2) : null;

        return [
            'type' => 'SEMESTRE',
            'semester_1' => $sem1,
            'semester_2' => $sem2,
            'trimester_1' => null,
            'trimester_2' => null,
            'trimester_3' => null,
            'annual_average' => $annualAvg,
        ];
    }

    /**
     * Extrait la moyenne générale pour une période donnée.
     *
     * @param  Collection<int, ReportCard>  $otherCards
     */
    private function extractAverageForPeriod(?Period $targetPeriod, Period $currentPeriod, float $currentAverage, $otherCards): ?float
    {
        if (! $targetPeriod) {
            return null;
        }

        if ($currentPeriod->id === $targetPeriod->id) {
            return $currentAverage;
        }

        if ($otherCards->has($targetPeriod->id)) {
            return (float) $otherCards->get($targetPeriod->id)->general_average;
        }

        return null;
    }

    /**
     * Determine checkboxes for Conduct, Work, and Table of Honor.
     *
     * @param  array<string, int>  $attendanceStats
     * @return array<string, mixed>
     */
    private function resolveDisciplineAndHonors(float $generalAvg, ?float $conductGrade, array $attendanceStats, bool $isOutOfTen = false): array
    {
        if ($isOutOfTen) {
            // Conduite sur 10
            $conductGrade = $conductGrade ?? 9.0;
            $conduiteChoice = match (true) {
                $conductGrade >= 8 => 'Très bien',
                $conductGrade >= 6.5 => 'Bien',
                $conductGrade >= 5 => 'Passable',
                $conductGrade >= 3.5 => 'Mauvaise',
                default => 'Avertissement',
            };

            // Tableau d'honneur sur 10
            $honorChoice = match (true) {
                $generalAvg >= 8.0 => 'Félicitations',
                $generalAvg >= 7.0 => 'Encouragements',
                $generalAvg >= 6.0 => 'Inscrit',
                default => 'Non inscrit',
            };

            // Travail sur 10
            $workChoice = match (true) {
                $generalAvg < 4.0 => 'Blâme',
                $generalAvg < 5.0 => 'Avertissement',
                default => 'Remarques',
            };

            return [
                'conduite' => $conduiteChoice,
                'tableau_honneur' => $honorChoice,
                'travail' => $workChoice,
            ];
        }

        // Conduite sur 20
        $conductGrade = $conductGrade ?? 18.0; // Valeur par défaut standard si non notée
        $conduiteChoice = match (true) {
            $conductGrade >= 16 => 'Très bien',
            $conductGrade >= 13 => 'Bien',
            $conductGrade >= 10 => 'Passable',
            $conductGrade >= 7 => 'Mauvaise',
            default => 'Avertissement',
        };

        // Tableau d'honneur sur 20
        $honorChoice = match (true) {
            $generalAvg >= 16.0 => 'Félicitations',
            $generalAvg >= 14.0 => 'Encouragements',
            $generalAvg >= 12.0 => 'Inscrit',
            default => 'Non inscrit',
        };

        // Travail sur 20
        $workChoice = match (true) {
            $generalAvg < 8.0 => 'Blâme',
            $generalAvg < 10.0 => 'Avertissement',
            default => 'Remarques',
        };

        return [
            'conduite' => $conduiteChoice,
            'tableau_honneur' => $honorChoice,
            'travail' => $workChoice,
        ];
    }
}
