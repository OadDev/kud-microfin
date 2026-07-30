<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OtpCode extends Model
{
    protected $fillable = [
        'mobile',
        'code',
        'expires_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    /**
     * Generate and store a fresh OTP for a mobile number. Real SMS delivery
     * is not wired up yet (per product decision) — the code is returned so
     * the caller can display/log it for the demo instead of texting it.
     */
    public static function generateFor(string $mobile): self
    {
        return DB::transaction(function () use ($mobile) {
            static::where('mobile', $mobile)->whereNull('consumed_at')->update(['consumed_at' => now()]);

            return static::create([
                'mobile' => $mobile,
                'code' => (string) random_int(100000, 999999),
                'expires_at' => now()->addMinutes(10),
            ]);
        });
    }

    public static function verify(string $mobile, string $code): bool
    {
        $otp = static::where('mobile', $mobile)
            ->where('code', $code)
            ->whereNull('consumed_at')
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (! $otp) {
            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }
}
