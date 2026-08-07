<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'type',
        'order',
        'start_date',
        'end_date',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_closed'  => 'boolean',
            'order'      => 'integer',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    /**
     * Get the currently active (open) period for a given academic year.
     */
    public static function getActive(int $academicYearId): ?static
    {
        return static::where('academic_year_id', $academicYearId)
            ->where('is_closed', false)
            ->orderBy('order')
            ->first();
    }
}
