<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE documents MODIFY type ENUM('welcome_letter','sanction_letter','noc') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE documents MODIFY type ENUM('welcome_letter','sanction_letter') NOT NULL");
    }
};
