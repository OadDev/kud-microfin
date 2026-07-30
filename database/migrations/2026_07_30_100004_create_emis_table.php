<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('emi_number');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'under_verification', 'paid'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->timestamps();

            $table->unique(['loan_id', 'emi_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emis');
    }
};
