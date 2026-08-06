<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSubjectCoefficient extends Model
{
    use HasFactory;

    protected $table = 'class_subject_coefficients';

    protected $fillable = [
        'level_id',
        'subject_id',
        'coefficient',
        'weekly_hours',
        'is_optional',
    ];

    protected function casts(): array
    {
        return [
            'is_optional' => 'boolean',
            'coefficient' => 'integer',
            'weekly_hours' => 'integer',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
