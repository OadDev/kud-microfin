<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('customer_code')->unique();
            $table->string('father_name')->nullable();
            $table->string('alt_mobile', 15)->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin', 6)->nullable();
            $table->string('pan', 10)->nullable();
            $table->string('aadhaar', 12)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('aadhaar_doc_path')->nullable();
            $table->string('pan_doc_path')->nullable();
            $table->foreignId('shop_owner_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
