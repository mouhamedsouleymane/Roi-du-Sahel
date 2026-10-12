<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'academic_year_id',
        'level_id',
        'student_last_name',
        'student_first_name',
        'birth_date',
        'birth_place',
        'gender',
        'blood_group',
        'previous_school',
        'medical_notes',
        'requested_cycle',
        'requested_level',
        'type',
        'guardian_first_name',
        'guardian_last_name',
        'relationship',
        'guardian_phone',
        'guardian_phone_secondary',
        'guardian_email',
        'guardian_city',
        'guardian_profession',
        'guardian_address',
        'status',
        'notes',
        'processed_at',
        'processed_by',
        'converted_enrollment_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'processed_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function convertedEnrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'converted_enrollment_id');
    }

    public function getStudentFullNameAttribute(): string
    {
        return "{$this->student_first_name} {$this->student_last_name}";
    }

    public function getGuardianFullNameAttribute(): string
    {
        return "{$this->guardian_first_name} {$this->guardian_last_name}";
    }
}
