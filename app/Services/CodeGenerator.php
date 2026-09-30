<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Order;
use App\Models\ShopOwner;
use Illuminate\Database\Eloquent\Builder;
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
            $next = self::maxSuffix(ShopOwner::query()->lockForUpdate(), 'shop_owner_code', 3) + 1;

            return 'SHP'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function nextCustomerCode(): string
    {
        return DB::transaction(function () {
            $next = self::maxSuffix(Customer::query()->lockForUpdate(), 'customer_code', 3) + 1;

            return 'CUS'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function nextLoanAccountNo(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "BPF{$year}";
            $next = self::maxSuffix(Loan::query()->lockForUpdate()->where('loan_account_no', 'like', "{$prefix}%"), 'loan_account_no', strlen($prefix)) + 1;

            return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function nextOrderNo(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $prefix = "ORD{$year}";
            $next = self::maxSuffix(Order::query()->lockForUpdate()->where('order_no', 'like', "{$prefix}%"), 'order_no', strlen($prefix)) + 1;

            return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * The highest existing numeric suffix on a code column (0 if there are
     * none). Codes are generated from this rather than a row count, since a
     * count silently collides the moment any row has ever been deleted out
     * of sequence -- e.g. the one-time production cleanup that removed test
     * customers but kept a higher-numbered reviewer account: with a
     * count-based scheme, every subsequent "Create Customer" submission
     * reused that reviewer's own code and failed on the table's unique
     * constraint.
     */
    private static function maxSuffix(Builder $query, string $column, int $prefixLength): int
    {
        return $query->get([$column])
            ->map(fn ($row) => (int) substr($row->{$column}, $prefixLength))
            ->max() ?? 0;
    }
}
