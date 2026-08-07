<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * Store a new payment for an invoice.
     */
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->status === 'PAYEE' || $invoice->status === 'ANNULEE') {
            return redirect()->back()->withErrors(['error' => 'Cette facture est déjà soldée ou annulée.']);
        }

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1|max:' . $invoice->amount_remaining,
            'payment_method' => 'required|in:ESPECES,VIREMENT,CHEQUE,MOBILE_MONEY',
            'payment_date'   => 'required|date|before_or_equal:today',
            'reference'      => 'nullable|string|max:125',
            'recorded_by'    => 'nullable|string|max:125',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            $payment = $this->paymentService->recordPayment($invoice, $validated);

            return redirect()->route('invoices.show', $invoice)
                ->with('status', "Paiement de {$payment->amount} FCFA enregistré. Reçu : {$payment->receipt_number}");
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
