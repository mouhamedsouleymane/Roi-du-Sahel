<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\FeeType;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeYear = AcademicYear::getActive();
        if (! $activeYear) {
            return;
        }

        $paymentService = new PaymentService;

        // Générer les factures pour chaque type de frais
        $feeTypes = FeeType::all();

        foreach ($feeTypes as $feeType) {
            $paymentService->generateInvoicesForYear($feeType, $activeYear);
        }
    }
}
