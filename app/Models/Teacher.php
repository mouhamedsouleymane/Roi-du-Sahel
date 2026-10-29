<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cycle_id',
        'matricule',
        'speciality',
        'qualification',
        'employment_type',
        'hire_date',
        'status',
        'is_polyvalent',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'is_polyvalent' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function cycles(): BelongsToMany
    {
        return $this->belongsToMany(Cycle::class, 'teacher_cycles')->withTimestamps();
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'teacher_levels')->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->user ? $this->user->name : "Enseignant #{$this->matricule}";
    }

    /**
     * Cycles whose teachers are polyvalent (teach all subjects in the class).
     * Detected by matching the cycle name (case-insensitive).
     */
    public static function polyvalentCycleNames(): array
    {
        return ['maternelle', 'primaire'];
    }

    /**
     * Whether this teacher's cycle is inherently polyvalent.
     */
    public function getIsPolyvalentCycleAttribute(): bool
    {
        if (! $this->cycle) {
            return false;
        }

        return in_array(
            strtolower(trim($this->cycle->name)),
            self::polyvalentCycleNames(),
            strict: true
        );
    }

    /**
     * Whether this teacher is restricted to a single class (Maternelle or Primaire).
     */
    public function isRestrictedToOneClass(): bool
    {
        if ($this->is_polyvalent || $this->is_polyvalent_cycle) {
            return true;
        }

        if ($this->relationLoaded('cycles')) {
            return $this->cycles->contains(
                fn ($c) => in_array(strtolower(trim($c->name)), self::polyvalentCycleNames(), true)
            );
        }

        return $this->cycles()
            ->whereIn(DB::raw('LOWER(TRIM(name))'), self::polyvalentCycleNames())
            ->exists();
    }

    /**
     * Get the distinct class IDs this teacher is assigned to for a given academic year.
     *
     * @return array<int>
     */
    public function assignedClassIds(int $academicYearId): array
    {
        return $this->assignments()
            ->where('academic_year_id', $academicYearId)
            ->pluck('class_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Check if the teacher can be assigned to a specific class for the given academic year.
     * - Maternelle & Primaire teachers: restricted to ONE and only ONE class of their cycle.
     * - Collège & Lycée teachers: specialist teachers who can teach across multiple secondary classes.
     * - Teachers cannot cross between Maternelle/Primaire and Secondaire (Collège/Lycée).
     */
    public function canBeAssignedToClass(int $classId, int $academicYearId, ?int $exceptAssignmentId = null): bool
    {
        $targetClass = SchoolClass::with('level.cycle')->find($classId);

        if (! $targetClass) {
            return false;
        }

        $targetCycleName = strtolower(trim($targetClass->level?->cycle?->name ?? ''));
        $isTargetPolyvalent = in_array($targetCycleName, self::polyvalentCycleNames(), true);

        // 1. Classe Maternelle ou Primaire :
        // Seul un enseignant de ce cycle peut être affecté, et à une seule classe max par an
        if ($isTargetPolyvalent) {
            if (! $this->isRestrictedToOneClass()) {
                return false;
            }

            // Vérifier que le cycle de l'instituteur correspond au cycle de la classe
            $allowedCycleIds = $this->relationLoaded('cycles')
                ? $this->cycles->pluck('id')->all()
                : $this->cycles()->pluck('cycles.id')->all();

            if ($this->cycle_id && ! in_array($this->cycle_id, $allowedCycleIds, true)) {
                $allowedCycleIds[] = $this->cycle_id;
            }

            if (! empty($allowedCycleIds) && ! in_array($targetClass->level->cycle_id, $allowedCycleIds, true)) {
                return false;
            }

            // Règle d'une et une seule classe max par année scolaire
            $query = $this->assignments()
                ->where('academic_year_id', $academicYearId)
                ->where('class_id', '!=', $classId);

            if ($exceptAssignmentId) {
                $query->where('id', '!=', $exceptAssignmentId);
            }

            return ! $query->exists();
        }

        // 2. Classe Secondaire (Collège ou Lycée) :
        // Un instituteur de Maternelle ou Primaire ne peut PAS enseigner au secondaire
        if ($this->isRestrictedToOneClass()) {
            return false;
        }

        // Pour un enseignant spécialiste du secondaire, il peut intervenir dans plusieurs classes
        return true;
    }

    /**
     * Get the distinct subject IDs this teacher is assigned to for a given academic year.
     *
     * @return array<int>
     */
    public function assignedSubjectIds(int $academicYearId): array
    {
        return $this->assignments()
            ->where('academic_year_id', $academicYearId)
            ->pluck('subject_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get the classes this teacher is assigned to for a given academic year.
     */
    public function assignedClasses(int $academicYearId)
    {
        $classIds = $this->assignedClassIds($academicYearId);

        return SchoolClass::with('level.cycle')->whereIn('id', $classIds)->get();
    }

    /**
     * Get the subjects this teacher is assigned to for a given academic year.
     */
    public function assignedSubjects(int $academicYearId)
    {
        $subjectIds = $this->assignedSubjectIds($academicYearId);

        return Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
    }
}
