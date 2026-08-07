<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_recurring',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_recurring' => 'boolean',
            'is_active'    => 'boolean',
        ];
    }

    public function structures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
