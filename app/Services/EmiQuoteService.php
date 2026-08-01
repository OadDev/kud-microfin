<?php

namespace App\Services;

/**
 * Shared math for "buy this on EMI" quotes -- used by both the Admin EMI
 * Calculator (a standalone quoting tool) and the real product-financing
 * checkout, so the numbers always agree. No interest is added here (unlike
 * cash loans): Loan Amount = Device Price - Down Payment, and the
 * Processing Fee is the only amount added on top before splitting into
 * installments.
 */
class EmiQuoteService
{
    public static function quote(float $devicePrice, float $downPayment, float $processingFee, int $numInstallments): array
    {
        $loanAmount = max(0, $devicePrice - $downPayment);
        $totalPayable = $loanAmount + $processingFee;
        $installment = $numInstallments > 0 ? round($totalPayable / $numInstallments) : 0;

        return [
            'device_price' => $devicePrice,
            'down_payment' => $downPayment,
            'loan_amount' => $loanAmount,
            'processing_fee' => $processingFee,
            'num_installments' => $numInstallments,
            'total_payable' => $totalPayable,
            'installment' => $installment,
        ];
    }
}
