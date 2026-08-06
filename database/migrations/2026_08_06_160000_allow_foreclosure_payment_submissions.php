<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE payment_submissions MODIFY emi_id BIGINT UNSIGNED NULL');

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->boolean('is_foreclosure')->default(false)->after('emi_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->dropColumn('is_foreclosure');
        });

        DB::statement('ALTER TABLE payment_submissions MODIFY emi_id BIGINT UNSIGNED NOT NULL');
    }
};
