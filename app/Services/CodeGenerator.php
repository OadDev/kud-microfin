<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Order;
use App\Models\ShopOwner;
use Illuminate\Support\Facades\DB;

/**
 * Generates the human-readable sequential codes shown throughout the app
 * (shop owner IDs, customer IDs, loan account numbers).
 */
class CodeGenerator
{
    public static function nextShopOwnerCode(): string
    {
        return DB::transaction(function () {
            $count = ShopOwner::query()->lockForUpdate()->count();

            return 'SHP'.str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
        });
    }

    public static function nextCustomerCode(): string
    {
        return DB::transaction(function () {
            $count = Customer::query()->lockForUpdate()->count();

            return 'CUS'.str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
        });
    }

    public static function nextLoanAccountNo(): string
    {
        return DB::transaction(function () {
            $count = Loan::query()->lockForUpdate()->count();
            $year = now()->year;

            return "BPF{$year}".str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
        });
    }

    public static function nextOrderNo(): string
    {
        return DB::transaction(function () {
            $count = Order::query()->lockForUpdate()->count();
            $year = now()->year;

            return "ORD{$year}".str_pad((string) ($count + 1), 6, '0', STR_PAD_LEFT);
        });
    }
}
