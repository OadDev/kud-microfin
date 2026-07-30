<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['welcome_letter', 'sanction_letter']);
            $table->timestamp('generated_at')->nullable();
            $table->string('signed_file_path')->nullable();
            $table->date('signed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['loan_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
