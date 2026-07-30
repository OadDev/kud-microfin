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
    ];

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
