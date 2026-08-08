<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Quick PIN login was previously (incorrectly) offered to every role --
 * this clears any Admin/Shop Owner accounts that already went through that
 * setup in production before it was restricted to Customers only.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', '!=', 'customer')
            ->update([
                'pin_hash' => null,
                'pin_enabled_at' => null,
                'quick_login_token_hash' => null,
            ]);
    }

    public function down(): void
    {
        // Not reversible -- the original PIN values are gone.
    }
};
