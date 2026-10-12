<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->is_polyvalent || $this->is_polyvalent_cycle;
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
     * For Maternelle and Primaire teachers, they can only be assigned to a single class.
     */
    public function canBeAssignedToClass(int $classId, int $academicYearId, ?int $exceptAssignmentId = null): bool
    {
        if (! $this->isRestrictedToOneClass()) {
            return true;
        }

        $query = $this->assignments()
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', '!=', $classId);

        if ($exceptAssignmentId) {
            $query->where('id', '!=', $exceptAssignmentId);
        }

        return ! $query->exists();
    }
}
