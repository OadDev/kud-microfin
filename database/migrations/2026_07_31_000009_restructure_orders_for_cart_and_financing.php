<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        // Orders switch from a single product_id/quantity/unit_price to
        // multiple order_items (cart checkout). No real customer orders
        // exist yet, so this is a straight structural change rather than a
        // data migration.
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'quantity', 'unit_price']);

            $table->foreignId('loan_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->decimal('down_payment_amount', 12, 2)->nullable()->after('total_amount');
        });

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod','razorpay','emi_financing') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loan_id');
            $table->dropColumn('down_payment_amount');
            $table->foreignId('product_id')->after('customer_id')->constrained();
            $table->unsignedInteger('quantity')->after('product_id');
            $table->decimal('unit_price', 12, 2)->after('quantity');
        });

        DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod','razorpay') NOT NULL");

        Schema::dropIfExists('order_items');
    }
};
