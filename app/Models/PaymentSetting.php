<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'upi_id',
        'upi_holder',
        'qr_path',
        'bank_name',
        'bank_holder',
        'account_no',
        'ifsc',
        'branch',
        'instructions',
        'razorpay_key_id',
        'razorpay_key_secret',
        'foreclosure_interest_rate',
        'product_emi_interest_rate',
    ];

    protected function casts(): array
    {
        return [
            'foreclosure_interest_rate' => 'decimal:2',
            'product_emi_interest_rate' => 'decimal:2',
        ];
    }

    /**
     * There is only ever one settings row (Admin-configured payment details).
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function razorpayEnabled(): bool
    {
        return filled($this->razorpay_key_id) && filled($this->razorpay_key_secret);
    }
}
