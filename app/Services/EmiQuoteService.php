<?php

namespace App\Services;

/**
 * Shared math for "buy this on EMI" quotes -- used by the Admin EMI
 * Calculator, product listing/detail "EMI from" badges, the real
 * product-financing checkout, and the staff-facing Create Customer & Loan
 * form, so the numbers always agree everywhere.
 *
 * Interest is a flat monthly rate, charged on the loan amount plus the
 * processing fee -- the fee is financed alongside the loan, not paid
 * separately:
 *   Loan + Fee       = Loan Amount + Processing Fee
 *   Monthly Interest = (Loan + Fee) x (Rate / 100)
 *   Total Interest   = Monthly Interest x Number of Installments
 *   Total Repayable  = Loan + Fee + Total Interest
 *   EMI              = Total Repayable / Number of Installments, rounded to
 *                       the nearest whole rupee (round-half-up).
 */
class EmiQuoteService
{
    public static function quote(float $devicePrice, float $downPayment, float $processingFee, int $numInstallments, float $interestRate): array
    {
        $loanAmount = max(0, $devicePrice - $downPayment);
        $rate = $interestRate;
        $base = $loanAmount + $processingFee;
        $monthlyInterest = $base * ($rate / 100);
        $interest = $numInstallments > 0 ? round($monthlyInterest * $numInstallments, 2) : 0;
        $totalPayable = $base + $interest;
        $installment = $numInstallments > 0 ? round($totalPayable / $numInstallments) : 0;

        return [
            'device_price' => $devicePrice,
            'down_payment' => $downPayment,
            'loan_amount' => $loanAmount,
            'interest_rate' => $rate,
            'interest' => $interest,
            'processing_fee' => $processingFee,
            'num_installments' => $numInstallments,
            'total_payable' => $totalPayable,
            'installment' => $installment,
        ];
    }
}
