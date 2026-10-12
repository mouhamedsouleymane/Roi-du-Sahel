<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'uniform_tshirt_color',
        'start_time',
        'end_time',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('order_index');
    }

    /**
     * Determine if this cycle is organized by trimesters (Maternelle & Primaire).
     */
    public function isTrimesterBased(): bool
    {
        $code = strtoupper($this->code ?? '');
        $name = strtoupper($this->name ?? '');

        return in_array($code, ['MATERNELLE', 'PRIMAIRE'], true)
            || in_array($name, ['MATERNELLE', 'PRIMAIRE'], true);
    }

    /**
     * Determine if this cycle is organized by semesters (Collège & Lycée).
     */
    public function isSemesterBased(): bool
    {
        $code = strtoupper($this->code ?? '');
        $name = strtoupper($this->name ?? '');

        return in_array($code, ['COLLEGE', 'LYCEE'], true)
            || in_array($name, ['COLLÈGE', 'COLLEGE', 'LYCÉE', 'LYCEE'], true);
    }

    /**
     * Get the expected period type for this cycle ('TRIMESTRE' or 'SEMESTRE').
     */
    public function expectedPeriodType(): string
    {
        return $this->isSemesterBased() ? 'SEMESTRE' : 'TRIMESTRE';
    }
}
