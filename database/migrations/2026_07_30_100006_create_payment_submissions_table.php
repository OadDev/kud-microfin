<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('loan_id')->constrained();
            $table->foreignId('emi_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->decimal('paid_amount', 12, 2);
            $table->enum('method', ['UPI', 'Bank Transfer']);
            $table->string('txn_reference');
            $table->string('screenshot_path');
            $table->text('remarks')->nullable();
            $table->enum('status', ['under_verification', 'approved', 'rejected'])->default('under_verification');
            $table->text('reject_reason')->nullable();
            $table->timestamp('submitted_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
    }
};
