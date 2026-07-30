<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\PaymentSubmission;
use App\Models\Product;
use App\Models\ShopOwner;
use App\Models\User;
use App\Services\CodeGenerator;
use App\Services\EmiScheduleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sample Shop Owners, Customers, Loans and payments for demoing/testing the
 * app. Deliberately does NOT create an Admin account — production installs
 * always create their own Admin via the setup wizard instead of shipping a
 * known default password.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $shopOwnerSeed = [
            ['name' => 'Amit Sharma', 'shop' => 'Sharma Finance Point', 'mobile' => '9876511223', 'city' => 'Pune', 'status' => 'approved', 'reg_type' => 'self_registered'],
            ['name' => 'Priya Verma', 'shop' => 'Verma Credit Solutions', 'mobile' => '9812345670', 'city' => 'Nagpur', 'status' => 'pending', 'reg_type' => 'self_registered'],
            ['name' => 'Ravi Patel', 'shop' => 'Patel Micro Loans', 'mobile' => '9898765432', 'city' => 'Surat', 'status' => 'approved', 'reg_type' => 'admin_created'],
            ['name' => 'Suresh Nair', 'shop' => 'Nair Finserve', 'mobile' => '9845098450', 'city' => 'Kochi', 'status' => 'rejected', 'reg_type' => 'self_registered'],
            ['name' => 'Kavita Rao', 'shop' => 'Rao Money Mart', 'mobile' => '9900112233', 'city' => 'Chennai', 'status' => 'suspended', 'reg_type' => 'admin_created'],
        ];

        $shopOwners = [];
        foreach ($shopOwnerSeed as $s) {
            $user = User::create([
                'name' => $s['name'],
                'email' => strtolower(str_replace(' ', '.', $s['name'])).'@example.com',
                'mobile' => $s['mobile'],
                'password' => Hash::make('password'),
                'role' => 'shop_owner',
                'status' => $s['status'],
            ]);

            $shopOwners[] = ShopOwner::create([
                'user_id' => $user->id,
                'shop_owner_code' => CodeGenerator::nextShopOwnerCode(),
                'shop_name' => $s['shop'],
                'pan' => strtoupper(substr($s['name'], 0, 5)).'1234K',
                'aadhaar' => (string) random_int(200000000000, 999999999999),
                'address' => '12 MG Road',
                'city' => $s['city'],
                'reg_type' => $s['reg_type'],
            ]);
        }

        $shopOwnerA = $shopOwners[0]; // Amit Sharma (approved)
        $shopOwnerB = $shopOwners[2]; // Ravi Patel (approved)

        $customerSeed = [
            [
                'name' => 'Rajesh Kumar', 'father' => 'Ram Kumar', 'mobile' => '9876543210', 'city' => 'Pune', 'state' => 'Maharashtra', 'pin' => '411005',
                'purpose' => 'Business Expansion', 'principal' => 50000, 'interest' => 10000, 'fee' => 1000, 'numEmis' => 10, 'frequency' => 'Monthly',
                'startMonthsAgo' => 6, 'paidCount' => 4, 'verificationIdx' => 5, 'shopOwner' => $shopOwnerA,
            ],
            [
                'name' => 'Sunita Devi', 'father' => 'Mohan Lal', 'mobile' => '9812309876', 'city' => 'Nagpur', 'state' => 'Maharashtra', 'pin' => '440001',
                'purpose' => 'Home Renovation', 'principal' => 30000, 'interest' => 6000, 'fee' => 600, 'numEmis' => 6, 'frequency' => 'Monthly',
                'startMonthsAgo' => 7, 'paidCount' => 6, 'verificationIdx' => 0, 'shopOwner' => $shopOwnerA,
            ],
            [
                'name' => 'Vikram Singh', 'father' => 'Harpal Singh', 'mobile' => '9900987654', 'city' => 'Surat', 'state' => 'Gujarat', 'pin' => '395003',
                'purpose' => 'Shop Equipment Purchase', 'principal' => 80000, 'interest' => 16000, 'fee' => 1500, 'numEmis' => 12, 'frequency' => 'Monthly',
                'startMonthsAgo' => 6, 'paidCount' => 2, 'verificationIdx' => 0, 'shopOwner' => $shopOwnerB,
            ],
            [
                'name' => 'Meena Kumari', 'father' => 'Suresh Chand', 'mobile' => '9765432109', 'city' => 'Pune', 'state' => 'Maharashtra', 'pin' => '411002',
                'purpose' => 'Medical Emergency', 'principal' => 20000, 'interest' => 4000, 'fee' => 500, 'numEmis' => 8, 'frequency' => 'Weekly',
                'startWeeksAgo' => 8, 'paidCount' => 1, 'verificationIdx' => 0, 'shopOwner' => $shopOwnerA,
            ],
            [
                'name' => 'Anil Yadav', 'father' => 'Ramesh Yadav', 'mobile' => '9723456781', 'city' => 'Surat', 'state' => 'Gujarat', 'pin' => '395007',
                'purpose' => 'Working Capital', 'principal' => 60000, 'interest' => 12000, 'fee' => 1200, 'numEmis' => 10, 'frequency' => 'Monthly',
                'startMonthsAgo' => 10, 'paidCount' => 10, 'verificationIdx' => 0, 'shopOwner' => $shopOwnerB,
            ],
        ];

        foreach ($customerSeed as $c) {
            $user = User::create([
                'name' => $c['name'],
                'email' => strtolower(str_replace(' ', '.', $c['name'])).'@example.com',
                'mobile' => $c['mobile'],
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'approved',
            ]);

            $customer = Customer::create([
                'user_id' => $user->id,
                'customer_code' => CodeGenerator::nextCustomerCode(),
                'father_name' => $c['father'],
                'dob' => now()->subYears(random_int(25, 45))->format('Y-m-d'),
                'gender' => 'Male',
                'address' => '12 Station Road',
                'city' => $c['city'],
                'state' => $c['state'],
                'pin' => $c['pin'],
                'pan' => strtoupper(substr($c['name'], 0, 5)).'4567Q',
                'aadhaar' => (string) random_int(200000000000, 999999999999),
                'shop_owner_id' => $c['shopOwner']->id,
            ]);

            $startDate = isset($c['startWeeksAgo'])
                ? now()->subWeeks($c['startWeeksAgo'])
                : now()->subMonthsNoOverflow($c['startMonthsAgo']);
            $firstDue = isset($c['startWeeksAgo']) ? $startDate->copy()->addDays(7) : $startDate->copy()->addMonthsNoOverflow(1);

            $totalPayable = $c['principal'] + $c['interest'] + $c['fee'];
            $emiAmount = round($totalPayable / $c['numEmis']);

            $loan = Loan::create([
                'customer_id' => $customer->id,
                'shop_owner_id' => $c['shopOwner']->id,
                'loan_account_no' => CodeGenerator::nextLoanAccountNo(),
                'purpose' => $c['purpose'],
                'principal' => $c['principal'],
                'interest' => $c['interest'],
                'processing_fee' => $c['fee'],
                'total_payable' => $totalPayable,
                'num_emis' => $c['numEmis'],
                'emi_amount' => $emiAmount,
                'frequency' => $c['frequency'],
                'late_fee' => 200,
                'start_date' => $startDate->format('Y-m-d'),
                'first_due_date' => $firstDue->format('Y-m-d'),
                'status' => 'active',
            ]);

            EmiScheduleService::generate($loan);

            $emis = $loan->emis()->orderBy('emi_number')->get();
            foreach ($emis as $emi) {
                if ($emi->emi_number <= $c['paidCount']) {
                    $emi->update(['status' => 'paid', 'payment_date' => $emi->due_date->copy()->subDay()]);
                } elseif ($emi->emi_number === $c['verificationIdx']) {
                    $emi->update(['status' => 'under_verification']);
                    PaymentSubmission::create([
                        'reference' => 'PAY'.random_int(100000, 999999),
                        'loan_id' => $loan->id,
                        'emi_id' => $emi->id,
                        'customer_id' => $customer->id,
                        'paid_amount' => $emi->amount,
                        'method' => 'UPI',
                        'txn_reference' => 'TXN'.random_int(100000, 999999),
                        'screenshot_path' => 'demo/screenshot-placeholder.png',
                        'status' => 'under_verification',
                        'submitted_at' => now()->subDay(),
                    ]);
                }
            }

            $loan->refreshStatus();
        }

        $this->seedShopCatalog();
    }

    /**
     * A handful of categories/products/banners so the Shop section and Home
     * banners aren't empty during a demo walkthrough. No image files ship
     * with the app, so products/banners here are left without images —
     * the UI falls back to a placeholder icon.
     */
    protected function seedShopCatalog(): void
    {
        $electronics = Category::create(['name' => 'Electronics', 'is_active' => true]);
        $household = Category::create(['name' => 'Household', 'is_active' => true]);
        $mobiles = Category::create(['name' => 'Mobiles', 'is_active' => true]);

        Product::create(['category_id' => $mobiles->id, 'name' => 'Smartphone 128GB', 'description' => 'Dual SIM, 6.5" display, 5000mAh battery.', 'price' => 12999, 'stock_quantity' => 25, 'is_active' => true]);
        Product::create(['category_id' => $electronics->id, 'name' => 'LED Television 32"', 'description' => 'HD Ready Smart TV with built-in apps.', 'price' => 10999, 'stock_quantity' => 10, 'is_active' => true]);
        Product::create(['category_id' => $household->id, 'name' => 'Mixer Grinder 750W', 'description' => '3-jar mixer grinder with 2-year warranty.', 'price' => 2499, 'stock_quantity' => 40, 'is_active' => true]);
        Product::create(['category_id' => $household->id, 'name' => 'Pressure Cooker 5L', 'description' => 'Stainless steel, induction compatible.', 'price' => 1799, 'stock_quantity' => null, 'is_active' => true]);
        Product::create(['category_id' => $electronics->id, 'name' => 'Bluetooth Speaker', 'description' => '10W portable speaker, 12-hour battery.', 'price' => 1299, 'stock_quantity' => 60, 'is_active' => true]);

        Banner::create(['title' => 'Festive Offers — Up to 20% Off', 'image_path' => 'demo/banner-placeholder.png', 'sort_order' => 1, 'is_active' => true]);
        Banner::create(['title' => 'New Arrivals in Electronics', 'image_path' => 'demo/banner-placeholder.png', 'sort_order' => 2, 'is_active' => true]);
    }
}
