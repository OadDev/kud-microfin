<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emis', function (Blueprint $table) {
            // Tracks cumulative amount paid so far -- lets an EMI be paid off
            // in more than one cash instalment (e.g. 1500 now, 500 later)
            // without changing what "status" means: an EMI only flips to
            // 'paid' once amount_paid reaches amount, same as before. Until
            // then it's still 'pending' and its displayed status (Upcoming/
            // Due Today/Overdue) still comes from the due date exactly as
            // it always has -- a partially-paid-but-overdue EMI still shows
            // as Overdue, correctly, since money is still owed.
            $table->decimal('amount_paid', 12, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('emis', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};
