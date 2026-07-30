<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentSetting;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

/**
 * Thin wrapper around the Razorpay PHP SDK. Keys come from the Admin-managed
 * PaymentSetting singleton (Admin > Payment Settings), not .env — they can
 * be changed at runtime without a redeploy.
 */
class RazorpayService
{
    protected function api(): Api
    {
        $settings = PaymentSetting::current();

        return new Api($settings->razorpay_key_id, $settings->razorpay_key_secret);
    }

    /**
     * Create a Razorpay order for the given app Order and return its
     * Razorpay order ID (amount is in paise, as Razorpay requires).
     */
    public function createOrder(Order $order): string
    {
        $razorpayOrder = $this->api()->order->create([
            'receipt' => $order->order_no,
            'amount' => (int) round(((float) $order->total_amount) * 100),
            'currency' => 'INR',
            'notes' => [
                'order_no' => $order->order_no,
                'customer_id' => $order->customer_id,
            ],
        ]);

        return $razorpayOrder['id'];
    }

    /**
     * Verify the signature Razorpay's Checkout.js returns after a successful
     * payment. Returns true only if it's authentic and untampered.
     */
    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        try {
            $this->api()->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
            ]);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }
}
