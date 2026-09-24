<?php

use App\Console\Commands\SendWhatsAppPaymentReminders;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Task Scheduler
|--------------------------------------------------------------------------
*/

// Planification automatique des rappels d'impayés de scolarité
Schedule::command(SendWhatsAppPaymentReminders::class)
    ->weeklyOn(1, '08:00')
    ->name('whatsapp-weekly-payment-reminders')
    ->withoutOverlapping();

// Planification automatique de fin de mois (le 28 du mois à 09h00)
Schedule::command(SendWhatsAppPaymentReminders::class, ['--days=2'])
    ->monthlyOn(28, '09:00')
    ->name('whatsapp-monthly-end-reminders')
    ->withoutOverlapping();
