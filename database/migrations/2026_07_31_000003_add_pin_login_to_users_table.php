<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin_hash')->nullable()->after('password');
            $table->timestamp('pin_enabled_at')->nullable()->after('pin_hash');
            // Identifies "this browser belongs to this user" for the quick
            // PIN/biometric login screen, before they've authenticated.
            // Single active device at a time -- setting up quick login on a
            // new device replaces the previous one's token.
            $table->string('quick_login_token_hash')->nullable()->after('pin_enabled_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin_hash', 'pin_enabled_at', 'quick_login_token_hash']);
        });
    }
};
