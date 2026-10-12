<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'expense_category_id',
        'academic_year_id',
        'user_id',
        'label',
        'amount',
        'expense_date',
        'payment_method',
        'supplier',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /** @return BelongsTo<ExpenseCategory, Expense> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /** @return BelongsTo<AcademicYear, Expense> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return BelongsTo<User, Expense> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique reference for a new expense.
     */
    public static function generateReference(): string
    {
        $prefix = 'DEP-'.date('Ymd');
        $lastRef = static::where('reference', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('reference');

        $seq = $lastRef ? (int) substr($lastRef, -4) + 1 : 1;

        return $prefix.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string,string>
     */
    public static function paymentMethods(): array
    {
        return [
            'ESPECES' => 'Espèces',
            'VIREMENT' => 'Virement Bancaire',
            'CHEQUE' => 'Chèque',
            'MOBILE_MONEY' => 'Mobile Money',
            'CARTE' => 'Carte Bancaire',
        ];
    }
}
