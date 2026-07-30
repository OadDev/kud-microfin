<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('shop_owner_code')->unique();
            $table->string('shop_name');
            $table->string('pan', 10)->nullable();
            $table->string('aadhaar', 12)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->enum('reg_type', ['admin_created', 'self_registered'])->default('admin_created');
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_owners');
    }
};
