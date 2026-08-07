<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'student_id',
        'enrollment_id',
        'academic_year_id',
        'fee_type_id',
        'amount_due',
        'amount_paid',
        'status',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_due'  => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_date'    => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getAmountRemainingAttribute(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->amount_remaining === 0.0;
    }

    /**
     * Recalculate and persist status based on paid amount.
     */
    public function recalculateStatus(): void
    {
        $paid = (float) $this->amount_paid;
        $due  = (float) $this->amount_due;

        if ($paid <= 0) {
            $this->status = 'IMPAYEE';
        } elseif ($paid >= $due) {
            $this->status = 'PAYEE';
        } else {
            $this->status = 'PARTIELLE';
        }

        $this->save();
    }
}
