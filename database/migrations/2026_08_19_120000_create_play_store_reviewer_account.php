<?php

use App\Services\CodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Creates a dedicated, harmless customer login for the Google Play /
     * App Store reviewer to sign in with. Idempotent (safe to re-run) and
     * touches no real customer data.
     */
    public function up(): void
    {
        $mobile = '7000070001';

        if (DB::table('users')->where('mobile', $mobile)->exists()) {
            return;
        }

        $userId = DB::table('users')->insertGetId([
            'name' => 'Store Reviewer',
            'mobile' => $mobile,
            'password' => Hash::make('Review@2026'),
            'role' => 'customer',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('customers')->insert([
            'user_id' => $userId,
            'customer_code' => CodeGenerator::nextCustomerCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $mobile = '7000070001';
        $user = DB::table('users')->where('mobile', $mobile)->first();

        if ($user) {
            DB::table('customers')->where('user_id', $user->id)->delete();
            DB::table('users')->where('id', $user->id)->delete();
        }
    }
};
