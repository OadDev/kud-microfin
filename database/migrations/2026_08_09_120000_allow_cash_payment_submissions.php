<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cash payments (admin marking an EMI paid directly, e.g. a customer
        // who paid in person rather than uploading a screenshot) have no
        // transaction reference or screenshot to attach.
        DB::statement("ALTER TABLE payment_submissions MODIFY method ENUM('UPI', 'Bank Transfer', 'Cash') NOT NULL");
        DB::statement('ALTER TABLE payment_submissions MODIFY txn_reference VARCHAR(255) NULL');
        DB::statement('ALTER TABLE payment_submissions MODIFY screenshot_path VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payment_submissions MODIFY txn_reference VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE payment_submissions MODIFY screenshot_path VARCHAR(255) NOT NULL');
        DB::statement("ALTER TABLE payment_submissions MODIFY method ENUM('UPI', 'Bank Transfer') NOT NULL");
    }
};
