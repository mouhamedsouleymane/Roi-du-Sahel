<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'level_id',
        'academic_year_id',
        'name',
        'capacity',
        'room_number',
        'main_teacher_id',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function mainTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'main_teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'class_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /**
     * Get the expected period type for this class ('TRIMESTRE' or 'SEMESTRE').
     */
    public function expectedPeriodType(): string
    {
        return $this->level?->cycle?->expectedPeriodType() ?? 'TRIMESTRE';
    }

    /**
     * Determine if this class is trimester-based.
     */
    public function isTrimesterBased(): bool
    {
        return $this->expectedPeriodType() === 'TRIMESTRE';
    }

    /**
     * Determine if this class is semester-based.
     */
    public function isSemesterBased(): bool
    {
        return $this->expectedPeriodType() === 'SEMESTRE';
    }
}
