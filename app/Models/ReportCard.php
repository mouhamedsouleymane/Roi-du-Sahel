<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'period_id',
        'class_id',
        'general_average',
        'rank',
        'total_students',
        'class_average',
        'max_average',
        'min_average',
        'literary_average',
        'scientific_average',
        'conduct_grade',
        'details',
        'appreciation',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'general_average' => 'decimal:2',
            'rank' => 'integer',
            'total_students' => 'integer',
            'class_average' => 'decimal:2',
            'max_average' => 'decimal:2',
            'min_average' => 'decimal:2',
            'literary_average' => 'decimal:2',
            'scientific_average' => 'decimal:2',
            'conduct_grade' => 'decimal:2',
            'details' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Return appreciation label based on general_average.
     */
    public function getAppreciationLabelAttribute(): string
    {
        $avg = (float) $this->general_average;
        $isOutOfTen = $this->schoolClass?->isPrimaire() || $this->schoolClass?->isMaternelle() || ! ($this->schoolClass?->isSemesterBased() ?? true);

        if ($isOutOfTen) {
            return match (true) {
                $avg >= 9 => 'Excellent',
                $avg >= 8 => 'Très Bien',
                $avg >= 7 => 'Bien',
                $avg >= 6 => 'Assez Bien',
                $avg >= 5 => 'Passable',
                default => 'Insuffisant',
            };
        }

        return match (true) {
            $avg >= 18 => 'Excellent',
            $avg >= 16 => 'Très Bien',
            $avg >= 14 => 'Bien',
            $avg >= 12 => 'Assez Bien',
            $avg >= 10 => 'Passable',
            default => 'Insuffisant',
        };
    }

    /**
     * Determine if student passes based on cycle scale (/10 or /20).
     */
    public function getIsPassingAttribute(): bool
    {
        $avg = (float) $this->general_average;
        $isOutOfTen = $this->schoolClass?->isPrimaire() || $this->schoolClass?->isMaternelle() || ! ($this->schoolClass?->isSemesterBased() ?? true);

        return $isOutOfTen ? $avg >= 5.0 : $avg >= 10.0;
    }
}
