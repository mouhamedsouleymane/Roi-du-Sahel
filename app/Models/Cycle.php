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

    public function isMaternelle(): bool
    {
        $v = strtoupper(($this->code ?? '').' '.($this->name ?? ''));

        return str_contains($v, 'MATER') || str_contains($v, 'PRESCO');
    }

    public function isPrimaire(): bool
    {
        $v = strtoupper(($this->code ?? '').' '.($this->name ?? ''));

        return str_contains($v, 'PRIM');
    }

    public function isCollege(): bool
    {
        $v = strtoupper(($this->code ?? '').' '.($this->name ?? ''));

        return str_contains($v, 'COLLEG');
    }

    public function isLycee(): bool
    {
        $v = strtoupper(($this->code ?? '').' '.($this->name ?? ''));

        return str_contains($v, 'LYC');
    }

    /**
     * Retourne l'emoji représentatif du cycle.
     */
    public function iconEmoji(): string
    {
        return match (true) {
            $this->isMaternelle() => '🧸',
            $this->isPrimaire() => '🎒',
            $this->isCollege() => '🏛️',
            $this->isLycee() => '🎓',
            default => '🏫',
        };
    }

    /**
     * Titre officiel du signataire pour ce cycle.
     */
    public function signatureTitle(): string
    {
        return match (true) {
            $this->isMaternelle() => 'La Directrice du Préscolaire',
            $this->isPrimaire() => 'Le Directeur de l\'École Primaire',
            $this->isCollege() => 'Le Principal du Collège',
            $this->isLycee() => 'Le Proviseur du Lycée',
            default => 'Le Chef d\'Établissement',
        };
    }
}
