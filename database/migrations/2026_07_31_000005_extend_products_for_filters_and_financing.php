<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('category_id');
            $table->string('storage')->nullable()->after('description');
            $table->string('ram')->nullable()->after('storage');
            $table->enum('network_type', ['3G', '4G', '5G', '4G_5G'])->nullable()->after('ram');
            $table->decimal('down_payment', 12, 2)->nullable()->after('price');
            $table->string('video_url')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand', 'storage', 'ram', 'network_type', 'down_payment', 'video_url']);
        });
    }
};
