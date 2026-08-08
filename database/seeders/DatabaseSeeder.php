<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Local development seed data. Production deployments do NOT use this —
 * they go through the /install wizard instead, which creates a real Admin
 * account and optionally loads DemoDataSeeder for sample records.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@bluepeakfintech.com',
            'mobile' => '9999900000',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $this->call([
            PaymentSettingsSeeder::class,
            NotificationTemplatesSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
