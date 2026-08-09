<?php

namespace App\Services;

use App\Models\PaymentSetting;

/**
 * Shared math for "buy this on EMI" quotes -- used by the Admin EMI
 * Calculator, product listing/detail "EMI from" badges, and the real
 * product-financing checkout, so the numbers always agree everywhere.
 *
 * Interest is Admin's annual rate (PaymentSetting::product_emi_interest_rate),
 * prorated by the chosen tenure: a 12% p.a. rate charges 12% of the loan
 * amount on a 12-month plan, 6% on a 6-month plan, and so on -- a longer
 * tenure costs more total interest, matching how real EMI financing works.
 */
class EmiQuoteService
{
    public static function quote(float $devicePrice, float $downPayment, float $processingFee, int $numInstallments): array
    {
        $loanAmount = max(0, $devicePrice - $downPayment);
        $rate = (float) PaymentSetting::current()->product_emi_interest_rate;
        $interest = $numInstallments > 0 ? round($loanAmount * ($rate / 100) * ($numInstallments / 12), 2) : 0;
        $totalPayable = $loanAmount + $processingFee + $interest;
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
