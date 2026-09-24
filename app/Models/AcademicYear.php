<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_closed',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get currently active academic year.
     */
    public static function getActive(): ?static
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Set this academic year as active (disabling others).
     */
    public function activate(): bool
    {
        static::where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->is_active = true;
        return $this->save();
    }
}
