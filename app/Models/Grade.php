<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'student_id',
        'score',
        'is_absent',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'score'     => 'decimal:2',
            'is_absent' => 'boolean',
        ];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Return the score normalized out of 20, taking max_score into account.
     */
    public function getNormalizedScoreAttribute(): ?float
    {
        if ($this->is_absent || $this->score === null) {
            return null;
        }

        $maxScore = $this->evaluation?->max_score ?? 20;

        return round(($this->score / $maxScore) * 20, 2);
    }
}
