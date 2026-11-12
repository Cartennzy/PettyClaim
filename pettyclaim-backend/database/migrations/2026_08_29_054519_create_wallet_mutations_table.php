<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_mutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->uuid('claim_id')->nullable();
            $table->enum('type', ['IN', 'OUT']); // IN = Pencairan Klaim / Topup, OUT = Penarikan (Withdraw)
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('channel')->nullable(); // BCA, MANDIRI, DANA, dll.
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();

            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_mutations');
    }
};