<?php

namespace App\Services;

use App\Models\PaymentSetting;

/**
 * Shared math for "buy this on EMI" quotes -- used by the Admin EMI
 * Calculator, product listing/detail "EMI from" badges, and the real
 * product-financing checkout, so the numbers always agree everywhere.
 *
 * Interest is Admin's flat monthly rate (PaymentSetting::product_emi_interest_rate),
 * charged on the loan amount plus the processing fee (also Admin-set, see
 * PaymentSetting::product_emi_processing_fee) -- the fee is financed
 * alongside the device, not paid separately:
 *   Loan + Fee       = Loan Amount + Processing Fee
 *   Monthly Interest = (Loan + Fee) x (Rate / 100)
 *   Total Interest   = Monthly Interest x Number of Installments
 *   Total Repayable  = Loan + Fee + Total Interest
 *   EMI              = Total Repayable / Number of Installments, rounded to
 *                       the nearest whole rupee (round-half-up).
 */
class EmiQuoteService
{
    public static function quote(float $devicePrice, float $downPayment, float $processingFee, int $numInstallments): array
    {
        $loanAmount = max(0, $devicePrice - $downPayment);
        $rate = (float) PaymentSetting::current()->product_emi_interest_rate;
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
