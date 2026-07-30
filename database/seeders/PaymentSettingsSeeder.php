<?php

namespace Database\Seeders;

use App\Models\PaymentSetting;
use Illuminate\Database\Seeder;

/**
 * Creates the single payment_settings row every environment needs (Admin
 * fills in the real UPI/bank details afterwards) — used both by local
 * `db:seed` and by the production installer wizard.
 */
class PaymentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (PaymentSetting::query()->exists()) {
            return;
        }

        PaymentSetting::create([
            'upi_id' => 'bluepeakfintech@okicici',
            'upi_holder' => 'BluePeak Fintech Pvt Ltd',
            'bank_name' => 'HDFC Bank',
            'bank_holder' => 'BluePeak Fintech Private Limited',
            'account_no' => '50200012345678',
            'ifsc' => 'HDFC0001234',
            'branch' => 'Andheri West, Mumbai',
            'instructions' => 'Please pay the exact EMI amount using the UPI ID or Bank details shown below. After payment, upload a clear screenshot along with the transaction/UTR number. Your EMI will be marked as paid only after Admin verification, which is usually completed within 24 hours.',
        ]);
    }
}
