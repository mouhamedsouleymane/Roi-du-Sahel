<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentService = new PaymentService;

        $invoices = Invoice::where('status', '!=', 'ANNULEE')->get();

        foreach ($invoices as $invoice) {
            // 70% des factures sont payées, 20% partiellement, 10% impayées
            $roll = rand(1, 100);

            try {
                if ($roll <= 70) {
                    // Paiement complet
                    $paymentService->recordPayment($invoice, [
                        'amount' => (float) $invoice->amount_due,
                        'payment_method' => ['ESPECES', 'VIREMENT', 'MOBILE_MONEY'][rand(0, 2)],
                        'payment_date' => '2025-10-20',
                        'recorded_by' => 'Caissier Principal',
                    ]);
                } elseif ($roll <= 90) {
                    // Paiement partiel (50%)
                    $partial = (float) $invoice->amount_due * 0.5;
                    $paymentService->recordPayment($invoice, [
                        'amount' => $partial,
                        'payment_method' => 'ESPECES',
                        'payment_date' => '2025-10-20',
                        'recorded_by' => 'Caissier Principal',
                    ]);
                }
                // Sinon : impayée
            } catch (\Exception $e) {
                // Ignorer les erreurs de paiement
            }
        }
    }
}
