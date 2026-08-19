<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            // Flat rupee fee added to the loan amount before interest is
            // calculated on Shop EMI financing -- see EmiQuoteService.
            $table->decimal('product_emi_processing_fee', 10, 2)->default(0)->after('product_emi_interest_rate');
        });
    }

    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $table->dropColumn('product_emi_processing_fee');
        });
    }
};
