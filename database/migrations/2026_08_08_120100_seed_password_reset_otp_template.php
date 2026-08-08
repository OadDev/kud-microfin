<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Inserted directly via DB rather than NotificationTemplatesSeeder::run() --
 * that seeder only ever runs once, from the /install wizard, and this app
 * was already installed in production before this template existed. A
 * migration is the only reliable way to get it there on the next deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('notification_templates')->insertOrIgnore([
            'key' => 'password_reset_otp',
            'name' => 'Password Reset OTP',
            'description' => 'Sent when a customer requests a password reset code. Placeholders: {{customer_name}}, {{otp_code}}',
            'is_active' => true,
            'email_enabled' => true,
            'email_subject' => 'Your BluePeak Fintech password reset code',
            'email_body' => "Hi {{customer_name}},\n\nUse this code to reset your password: {{otp_code}}\n\nThis code expires in 10 minutes. If you didn't request this, you can safely ignore this email.\n\n— BluePeak Fintech",
            'push_enabled' => false,
            'push_title' => null,
            'push_body' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('notification_templates')->where('key', 'password_reset_otp')->delete();
    }
};
