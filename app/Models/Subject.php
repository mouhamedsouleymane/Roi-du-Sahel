<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function coefficients(): HasMany
    {
        return $this->hasMany(ClassSubjectCoefficient::class);
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'class_subject_coefficients')
            ->withPivot(['coefficient', 'weekly_hours', 'is_optional'])
            ->withTimestamps();
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Resolve the normalized category (LITTERAIRE, SCIENTIFIQUE, AUTRE).
     */
    public function resolvedCategory(): string
    {
        $rawCat = strtoupper(trim((string) $this->category));
        if (str_contains($rawCat, 'LIT')) {
            return 'LITTERAIRE';
        }
        if (str_contains($rawCat, 'SCI')) {
            return 'SCIENTIFIQUE';
        }

        $normalized = strtoupper(trim($this->name.' '.$this->code));

        if (preg_match('/(FRANCAIS|FRANÇAIS|ANGLAIS|ARABE|HISTO|GEO|GÉO|ECM|PHILOSOPHIE|LITTERATURE|LITTÉRATURE|REDACTION|RÉDACTION|DICTEE|DICTÉE|LECTURE)/u', $normalized)) {
            return 'LITTERAIRE';
        }

        if (preg_match('/(MATH|PHYSIQUE|CHIMIE|SVT|SCIENCE|BIO|INFORMATIQUE|TIC)/u', $normalized)) {
            return 'SCIENTIFIQUE';
        }

        return 'AUTRE';
    }

    public function isLiterary(): bool
    {
        return $this->resolvedCategory() === 'LITTERAIRE';
    }

    public function isScientific(): bool
    {
        return $this->resolvedCategory() === 'SCIENTIFIQUE';
    }
}
