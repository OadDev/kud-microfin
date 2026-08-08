<?php

use App\Models\Customer;
use App\Models\User;
use App\Services\CodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one real, always-known customer login for testing the live app
 * (mobile app included) against production -- distinct from demoLogin(),
 * which only works in local/dev. Idempotent on mobile number so re-running
 * `migrate` never resets the password if it's since been changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        $user = User::firstOrCreate(
            ['mobile' => '9999900001'],
            [
                'name' => 'Demo Customer',
                'email' => 'demo.customer@bluepeakfintech.com',
                'password' => Hash::make('Demo@1234'),
                'role' => 'customer',
                'status' => 'approved',
            ]
        );

        if (! $user->customer) {
            Customer::create([
                'user_id' => $user->id,
                'customer_code' => CodeGenerator::nextCustomerCode(),
                'city' => 'Pune',
                'state' => 'Maharashtra',
            ]);
        }
    }

    public function down(): void
    {
        $user = User::where('mobile', '9999900001')->where('role', 'customer')->first();
        $user?->customer?->delete();
        $user?->delete();
    }
};
