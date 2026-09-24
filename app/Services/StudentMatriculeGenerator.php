<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Student;

class StudentMatriculeGenerator
{
    /**
     * Generate unique student matricule (e.g. RS-2025-0001).
     */
    public static function generate(?string $yearName = null): string
    {
        $yearPrefix = '2025';

        if ($yearName) {
            $parts = explode('-', $yearName);
            $yearPrefix = $parts[0] ?? '2025';
        } else {
            $activeYear = AcademicYear::getActive();
            if ($activeYear) {
                $parts = explode('-', $activeYear->name);
                $yearPrefix = $parts[0] ?? '2025';
            }
        }

        $latestStudent = Student::withTrashed()
            ->where('matricule', 'LIKE', "RS-{$yearPrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestStudent) {
            $parts = explode('-', $latestStudent->matricule);
            $lastNum = isset($parts[2]) ? (int) $parts[2] : 0;
            $nextNumber = $lastNum + 1;
        }

        return sprintf('RS-%s-%04d', $yearPrefix, $nextNumber);
    }

    /**
     * Generate unique enrollment number (e.g. INS-2025-0001).
     */
    public static function generateEnrollmentNumber(?string $yearName = null): string
    {
        $yearPrefix = '2025';
        if ($yearName) {
            $parts = explode('-', $yearName);
            $yearPrefix = $parts[0] ?? '2025';
        }

        $latestEnrollment = Enrollment::where('enrollment_number', 'LIKE', "INS-{$yearPrefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestEnrollment) {
            $parts = explode('-', $latestEnrollment->enrollment_number);
            $lastNum = isset($parts[2]) ? (int) $parts[2] : 0;
            $nextNumber = $lastNum + 1;
        }

        return sprintf('INS-%s-%04d', $yearPrefix, $nextNumber);
    }
}
