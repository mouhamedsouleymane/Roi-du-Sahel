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
        'appreciation',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'general_average' => 'decimal:2',
            'rank'            => 'integer',
            'total_students'  => 'integer',
            'is_published'    => 'boolean',
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

        return match (true) {
            $avg >= 18 => 'Excellent',
            $avg >= 16 => 'Très Bien',
            $avg >= 14 => 'Bien',
            $avg >= 12 => 'Assez Bien',
            $avg >= 10 => 'Passable',
            default    => 'Insuffisant',
        };
    }
}
