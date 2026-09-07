<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * One-time production cleanup: removes every Customer and Shop Owner
     * account (and everything tied to them -- loans, EMIs, payment
     * history, orders, uploaded KYC/documents) created while building and
     * testing the app. Keeps only Admin accounts, the Play Store/App Store
     * reviewer account, and app configuration (products, banners, payment
     * settings). Deletes in FK dependency order rather than disabling
     * constraint checks, so a genuine schema problem still surfaces as an
     * error instead of leaving orphaned rows behind.
     */
    public function up(): void
    {
        $reviewerMobile = '7000070001';

        $customerIds = DB::table('customers')
            ->join('users', 'users.id', '=', 'customers.user_id')
            ->where('users.mobile', '!=', $reviewerMobile)
            ->pluck('customers.id');

        $loanIds = DB::table('loans')->whereIn('customer_id', $customerIds)->pluck('id');

        // Collect uploaded files before the rows pointing to them are gone.
        $filesToDelete = [];

        foreach (DB::table('customers')->whereIn('id', $customerIds)->get(['photo_path', 'aadhaar_doc_path', 'pan_doc_path']) as $c) {
            foreach ([$c->photo_path, $c->aadhaar_doc_path, $c->pan_doc_path] as $path) {
                if ($path) {
                    $filesToDelete[] = $path;
                }
            }
        }

        foreach (DB::table('documents')->whereIn('loan_id', $loanIds)->get(['signed_file_path']) as $d) {
            if ($d->signed_file_path) {
                $filesToDelete[] = $d->signed_file_path;
            }
        }

        foreach (DB::table('payment_submissions')->whereIn('customer_id', $customerIds)->get(['screenshot_path']) as $p) {
            if ($p->screenshot_path) {
                $filesToDelete[] = $p->screenshot_path;
            }
        }

        // Delete RESTRICT-constrained children first (payment_submissions
        // references loans/emis/customers without cascade; orders
        // references customers without cascade).
        DB::table('payment_submissions')->whereIn('customer_id', $customerIds)->delete();
        DB::table('orders')->whereIn('customer_id', $customerIds)->delete();

        // Deleting the user cascades: customers -> loans -> emis/documents,
        // customers -> cart_items, customers -> favourites (all
        // cascadeOnDelete); customers -> notification_logs (nullOnDelete).
        DB::table('users')->where('role', 'customer')->where('mobile', '!=', $reviewerMobile)->delete();

        // Deleting the user cascades to the shop_owners row. Safe now --
        // every loan referencing a shop_owner_id was already removed above.
        DB::table('users')->where('role', 'shop_owner')->delete();

        foreach (array_unique($filesToDelete) as $path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function down(): void
    {
        // Deliberately irreversible -- this is a one-time data cleanup,
        // not a schema change.
    }
};
