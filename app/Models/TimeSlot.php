<?php

namespace App\Models;

use Database\Factories\TimeSlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    /** @use HasFactory<TimeSlotFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'start_time',
        'end_time',
        'cycle_id',
        'academic_year_id',
        'order_index',
        'is_pause',
        'is_active',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'is_pause' => 'boolean',
            'is_active' => 'boolean',
            'order_index' => 'integer',
        ];
    }

    /**
     * Scope a query to only include active time slots.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by order_index then start_time.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_index')->orderBy('start_time');
    }

    /**
     * Scope a query to filter by cycle or global.
     */
    public function scopeForCycle(Builder $query, ?int $cycleId): Builder
    {
        if (! $cycleId) {
            return $query->whereNull('cycle_id');
        }

        return $query->where(function ($q) use ($cycleId) {
            $q->where('cycle_id', $cycleId)->orWhereNull('cycle_id');
        });
    }

    /**
     * Relation avec le cycle pédagogique (optionnel).
     */
    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    /**
     * Relation avec l'année académique (optionnelle).
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relation avec les créneaux d'emploi du temps associés.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
