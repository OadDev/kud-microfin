<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            // Annual rate, prorated by the chosen tenure -- e.g. an 18% p.a.
            // rate charges ~9% of the loan amount on a 6-month plan and ~18%
            // on a 12-month plan. See EmiQuoteService.
            $table->decimal('product_emi_interest_rate', 5, 2)->default(0)->after('foreclosure_interest_rate');
        });
    }

    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->dropColumn('product_emi_interest_rate');
        });
    }
};
