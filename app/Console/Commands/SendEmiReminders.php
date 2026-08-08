<?php

namespace App\Console\Commands;

use App\Models\Emi;
use App\Models\NotificationSetting;
use App\Services\CustomerNotifier;
use Illuminate\Console\Command;

/**
 * Runs daily (see routes/console.php). Reminds a customer once per EMI,
 * N days before it's due (Admin-configurable, see Notification Settings).
 * reminder_sent_at is the de-dupe guard -- once set, that EMI is never
 * reminded again regardless of how many times this command runs.
 */
class SendEmiReminders extends Command
{
    protected $signature = 'emi:send-reminders';

    protected $description = 'Send an EMI-due reminder (email + push) to customers whose EMI is due soon';

    public function handle(): int
    {
        $daysBefore = NotificationSetting::current()->emi_reminder_days_before;
        $targetDate = now()->addDays($daysBefore)->toDateString();

        $emis = Emi::with('loan.customer')
            ->whereNull('reminder_sent_at')
            ->whereIn('status', ['pending'])
            ->whereDate('due_date', $targetDate)
            ->whereHas('loan', fn ($q) => $q->whereIn('status', ['active', 'overdue']))
            ->get();

        $sent = 0;
        foreach ($emis as $emi) {
            $customer = $emi->loan->customer;
            if (! $customer) {
                continue;
            }

            CustomerNotifier::send('emi_reminder', $customer, [
                'emi_number' => $emi->emi_number,
                'due_date' => $emi->due_date->format('d/m/Y'),
                'amount' => number_format((float) $emi->amount, 2),
            ]);

            $emi->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} EMI reminder(s) for EMIs due on {$targetDate}.");

        return self::SUCCESS;
    }
}
