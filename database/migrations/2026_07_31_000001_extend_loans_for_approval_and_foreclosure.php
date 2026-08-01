<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen the status enum via raw SQL (avoids needing doctrine/dbal
        // just to modify an enum column).
        DB::statement("ALTER TABLE loans MODIFY status ENUM('pending','active','overdue','closed','rejected','foreclosed') NOT NULL DEFAULT 'pending'");

        Schema::table('loans', function (Blueprint $table) {
            $table->enum('loan_type', ['cash', 'product'])->default('cash')->after('status');
            $table->foreignId('order_id')->nullable()->after('loan_type')->constrained()->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('order_id')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('reject_reason')->nullable()->after('approved_at');
            $table->timestamp('foreclosed_at')->nullable()->after('reject_reason');
            $table->decimal('foreclosure_amount', 12, 2)->nullable()->after('foreclosed_at');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['loan_type', 'approved_at', 'reject_reason', 'foreclosed_at', 'foreclosure_amount']);
        });

        DB::statement("ALTER TABLE loans MODIFY status ENUM('active','overdue','closed') NOT NULL DEFAULT 'active'");
    }
};
