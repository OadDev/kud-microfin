<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_owner_id')->constrained();
            $table->string('loan_account_no')->unique();
            $table->string('purpose');
            $table->decimal('principal', 12, 2);
            $table->decimal('interest', 12, 2)->default(0);
            $table->decimal('processing_fee', 12, 2)->default(0);
            $table->decimal('total_payable', 12, 2);
            $table->unsignedInteger('num_emis');
            $table->decimal('emi_amount', 12, 2);
            $table->enum('frequency', ['Weekly', 'Monthly'])->default('Monthly');
            $table->decimal('late_fee', 10, 2)->default(200);
            $table->date('start_date');
            $table->date('first_due_date');
            $table->enum('status', ['active', 'overdue', 'closed'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
