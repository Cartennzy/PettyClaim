<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('claim_id')->constrained('claims')->onDelete('cascade');
            $table->string('journal_number')->unique();
            $table->string('account_debit')->default('1101-Beban Operasional');
            $table->string('account_credit')->default('1001-Kas Kecil');
            $table->decimal('amount', 15, 2);
            $table->date('entry_date');
            $table->boolean('is_exported')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};