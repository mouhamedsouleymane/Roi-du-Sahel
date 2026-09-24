<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SendWhatsAppPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:send-payment-reminders {--days=3 : Nombre de jours avant ou après échéance} {--type=all : Type de facture (all, scolarite, inscription)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des rappels WhatsApp automatiques aux parents ayant des factures impayées ou arrivant à échéance';

    public function handle(WhatsAppService $whatsapp): int
    {
        $this->info("🚀 Démarrage de l'envoi des rappels WhatsApp de paiement...");

        // Vérification de la présence des tables nécessaires
        if (!Schema::hasTable('invoices') || !Schema::hasTable('guardians')) {
            $this->warn("⚠️ Les tables nécessaires (invoices / guardians) ne sont pas encore présentes ou configurées.");
            return Command::SUCCESS;
        }

        // Récupérer les factures en attente / impayées
        $unpaidInvoices = DB::table('invoices')
            ->join('students', 'invoices.student_id', '=', 'students.id')
            ->join('guardians', 'students.guardian_id', '=', 'guardians.id')
            ->whereIn('invoices.status', ['pending', 'unpaid', 'partial'])
            ->whereNotNull('guardians.phone')
            ->select(
                'invoices.id',
                'invoices.title',
                'invoices.amount',
                'invoices.due_date',
                'students.first_name as student_first_name',
                'students.last_name as student_last_name',
                'guardians.phone as guardian_phone'
            )
            ->get();

        $count = 0;
        foreach ($unpaidInvoices as $inv) {
            $studentName = "{$inv->student_first_name} {$inv->student_last_name}";
            $dueDate = $inv->due_date ? date('d/m/Y', strtotime($inv->due_date)) : 'Fin du mois en cours';
            $title = $inv->title ?? 'Frais de scolarité / Inscription';

            $this->line("Envoi du rappel à {$inv->guardian_phone} pour {$studentName} ({$inv->amount} FCFA)...");

            $whatsapp->sendPaymentReminder(
                parentPhone: $inv->guardian_phone,
                studentName: $studentName,
                invoiceTitle: $title,
                amountDue: $inv->amount,
                dueDate: $dueDate
            );

            $count++;
            usleep(100000); // 100ms de pause
        }

        $this->info("✅ {$count} rappels de paiement envoyés avec succès.");
        return Command::SUCCESS;
    }
}
