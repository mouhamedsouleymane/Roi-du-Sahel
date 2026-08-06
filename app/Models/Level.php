<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_id',
        'code',
        'name',
        'order_index',
        'description',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'level_id');
    }

    public function coefficients(): HasMany
    {
        return $this->hasMany(ClassSubjectCoefficient::class, 'level_id');
    }
}
