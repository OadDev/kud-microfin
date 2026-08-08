<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * webauthn_credentials was early scaffolding for biometric login that was
 * never wired up to any controller/route -- superseded by laravel/passkeys'
 * own `passkeys` table (see the vendor:publish'd passkeys migration).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('webauthn_credentials');
    }

    public function down(): void
    {
        Schema::create('webauthn_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('credential_id')->unique();
            $table->text('public_key');
            $table->unsignedBigInteger('sign_count')->default(0);
            $table->string('name')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }
};
