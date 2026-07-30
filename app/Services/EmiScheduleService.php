<?php

namespace App\Services;

use App\Models\Emi;
use App\Models\Loan;

class EmiScheduleService
{
    /**
     * Build the EMI rows for a loan (weekly +7 days / monthly +1 month from
     * the first due date) and persist them.
     */
    public static function generate(Loan $loan): void
    {
        for ($number = 1; $number <= $loan->num_emis; $number++) {
            $dueDate = $loan->frequency === 'Weekly'
                ? $loan->first_due_date->copy()->addDays(7 * ($number - 1))
                : $loan->first_due_date->copy()->addMonthsNoOverflow($number - 1);

            Emi::create([
                'loan_id' => $loan->id,
                'emi_number' => $number,
                'due_date' => $dueDate,
                'amount' => $loan->emi_amount,
                'status' => 'pending',
            ]);
        }
    }
}
